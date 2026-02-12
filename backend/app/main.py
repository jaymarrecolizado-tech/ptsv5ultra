"""
FastAPI application entry point
"""

from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.middleware.gzip import GZipMiddleware
from fastapi.responses import JSONResponse
from fastapi.staticfiles import StaticFiles
from fastapi.exceptions import RequestValidationError
from starlette.exceptions import HTTPException as StarletteHTTPException
import logging

from app.core.config import get_settings
from app.api.endpoints import (
    auth,
    projects,
    users,
    comments,
    attachments,
    tags,
    notifications,
    reports,
    analytics,
)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

settings = get_settings()

# Create FastAPI app
app = FastAPI(
    title=settings.PROJECT_NAME,
    version=settings.VERSION,
    description="Enterprise-grade ATLAS - Advanced Tracking & Location-based Analytics System API",
    docs_url="/api/docs",
    redoc_url="/api/redoc",
    openapi_url="/api/openapi.json",
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.BACKEND_CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# GZip compression
app.add_middleware(GZipMiddleware, minimum_size=1000)


# Exception handlers
@app.exception_handler(StarletteHTTPException)
async def http_exception_handler(request: Request, exc: StarletteHTTPException):
    """Handle HTTP exceptions"""
    return JSONResponse(
        status_code=exc.status_code,
        content={"detail": exc.detail, "status_code": exc.status_code},
    )


@app.exception_handler(RequestValidationError)
async def validation_exception_handler(request: Request, exc: RequestValidationError):
    """Handle validation errors"""
    return JSONResponse(
        status_code=422, content={"detail": "Validation Error", "errors": exc.errors()}
    )


# Startup event
@app.on_event("startup")
async def startup_event():
    """Initialize database and other resources"""
    from app.db.session import init_db

    logger.info("Starting up...")
    await init_db()
    logger.info("Database initialized")


# Shutdown event
@app.on_event("shutdown")
async def shutdown_event():
    """Cleanup resources"""
    logger.info("Shutting down...")


# Health check endpoint
@app.get("/api/health")
async def health_check():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "app": settings.PROJECT_NAME,
        "version": settings.VERSION,
    }


# Root endpoint
@app.get("/")
async def root():
    """Root endpoint"""
    return {
        "message": "ATLAS API",
        "version": settings.VERSION,
        "docs": "/api/docs",
        "redoc": "/api/redoc",
    }


# Include routers
app.include_router(
    auth.router, prefix=f"{settings.API_V1_PREFIX}/auth", tags=["Authentication"]
)

app.include_router(
    projects.router, prefix=f"{settings.API_V1_PREFIX}/projects", tags=["Projects"]
)

app.include_router(
    users.router, prefix=f"{settings.API_V1_PREFIX}/users", tags=["Users"]
)

app.include_router(
    comments.router, prefix=f"{settings.API_V1_PREFIX}/comments", tags=["Comments"]
)

app.include_router(
    attachments.router,
    prefix=f"{settings.API_V1_PREFIX}/attachments",
    tags=["Attachments"],
)

app.include_router(tags.router, prefix=f"{settings.API_V1_PREFIX}/tags", tags=["Tags"])

app.include_router(
    notifications.router,
    prefix=f"{settings.API_V1_PREFIX}/notifications",
    tags=["Notifications"],
)

app.include_router(
    reports.router, prefix=f"{settings.API_V1_PREFIX}/reports", tags=["Reports"]
)

app.include_router(
    analytics.router, prefix=f"{settings.API_V1_PREFIX}/analytics", tags=["Analytics"]
)


# Mount static files (uploads)
app.mount("/uploads", StaticFiles(directory="uploads"), name="uploads")
