/**
 * Authentication Service for Static Dashboard
 */
const AuthService = {
    isLoggedIn: false,
    currentUser: null,

    init() {
        this.checkAuth();
        this.setupLogoutListener();
        this.setupUI();
    },

    checkAuth() {
        const token = localStorage.getItem('access_token');
        const userStr = localStorage.getItem('user');
        const currentPath = window.location.pathname;

        // Allow access to landing and login pages
        if (currentPath.endsWith('landing.html') || currentPath.endsWith('login.html')) {
            return;
        }

        // Check if authenticated
        if (token && userStr) {
            try {
                this.currentUser = JSON.parse(userStr);
                this.isLoggedIn = true;
                this.showDashboard();
            } catch (e) {
                this.isLoggedIn = false;
                this.showAuthOverlay();
            }
        } else {
            this.isLoggedIn = false;
            this.showAuthOverlay();
        }
    },

    showDashboard() {
        const authOverlay = document.getElementById('auth-overlay');
        const dashboardContainer = document.getElementById('dashboard-container');

        if (authOverlay) {
            authOverlay.classList.remove('active');
            authOverlay.classList.add('hidden');
        }

        if (dashboardContainer) {
            dashboardContainer.classList.add('active');
        }

        // Update map and charts when dashboard becomes visible
        setTimeout(() => {
            try {
                if (window.MapService && MapService.renderProjects && window.DataService) {
                    MapService.renderProjects(DataService.getAllProjects());
                }
            } catch (e) {
                console.error('Error updating map:', e);
            }

            try {
                if (window.ChartService && ChartService.updateAllCharts && window.DataService) {
                    ChartService.updateAllCharts();
                }
            } catch (e) {
                console.error('Error updating charts:', e);
            }

            try {
                if (window.UIService && UIService.updateStats && window.DataService) {
                    UIService.updateStats();
                }
            } catch (e) {
                console.error('Error updating stats:', e);
            }
        }, 100);

        this.applyRBAC();
    },

    showAuthOverlay() {
        const authOverlay = document.getElementById('auth-overlay');
        const dashboardContainer = document.getElementById('dashboard-container');

        if (authOverlay) {
            authOverlay.classList.remove('hidden');
            setTimeout(() => {
                authOverlay.classList.add('active');
            }, 10);
        }

        if (dashboardContainer) {
            dashboardContainer.classList.remove('active');
        }
    },

    logout() {
        // Clear local storage
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user');

        this.isLoggedIn = false;
        this.currentUser = null;

        // Redirect to login page
        window.location.href = 'login.html';
    },

    getUser() {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    },

    hasRole(roles) {
        const user = this.getUser();
        if (!user) return false;
        return roles.includes(user.role);
    },

    hasPermission(permission) {
        const user = this.getUser();
        if (!user) return false;

        const permissions = {
            admin: ['view_dashboard', 'view_projects', 'add_project', 'edit_project', 'delete_project', 'manage_users', 'view_reports', 'export_data'],
            editor: ['view_dashboard', 'view_projects', 'add_project', 'edit_project', 'view_reports', 'export_data'],
            viewer: ['view_dashboard', 'view_projects', 'view_reports', 'export_data']
        };

        return permissions[user.role]?.includes(permission) || false;
    },

    applyRBAC() {
        const user = this.getUser();
        if (!user) return;

        // Add project button - Admin and Editor only
        const addProjectBtn = document.getElementById('add-project-btn');
        if (addProjectBtn) {
            addProjectBtn.style.display = this.hasPermission('add_project') ? 'block' : 'none';
        }

        // Import data - Admin and Editor only
        const importDataTab = document.querySelector('[data-tab="data-migration"]');
        if (importDataTab) {
            importDataTab.style.display = this.hasPermission('add_project') ? 'flex' : 'none';
        }

        // Manual entry - Admin and Editor only
        const manualEntryTab = document.querySelector('[data-tab="manual-entry"]');
        if (manualEntryTab) {
            manualEntryTab.style.display = this.hasPermission('add_project') ? 'flex' : 'none';
        }

        // Delete project buttons
        document.querySelectorAll('.delete-project-btn').forEach(btn => {
            btn.style.display = this.hasPermission('delete_project') ? 'block' : 'none';
        });
    },

    setupLogoutListener() {
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to logout?')) {
                    this.logout();
                }
            });
        }
    },

    setupUI() {
        // Add user info to header if available
        const user = this.getUser();
        if (!user) return;

        // Check if user info already exists to prevent duplicates
        const existingUserInfo = document.querySelector('.user-info');
        if (existingUserInfo) {
            existingUserInfo.remove();
        }

        const headerActions = document.querySelector('.header-actions');
        if (headerActions) {
                const userInfo = document.createElement('div');
                userInfo.className = 'user-info';
                userInfo.style.cssText = 'display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-right: 12px;';
                userInfo.innerHTML = `
                    <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                        ${(user.username || user.email || 'U').charAt(0).toUpperCase()}
                    </div>
                    <div style="text-align: left;">
                        <div style="font-weight: 600; font-size: 0.875rem; color: var(--text);">${user.username || user.email || 'User'}</div>
                        <div style="font-size: 0.75rem; color: var(--text-light); text-transform: capitalize;">${user.role || 'Viewer'}</div>
                    </div>
                `;
                const logoutBtn = document.getElementById('logout-btn');
                if (logoutBtn) {
                    headerActions.insertBefore(userInfo, logoutBtn);
                }
            }
        }
    },

    // For demo purposes, allow setting a mock user
    setDemoUser(username, role = 'admin') {
        const user = {
            id: 'demo-user-1',
            username: username,
            email: `${username}@example.com`,
            role: role
        };
        localStorage.setItem('access_token', 'demo-token-' + Date.now());
        localStorage.setItem('user', JSON.stringify(user));
        this.currentUser = user;
        this.isLoggedIn = true;
    }
};

// Initialize auth service when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AuthService.init());
} else {
    AuthService.init();
}

// Make it available globally
window.AuthService = AuthService;
