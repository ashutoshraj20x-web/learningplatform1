<?php
// admin/auth_check.php - Enhanced Admin Authentication & Premium Sidebar Layout
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $_SESSION['flash_error'] = 'Please sign in to access the Admin Console.';
        header('Location: login.php');
        exit;
    }
}

function setFlash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function getFlash($type) {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

function renderAdminHeader($title = 'Dashboard', $activeNav = 'dashboard', $breadcrumb = '') {
    $flashSuccess = getFlash('success');
    $flashError = getFlash('error');
    $adminName = $_SESSION['admin_name'] ?? 'Administrator';
    $adminUser = $_SESSION['admin_username'] ?? 'admin';
    ?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — LearnHub Admin Workspace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #94a3b8;
            transition: all 0.15s ease-in-out;
        }
        .sidebar-item:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(2px);
        }
        .sidebar-item.active {
            color: #ffffff;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }
        .sidebar-group-title {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 1.25rem 0.85rem 0.4rem 0.85rem;
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased flex overflow-hidden">

    <!-- Mobile Sidebar Backdrop -->
    <div id="mobileSidebarBackdrop" onclick="toggleAdminSidebar()" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

    <!-- SIDEBAR -->
    <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 border-r border-slate-800/80 flex flex-col justify-between transition-transform duration-200 -translate-x-full lg:translate-x-0 lg:static shrink-0 shadow-2xl lg:shadow-none">
        
        <!-- Sidebar Top / Brand -->
        <div class="flex flex-col h-full overflow-y-auto">
            <div class="h-20 px-6 border-b border-slate-800/80 flex items-center justify-between shrink-0">
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 flex items-center justify-center font-black text-white shadow-lg shadow-sky-600/30 group-hover:scale-105 transition">
                        LH
                    </div>
                    <div>
                        <div class="text-lg font-black tracking-tight text-white flex items-center gap-1.5">
                            Learn<span class="text-sky-400">Hub</span>
                        </div>
                        <div class="text-[10px] font-bold tracking-widest uppercase text-sky-400/80">Admin Console v2.0</div>
                    </div>
                </a>
                <button onclick="toggleAdminSidebar()" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Admin User Card -->
            <div class="p-4 mx-3 my-3 rounded-2xl bg-slate-800/50 border border-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 font-black text-sm flex items-center justify-center shrink-0">
                    <?= strtoupper(substr($adminName, 0, 2)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-white truncate"><?= htmlspecialchars($adminName) ?></div>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span class="text-[11px] font-medium text-slate-400 truncate">@<?= htmlspecialchars($adminUser) ?></span>
                    </div>
                </div>
                <a href="settings.php" title="Profile Settings" class="text-slate-400 hover:text-sky-400 p-1.5 rounded-lg hover:bg-slate-700/50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </a>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-3 space-y-1 pb-4">
                
                <div class="sidebar-group-title">Overview</div>
                <a href="index.php" class="sidebar-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Dashboard</span>
                </a>

                <div class="sidebar-group-title">Academic Modules</div>
                <a href="subjects_lectures.php" class="sidebar-item <?= $activeNav === 'lectures' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Unit Lectures</span>
                </a>

                <a href="notes.php" class="sidebar-item <?= $activeNav === 'notes' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Unit Notes (PDF)</span>
                </a>

                <a href="practicals.php" class="sidebar-item <?= $activeNav === 'practicals' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    <span>Practicals & Labs</span>
                </a>

                <a href="pyqs.php" class="sidebar-item <?= $activeNav === 'pyqs' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                    <span>PYQs Papers (PDF)</span>
                </a>

                <div class="sidebar-group-title">Assessment Engines</div>
                <a href="test_series.php" class="sidebar-item <?= $activeNav === 'test_series' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Test Series MCQs</span>
                </a>

                <a href="coding_contest.php" class="sidebar-item <?= $activeNav === 'coding_contest' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                    <span>Coding Contests</span>
                </a>

                <div class="sidebar-group-title">Preferences</div>
                <a href="settings.php" class="sidebar-item <?= $activeNav === 'settings' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    <span>Admin Settings</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/50 space-y-2 shrink-0">
            <a href="../index.php" target="_blank" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700/80 text-slate-200 text-xs font-bold transition shadow-sm border border-slate-700/50">
                <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                <span>Live Student Portal</span>
            </a>
            <a href="logout.php" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-semibold transition border border-rose-500/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-950 overflow-y-auto">
        
        <!-- Top Navbar -->
        <header class="h-20 bg-slate-900/80 backdrop-blur border-b border-slate-800 sticky top-0 z-30 px-5 sm:px-8 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <button onclick="toggleAdminSidebar()" class="lg:hidden p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex flex-col">
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                        <span>Admin</span>
                        <span>/</span>
                        <span class="text-sky-400 font-semibold"><?= htmlspecialchars($breadcrumb ?: $title) ?></span>
                    </div>
                    <h1 class="text-base sm:text-lg font-black text-white"><?= htmlspecialchars($title) ?></h1>
                </div>
            </div>

            <!-- Header Right Stats & Actions -->
            <div class="flex items-center gap-3 text-xs">
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/60 text-slate-300 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Database: <b class="text-white">Active</b></span>
                </div>
                <a href="../index.php" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition shadow-md shadow-sky-600/20">
                    <span>Preview Site ↗</span>
                </a>
            </div>
        </header>

        <!-- Page Content Area -->
        <main class="flex-1 p-5 sm:p-8 max-w-7xl w-full mx-auto">
            
            <?php if ($flashSuccess): ?>
                <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-medium flex items-center justify-between animate-fade">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                        <span><?= htmlspecialchars($flashSuccess) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm font-medium flex items-center justify-between animate-fade">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold text-xs">⚠</span>
                        <span><?= htmlspecialchars($flashError) ?></span>
                    </div>
                </div>
            <?php endif; ?>
    <?php
}

function renderAdminFooter() {
    ?>
        </main>
        
        <footer class="mt-auto px-8 py-5 border-t border-slate-900 bg-slate-950 text-slate-500 text-xs flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>LearnHub Administrator Control Panel &bull; <b>RRSDCE Begusarai</b></span>
            <span>Signed in as <b class="text-slate-400"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?></b></span>
        </footer>
    </div>

    <script>
    function toggleAdminSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('mobileSidebarBackdrop');
        const isHidden = sidebar.classList.contains('-translate-x-full');
        if (isHidden) {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }
    }
    </script>
</body>
</html>
    <?php
}
