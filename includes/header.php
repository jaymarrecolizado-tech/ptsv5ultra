<?php
/**
 * Common header template
 */

require_once __DIR__ . '/auth.php';

$pageTitle = isset($pageTitle) ? $pageTitle : 'Project Tracking System';
$activeTab = isset($activeTab) ? $activeTab : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>
    
    <!-- Leaflet MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <?php if (isLoggedIn()): ?>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 text-white flex flex-col shadow-xl">
            <!-- Logo -->
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <h1 class="text-xl font-bold">Project Tracker</h1>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="../pages/dashboard.php" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $activeTab === 'dashboard' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                
                <a href="../pages/projects.php" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $activeTab === 'projects' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>All Projects</span>
                </a>
                
                <a href="../pages/project-form.php" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $activeTab === 'manual-entry' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Manual Entry</span>
                </a>
                
                <a href="../pages/import.php" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $activeTab === 'import' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>Data Migration</span>
                </a>
                
                <a href="../pages/reports.php" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $activeTab === 'reports' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Reports</span>
                </a>
                
                <a href="../pages/advanced-reports.php" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $activeTab === 'advanced-reports' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Advanced Reports</span>
                </a>
                
                <?php if (isAdmin()): ?>
                <a href="../pages/admin.php" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $activeTab === 'admin' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Admin Panel</span>
                </a>
                <?php endif; ?>
            </nav>
            
            <!-- Stats Summary -->
            <div class="px-4 py-6 border-t border-slate-700">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Total Projects</span>
                        <span class="font-semibold" id="sidebar-total">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Completed</span>
                        <span class="font-semibold text-green-400" id="sidebar-completed">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Pending</span>
                        <span class="font-semibold text-orange-400" id="sidebar-pending">0</span>
                    </div>
                </div>
            </div>
            
            <!-- User Info -->
            <div class="p-4 border-t border-slate-700">
                <a href="../pages/profile.php" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-xs text-slate-400"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
                    </div>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-8 py-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h2>
                    <div class="flex gap-3">
                        <a href="../index.php" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Home
                        </a>
                        <button id="export-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export Data
                        </button>
                        <button id="refresh-btn" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <a href="../logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="flex-1 overflow-auto p-8">
    <?php endif; ?>
