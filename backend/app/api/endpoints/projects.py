"""
Project management endpoints
"""
from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, or_, and_
from sqlalchemy.orm import selectinload
from typing import List, Optional
import logging

from app.db.session import get_db
from app.core.security import get_current_user_id, require_editor
from app.schemas.schemas import (
    ProjectCreate,
    ProjectUpdate,
    Project,
    ProjectList,
    ProjectWithHistory,
    ProjectInDB,
    BulkActionRequest,
    BulkActionResponse,
    MessageResponse,
    ProjectStats,
)
from app.models.models import (
    Project as ProjectModel,
    User as UserModel,
    ProjectHistory,
    ProjectStatus,
    Tag,
)
from datetime import datetime

logger = logging.getLogger(__name__)

router = APIRouter()


@router.get("", response_model=ProjectList)
async def get_projects(
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    status: Optional[ProjectStatus] = None,
    province: Optional[str] = None,
    municipality: Optional[str] = None,
    district: Optional[str] = None,
    search: Optional[str] = None,
    assigned_to: Optional[int] = None,
    sort_by: str = "created_at",
    sort_desc: bool = True,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get paginated list of projects with filters"""
    # Build query
    query = select(ProjectModel)

    # Apply filters
    if status:
        query = query.where(ProjectModel.status == status)
    if province:
        query = query.where(ProjectModel.province == province)
    if municipality:
        query = query.where(ProjectModel.municipality == municipality)
    if district:
        query = query.where(ProjectModel.district == district)
    if assigned_to:
        query = query.where(ProjectModel.assigned_to == assigned_to)

    # Full-text search
    if search:
        search_pattern = f"%{search}%"
        query = query.where(
            or_(
                ProjectModel.project_name.ilike(search_pattern),
                ProjectModel.site_name.ilike(search_pattern),
                ProjectModel.site_code.ilike(search_pattern),
                ProjectModel.barangay.ilike(search_pattern)
            )
        )

    # Get total count
    count_query = select(func.count()).select_from(query.subquery())
    total_result = await db.execute(count_query)
    total = total_result.scalar()

    # Apply sorting
    sort_column = getattr(ProjectModel, sort_by, ProjectModel.created_at)
    if sort_desc:
        query = query.order_by(sort_column.desc())
    else:
        query = query.order_by(sort_column.asc())

    # Apply pagination
    query = query.offset((page - 1) * page_size).limit(page_size)

    # Eager load relationships
    query = query.options(
        selectinload(ProjectModel.creator),
        selectinload(ProjectModel.assignee),
        selectinload(ProjectModel.tags)
    )

    # Execute query
    result = await db.execute(query)
    projects = result.scalars().all()

    return {
        "total": total,
        "page": page,
        "page_size": page_size,
        "projects": [Project.model_validate(p) for p in projects]
    }


@router.get("/{project_id}", response_model=ProjectWithHistory)
async def get_project(
    project_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get a single project by ID with history"""
    # Get project with relationships
    result = await db.execute(
        select(ProjectModel)
        .options(
            selectinload(ProjectModel.creator),
            selectinload(ProjectModel.assignee),
            selectinload(ProjectModel.tags)
        )
        .where(ProjectModel.id == project_id)
    )
    project = result.scalar_one_or_none()

    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found"
        )

    # Get project history
    history_result = await db.execute(
        select(ProjectHistory)
        .where(ProjectHistory.project_id == project_id)
        .order_by(ProjectHistory.created_at.desc())
        .limit(20)
    )
    history = history_result.scalars().all()

    return ProjectWithHistory(
        **Project.model_validate(project).model_dump(),
        history=[ProjectHistoryItem.model_validate(h) for h in history]
    )


@router.post("", response_model=Project, status_code=status.HTTP_201_CREATED)
async def create_project(
    project_data: ProjectCreate,
    user_id: int = Depends(require_editor),
    db: AsyncSession = Depends(get_db)
):
    """Create a new project"""
    # Check if site code already exists
    result = await db.execute(
        select(ProjectModel).where(ProjectModel.site_code == project_data.site_code)
    )
    if result.scalar_one_or_none():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Site code already exists"
        )

    # Create project
    new_project = ProjectModel(
        **project_data.model_dump(exclude={"tags"}),
        created_by=user_id
    )

    db.add(new_project)
    await db.flush()  # Flush to get the project ID

    # Add tags if provided
    if project_data.tags:
        for tag_id in project_data.tags:
            # Verify tag exists
            tag_result = await db.execute(
                select(Tag).where(Tag.id == tag_id)
            )
            tag = tag_result.scalar_one_or_none()
            if tag:
                new_project.tags.append(tag)

    # Create history entry
    history = ProjectHistory(
        project_id=new_project.id,
        changed_by=user_id,
        new_status=new_project.status.value,
        change_reason="Project created"
    )
    db.add(history)

    await db.commit()
    await db.refresh(new_project)

    # Load relationships
    await db.refresh(new_project, ["creator", "assignee", "tags"])

    logger.info(f"Project created: {new_project.site_code} by user {user_id}")

    return Project.model_validate(new_project)


@router.put("/{project_id}", response_model=Project)
async def update_project(
    project_id: int,
    project_data: ProjectUpdate,
    user_id: int = Depends(require_editor),
    db: AsyncSession = Depends(get_db)
):
    """Update an existing project"""
    # Get project
    result = await db.execute(
        select(ProjectModel).where(ProjectModel.id == project_id)
    )
    project = result.scalar_one_or_none()

    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found"
        )

    # Track changes for history
    changed_fields = {}
    old_status = project.status
    old_assigned_to = project.assigned_to

    # Update fields
    update_data = project_data.model_dump(exclude_unset=True, exclude={"tags", "change_reason"})

    if "status" in update_data:
        changed_fields["status"] = {"old": old_status.value, "new": update_data["status"].value}

    if "assigned_to" in update_data:
        changed_fields["assigned_to"] = {"old": old_assigned_to, "new": update_data["assigned_to"]}

    for field, value in update_data.items():
        if hasattr(project, field):
            setattr(project, field, value)

    # Handle tags update
    if project_data.tags is not None:
        # Clear existing tags
        project.tags.clear()

        # Add new tags
        for tag_id in project_data.tags:
            tag_result = await db.execute(
                select(Tag).where(Tag.id == tag_id)
            )
            tag = tag_result.scalar_one_or_none()
            if tag:
                project.tags.append(tag)

    project.updated_at = datetime.utcnow()

    # Create history entry if significant changes
    if changed_fields or project_data.change_reason:
        history = ProjectHistory(
            project_id=project.id,
            old_status=old_status.value if "status" in changed_fields else None,
            new_status=project.status.value if "status" in changed_fields else None,
            old_assigned_to=old_assigned_to if "assigned_to" in changed_fields else None,
            new_assigned_to=project.assigned_to if "assigned_to" in changed_fields else None,
            changed_fields=changed_fields if changed_fields else None,
            changed_by=user_id,
            change_reason=project_data.change_reason
        )
        db.add(history)

    await db.commit()
    await db.refresh(project)

    # Load relationships
    await db.refresh(project, ["creator", "assignee", "tags"])

    logger.info(f"Project updated: {project.site_code} by user {user_id}")

    return Project.model_validate(project)


@router.delete("/{project_id}", response_model=MessageResponse)
async def delete_project(
    project_id: int,
    user_id: int = Depends(require_editor),
    db: AsyncSession = Depends(get_db)
):
    """Delete a project"""
    # Get project
    result = await db.execute(
        select(ProjectModel).where(ProjectModel.id == project_id)
    )
    project = result.scalar_one_or_none()

    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found"
        )

    site_code = project.site_code

    # Delete project (cascade will handle related records)
    await db.delete(project)
    await db.commit()

    logger.info(f"Project deleted: {site_code} by user {user_id}")

    return {"message": f"Project {site_code} deleted successfully"}


@router.get("/map/all", response_model=List[Project])
async def get_projects_for_map(
    status: Optional[ProjectStatus] = None,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get all projects for map display (no pagination)"""
    query = select(ProjectModel)

    if status:
        query = query.where(ProjectModel.status == status)

    query = query.options(
        selectinload(ProjectModel.creator),
        selectinload(ProjectModel.assignee),
        selectinload(ProjectModel.tags)
    )

    result = await db.execute(query)
    projects = result.scalars().all()

    return [Project.model_validate(p) for p in projects]


@router.post("/bulk", response_model=BulkActionResponse)
async def bulk_action(
    action_data: BulkActionRequest,
    user_id: int = Depends(require_editor),
    db: AsyncSession = Depends(get_db)
):
    """Perform bulk actions on multiple projects"""
    success_count = 0
    failed_count = 0
    errors = []

    # Get projects
    result = await db.execute(
        select(ProjectModel).where(ProjectModel.id.in_(action_data.project_ids))
    )
    projects = result.scalars().all()

    for project in projects:
        try:
            if action_data.action == "delete":
                await db.delete(project)

            elif action_data.action == "update_status":
                if action_data.data and "status" in action_data.data:
                    project.status = ProjectStatus(action_data.data["status"])
                    project.updated_at = datetime.utcnow()

                    # Create history entry
                    history = ProjectHistory(
                        project_id=project.id,
                        old_status=project.status.value,
                        new_status=action_data.data["status"],
                        changed_by=user_id,
                        change_reason="Bulk status update"
                    )
                    db.add(history)

            elif action_data.action == "assign":
                if action_data.data and "assigned_to" in action_data.data:
                    project.assigned_to = action_data.data["assigned_to"]
                    project.updated_at = datetime.utcnow()

            elif action_data.action == "add_tags":
                if action_data.data and "tag_ids" in action_data.data:
                    for tag_id in action_data.data["tag_ids"]:
                        tag_result = await db.execute(
                            select(Tag).where(Tag.id == tag_id)
                        )
                        tag = tag_result.scalar_one_or_none()
                        if tag and tag not in project.tags:
                            project.tags.append(tag)

            success_count += 1

        except Exception as e:
            failed_count += 1
            errors.append(f"Project {project.site_code}: {str(e)}")

    await db.commit()

    logger.info(f"Bulk action '{action_data.action}' completed: {success_count} success, {failed_count} failed")

    return BulkActionResponse(
        success_count=success_count,
        failed_count=failed_count,
        errors=errors
    )


@router.get("/stats/overview", response_model=ProjectStats)
async def get_project_stats(
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get project statistics"""
    # Total projects
    total_result = await db.execute(
        select(func.count()).select_from(ProjectModel)
    )
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


# Import for ProjectHistoryItem
from app.schemas.schemas import ProjectHistoryItem
