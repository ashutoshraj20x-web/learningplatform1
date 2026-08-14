<?php
// admin/login.php - Admin Authentication Page
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $pdo = getDBConnection();
        if (!$pdo) {
            $error = 'Database connection failed. Please ensure MySQL is running.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['name'];
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (Exception $e) {
                $error = 'Login error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — LearnHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: Inter, system-ui, -apple-system, sans-serif; background: #0f172a; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-5 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="../index.php" class="text-3xl font-black text-white tracking-tight">Learn<span class="text-sky-400">Hub</span></a>
            <p class="text-slate-400 text-sm mt-2">Management & Admin Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl shadow-sky-950/20 backdrop-blur">
            <h2 class="text-xl font-bold text-white mb-2">Admin Sign In</h2>
            <p class="text-slate-400 text-xs mb-6">Enter your administrator credentials to manage courses, notes, and content.</p>

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-medium flex items-center gap-2">
                    <span>⚠</span> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required autofocus
                           class="w-full px-4 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500"
                           placeholder="e.g. admin">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500"
                           placeholder="••••••••">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-lg shadow-sky-600/30 transition duration-150">
                        Sign In to Dashboard →
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800/80 text-center">
                <p class="text-xs text-slate-500">Default: <b class="text-slate-300">admin</b> / <b class="text-slate-300">admin123</b></p>
                <div class="mt-3">
                    <a href="../index.php" class="text-xs text-sky-400 hover:text-sky-300 transition">← Back to Main Website</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
