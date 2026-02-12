"""
User management endpoints
"""
from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from typing import List

from app.db.session import get_db
from app.core.security import get_current_user_id, require_admin
from app.schemas.schemas import User, UserUpdate, UserList
from app.models.models import User as UserModel, UserRole
import logging

logger = logging.getLogger(__name__)

router = APIRouter()


@router.get("", response_model=List[User])
async def get_users(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    role: UserRole = None,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get list of users"""
    query = select(UserModel)

    if role:
        query = query.where(UserModel.role == role)

    query = query.offset(skip).limit(limit)

    result = await db.execute(query)
    users = result.scalars().all()

    return [User.model_validate(u) for u in users]


@router.get("/{user_id}", response_model=User)
async def get_user(
    user_id: int,
    current_user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get a user by ID"""
    # Users can only view their own profile unless admin
    if user_id != current_user_id:
        # TODO: Check if current user is admin
        pass

    result = await db.execute(
        select(UserModel).where(UserModel.id == user_id)
    )
    user = result.scalar_one_or_none()

    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="User not found"
        )

    return User.model_validate(user)


@router.put("/{user_id}", response_model=User)
async def update_user(
    user_id: int,
    user_data: UserUpdate,
    current_user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Update a user"""
    # Users can only update their own profile unless admin
    if user_id != current_user_id:
        # TODO: Check if current user is admin
        pass

    result = await db.execute(
        select(UserModel).where(UserModel.id == user_id)
    )
    user = result.scalar_one_or_none()

    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="User not found"
        )

    # Update fields
    update_data = user_data.model_dump(exclude_unset=True)
    for field, value in update_data.items():
        setattr(user, field, value)

    await db.commit()
    await db.refresh(user)

    logger.info(f"User updated: {user.username} by user {current_user_id}")

    return User.model_validate(user)


@router.delete("/{user_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_user(
    user_id: int,
    current_user_id: int = Depends(require_admin),
    db: AsyncSession = Depends(get_db)
):
    """Delete a user (admin only)"""
    result = await db.execute(
        select(UserModel).where(UserModel.id == user_id)
    )
    user = result.scalar_one_or_none()

    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="User not found"
        )

    # Prevent deleting yourself
    if user_id == current_user_id:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Cannot delete your own account"
        )

    await db.delete(user)
    await db.commit()

    logger.info(f"User deleted: {user.username} by user {current_user_id}")
