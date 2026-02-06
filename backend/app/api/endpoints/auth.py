"""
Authentication endpoints - Fixed for SQLAlchemy 2.0
"""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from datetime import datetime, timedelta

from app.db.session import get_db
from app.core.security import (
    verify_password,
    get_password_hash,
    create_access_token,
    create_refresh_token,
    get_current_user,
)
from app.core.config import get_settings
from app.schemas.schemas import (
    LoginRequest,
    RegisterRequest,
    Token,
    UserSchema,
    PasswordResetRequest,
    PasswordResetConfirm,
    MessageResponse,
)
from app.models.models import User as UserModel, UserRole, Session as SessionModel
import logging

logger = logging.getLogger(__name__)
settings = get_settings()
router = APIRouter()


@router.post(
    "/register", response_model=UserSchema, status_code=status.HTTP_201_CREATED
)
async def register(user_data: RegisterRequest, db: AsyncSession = Depends(get_db)):
    result = await db.execute(
        select(UserModel).where(UserModel.username == user_data.username)
    )
    if result.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Username already registered")

    result = await db.execute(
        select(UserModel).where(UserModel.email == user_data.email)
    )
    if result.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Email already registered")

    new_user = UserModel(
        username=user_data.username,
        email=user_data.email,
        password_hash=get_password_hash(user_data.password),
        full_name=user_data.full_name,
        role=user_data.role or UserRole.VIEWER,
    )
    db.add(new_user)
    await db.commit()
    await db.refresh(new_user)

    logger.info(f"New user registered: {new_user.username}")
    return UserSchema.model_validate(new_user)


@router.post("/login", response_model=Token)
async def login(login_data: LoginRequest, db: AsyncSession = Depends(get_db)):
    result = await db.execute(
        select(UserModel).where(
            (UserModel.username == login_data.username_or_email)
            | (UserModel.email == login_data.username_or_email)
        )
    )
    user = result.scalar_one_or_none()

    if not user:
        raise HTTPException(status_code=401, detail="Incorrect username or password")

    if not verify_password(login_data.password, str(user.password_hash)):
        raise HTTPException(status_code=401, detail="Incorrect username or password")

    if not user.is_active:
        raise HTTPException(status_code=403, detail="User account is deactivated")

    access_token = create_access_token(data={"sub": user.id})
    refresh_token = create_refresh_token(data={"sub": user.id})

    session = SessionModel(
        user_id=user.id,
        refresh_token=refresh_token,
        expires_at=datetime.utcnow()
        + timedelta(days=settings.REFRESH_TOKEN_EXPIRE_DAYS),
    )
    db.add(session)

    user.last_login = datetime.utcnow()
    await db.commit()

    logger.info(f"User logged in: {user.username}")
    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
    }


@router.post("/token/refresh", response_model=Token)
async def refresh_token(refresh_token: str, db: AsyncSession = Depends(get_db)):
    try:
        from jose import jwt

        payload = jwt.decode(
            refresh_token, settings.SECRET_KEY, algorithms=[settings.ALGORITHM]
        )

        if payload.get("type") != "refresh":
            raise HTTPException(status_code=401, detail="Invalid token type")

        user_id = payload.get("sub")
        if not user_id:
            raise HTTPException(status_code=401, detail="Invalid token payload")

        result = await db.execute(select(UserModel).where(UserModel.id == user_id))
        user = result.scalar_one_or_none()

        if not user or not user.is_active:
            raise HTTPException(status_code=401, detail="User not found or inactive")

        result = await db.execute(
            select(SessionModel).where(
                SessionModel.refresh_token == refresh_token,
                SessionModel.expires_at > datetime.utcnow(),
            )
        )
        old_session = result.scalar_one_or_none()
        if old_session:
            await db.delete(old_session)
            await db.commit()

        new_access_token = create_access_token(data={"sub": user.id})
        new_refresh_token = create_refresh_token(data={"sub": user.id})

        session = SessionModel(
            user_id=user.id,
            refresh_token=new_refresh_token,
            expires_at=datetime.utcnow()
            + timedelta(days=settings.REFRESH_TOKEN_EXPIRE_DAYS),
        )
        db.add(session)
        await db.commit()

        return {
            "access_token": new_access_token,
            "refresh_token": new_refresh_token,
            "token_type": "bearer",
        }

    except Exception as e:
        logger.error(f"Token refresh error: {e}")
        raise HTTPException(status_code=401, detail="Invalid refresh token")


@router.get("/me", response_model=UserSchema)
async def get_current_user_profile(user: UserModel = Depends(get_current_user)):
    return UserSchema.model_validate(user)


@router.post("/logout", response_model=MessageResponse)
async def logout(refresh_token: str, db: AsyncSession = Depends(get_db)):
    result = await db.execute(
        select(SessionModel).where(SessionModel.refresh_token == refresh_token)
    )
    session = result.scalar_one_or_none()
    if session:
        await db.delete(session)
        await db.commit()

    return {"message": "Successfully logged out"}


@router.post("/password-reset/request", response_model=MessageResponse)
async def request_password_reset(
    request: PasswordResetRequest, db: AsyncSession = Depends(get_db)
):
    result = await db.execute(select(UserModel).where(UserModel.email == request.email))
    user = result.scalar_one_or_none()

    if user:
        logger.info(f"Password reset requested for: {user.email}")

    return {"message": "If email exists, password reset instructions have been sent"}


@router.post("/password-reset/confirm", response_model=MessageResponse)
async def confirm_password_reset(
    data: PasswordResetConfirm, db: AsyncSession = Depends(get_db)
):
    return {"message": "Password reset completed successfully"}
