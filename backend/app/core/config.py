"""
Core configuration for the application
"""

from pydantic_settings import BaseSettings
from typing import Optional
from functools import lru_cache


class Settings(BaseSettings):
    """Application settings"""

    # API
    API_V1_PREFIX: str = "/api/v1"
    PROJECT_NAME: str = "ATLAS"
    VERSION: str = "1.0.0"

    # Security
    SECRET_KEY: str = "your-secret-key-change-in-production-use-environment-variable"
    ALGORITHM: str = "HS256"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 30
    REFRESH_TOKEN_EXPIRE_DAYS: int = 7

    # Database
    DATABASE_URL: str = (
        "mysql+aiomysql://tracker_user:tracker_password@localhost:3306/project_tracker"
    )
    DB_POOL_SIZE: int = 10
    DB_MAX_OVERFLOW: int = 20

    # Redis
    REDIS_URL: str = "redis://localhost:6379/0"
    CACHE_TTL: int = 3600  # 1 hour

    # CORS
    BACKEND_CORS_ORIGINS: list[str] = ["http://localhost:3000", "http://localhost:8000"]

    # Email
    SMTP_HOST: str = "smtp.gmail.com"
    SMTP_PORT: int = 587
    SMTP_USER: Optional[str] = None
    SMTP_PASSWORD: Optional[str] = None
    SMTP_TLS: bool = True
    EMAILS_FROM_EMAIL: Optional[str] = None
    EMAILS_FROM_NAME: str = "Project Tracker"

    # File Upload
    UPLOAD_DIR: str = "./uploads"
    MAX_FILE_SIZE: int = 10 * 1024 * 1024  # 10MB
    ALLOWED_FILE_TYPES: list[str] = [
        "image/jpeg",
        "image/png",
        "image/gif",
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "text/csv",
    ]

    # Pagination
    DEFAULT_PAGE_SIZE: int = 20
    MAX_PAGE_SIZE: int = 100

    # WebSocket
    WS_MESSAGE_QUEUE_SIZE: int = 100

    class Config:
        env_file = ".env"
        case_sensitive = True


@lru_cache()
def get_settings() -> Settings:
    """Get cached settings instance"""
    return Settings()
