"""
SQLAlchemy database models
"""

from sqlalchemy import (
    Column,
    Integer,
    String,
    DateTime,
    Boolean,
    Text,
    Enum as SQLEnum,
    ForeignKey,
    Numeric,
    DECIMAL,
    Date,
    JSON,
    Index,
    CheckConstraint,
)
from sqlalchemy.orm import relationship, backref
from sqlalchemy.sql import func
from app.db.session import Base
import enum


class UserRole(str, enum.Enum):
    """User roles"""

    ADMIN = "admin"
    EDITOR = "editor"
    VIEWER = "viewer"


class ProjectStatus(str, enum.Enum):
    """Project status options"""

    PLANNING = "planning"
    IN_PROGRESS = "in_progress"
    ON_HOLD = "on_hold"
    DONE = "done"
    PENDING = "pending"
    CANCELLED = "cancelled"


class NotificationType(str, enum.Enum):
    """Notification types"""

    PROJECT_ASSIGNED = "project_assigned"
    PROJECT_STATUS_CHANGED = "project_status_changed"
    COMMENT_ADDED = "comment_added"
    DEADLINE_APPROACHING = "deadline_approaching"
    PROJECT_COMPLETED = "project_completed"
    NEW_PROJECT = "new_project"


class ReportType(str, enum.Enum):
    """Report types"""

    SUMMARY = "summary"
    PROVINCE = "province"
    TIMELINE = "timeline"
    STATUS = "status"
    CUSTOM = "custom"


class User(Base):
    """User model"""

    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(50), unique=True, nullable=False, index=True)
    email = Column(String(100), unique=True, nullable=False, index=True)
    password_hash = Column(String(255), nullable=False)
    full_name = Column(String(100))
    role = Column(
        SQLEnum(UserRole), nullable=False, default=UserRole.VIEWER, index=True
    )
    avatar = Column(String(255))
    is_active = Column(Boolean, default=True)
    is_verified = Column(Boolean, default=False)
    email_verification_token = Column(String(255))
    password_reset_token = Column(String(255))
    password_reset_expires = Column(DateTime)
    last_login = Column(DateTime)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(
        DateTime(timezone=True), server_default=func.now(), onupdate=func.now()
    )

    # Relationships
    created_projects = relationship(
        "Project", foreign_keys="Project.created_by", back_populates="creator"
    )
    assigned_projects = relationship(
        "Project", foreign_keys="Project.assigned_to", back_populates="assignee"
    )
    comments = relationship("Comment", back_populates="user")
    attachments = relationship("Attachment", back_populates="uploader")
    notifications = relationship("Notification", back_populates="user")
    activity_logs = relationship("ActivityLog", back_populates="user")
    sessions = relationship("Session", back_populates="user")
    saved_filters = relationship("SavedFilter", back_populates="user")
    saved_reports = relationship("SavedReport", back_populates="user")


class Project(Base):
    """Project model"""

    __tablename__ = "projects"

    id = Column(Integer, primary_key=True, index=True)
    site_code = Column(String(50), unique=True, nullable=False, index=True)
    project_name = Column(String(200), nullable=False)
    site_name = Column(String(200), nullable=False)
    barangay = Column(String(100), nullable=False)
    municipality = Column(String(100), nullable=False, index=True)
    province = Column(String(100), nullable=False, index=True)
    district = Column(String(50))
    latitude = Column(DECIMAL(10, 6), nullable=False)
    longitude = Column(DECIMAL(10, 6), nullable=False)
    activation_date = Column(Date, nullable=False, index=True)
    completion_date = Column(Date)
    status = Column(
        SQLEnum(ProjectStatus),
        nullable=False,
        default=ProjectStatus.PLANNING,
        index=True,
    )
    notes = Column(Text)
    progress = Column(Integer, default=0)
    __table_args__ = (
        CheckConstraint("progress >= 0 AND progress <= 100"),
        Index(
            "ft_search", "project_name", "site_name", "notes", mysql_prefix="FULLTEXT"
        ),
    )


class ProjectHistory(Base):
    """Project history/audit trail model"""

    __tablename__ = "project_history"

    id = Column(Integer, primary_key=True, index=True)
    project_id = Column(
        Integer,
        ForeignKey("projects.id", ondelete="CASCADE"),
        nullable=False,
        index=True,
    )
    old_status = Column(String(20))
    new_status = Column(String(20))
    old_assigned_to = Column(Integer)
    new_assigned_to = Column(Integer)
    changed_fields = Column(JSON)
    changed_by = Column(
        Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True
    )
    change_reason = Column(Text)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), index=True)

    # Relationships
    project = relationship("Project", back_populates="history")
    changer = relationship("User")


class Attachment(Base):
    """File attachment model"""

    __tablename__ = "attachments"

    id = Column(Integer, primary_key=True, index=True)
    project_id = Column(
        Integer,
        ForeignKey("projects.id", ondelete="CASCADE"),
        nullable=False,
        index=True,
    )
    filename = Column(String(255), nullable=False)
    original_filename = Column(String(255), nullable=False)
    file_path = Column(String(500), nullable=False)
    file_size = Column(Integer, nullable=False)
    file_type = Column(String(100), nullable=False)
    uploaded_by = Column(
        Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True
    )
    description = Column(Text)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    # Relationships
    project = relationship("Project", back_populates="attachments")
    uploader = relationship("User", back_populates="attachments")


class Comment(Base):
    """Comment model"""

    __tablename__ = "comments"

    id = Column(Integer, primary_key=True, index=True)
    project_id = Column(
        Integer,
        ForeignKey("projects.id", ondelete="CASCADE"),
        nullable=False,
        index=True,
    )
    user_id = Column(
        Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True
    )
    content = Column(Text, nullable=False)
    parent_id = Column(
        Integer, ForeignKey("comments.id", ondelete="CASCADE"), index=True
    )
    mentions = Column(JSON)
    is_edited = Column(Boolean, default=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), index=True)
    updated_at = Column(
        DateTime(timezone=True), server_default=func.now(), onupdate=func.now()
    )

    # Relationships
    project = relationship("Project", back_populates="comments")
    user = relationship("User", back_populates="comments")
    parent = relationship("Comment", remote_side=[id], backref="replies")


class Tag(Base):
    """Tag model"""

    __tablename__ = "tags"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(50), unique=True, nullable=False, index=True)
    color = Column(String(7), default="#3498db")
    description = Column(Text)
    created_by = Column(
        Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False
    )
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    # Relationships
    creator = relationship("User")
    projects = relationship("Project", secondary="project_tags", back_populates="tags")


class ProjectTag(Base):
    """Many-to-many relationship between projects and tags"""

    __tablename__ = "project_tags"

    project_id = Column(
        Integer,
        ForeignKey("projects.id", ondelete="CASCADE"),
        primary_key=True,
        index=True,
    )
    tag_id = Column(
        Integer, ForeignKey("tags.id", ondelete="CASCADE"), primary_key=True, index=True
    )
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class Notification(Base):
    """Notification model"""

    __tablename__ = "notifications"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(
        Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True
    )
    type = Column(SQLEnum(NotificationType), nullable=False, index=True)
    title = Column(String(200), nullable=False)
    message = Column(Text, nullable=False)
    data = Column(JSON)
    is_read = Column(Boolean, default=False, index=True)
    read_at = Column(DateTime)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), index=True)

    # Relationships
    user = relationship("User", back_populates="notifications")


class SavedFilter(Base):
    """Saved filter presets model"""

    __tablename__ = "saved_filters"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(
        Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True
    )
    name = Column(String(100), nullable=False, index=True)
    filters = Column(JSON, nullable=False)
    is_default = Column(Boolean, default=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    # Relationships
    user = relationship("User", back_populates="saved_filters")


class SavedReport(Base):
    """Saved report configurations model"""

    __tablename__ = "saved_reports"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(
        Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True
    )
    name = Column(String(100), nullable=False)
    report_type = Column(SQLEnum(ReportType), nullable=False, index=True)
    config = Column(JSON, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    # Relationships
    user = relationship("User", back_populates="saved_reports")


class ActivityLog(Base):
    """Global activity log model"""

    __tablename__ = "activity_log"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id", ondelete="SET NULL"), index=True)
    action = Column(String(100), nullable=False)
    entity_type = Column(String(50), nullable=False)
    entity_id = Column(Integer, nullable=False)
    details = Column(JSON)
    ip_address = Column(String(45))
    user_agent = Column(Text)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), index=True)

    # Relationships
    user = relationship("User", back_populates="activity_logs")


class Session(Base):
    """User session model for refresh tokens"""

    __tablename__ = "sessions"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(
        Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True
    )
    refresh_token = Column(String(255), unique=True, nullable=False, index=True)
    device_info = Column(String(255))
    ip_address = Column(String(45))
    expires_at = Column(DateTime, nullable=False, index=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    # Relationships
    user = relationship("User", back_populates="sessions")
