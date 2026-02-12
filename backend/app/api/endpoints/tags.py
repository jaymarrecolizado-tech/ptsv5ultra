"""
Tag management endpoints
"""
from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func
from typing import List
import logging

from app.db.session import get_db
from app.core.security import get_current_user_id
from app.schemas.schemas import TagCreate, TagUpdate, Tag
from app.models.models import Tag as TagModel, User

logger = logging.getLogger(__name__)

router = APIRouter()


@router.get("", response_model=List[Tag])
async def get_tags(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get all tags"""
    result = await db.execute(
        select(TagModel)
        .offset(skip)
        .limit(limit)
        .order_by(TagModel.name)
    )
    tags = result.scalars().all()

    return [Tag.model_validate(t) for t in tags]


@router.get("/{tag_id}", response_model=Tag)
async def get_tag(
    tag_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get a tag by ID"""
    result = await db.execute(
        select(TagModel).where(TagModel.id == tag_id)
    )
    tag = result.scalar_one_or_none()

    if not tag:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Tag not found"
        )

    return Tag.model_validate(tag)


@router.post("", response_model=Tag, status_code=status.HTTP_201_CREATED)
async def create_tag(
    tag_data: TagCreate,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Create a new tag"""
    # Check if tag name already exists
    result = await db.execute(
        select(TagModel).where(TagModel.name == tag_data.name)
    )
    if result.scalar_one_or_none():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Tag name already exists"
        )

    new_tag = TagModel(
        **tag_data.model_dump(),
        created_by=user_id
    )

    db.add(new_tag)
    await db.commit()
    await db.refresh(new_tag)

    logger.info(f"Tag created: {new_tag.name} by user {user_id}")

    return Tag.model_validate(new_tag)


@router.put("/{tag_id}", response_model=Tag)
async def update_tag(
    tag_id: int,
    tag_data: TagUpdate,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Update a tag"""
    result = await db.execute(
        select(TagModel).where(TagModel.id == tag_id)
    )
    tag = result.scalar_one_or_none()

    if not tag:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Tag not found"
        )

    # Update fields
    update_data = tag_data.model_dump(exclude_unset=True)
    for field, value in update_data.items():
        setattr(tag, field, value)

    await db.commit()
    await db.refresh(tag)

    logger.info(f"Tag {tag_id} updated by user {user_id}")

    return Tag.model_validate(tag)


@router.delete("/{tag_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_tag(
    tag_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Delete a tag"""
    result = await db.execute(
        select(TagModel).where(TagModel.id == tag_id)
    )
    tag = result.scalar_one_or_none()

    if not tag:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Tag not found"
        )

    await db.delete(tag)
    await db.commit()

    logger.info(f"Tag {tag_id} deleted by user {user_id}")


@router.get("/{tag_id}/projects", response_model=List[dict])
async def get_tag_projects(
    tag_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get all projects with this tag"""
    result = await db.execute(
        select(TagModel).where(TagModel.id == tag_id)
    )
    tag = result.scalar_one_or_none()

    if not tag:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Tag not found"
        )

    # Get projects with count
    projects = [
        {
            "id": project.id,
            "site_code": project.site_code,
            "project_name": project.project_name,
            "status": project.status.value
        }
        for project in tag.projects
    ]

    return projects
