"""
File attachment endpoints
"""
from fastapi import APIRouter, Depends, HTTPException, status, UploadFile, File, Form
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from typing import List
import os
import uuid
from datetime import datetime

from app.db.session import get_db
from app.core.security import get_current_user_id
from app.core.config import get_settings
from app.schemas.schemas import AttachmentCreate, Attachment
from app.models.models import Attachment as AttachmentModel, Project
import logging

logger = logging.getLogger(__name__)
settings = get_settings()

router = APIRouter()


@router.get("/project/{project_id}", response_model=List[Attachment])
async def get_project_attachments(
    project_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Get all attachments for a project"""
    result = await db.execute(
        select(AttachmentModel)
        .where(AttachmentModel.project_id == project_id)
        .order_by(AttachmentModel.created_at.desc())
    )
    attachments = result.scalars().all()

    return [Attachment.model_validate(a) for a in attachments]


@router.post("/upload", response_model=Attachment, status_code=status.HTTP_201_CREATED)
async def upload_file(
    project_id: int = Form(...),
    file: UploadFile = File(...),
    description: str = Form(None),
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Upload a file attachment"""
    # Verify project exists
    project_result = await db.execute(
        select(Project).where(Project.id == project_id)
    )
    if not project_result.scalar_one_or_none():
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found"
        )

    # Validate file type
    if file.content_type not in settings.ALLOWED_FILE_TYPES:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"File type {file.content_type} not allowed"
        )

    # Read file content
    content = await file.read()
    file_size = len(content)

    # Check file size
    if file_size > settings.MAX_FILE_SIZE:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"File size exceeds maximum allowed size of {settings.MAX_FILE_SIZE} bytes"
        )

    # Generate unique filename
    file_extension = os.path.splitext(file.filename)[1]
    unique_filename = f"{uuid.uuid4()}{file_extension}"

    # Determine upload directory
    upload_dir = os.path.join(settings.UPLOAD_DIR, "projects", str(project_id))
    os.makedirs(upload_dir, exist_ok=True)

    # Save file
    file_path = os.path.join(upload_dir, unique_filename)
    with open(file_path, "wb") as f:
        f.write(content)

    # Create database record
    new_attachment = AttachmentModel(
        project_id=project_id,
        filename=unique_filename,
        original_filename=file.filename,
        file_path=file_path,
        file_size=file_size,
        file_type=file.content_type,
        description=description,
        uploaded_by=user_id
    )

    db.add(new_attachment)
    await db.commit()
    await db.refresh(new_attachment)

    logger.info(f"File uploaded to project {project_id} by user {user_id}: {file.filename}")

    return Attachment.model_validate(new_attachment)


@router.delete("/{attachment_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_attachment(
    attachment_id: int,
    user_id: int = Depends(get_current_user_id),
    db: AsyncSession = Depends(get_db)
):
    """Delete an attachment"""
    result = await db.execute(
        select(AttachmentModel).where(AttachmentModel.id == attachment_id)
    )
    attachment = result.scalar_one_or_none()

    if not attachment:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Attachment not found"
        )

    # Delete file from filesystem
    try:
        if os.path.exists(attachment.file_path):
            os.remove(attachment.file_path)
    except Exception as e:
        logger.error(f"Error deleting file: {e}")

    # Delete from database
    await db.delete(attachment)
    await db.commit()

    logger.info(f"Attachment {attachment_id} deleted by user {user_id}")
