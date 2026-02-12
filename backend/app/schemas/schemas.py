"""
Pydantic schemas for request/response validation
"""

from pydantic import BaseModel, EmailStr, Field, validator
from typing import Optional, List, Dict, Any
from datetime import datetime
from app.models.models import UserRole, ProjectStatus, NotificationType, ReportType


# ==================== User Schemas ====================


class UserBase(BaseModel):
    """Base user schema"""

    username: str = Field(..., min_length=3, max_length=50)
    email: EmailStr
    full_name: Optional[str] = Field(None, max_length=100)
    role: UserRole = UserRole.VIEWER


class UserCreate(UserBase):
    """Schema for creating a user"""

    password: str = Field(..., min_length=8, max_length=100)

    @validator("password")
    def password_strength(cls, v):
        if len(v) < 8:
            raise ValueError("Password must be at least 8 characters")
        return v


class UserUpdate(BaseModel):
    """Schema for updating a user"""

    full_name: Optional[str] = Field(None, max_length=100)
    role: Optional[UserRole] = None
    avatar: Optional[str] = None


class UserInDB(UserBase):
    """Schema for user data in database (with ID)"""

    id: int
    is_active: bool
    is_verified: bool
    avatar: Optional[str] = None
    last_login: Optional[datetime] = None
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class User(UserInDB):
    """Schema for user response"""

    pass


class UserSchema(UserInDB):
    """Alias for User schema for compatibility"""

    pass


# ==================== Authentication Schemas ====================


class Token(BaseModel):
    """JWT Token response"""

    access_token: str
    refresh_token: str
    token_type: str = "bearer"


class TokenPayload(BaseModel):
    """JWT Token payload"""

    sub: Optional[int] = None
    exp: Optional[datetime] = None
    type: Optional[str] = None


class LoginRequest(BaseModel):
    """Login request schema"""

    username_or_email: str = Field(..., min_length=1)
    password: str


class RegisterRequest(UserCreate):
    """Registration request schema"""

    pass


class RefreshTokenRequest(BaseModel):
    """Refresh token request schema"""

    refresh_token: str


class PasswordResetRequest(BaseModel):
    """Password reset request schema"""

    email: EmailStr


class PasswordResetConfirm(BaseModel):
    """Password reset confirmation schema"""

    token: str
    new_password: str = Field(..., min_length=8, max_length=100)


# ==================== Project Schemas ====================


class ProjectBase(BaseModel):
    """Base project schema"""

    site_code: str = Field(..., min_length=1, max_length=50)
    project_name: str = Field(..., min_length=1, max_length=200)
    site_name: str = Field(..., min_length=1, max_length=200)
    barangay: str = Field(..., min_length=1, max_length=100)
    municipality: str = Field(..., min_length=1, max_length=100)
    province: str = Field(..., min_length=1, max_length=100)
    district: Optional[str] = Field(None, max_length=50)
    latitude: float = Field(..., ge=-90, le=90)
    longitude: float = Field(..., ge=-180, le=180)
    activation_date: str  # ISO format date string
    completion_date: Optional[str] = None
    status: ProjectStatus = ProjectStatus.PLANNING
    notes: Optional[str] = None
    progress: int = Field(0, ge=0, le=100)
    assigned_to: Optional[int] = None


class ProjectCreate(ProjectBase):
    """Schema for creating a project"""

    tags: Optional[List[int]] = Field([], description="List of tag IDs to attach")


class ProjectUpdate(BaseModel):
    """Schema for updating a project"""

    project_name: Optional[str] = Field(None, min_length=1, max_length=200)
    site_name: Optional[str] = Field(None, min_length=1, max_length=200)
    barangay: Optional[str] = Field(None, min_length=1, max_length=100)
    municipality: Optional[str] = Field(None, min_length=1, max_length=100)
    province: Optional[str] = Field(None, min_length=1, max_length=100)
    district: Optional[str] = Field(None, max_length=50)
    latitude: Optional[float] = Field(None, ge=-90, le=90)
    longitude: Optional[float] = Field(None, ge=-180, le=180)
    activation_date: Optional[str] = None
    completion_date: Optional[str] = None
    status: Optional[ProjectStatus] = None
    notes: Optional[str] = None
    progress: Optional[int] = Field(None, ge=0, le=100)
    assigned_to: Optional[int] = None
    tags: Optional[List[int]] = None
    change_reason: Optional[str] = Field(None, description="Reason for the change")


class ProjectInDB(ProjectBase):
    """Schema for project data in database"""

    id: int
    created_by: int
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class Project(ProjectInDB):
    """Schema for project response"""

    creator: Optional[User] = None
    assignee: Optional[User] = None
    tags: List["Tag"] = []


class UserList(BaseModel):
    """Schema for paginated user list"""

    total: int
    page: int
    page_size: int
    users: List[User]


class ProjectList(BaseModel):
    """Schema for paginated project list"""

    total: int
    page: int
    page_size: int
    projects: List[Project]


class ProjectWithHistory(Project):
    """Schema for project with history"""

    history: List["ProjectHistoryItem"] = []


# ==================== Project History Schemas ====================


class ProjectHistoryItem(BaseModel):
    """Schema for project history item"""

    id: int
    project_id: int
    old_status: Optional[str] = None
    new_status: Optional[str] = None
    old_assigned_to: Optional[int] = None
    new_assigned_to: Optional[int] = None
    changed_fields: Optional[Dict[str, Any]] = None
    changed_by: int
    change_reason: Optional[str] = None
    created_at: datetime
    changer: Optional[User] = None

    class Config:
        from_attributes = True


# ==================== Attachment Schemas ====================


class AttachmentBase(BaseModel):
    """Base attachment schema"""

    filename: str
    original_filename: str
    file_path: str
    file_size: int
    file_type: str
    description: Optional[str] = None


class AttachmentCreate(BaseModel):
    """Schema for creating an attachment"""

    project_id: int
    description: Optional[str] = None


class AttachmentInDB(AttachmentBase):
    """Schema for attachment data in database"""

    id: int
    project_id: int
    uploaded_by: int
    created_at: datetime

    class Config:
        from_attributes = True


class Attachment(AttachmentInDB):
    """Schema for attachment response"""

    uploader: Optional[User] = None


# ==================== Comment Schemas ====================


class CommentBase(BaseModel):
    """Base comment schema"""

    content: str = Field(..., min_length=1)


class CommentCreate(CommentBase):
    """Schema for creating a comment"""

    project_id: int
    parent_id: Optional[int] = None


class CommentUpdate(BaseModel):
    """Schema for updating a comment"""

    content: str = Field(..., min_length=1)


class CommentInDB(CommentBase):
    """Schema for comment data in database"""

    id: int
    project_id: int
    user_id: int
    parent_id: Optional[int] = None
    mentions: Optional[List[int]] = None
    is_edited: bool
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class Comment(CommentInDB):
    """Schema for comment response"""

    user: Optional[User] = None
    replies: List["Comment"] = []


# ==================== Tag Schemas ====================


class TagBase(BaseModel):
    """Base tag schema"""

    name: str = Field(..., min_length=1, max_length=50)
    color: str = Field("#3498db", pattern=r"^#[0-9A-Fa-f]{6}$")
    description: Optional[str] = None


class TagCreate(TagBase):
    """Schema for creating a tag"""

    pass


class TagUpdate(BaseModel):
    """Schema for updating a tag"""

    name: Optional[str] = Field(None, min_length=1, max_length=50)
    color: Optional[str] = Field(None, pattern=r"^#[0-9A-Fa-f]{6}$")
    description: Optional[str] = None


class TagInDB(TagBase):
    """Schema for tag data in database"""

    id: int
    created_by: int
    created_at: datetime

    class Config:
        from_attributes = True


class Tag(TagInDB):
    """Schema for tag response"""

    creator: Optional[User] = None
    project_count: Optional[int] = 0


# ==================== Notification Schemas ====================


class NotificationBase(BaseModel):
    """Base notification schema"""

    type: NotificationType
    title: str
    message: str
    data: Optional[Dict[str, Any]] = None


class NotificationCreate(NotificationBase):
    """Schema for creating a notification"""

    user_id: int


class NotificationInDB(NotificationBase):
    """Schema for notification data in database"""

    id: int
    user_id: int
    is_read: bool
    read_at: Optional[datetime] = None
    created_at: datetime

    class Config:
        from_attributes = True


class Notification(NotificationInDB):
    """Schema for notification response"""

    pass


class NotificationUpdate(BaseModel):
    """Schema for updating notification"""

    is_read: bool = True


# ==================== Filter & Report Schemas ====================


class SavedFilterCreate(BaseModel):
    """Schema for creating a saved filter"""

    name: str = Field(..., min_length=1, max_length=100)
    filters: Dict[str, Any]
    is_default: bool = False


class SavedFilterUpdate(BaseModel):
    """Schema for updating a saved filter"""

    name: Optional[str] = Field(None, min_length=1, max_length=100)
    filters: Optional[Dict[str, Any]] = None
    is_default: Optional[bool] = None


class SavedFilterInDB(BaseModel):
    """Schema for saved filter data in database"""

    id: int
    user_id: int
    name: str
    filters: Dict[str, Any]
    is_default: bool
    created_at: datetime

    class Config:
        from_attributes = True


class SavedReportCreate(BaseModel):
    """Schema for creating a saved report"""

    name: str = Field(..., min_length=1, max_length=100)
    report_type: ReportType
    config: Dict[str, Any]


class SavedReportUpdate(BaseModel):
    """Schema for updating a saved report"""

    name: Optional[str] = Field(None, min_length=1, max_length=100)
    config: Optional[Dict[str, Any]] = None


class SavedReportInDB(BaseModel):
    """Schema for saved report data in database"""

    id: int
    user_id: int
    name: str
    report_type: ReportType
    config: Dict[str, Any]
    created_at: datetime

    class Config:
        from_attributes = True


class SavedReport(SavedReportInDB):
    """Schema for saved report response"""

    pass


# ==================== Common Schemas ====================


class MessageResponse(BaseModel):
    """Generic message response"""

    message: str
    data: Optional[Any] = None


class ErrorResponse(BaseModel):
    """Error response schema"""

    detail: str
    error_code: Optional[str] = None


# ==================== Analytics Schemas ====================


class ProjectStats(BaseModel):
    """Project statistics"""

    total: int
    by_status: Dict[str, int]
    by_province: Dict[str, int]
    completed: int
    pending: int
    in_progress: int
    completion_rate: float


class ActivityFeedItem(BaseModel):
    """Activity feed item"""

    id: int
    user_id: Optional[int]
    action: str
    entity_type: str
    entity_id: int
    details: Optional[Dict[str, Any]] = None
    created_at: datetime
    user: Optional[User] = None


class HeatMapData(BaseModel):
    """Heat map data point"""

    lat: float
    lng: float
    count: int
    status: str


class TrendData(BaseModel):
    """Trend analysis data"""

    date: str
    new_projects: int
    completed_projects: int
    total: int


# ==================== Bulk Actions ====================


class BulkActionRequest(BaseModel):
    """Schema for bulk actions on projects"""

    project_ids: List[int]
    action: str = Field(
        ..., description="Action to perform: delete, assign, update_status, add_tags"
    )
    data: Optional[Dict[str, Any]] = None


class BulkActionResponse(BaseModel):
    """Response for bulk actions"""

    success_count: int
    failed_count: int
    errors: List[str]


# Update forward references
Project.model_rebuild()
Comment.model_rebuild()
Tag.model_rebuild()
