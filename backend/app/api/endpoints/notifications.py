"""
Notification endpoints
"""
from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, update
from typing import List
from datetime import datetime
import logging

from app.db.session import get_db
from app.core.security import get_current_user_id
from app.schemas.schemas import Notification, NotificationUpdate
from app.models.models import Notification as NotificationModel

logger = logging.getLogger(__name__)

router = APIRouter()


@router.get("", response_model=List[Notification])
async def get_notifications(
    unread_only: bool = Query(False),
    skip: int = Query(0, ge=0),
    limit: int = Query(50, ge=1, le=100),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get user notifications"""
    query = select(NotificationModel).where(NotificationModel.user_id == user_id)

    if unread_only:
        query = query.where(NotificationModel.is_read == False)

    query = query.order_by(NotificationModel.created_at.desc())
    query = query.offset(skip).limit(limit)

    result = await db.execute(query)
    notifications = result.scalars().all()

    return [Notification.model_validate(n) for n in notifications]


@router.get("/{notification_id}", response_model=Notification)
async def get_notification(
    notification_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get a notification by ID"""
    result = await db.execute(
        select(NotificationModel).where(
            NotificationModel.id == notification_id,
            NotificationModel.user_id == user_id
        )
    )
    notification = result.scalar_one_or_none()

    if not notification:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Notification not found"
        )

    return Notification.model_validate(notification)


@router.put("/{notification_id}", response_model=Notification)
async def update_notification(
    notification_id: int,
    notification_data: NotificationUpdate,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Update a notification (mark as read/unread)"""
    result = await db.execute(
        select(NotificationModel).where(
            NotificationModel.id == notification_id,
            NotificationModel.user_id == user_id
        )
    )
    notification = result.scalar_one_or_none()

    if not notification:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Notification not found"
        )

    notification.is_read = notification_data.is_read

    if notification_data.is_read and not notification.read_at:
        notification.read_at = datetime.utcnow()

    await db.commit()
    await db.refresh(notification)

    return Notification.model_validate(notification)


@router.post("/mark-all-read", status_code=status.HTTP_204_NO_CONTENT)
async def mark_all_as_read(
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Mark all notifications as read"""
    await db.execute(
        update(NotificationModel)
        .where(NotificationModel.user_id == user_id, NotificationModel.is_read == False)
        .values(is_read=True, read_at=datetime.utcnow())
    )
    await db.commit()

    logger.info(f"All notifications marked as read for user {user_id}")


@router.delete("/{notification_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_notification(
    notification_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Delete a notification"""
    result = await db.execute(
        select(NotificationModel).where(
            NotificationModel.id == notification_id,
            NotificationModel.user_id == user_id
        )
    )
    notification = result.scalar_one_or_none()

    if not notification:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Notification not found"
        )

    await db.delete(notification)
    await db.commit()

    logger.info(f"Notification {notification_id} deleted by user {user_id}")


@router.get("/unread/count")
async def get_unread_count(
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get count of unread notifications"""
    result = await db.execute(
        select(NotificationModel).where(
            NotificationModel.user_id == user_id,
            NotificationModel.is_read == False
        )
    )
    count = len(result.scalars().all())

    return {"count": count}
