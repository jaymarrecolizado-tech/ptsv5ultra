import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Project, Comment, Attachment } from '@/types';
import {
  ArrowLeft,
  MapPin,
  Calendar,
  Edit,
  Trash2,
  Send,
  Paperclip,
  Download,
  File,
  X,
} from 'lucide-react';
import toast from 'react-hot-toast';

const statusColors: Record<string, string> = {
  planning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
  in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
  on_hold: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  done: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
  pending: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

const mockProject: Project = {
  id: 1,
  site_code: 'UNDP-GI-0009A',
  project_name: 'Free-WIFI for All',
  site_name: 'Raele Barangay Hall - AP 1',
  barangay: 'Raele',
  municipality: 'Itbayat',
  province: 'Batanes',
  district: 'District I',
  latitude: 20.728794,
  longitude: 121.804235,
  activation_date: '2024-04-30',
  status: 'done',
  progress: 100,
  created_by: 1,
  created_at: '2024-04-01',
  updated_at: '2024-04-30',
  creator: {
    id: 1,
    username: 'admin',
    email: 'admin@example.com',
    full_name: 'Admin User',
    role: 'admin',
    is_active: true,
    is_verified: true,
    created_at: '2024-01-01',
    updated_at: '2024-01-01',
  },
};

export default function ProjectDetailPage() {
  const { id: _id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [newComment, setNewComment] = useState('');
  const [replyTo, setReplyTo] = useState<number | null>(null);
  const [replyText, setReplyText] = useState('');

  const project = mockProject;
  const comments: Comment[] = [];
  const attachments: Attachment[] = [];

  const handleAddComment = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newComment.trim()) return;
    setNewComment('');
    toast.success('Comment added');
  };

  const handleReply = (e: React.FormEvent, _commentId: number) => {
    e.preventDefault();
    if (!replyText.trim()) return;
    setReplyText('');
    setReplyTo(null);
    toast.success('Reply added');
  };

  const handleDeleteComment = (_commentId: number) => {
    if (!window.confirm('Delete this comment?')) return;
    toast.success('Comment deleted');
  };

  const handleDeleteAttachment = (_attachmentId: number) => {
    if (!window.confirm('Delete this attachment?')) return;
    toast.success('Attachment deleted');
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    e.target.value = '';
    toast.success('File uploaded');
  };

  if (!project) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-gray-500 dark:text-gray-400">Project not found</div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-4">
          <button
            onClick={() => navigate('/projects')}
            className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          >
            <ArrowLeft size={20} className="text-gray-600 dark:text-gray-400" />
          </button>
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
              {project.project_name}
            </h1>
            <p className="text-gray-600 dark:text-gray-400">{project.site_name}</p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <button
            onClick={() => navigate(`/projects/${project.id}/edit`)}
            className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
          >
            <Edit size={18} />
            Edit Project
          </button>
        </div>
      </div>

      {/* Project Details */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Main Details */}
        <div className="lg:col-span-2 space-y-6">
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Project Information
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Site Code</p>
                <p className="text-gray-900 dark:text-white font-medium">
                  {project.site_code}
                </p>
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</p>
                <span
                  className={`px-3 py-1 rounded-full text-sm font-medium capitalize ${
                    statusColors[project.status] || statusColors.pending
                  }`}
                >
                  {project.status.replace('_', ' ')}
                </span>
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Progress</p>
                <div className="flex items-center gap-2">
                  <div className="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div
                      className="h-full bg-blue-600 rounded-full transition-all"
                      style={{ width: `${project.progress}%` }}
                    />
                  </div>
                  <span className="text-sm text-gray-900 dark:text-white font-medium min-w-[3rem]">
                    {project.progress}%
                  </span>
                </div>
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">District</p>
                <p className="text-gray-900 dark:text-white font-medium">
                  {project.district || 'N/A'}
                </p>
              </div>
              <div className="col-span-2">
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Location</p>
                <div className="flex items-start gap-2">
                  <MapPin size={18} className="text-gray-400 mt-0.5" />
                  <p className="text-gray-900 dark:text-white font-medium">
                    {project.barangay}, {project.municipality}, {project.province}
                  </p>
                </div>
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Activation Date</p>
                <div className="flex items-center gap-2">
                  <Calendar size={18} className="text-gray-400" />
                  <p className="text-gray-900 dark:text-white font-medium">
                    {new Date(project.activation_date).toLocaleDateString()}
                  </p>
                </div>
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Completion Date</p>
                <div className="flex items-center gap-2">
                  <Calendar size={18} className="text-gray-400" />
                  <p className="text-gray-900 dark:text-white font-medium">
                    {project.completion_date
                      ? new Date(project.completion_date).toLocaleDateString()
                      : 'Not completed'}
                  </p>
                </div>
              </div>
            </div>
            {project.notes && (
              <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Notes</p>
                <p className="text-gray-900 dark:text-white">{project.notes}</p>
              </div>
            )}
          </div>

          {/* Comments */}
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Comments
            </h2>

            {/* Add Comment Form */}
            <form onSubmit={handleAddComment} className="mb-6">
              <div className="flex gap-3">
                <textarea
                  value={newComment}
                  onChange={(e) => setNewComment(e.target.value)}
                  placeholder="Write a comment..."
                  rows={3}
                  className="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white resize-none"
                />
                <button
                  type="submit"
                  disabled={!newComment.trim()}
                  className="self-end px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Send size={20} />
                </button>
              </div>
            </form>

            {/* Comments List */}
            <div className="space-y-4">
              {comments && comments.length > 0 ? (
                comments.map((comment) => (
                  <div
                    key={comment.id}
                    className="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4"
                  >
                    <div className="flex items-start justify-between">
                      <div className="flex items-start gap-3">
                        <div className="flex-shrink-0 h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                          {comment.user?.username?.charAt(0).toUpperCase() || 'U'}
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center gap-2 mb-1">
                            <p className="font-medium text-gray-900 dark:text-white">
                              {comment.user?.full_name || comment.user?.username}
                            </p>
                            <span className="text-xs text-gray-500 dark:text-gray-400">
                              {new Date(comment.created_at).toLocaleString()}
                            </span>
                          </div>
                          <p className="text-gray-700 dark:text-gray-300">{comment.content}</p>

                          {/* Replies */}
                          {comment.replies && comment.replies.length > 0 && (
                            <div className="mt-4 space-y-3 ml-4 pl-4 border-l-2 border-gray-300 dark:border-gray-600">
                              {comment.replies.map((reply) => (
                                <div key={reply.id} className="text-sm">
                                  <div className="flex items-center gap-2 mb-1">
                                    <p className="font-medium text-gray-900 dark:text-white">
                                      {reply.user?.full_name || reply.user?.username}
                                    </p>
                                    <span className="text-xs text-gray-500 dark:text-gray-400">
                                      {new Date(reply.created_at).toLocaleString()}
                                    </span>
                                  </div>
                                  <p className="text-gray-700 dark:text-gray-300">
                                    {reply.content}
                                  </p>
                                </div>
                              ))}
                            </div>
                          )}

                          {/* Reply Form */}
                          {replyTo === comment.id ? (
                            <form
                              onSubmit={(e) => handleReply(e, comment.id)}
                              className="mt-3 flex gap-2"
                            >
                              <input
                                type="text"
                                value={replyText}
                                onChange={(e) => setReplyText(e.target.value)}
                                placeholder="Write a reply..."
                                className="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm"
                                autoFocus
                              />
                              <button
                                type="submit"
                                className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors"
                              >
                                <Send size={16} />
                              </button>
                              <button
                                type="button"
                                onClick={() => {
                                  setReplyTo(null);
                                  setReplyText('');
                                }}
                                className="px-3 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
                              >
                                <X size={16} />
                              </button>
                            </form>
                          ) : (
                            <button
                              onClick={() => setReplyTo(comment.id)}
                              className="mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline"
                            >
                              Reply
                            </button>
                          )}
                        </div>
                      </div>
                      <button
                        onClick={() => handleDeleteComment(comment.id)}
                        className="p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors"
                      >
                        <Trash2 size={16} />
                      </button>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center text-gray-500 dark:text-gray-400 py-8">
                  No comments yet
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Attachments */}
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                Attachments
              </h2>
              <label className="cursor-pointer">
                <input
                  type="file"
                  onChange={handleFileUpload}
                  className="hidden"
                />
                <div className="flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm">
                  <Paperclip size={16} />
                  Upload
                </div>
              </label>
            </div>

            {attachments && attachments.length > 0 ? (
              <div className="space-y-3">
                {attachments.map((attachment) => (
                  <div
                    key={attachment.id}
                    className="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                  >
                    <File
                      size={20}
                      className="text-gray-400 flex-shrink-0 mt-0.5"
                    />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {attachment.original_filename}
                      </p>
                      {attachment.description && (
                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                          {attachment.description}
                        </p>
                      )}
                      <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {(attachment.file_size / 1024).toFixed(1)} KB
                      </p>
                    </div>
                    <div className="flex items-center gap-1">
                      <button
                        onClick={() => window.open(`/uploads/${attachment.file_path}`, '_blank')}
                        className="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded transition-colors"
                        title="Download"
                      >
                        <Download size={14} />
                      </button>
                      <button
                        onClick={() => handleDeleteAttachment(attachment.id)}
                        className="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors"
                        title="Delete"
                      >
                        <Trash2 size={14} />
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center text-gray-500 dark:text-gray-400 py-4">
                No attachments
              </div>
            )}
          </div>

          {/* Tags */}
          {project.tags && project.tags.length > 0 && (
            <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Tags
              </h2>
              <div className="flex flex-wrap gap-2">
                {project.tags.map((tag) => (
                  <span
                    key={tag.id}
                    className="px-3 py-1 rounded-full text-sm font-medium text-white"
                    style={{ backgroundColor: tag.color }}
                  >
                    {tag.name}
                  </span>
                ))}
              </div>
            </div>
          )}

          {/* Coordinates */}
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Coordinates
            </h2>
            <div className="space-y-2 text-sm">
              <div>
                <p className="text-gray-600 dark:text-gray-400">Latitude</p>
                <p className="text-gray-900 dark:text-white font-mono">
                  {project.latitude.toFixed(6)}
                </p>
              </div>
              <div>
                <p className="text-gray-600 dark:text-gray-400">Longitude</p>
                <p className="text-gray-900 dark:text-white font-mono">
                  {project.longitude.toFixed(6)}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
