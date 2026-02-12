"""
Comments endpoints
"""
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from typing import List
import logging

from app.db.session import get_db
from app.core.security import get_current_user_id
from app.schemas.schemas import CommentCreate, CommentUpdate, Comment
from app.models.models import Comment as CommentModel, Project
from datetime import datetime

logger = logging.getLogger(__name__)

router = APIRouter()


@router.get("/project/{project_id}", response_model=List[Comment])
async def get_project_comments(
    project_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get all comments for a project"""
    result = await db.execute(
        select(CommentModel)
        .where(CommentModel.project_id == project_id, CommentModel.parent_id.is_(None))
        .order_by(CommentModel.created_at.desc())
    )
    comments = result.scalars().all()

    return [Comment.model_validate(c) for c in comments]


@router.post("", response_model=Comment, status_code=status.HTTP_201_CREATED)
async def create_comment(
    comment_data: CommentCreate,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Create a new comment"""
    # Verify project exists
    project_result = await db.execute(
        select(Project).where(Project.id == comment_data.project_id)
    )
    if not project_result.scalar_one_or_none():
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found"
        )

    new_comment = CommentModel(
        **comment_data.model_dump(),
        user_id=user_id
    )

    db.add(new_comment)
    await db.commit()
    await db.refresh(new_comment)

    logger.info(f"Comment created for project {comment_data.project_id} by user {user_id}")

    return Comment.model_validate(new_comment)


@router.put("/{comment_id}", response_model=Comment)
async def update_comment(
    comment_id: int,
    comment_data: CommentUpdate,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Update a comment"""
    result = await db.execute(
        select(CommentModel).where(CommentModel.id == comment_id)
    )
    comment = result.scalar_one_or_none()

    if not comment:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Comment not found"
        )

    # Users can only edit their own comments
    if comment.user_id != user_id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="You can only edit your own comments"
        )

    comment.content = comment_data.content
    comment.is_edited = True
    comment.updated_at = datetime.utcnow()

    await db.commit()
    await db.refresh(comment)

    logger.info(f"Comment {comment_id} updated by user {user_id}")

    return Comment.model_validate(comment)


@router.delete("/{comment_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_comment(
    comment_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Delete a comment"""
    result = await db.execute(
        select(CommentModel).where(CommentModel.id == comment_id)
    )
    comment = result.scalar_one_or_none()

    if not comment:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Comment not found"
        )

    # Users can only delete their own comments
    if comment.user_id != user_id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="You can only delete your own comments"
        )

    await db.delete(comment)
    await db.commit()

    logger.info(f"Comment {comment_id} deleted by user {user_id}")
