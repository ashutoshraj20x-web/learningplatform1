<?php
// admin/index.php - Structured Premium Admin Dashboard
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();

$pdo = getDBConnection();
$counts = [
    'subjects' => 0,
    'units' => 0,
    'lectures' => 0,
    'notes' => 0,
    'practicals' => 0,
    'experiments' => 0,
    'pyqs' => 0,
    'tests' => 0,
    'test_questions' => 0,
    'contests' => 0,
    'contest_questions' => 0,
];

if ($pdo) {
    try {
        $counts['subjects'] = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
        $counts['units'] = $pdo->query("SELECT COUNT(*) FROM units")->fetchColumn();
        $counts['lectures'] = $pdo->query("SELECT COUNT(*) FROM lectures")->fetchColumn();
        $counts['notes'] = $pdo->query("SELECT COUNT(*) FROM units WHERE notes_pdf_url IS NOT NULL AND notes_pdf_url != ''")->fetchColumn();
        $counts['practicals'] = $pdo->query("SELECT COUNT(*) FROM practicals")->fetchColumn();
        $counts['experiments'] = $pdo->query("SELECT COUNT(*) FROM practical_experiments")->fetchColumn();
        $counts['pyqs'] = $pdo->query("SELECT COUNT(*) FROM pyqs")->fetchColumn();
        $counts['tests'] = $pdo->query("SELECT COUNT(*) FROM test_series")->fetchColumn();
        $counts['test_questions'] = $pdo->query("SELECT COUNT(*) FROM test_questions")->fetchColumn();
        $counts['contests'] = $pdo->query("SELECT COUNT(*) FROM coding_contests")->fetchColumn();
        $counts['contest_questions'] = $pdo->query("SELECT COUNT(*) FROM contest_questions")->fetchColumn();
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

renderAdminHeader('Dashboard Overview', 'dashboard', 'Overview');
?>

<div class="space-y-8">
    
    <!-- Hero Banner Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 border border-slate-800 p-6 sm:p-8 shadow-2xl shadow-sky-950/20">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs font-bold uppercase tracking-wider mb-3">
                    <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                    Central Administration
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>! 👋</h2>
                <p class="mt-2 text-slate-400 text-sm leading-relaxed">
                    Here is an overview of your educational resources, lecture counts, uploaded PDFs, test question banks, and live coding contests.
                </p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="subjects_lectures.php" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-lg shadow-sky-600/30 transition flex items-center gap-2">
                    <span>+</span> Add New Lecture
                </a>
                <a href="notes.php" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs transition flex items-center gap-2">
                    <span>📤</span> Upload Notes PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Structured Metric Statistics -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Resource Statistics</h3>
            <span class="text-xs text-slate-500">Live Database Counters</span>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            
            <!-- Card: Lectures -->
            <a href="subjects_lectures.php" class="group rounded-2xl bg-slate-900 border border-slate-800/80 p-5 hover:border-sky-500/50 hover:bg-slate-800/60 transition duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded-full">Videos</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-black text-white group-hover:text-sky-400 transition"><?= $counts['lectures'] ?></div>
                    <div class="text-xs text-slate-400 mt-0.5">Unit Lectures</div>
                </div>
            </a>

            <!-- Card: Notes -->
            <a href="notes.php" class="group rounded-2xl bg-slate-900 border border-slate-800/80 p-5 hover:border-emerald-500/50 hover:bg-slate-800/60 transition duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">PDFs</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-black text-white group-hover:text-emerald-400 transition"><?= $counts['notes'] ?></div>
                    <div class="text-xs text-slate-400 mt-0.5">Unit Notes</div>
                </div>
            </a>

            <!-- Card: Practicals -->
            <a href="practicals.php" class="group rounded-2xl bg-slate-900 border border-slate-800/80 p-5 hover:border-amber-500/50 hover:bg-slate-800/60 transition duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full">Labs</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-black text-white group-hover:text-amber-400 transition"><?= $counts['experiments'] ?></div>
                    <div class="text-xs text-slate-400 mt-0.5">Lab Experiments</div>
                </div>
            </a>

            <!-- Card: PYQs -->
            <a href="pyqs.php" class="group rounded-2xl bg-slate-900 border border-slate-800/80 p-5 hover:border-violet-500/50 hover:bg-slate-800/60 transition duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-violet-400 bg-violet-500/10 px-2 py-0.5 rounded-full">Exams</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-black text-white group-hover:text-violet-400 transition"><?= $counts['pyqs'] ?></div>
                    <div class="text-xs text-slate-400 mt-0.5">PYQ Papers</div>
                </div>
            </a>

            <!-- Card: Tests -->
            <a href="test_series.php" class="group rounded-2xl bg-slate-900 border border-slate-800/80 p-5 hover:border-orange-500/50 hover:bg-slate-800/60 transition duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-orange-400 bg-orange-500/10 px-2 py-0.5 rounded-full">MCQs</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-black text-white group-hover:text-orange-400 transition"><?= $counts['test_questions'] ?></div>
                    <div class="text-xs text-slate-400 mt-0.5">Test Questions</div>
                </div>
            </a>

            <!-- Card: Coding Contests -->
            <a href="coding_contest.php" class="group rounded-2xl bg-slate-900 border border-slate-800/80 p-5 hover:border-indigo-500/50 hover:bg-slate-800/60 transition duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-full">Code</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-black text-white group-hover:text-indigo-400 transition"><?= $counts['contest_questions'] ?></div>
                    <div class="text-xs text-slate-400 mt-0.5">Code Quizzes</div>
                </div>
            </a>

        </div>
    </div>

    <!-- Structured 2-Column Section: Modules & System Status -->
    <div class="grid lg:grid-cols-3 gap-8">
        
        <!-- Left: Quick Action Management Grid (2 Cols wide on desktop) -->
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Content Management Modules</h3>
            
            <div class="grid sm:grid-cols-2 gap-4">
                
                <!-- Module 1: Lectures -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center font-bold text-sm">01</span>
                            <h4 class="font-bold text-white text-sm">Unit-wise Video Lectures</h4>
                        </div>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Organize video lectures across 6 engineering subjects (OS, DE, DSA, DMGT, Java, UHV). Add or edit YouTube embed links.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500"><?= $counts['lectures'] ?> active links</span>
                        <a href="subjects_lectures.php" class="text-xs font-bold text-sky-400 hover:text-sky-300 flex items-center gap-1">Manage &rarr;</a>
                    </div>
                </div>

                <!-- Module 2: Notes -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-sm">02</span>
                            <h4 class="font-bold text-white text-sm">Unit-wise Notes & PDFs</h4>
                        </div>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Upload official PDF study materials directly from your computer. Automatic instant view and download buttons on frontend.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500"><?= $counts['notes'] ?> PDF notes linked</span>
                        <a href="notes.php" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 flex items-center gap-1">Manage &rarr;</a>
                    </div>
                </div>

                <!-- Module 3: Practicals & Labs -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-sm">03</span>
                            <h4 class="font-bold text-white text-sm">Practicals & DE Lab PDFs</h4>
                        </div>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Manage code snippets for Java, C and DSA labs, or upload experiment PDFs for the Digital Electronics Lab.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500"><?= $counts['experiments'] ?> experiments</span>
                        <a href="practicals.php" class="text-xs font-bold text-amber-400 hover:text-amber-300 flex items-center gap-1">Manage &rarr;</a>
                    </div>
                </div>

                <!-- Module 4: PYQs -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-violet-500/10 text-violet-400 flex items-center justify-center font-bold text-sm">04</span>
                            <h4 class="font-bold text-white text-sm">Previous Year Questions</h4>
                        </div>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Upload question papers for years 2021 through 2026. Manage paper archives with single-click PDF replacement.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500"><?= $counts['pyqs'] ?> papers</span>
                        <a href="pyqs.php" class="text-xs font-bold text-violet-400 hover:text-violet-300 flex items-center gap-1">Manage &rarr;</a>
                    </div>
                </div>

                <!-- Module 5: Test Series -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-orange-500/10 text-orange-400 flex items-center justify-center font-bold text-sm">05</span>
                            <h4 class="font-bold text-white text-sm">Test Series Question Bank</h4>
                        </div>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Create subject-wise mock tests with multiple choice questions, timer durations, answer keys, and explanations.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500"><?= $counts['test_questions'] ?> MCQs</span>
                        <a href="test_series.php" class="text-xs font-bold text-orange-400 hover:text-orange-300 flex items-center gap-1">Manage &rarr;</a>
                    </div>
                </div>

                <!-- Module 6: Coding Contest -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-sm">06</span>
                            <h4 class="font-bold text-white text-sm">Coding Contests Bank</h4>
                        </div>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Add programming logic challenges in Java, C, Python, C++, and SQL with syntax-highlighted code snippets.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500"><?= $counts['contest_questions'] ?> challenges</span>
                        <a href="coding_contest.php" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1">Manage &rarr;</a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Column: System Status & Diagnostic Summary -->
        <div class="space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Server & Environment</h3>
            
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <span class="text-xs text-slate-400">Backend Engine</span>
                    <span class="text-xs font-bold text-white font-mono">PHP <?= phpversion() ?></span>
                </div>

                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <span class="text-xs text-slate-400">Database Driver</span>
                    <span class="text-xs font-bold text-emerald-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> PDO MySQL
                    </span>
                </div>

                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <span class="text-xs text-slate-400">Upload Directories</span>
                    <span class="text-xs font-bold text-sky-400 font-mono">uploads/</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Session Security</span>
                    <span class="text-xs font-bold text-emerald-400 font-mono">Bcrypt Hashing</span>
                </div>
            </div>

            <!-- Fast Navigation Shortcut Card -->
            <div class="rounded-2xl bg-gradient-to-br from-sky-950 to-slate-900 border border-sky-900/40 p-5">
                <h4 class="font-bold text-white text-sm mb-1">Direct Student Access</h4>
                <p class="text-xs text-slate-400 mb-4">You can test how your updates look to students on the live platform.</p>
                <a href="../index.php" target="_blank" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md transition">
                    <span>Open Live Website ↗</span>
                </a>
            </div>

            <!-- Quick Account Profile Card -->
            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-white">Administrator Account</div>
                    <div class="text-[11px] text-slate-400">Change password or credentials</div>
                </div>
                <a href="settings.php" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition">
                    Settings
                </a>
            </div>
        </div>

    </div>

</div>

<?php renderAdminFooter(); ?>
