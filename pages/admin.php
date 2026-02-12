<?php
/**
 * Admin Panel - User Management & Settings
 */

$pageTitle = 'Admin Panel';
$activeTab = '';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

// Check admin access
if (!isAdmin()) {
    header('Location: /projects/newPTS/pages/dashboard.php');
    exit;
}
?>

<div class="space-y-6">
    <!-- Admin Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2">
        <div class="flex flex-wrap gap-2">
            <button onclick="switchAdminTab('users')" id="admin-tab-users" 
                    class="admin-tab px-4 py-2 rounded-lg font-medium transition-colors bg-blue-600 text-white">
                User Management
            </button>
            <button onclick="switchAdminTab('activity')" id="admin-tab-activity" 
                    class="admin-tab px-4 py-2 rounded-lg font-medium transition-colors text-gray-600 hover:bg-gray-100">
                Activity Logs
            </button>
            <button onclick="switchAdminTab('settings')" id="admin-tab-settings" 
                    class="admin-tab px-4 py-2 rounded-lg font-medium transition-colors text-gray-600 hover:bg-gray-100">
                Settings
            </button>
            <button onclick="switchAdminTab('backup')" id="admin-tab-backup" 
                    class="admin-tab px-4 py-2 rounded-lg font-medium transition-colors text-gray-600 hover:bg-gray-100">
                Backup & Restore
            </button>
        </div>
    </div>
    
    <!-- User Management -->
    <div id="admin-content-users" class="admin-content">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add User Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New User</h3>
                <form id="add-user-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                        <input type="text" name="username" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Create User
                    </button>
                </form>
            </div>
            
            <!-- Users List -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">All Users</h3>
                    <div class="flex gap-4">
                        <div class="text-sm">
                            <span class="text-gray-500">Total:</span>
                            <span id="total-users" class="font-semibold">0</span>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-list" class="divide-y divide-gray-200">
                            <!-- Filled by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Activity Logs -->
    <div id="admin-content-activity" class="admin-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Activity Logs</h3>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                        </tr>
                    </thead>
                    <tbody id="activity-logs-list" class="divide-y divide-gray-200">
                        <!-- Filled by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Settings -->
    <div id="admin-content-settings" class="admin-content hidden">
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Application Settings</h3>
                <form id="settings-form" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Application Name</label>
                        <input type="text" name="app_name" id="setting-app-name"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Enable Registration</label>
                            <p class="text-xs text-gray-500">Allow new users to register</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enable_registration" id="setting-enable-registration" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Items Per Page</label>
                        <input type="number" name="items_per_page" id="setting-items-per-page" min="10" max="100"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Maintenance Mode</label>
                            <p class="text-xs text-gray-500">Disable access for non-admin users</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="maintenance_mode" id="setting-maintenance-mode" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Backup & Restore -->
    <div id="admin-content-backup" class="admin-content hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Database Backup</h3>
                <p class="text-gray-600 mb-4">Download a complete backup of your database including all projects, users, and settings.</p>
                <button onclick="downloadBackup()" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Backup
                </button>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Restore Database</h3>
                <p class="text-gray-600 mb-4">Restore from a previous backup file. This will overwrite all current data.</p>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors cursor-pointer" onclick="document.getElementById('restore-file').click()">
                    <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <p class="text-sm text-gray-500">Click to select backup file</p>
                    <input type="file" id="restore-file" accept=".sql" class="hidden" onchange="restoreBackup(this)">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Admin panel JavaScript
function switchAdminTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.admin-tab').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('text-gray-600', 'hover:bg-gray-100');
    });
    
    document.getElementById('admin-tab-' + tab).classList.remove('text-gray-600', 'hover:bg-gray-100');
    document.getElementById('admin-tab-' + tab).classList.add('bg-blue-600', 'text-white');
    
    // Show content
    document.querySelectorAll('.admin-content').forEach(content => {
        content.classList.add('hidden');
    });
    document.getElementById('admin-content-' + tab).classList.remove('hidden');
    
    // Load data
    if (tab === 'users') loadUsers();
    if (tab === 'activity') loadActivityLogs();
    if (tab === 'settings') loadSettings();
}

async function loadUsers() {
    try {
        const response = await fetch('/projects/newPTS/api/admin.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-users').textContent = data.data.users.length;
            
            const tbody = document.getElementById('users-list');
            tbody.innerHTML = data.data.users.map(user => `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                ${user.username.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">${user.username}</p>
                                <p class="text-xs text-gray-500">${user.first_name || ''} ${user.last_name || ''}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">${user.email}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}">
                            ${user.role}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">${user.department || '-'}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button onclick="resetPassword(${user.id})" class="text-sm text-blue-600 hover:text-blue-800">Reset PW</button>
                            ${user.id !== <?php echo $_SESSION['user_id']; ?> ? `
                                <button onclick="deleteUser(${user.id})" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    }
}

async function loadActivityLogs() {
    try {
        const response = await fetch('/projects/newPTS/api/activity.php');
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('activity-logs-list');
            tbody.innerHTML = data.data.logs.map(log => `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-500">${new Date(log.created_at).toLocaleString()}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${log.username || 'System'}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            ${log.action === 'create' ? 'bg-green-100 text-green-800' : 
                              log.action === 'update' ? 'bg-blue-100 text-blue-800' : 
                              log.action === 'delete' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'}">
                            ${log.action}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">${log.entity_type} #${log.entity_id}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">${log.ip_address}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load activity logs:', error);
    }
}

async function loadSettings() {
    // Load current settings
    document.getElementById('setting-app-name').value = 'Project Tracking System';
}

// Add user form
document.getElementById('add-user-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    data.action = 'create_user';
    
    try {
        const response = await fetch('/projects/newPTS/api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            alert('User created successfully');
            e.target.reset();
            loadUsers();
        } else {
            alert(result.message || 'Failed to create user');
        }
    } catch (error) {
        alert('Error creating user');
    }
});

async function resetPassword(userId) {
    if (!confirm('Reset password for this user?')) return;
    
    try {
        const response = await fetch('/projects/newPTS/api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reset_password', user_id: userId })
        });
        const data = await response.json();
        
        if (data.success) {
            alert(`Password reset. New password: ${data.data.password}`);
        }
    } catch (error) {
        alert('Failed to reset password');
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    try {
        const response = await fetch('/projects/newPTS/api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_user', user_id: userId })
        });
        const data = await response.json();
        
        if (data.success) {
            loadUsers();
        }
    } catch (error) {
        alert('Failed to delete user');
    }
}

// Initialize
loadUsers();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
