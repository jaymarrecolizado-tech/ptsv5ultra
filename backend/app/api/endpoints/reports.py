"""
Report generation endpoints
"""
from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, desc
from typing import List, Optional
import logging

from app.db.session import get_db
from app.core.security import get_current_user_id
from app.schemas.schemas import (
    SavedReportCreate,
    SavedReportUpdate,
    SavedReport,
    Project,
)
from app.models.models import (
    SavedReport as SavedReportModel,
    SavedFilter as SavedFilterModel,
    Project as ProjectModel,
    ProjectStatus,
)
from datetime import datetime, timedelta
import io
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
from fastapi.responses import StreamingResponse

logger = logging.getLogger(__name__)

router = APIRouter()


@router.get("/summary", response_model=dict)
async def get_summary_report(
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Generate summary report"""
    # Get total projects
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
        .order_by(desc(func.count()))
    )
    by_province = [{"province": prov, "count": count} for prov, count in province_result.all()]

    # Recent projects (last 7 days)
    week_ago = datetime.utcnow() - timedelta(days=7)
    recent_result = await db.execute(
        select(func.count())
        .select_from(ProjectModel)
        .where(ProjectModel.created_at >= week_ago)
    )
    recent_count = recent_result.scalar()

    return {
        "total_projects": total,
        "by_status": by_status,
        "by_province": by_province,
        "recent_projects": recent_count,
        "generated_at": datetime.utcnow().isoformat()
    }


@router.get("/province/{province}", response_model=dict)
async def get_province_report(
    province: str,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Generate province-specific report"""
    # Get projects in province
    result = await db.execute(
        select(ProjectModel).where(ProjectModel.province == province)
    )
    projects = result.scalars().all()

    # Calculate stats
    total = len(projects)
    completed = sum(1 for p in projects if p.status == ProjectStatus.DONE)
    pending = sum(1 for p in projects if p.status == ProjectStatus.PENDING)
    in_progress = sum(1 for p in projects if p.status == ProjectStatus.IN_PROGRESS)

    # By municipality
    by_municipality = {}
    for project in projects:
        mun = project.municipality
        by_municipality[mun] = by_municipality.get(mun, 0) + 1

    # By district
    by_district = {}
    for project in projects:
        dist = project.district or "Unassigned"
        by_district[dist] = by_district.get(dist, 0) + 1

    return {
        "province": province,
        "total_projects": total,
        "completed": completed,
        "pending": pending,
        "in_progress": in_progress,
        "completion_rate": round((completed / total * 100) if total > 0 else 0, 2),
        "by_municipality": by_municipality,
        "by_district": by_district,
        "generated_at": datetime.utcnow().isoformat()
    }


@router.get("/timeline", response_model=dict)
async def get_timeline_report(
    months: int = Query(12, ge=1, le=36),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Generate timeline report"""
    months_ago = datetime.utcnow() - timedelta(days=30 * months)

    # Get projects created in timeframe
    result = await db.execute(
        select(ProjectModel)
        .where(ProjectModel.created_at >= months_ago)
        .order_by(ProjectModel.created_at)
    )
    projects = result.scalars().all()

    # Group by month
    monthly_data = {}
    for project in projects:
        month_key = project.created_at.strftime("%Y-%m")
        if month_key not in monthly_data:
            monthly_data[month_key] = {
                "new": 0,
                "completed": 0,
                "pending": 0
            }

        monthly_data[month_key]["new"] += 1
        if project.status == ProjectStatus.DONE:
            monthly_data[month_key]["completed"] += 1
        elif project.status == ProjectStatus.PENDING:
            monthly_data[month_key]["pending"] += 1

    # Convert to list
    timeline = [
        {
            "month": month,
            **data
        }
        for month, data in sorted(monthly_data.items())
    ]

    return {
        "timeline": timeline,
        "months_analyzed": months,
        "total_projects": len(projects),
        "generated_at": datetime.utcnow().isoformat()
    }


@router.get("/status", response_model=dict)
async def get_status_report(
    status: Optional[ProjectStatus] = None,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Generate status analysis report"""
    query = select(ProjectModel)
    if status:
        query = query.where(ProjectModel.status == status)

    result = await db.execute(query)
    projects = result.scalars().all()

    # Group by province
    by_province = {}
    for project in projects:
        prov = project.province
        if prov not in by_province:
            by_province[prov] = {
                "total": 0,
                "completed": 0,
                "pending": 0
            }
        by_province[prov]["total"] += 1
        if project.status == ProjectStatus.DONE:
            by_province[prov]["completed"] += 1
        elif project.status == ProjectStatus.PENDING:
            by_province[prov]["pending"] += 1

    return {
        "status_filter": status.value if status else "all",
        "total_projects": len(projects),
        "by_province": by_province,
        "generated_at": datetime.utcnow().isoformat()
    }


@router.get("/export/pdf")
async def export_pdf_report(
    report_type: str = Query(..., description="summary, province, timeline, status"),
    province: Optional[str] = Query(None),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Export report as PDF"""
    # Get data based on report type
    if report_type == "summary":
        data = await get_summary_report(user_id, db)
    elif report_type == "province" and province:
        data = await get_province_report(province, user_id, db)
    elif report_type == "timeline":
        data = await get_timeline_report(12, user_id, db)
    elif report_type == "status":
        data = await get_status_report(None, user_id, db)
    else:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Invalid report type or missing required parameters"
        )

    # Generate PDF
    buffer = io.BytesIO()
    doc = SimpleDocTemplate(buffer, pagesize=letter)

    elements = []
    styles = getSampleStyleSheet()

    # Title
    title = f"Project Report - {report_type.title()}"
    if province:
        title += f" ({province})"
    elements.append(Paragraph(title, styles['Title']))
    elements.append(Spacer(1, 12))

    # Add summary stats
    if "total_projects" in data:
        elements.append(Paragraph(f"Total Projects: {data['total_projects']}", styles['Heading2']))

    # Add table data
    if "by_province" in data:
        table_data = [["Province", "Count"]]
        for item in data['by_province']:
            if isinstance(item, dict):
                table_data.append([item['province'], item['count']])
            else:
                table_data.append([item, data['by_province'][item]])

        table = Table(table_data)
        table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 14),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
            ('GRID', (0, 0), (-1, -1), 1, colors.black)
        ]))
        elements.append(table)

    # Add timestamp
    elements.append(Spacer(1, 24))
    elements.append(Paragraph(f"Generated: {data.get('generated_at', '')}", styles['Normal']))

    doc.build(elements)
    buffer.seek(0)

    return StreamingResponse(
        io.BytesIO(buffer.read()),
        media_type="application/pdf",
        headers={"Content-Disposition": f"attachment; filename={report_type}_report.pdf"}
    )


# Saved reports management
@router.get("/saved", response_model=List[SavedReport])
async def get_saved_reports(
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get user's saved reports"""
    result = await db.execute(
        select(SavedReportModel)
        .where(SavedReportModel.user_id == user_id)
        .order_by(SavedReportModel.created_at.desc())
    )
    reports = result.scalars().all()

    return [SavedReport.model_validate(r) for r in reports]


@router.post("/saved", response_model=SavedReport, status_code=status.HTTP_201_CREATED)
async def save_report(
    report_data: SavedReportCreate,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Save a report configuration"""
    new_report = SavedReportModel(
        **report_data.model_dump(),
        user_id=user_id
    )

    db.add(new_report)
    await db.commit()
    await db.refresh(new_report)

    logger.info(f"Report saved: {report_data.name} by user {user_id}")

    return SavedReport.model_validate(new_report)


@router.delete("/saved/{report_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_saved_report(
    report_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Delete a saved report"""
    result = await db.execute(
        select(SavedReportModel).where(
            SavedReportModel.id == report_id,
            SavedReportModel.user_id == user_id
        )
    )
    report = result.scalar_one_or_none()

    if not report:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Report not found"
        )

    await db.delete(report)
    await db.commit()

    logger.info(f"Saved report {report_id} deleted by user {user_id}")
