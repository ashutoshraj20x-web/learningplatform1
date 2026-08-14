<?php
// admin/settings.php - Admin Profile & Password Settings
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.");
}

$adminId = $_SESSION['admin_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);

        if (!empty($name) && !empty($username)) {
            try {
                $stmt = $pdo->prepare("UPDATE admins SET name = ?, username = ? WHERE id = ?");
                $stmt->execute([$name, $username, $adminId]);
                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_username'] = $username;
                setFlash('success', 'Profile information updated successfully!');
            } catch (Exception $e) {
                setFlash('error', 'Username might already exist: ' . $e->getMessage());
            }
        } else {
            setFlash('error', 'Name and username cannot be empty.');
        }
        header("Location: settings.php");
        exit;
    }

    if ($action === 'change_password') {
        $currentPass = trim($_POST['current_password']);
        $newPass = trim($_POST['new_password']);
        $confirmPass = trim($_POST['confirm_password']);

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            setFlash('error', 'Please fill in all password fields.');
        } elseif ($newPass !== $confirmPass) {
            setFlash('error', 'New password and confirmation password do not match.');
        } elseif (strlen($newPass) < 6) {
            setFlash('error', 'New password must be at least 6 characters long.');
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);
            $hash = $stmt->fetchColumn();

            if ($hash && password_verify($currentPass, $hash)) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                $updateStmt->execute([$newHash, $adminId]);
                setFlash('success', 'Password changed successfully!');
            } else {
                setFlash('error', 'Incorrect current password.');
            }
        }
        header("Location: settings.php");
        exit;
    }
}

// Fetch current admin info
$stmtAdmin = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmtAdmin->execute([$adminId]);
$admin = $stmtAdmin->fetch() ?: ['username' => 'admin', 'name' => 'Administrator'];

renderAdminHeader('Settings & Security', 'settings', 'Settings');
?>

<div class="max-w-4xl space-y-8">
    <!-- Header -->
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-white">Administrator Profile &amp; Security</h2>
        <p class="text-xs text-slate-400 mt-1">Manage administrator profile credentials and review system environment.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Profile Form -->
        <div class="bg-slate-900 rounded-3xl border border-slate-800 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                <span class="text-sky-400">👤</span> Admin Profile
            </h3>
            <form method="POST" action="settings.php" class="space-y-4">
                <input type="hidden" name="action" value="update_profile">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md transition">
                    Save Profile Changes
                </button>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="bg-slate-900 rounded-3xl border border-slate-800 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                <span class="text-sky-400">🔒</span> Change Password
            </h3>
            <form method="POST" action="settings.php" class="space-y-4">
                <input type="hidden" name="action" value="change_password">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Current Password</label>
                    <input type="password" name="current_password" required placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">New Password</label>
                    <input type="password" name="new_password" required minlength="6" placeholder="Min. 6 characters"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6" placeholder="Re-type new password"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs shadow-md transition">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- System Info Card -->
    <div class="bg-slate-900 rounded-3xl border border-slate-800 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-white mb-3 pb-3 border-b border-slate-800 flex items-center gap-2">
            <span class="text-sky-400">💻</span> Server & Environment Diagnostics
        </h3>
        <div class="grid sm:grid-cols-3 gap-4 text-xs">
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-slate-500 block font-medium">PHP Version</span>
                <b class="text-white text-sm mt-0.5 block font-mono"><?= phpversion() ?></b>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-slate-500 block font-medium">Database Server</span>
                <b class="text-emerald-400 text-sm mt-0.5 block">MySQL via PDO</b>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-slate-500 block font-medium">Upload Directory</span>
                <b class="text-sky-400 text-sm mt-0.5 block font-mono">uploads/</b>
            </div>
        </div>
    </div>
</div>

<?php renderAdminFooter(); ?>
