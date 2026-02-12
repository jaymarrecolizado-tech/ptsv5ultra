"""
Analytics endpoints
"""
from fastapi import APIRouter, Depends, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, desc
from typing import List
import logging

from app.db.session import get_db
from app.core.security import get_current_user_id
from app.schemas.schemas import (
    ProjectStats,
    HeatMapData,
    TrendData,
    ActivityFeedItem,
)
from app.models.models import (
    Project as ProjectModel,
    ActivityLog as ActivityLogModel,
    ProjectStatus,
)
from datetime import datetime, timedelta

logger = logging.getLogger(__name__)

router = APIRouter()


@router.get("/dashboard", response_model=ProjectStats)
async def get_dashboard_stats(
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get dashboard statistics"""
    # Total projects
    total_result = await db.execute(select(func.count()).select_from(ProjectModel))
    total = total_result.scalar()

    # By status
    status_result = await db.execute(
        select(ProjectModel.status, func.count())
        .group_by(ProjectModel.status)
    )
    by_status = {status.value: count for status, count in status_result.all()}

    # By province
    province_result = await db.execute(
        select(ProjectModel.province, func.count())
        .group_by(ProjectModel.province)
    )
    by_province = {province: count for province, count in province_result.all()}

    completed = by_status.get("done", 0)
    pending = by_status.get("pending", 0)
    in_progress = by_status.get("in_progress", 0)
    completion_rate = (completed / total * 100) if total > 0 else 0

    return ProjectStats(
        total=total,
        by_status=by_status,
        by_province=by_province,
        completed=completed,
        pending=pending,
        in_progress=in_progress,
        completion_rate=round(completion_rate, 2)
    )


@router.get("/heatmap", response_model=List[HeatMapData])
async def get_heatmap_data(
    status: ProjectStatus = None,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get heat map data (coordinates with counts)"""
    query = select(
        ProjectModel.latitude,
        ProjectModel.longitude,
        func.count().label('count'),
        ProjectModel.status
    ).group_by(
        func.round(ProjectModel.latitude, 2),
        func.round(ProjectModel.longitude, 2),
        ProjectModel.status
    )

    if status:
        query = query.where(ProjectModel.status == status)

    result = await db.execute(query)

    heatmap_data = [
        HeatMapData(
            lat=float(row.latitude),
            lng=float(row.longitude),
            count=row.count,
            status=row.status.value
        )
        for row in result.all()
    ]

    return heatmap_data


@router.get("/trends", response_model=List[TrendData])
async def get_trends(
    months: int = Query(12, ge=1, le=36),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get trend analysis over time"""
    months_ago = datetime.utcnow() - timedelta(days=30 * months)

    # Get projects created in timeframe
    result = await db.execute(
        select(
            func.date_trunc('month', ProjectModel.created_at).label('month'),
            func.count().label('new_projects'),
            func.sum(func.case((ProjectModel.status == ProjectStatus.DONE, 1), else_=0)).label('completed_projects')
        )
        .where(ProjectModel.created_at >= months_ago)
        .group_by(func.date_trunc('month', ProjectModel.created_at))
        .order_by('month')
    )

    cumulative_total = 0
    trends = []
    for row in result.all():
        cumulative_total += row.new_projects
        trends.append(TrendData(
            date=row.month.strftime("%Y-%m"),
            new_projects=row.new_projects or 0,
            completed_projects=row.completed_projects or 0,
            total=cumulative_total
        ))

    return trends


@router.get("/activity-feed", response_model=List[ActivityFeedItem])
async def get_activity_feed(
    limit: int = Query(50, ge=1, le=200),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get activity feed"""
    result = await db.execute(
        select(ActivityLogModel)
        .order_by(desc(ActivityLogModel.created_at))
        .limit(limit)
    )

    activities = result.scalars().all()

    return [ActivityFeedItem.model_validate(a) for a in activities]


@router.get("/province-performance", response_model=List[dict])
async def get_province_performance(
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get performance metrics by province"""
    result = await db.execute(
        select(
            ProjectModel.province,
            func.count().label('total'),
            func.sum(func.case((ProjectModel.status == ProjectStatus.DONE, 1), else_=0)).label('completed'),
            func.sum(func.case((ProjectModel.status == ProjectStatus.PENDING, 1), else_=0)).label('pending'),
            func.sum(func.case((ProjectModel.status == ProjectStatus.IN_PROGRESS, 1), else_=0)).label('in_progress')
        )
        .group_by(ProjectModel.province)
        .order_by(desc('total'))
    )

    performance = []
    for row in result.all():
        total = row.total or 0
        completed = row.completed or 0
        completion_rate = (completed / total * 100) if total > 0 else 0

        performance.append({
            "province": row.province,
            "total": total,
            "completed": completed,
            "pending": row.pending or 0,
            "in_progress": row.in_progress or 0,
            "completion_rate": round(completion_rate, 2)
        })

    return performance


@router.get("/district-performance", response_model=List[dict])
async def get_district_performance(
    province: str = Query(None, description="Filter by province"),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get performance metrics by district"""
    query = select(
        ProjectModel.district,
        func.count().label('total'),
        func.sum(func.case((ProjectModel.status == ProjectStatus.DONE, 1), else_=0)).label('completed'),
        func.sum(func.case((ProjectModel.status == ProjectStatus.PENDING, 1), else_=0)).label('pending')
    ).group_by(ProjectModel.district)

    if province:
        query = query.where(ProjectModel.province == province)

    query = query.order_by(desc('total'))

    result = await db.execute(query)

    performance = []
    for row in result.all():
        total = row.total or 0
        completed = row.completed or 0
        completion_rate = (completed / total * 100) if total > 0 else 0

        performance.append({
            "district": row.district or "Unassigned",
            "total": total,
            "completed": completed,
            "pending": row.pending or 0,
            "completion_rate": round(completion_rate, 2)
        })

    return performance


@router.get("/completion-rate")
async def get_completion_rate(
    period: str = Query("all", description="all, 7d, 30d, 90d, 1y"),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get completion rate statistics"""
    query = select(ProjectModel)

    # Filter by period
    if period == "7d":
        cutoff = datetime.utcnow() - timedelta(days=7)
        query = query.where(ProjectModel.created_at >= cutoff)
    elif period == "30d":
        cutoff = datetime.utcnow() - timedelta(days=30)
        query = query.where(ProjectModel.created_at >= cutoff)
    elif period == "90d":
        cutoff = datetime.utcnow() - timedelta(days=90)
        query = query.where(ProjectModel.created_at >= cutoff)
    elif period == "1y":
        cutoff = datetime.utcnow() - timedelta(days=365)
        query = query.where(ProjectModel.created_at >= cutoff)

    result = await db.execute(query)
    projects = result.scalars().all()

    total = len(projects)
    completed = sum(1 for p in projects if p.status == ProjectStatus.DONE)

    return {
        "period": period,
        "total_projects": total,
        "completed": completed,
        "completion_rate": round((completed / total * 100) if total > 0 else 0, 2),
        "pending": sum(1 for p in projects if p.status == ProjectStatus.PENDING),
        "in_progress": sum(1 for p in projects if p.status == ProjectStatus.IN_PROGRESS)
    }
