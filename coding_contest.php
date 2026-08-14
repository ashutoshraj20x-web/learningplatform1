<?php
// admin/coding_contest.php - Manage Multi-Language Coding Contests & Code Questions
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.");
}

$selectedContestId = isset($_GET['contest']) ? (int)$_GET['contest'] : null;

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_contest') {
        $language = trim($_POST['language']);
        $title = trim($_POST['title']);
        $duration = (int)($_POST['duration_minutes'] ?? 20);
        $desc = trim($_POST['description'] ?? '');

        if (!empty($language) && !empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO coding_contests (language, title, duration_minutes, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$language, $title, $duration, $desc]);
            $newId = $pdo->lastInsertId();
            setFlash('success', 'New coding contest created successfully!');
            header("Location: coding_contest.php?contest={$newId}");
            exit;
        } else {
            setFlash('error', 'Please enter contest language and title.');
        }
        header("Location: coding_contest.php");
        exit;
    }

    if ($action === 'add_question') {
        $contestId = (int)$_POST['contest_id'];
        $qText = trim($_POST['question_text']);
        $code = trim($_POST['code_snippet'] ?? '');
        $optA = trim($_POST['option_a']);
        $optB = trim($_POST['option_b']);
        $optC = trim($_POST['option_c']);
        $optD = trim($_POST['option_d']);
        $correct = strtoupper(trim($_POST['correct_option']));
        $exp = trim($_POST['explanation'] ?? '');

        if ($contestId > 0 && !empty($qText) && !empty($optA) && !empty($optB) && in_array($correct, ['A', 'B', 'C', 'D'])) {
            $stmt = $pdo->prepare("INSERT INTO contest_questions (contest_id, question_text, code_snippet, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$contestId, $qText, $code, $optA, $optB, $optC, $optD, $correct, $exp]);
            setFlash('success', 'Contest question added successfully!');
        } else {
            setFlash('error', 'Please fill in question, options, and choose correct answer.');
        }
        header("Location: coding_contest.php?contest={$contestId}");
        exit;
    }

    if ($action === 'edit_question') {
        $qId = (int)$_POST['question_id'];
        $contestId = (int)$_POST['contest_id'];
        $qText = trim($_POST['question_text']);
        $code = trim($_POST['code_snippet'] ?? '');
        $optA = trim($_POST['option_a']);
        $optB = trim($_POST['option_b']);
        $optC = trim($_POST['option_c']);
        $optD = trim($_POST['option_d']);
        $correct = strtoupper(trim($_POST['correct_option']));
        $exp = trim($_POST['explanation'] ?? '');

        if ($qId > 0 && !empty($qText) && in_array($correct, ['A', 'B', 'C', 'D'])) {
            $stmt = $pdo->prepare("UPDATE contest_questions SET question_text = ?, code_snippet = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ?, explanation = ? WHERE id = ?");
            $stmt->execute([$qText, $code, $optA, $optB, $optC, $optD, $correct, $exp, $qId]);
            setFlash('success', 'Contest question updated successfully!');
        }
        header("Location: coding_contest.php?contest={$contestId}");
        exit;
    }

    if ($action === 'delete_question') {
        $qId = (int)$_POST['question_id'];
        $contestId = (int)$_POST['contest_id'];
        if ($qId > 0) {
            $stmt = $pdo->prepare("DELETE FROM contest_questions WHERE id = ?");
            $stmt->execute([$qId]);
            setFlash('success', 'Contest question deleted.');
        }
        header("Location: coding_contest.php?contest={$contestId}");
        exit;
    }

    if ($action === 'delete_contest') {
        $contestId = (int)$_POST['contest_id'];
        if ($contestId > 0) {
            $stmt = $pdo->prepare("DELETE FROM coding_contests WHERE id = ?");
            $stmt->execute([$contestId]);
            setFlash('success', 'Coding contest deleted.');
        }
        header("Location: coding_contest.php");
        exit;
    }
}

// Fetch all contests
$contests = $pdo->query("SELECT c.*, COUNT(q.id) as question_count 
                         FROM coding_contests c 
                         LEFT JOIN contest_questions q ON c.id = q.contest_id 
                         GROUP BY c.id 
                         ORDER BY c.id ASC")->fetchAll();

if (!$selectedContestId && !empty($contests)) {
    $selectedContestId = $contests[0]['id'];
}

// Fetch active contest details & its questions
$activeContest = null;
$questions = [];
if ($selectedContestId) {
    foreach ($contests as $c) {
        if ($c['id'] == $selectedContestId) {
            $activeContest = $c;
            break;
        }
    }
    $stmtQ = $pdo->prepare("SELECT * FROM contest_questions WHERE contest_id = ? ORDER BY id ASC");
    $stmtQ->execute([$selectedContestId]);
    $questions = $stmtQ->fetchAll();
}

renderAdminHeader('Coding Contest Management', 'coding_contest', 'Contests');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Coding Contests &amp; Challenges</h2>
            <p class="text-xs text-slate-400 mt-1">Manage programming contests across Java, C, Python, C++, and SQL with code snippets and solutions.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="document.getElementById('createContestModal').classList.remove('hidden')" 
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs shadow-sm transition">
                + Create New Contest
            </button>
            <?php if ($activeContest): ?>
                <button onclick="document.getElementById('addQModal').classList.remove('hidden')" 
                        class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20 transition">
                    + Add Question
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contest Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        <?php foreach ($contests as $c): ?>
            <a href="coding_contest.php?contest=<?= $c['id'] ?>" 
               class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $selectedContestId == $c['id'] ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white hover:bg-slate-800' ?>">
                <?= htmlspecialchars($c['language']) ?>: <?= htmlspecialchars($c['title']) ?> 
                <span class="ml-1 opacity-80">(<?= $c['question_count'] ?> Qs)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($activeContest): ?>
        <div class="bg-slate-900 rounded-3xl border border-slate-800 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-3 mb-6">
                <div>
                    <span class="inline-flex px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 text-xs font-black mb-1">
                        Language: <?= htmlspecialchars($activeContest['language']) ?>
                    </span>
                    <h3 class="text-base sm:text-lg font-black text-white"><?= htmlspecialchars($activeContest['title']) ?></h3>
                    <p class="text-xs text-slate-400 mt-1">Duration: <b><?= $activeContest['duration_minutes'] ?> mins</b> &bull; Questions: <b><?= count($questions) ?></b></p>
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="coding_contest.php" onsubmit="return confirm('Delete this entire contest and all questions?');">
                        <input type="hidden" name="action" value="delete_contest">
                        <input type="hidden" name="contest_id" value="<?= $activeContest['id'] ?>">
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 font-bold text-xs hover:bg-rose-500/20 transition">
                            Delete Contest
                        </button>
                    </form>
                </div>
            </div>

            <!-- Questions List -->
            <?php if (empty($questions)): ?>
                <div class="text-center py-10 text-slate-500">
                    <p class="text-xs italic mb-3">No coding challenge questions added to this contest yet.</p>
                    <button onclick="document.getElementById('addQModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs">
                        + Add First Question
                    </button>
                </div>
            <?php else: ?>
                <div class="space-y-5">
                    <?php foreach ($questions as $idx => $q): ?>
                        <div class="border border-slate-800 rounded-2xl p-5 bg-slate-950/60 hover:border-slate-700 transition">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 font-black text-xs flex items-center justify-center shrink-0 mt-0.5">
                                        <?= $idx + 1 ?>
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-white text-xs sm:text-sm"><?= nl2br(htmlspecialchars($q['question_text'])) ?></h4>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button onclick="openEditContestQ(<?= htmlspecialchars(json_encode($q)) ?>)"
                                            class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs hover:bg-slate-700">
                                        Edit
                                    </button>
                                    <form method="POST" action="coding_contest.php?contest=<?= $activeContest['id'] ?>" onsubmit="return confirm('Delete this question?');">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                        <input type="hidden" name="contest_id" value="<?= $activeContest['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 font-bold text-xs hover:bg-rose-500/20">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <?php if (!empty($q['code_snippet'])): ?>
                                <div class="mt-3 bg-slate-900 text-slate-200 rounded-xl p-3.5 text-xs font-mono overflow-x-auto max-h-48 border border-slate-800">
                                    <pre><code><?= htmlspecialchars($q['code_snippet']) ?></code></pre>
                                </div>
                            <?php endif; ?>

                            <!-- Options Grid -->
                            <div class="grid sm:grid-cols-2 gap-2 mt-4 text-xs font-medium">
                                <div class="p-2.5 rounded-xl border <?= $q['correct_option'] === 'A' ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 font-bold' : 'bg-slate-900 border-slate-800 text-slate-300' ?>">
                                    <b>A.</b> <?= htmlspecialchars($q['option_a']) ?>
                                    <?= $q['correct_option'] === 'A' ? ' <span class="text-emerald-400 text-[10px] font-black uppercase ml-1">✓ Correct</span>' : '' ?>
                                </div>
                                <div class="p-2.5 rounded-xl border <?= $q['correct_option'] === 'B' ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 font-bold' : 'bg-slate-900 border-slate-800 text-slate-300' ?>">
                                    <b>B.</b> <?= htmlspecialchars($q['option_b']) ?>
                                    <?= $q['correct_option'] === 'B' ? ' <span class="text-emerald-400 text-[10px] font-black uppercase ml-1">✓ Correct</span>' : '' ?>
                                </div>
                                <div class="p-2.5 rounded-xl border <?= $q['correct_option'] === 'C' ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 font-bold' : 'bg-slate-900 border-slate-800 text-slate-300' ?>">
                                    <b>C.</b> <?= htmlspecialchars($q['option_c']) ?>
                                    <?= $q['correct_option'] === 'C' ? ' <span class="text-emerald-400 text-[10px] font-black uppercase ml-1">✓ Correct</span>' : '' ?>
                                </div>
                                <div class="p-2.5 rounded-xl border <?= $q['correct_option'] === 'D' ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 font-bold' : 'bg-slate-900 border-slate-800 text-slate-300' ?>">
                                    <b>D.</b> <?= htmlspecialchars($q['option_d']) ?>
                                    <?= $q['correct_option'] === 'D' ? ' <span class="text-emerald-400 text-[10px] font-black uppercase ml-1">✓ Correct</span>' : '' ?>
                                </div>
                            </div>

                            <?php if (!empty($q['explanation'])): ?>
                                <div class="mt-3 bg-slate-900 border border-slate-800 rounded-xl p-3 text-xs text-slate-300">
                                    <b class="text-indigo-400">💡 Explanation:</b> <?= htmlspecialchars($q['explanation']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Contest Modal -->
<div id="createContestModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Create Coding Contest</h3>
            <button onclick="document.getElementById('createContestModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="coding_contest.php" class="space-y-4">
            <input type="hidden" name="action" value="create_contest">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Programming Language</label>
                <select name="language" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                    <option value="Java">Java</option>
                    <option value="C">C Language</option>
                    <option value="Python">Python</option>
                    <option value="C++">C++</option>
                    <option value="SQL">SQL</option>
                    <option value="JavaScript">JavaScript</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Contest Title</label>
                <input type="text" name="title" required placeholder="e.g. Python Core Logic & Slicing Quiz"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Duration (Minutes)</label>
                <input type="number" name="duration_minutes" value="20" min="1" max="180" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description of contest..."
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs"></textarea>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('createContestModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md">Create Contest</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Question Modal -->
<div id="addQModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Add Question to <?= htmlspecialchars($activeContest['language'] ?? '') ?> Contest</h3>
            <button onclick="document.getElementById('addQModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="coding_contest.php?contest=<?= $selectedContestId ?>" class="space-y-4">
            <input type="hidden" name="action" value="add_question">
            <input type="hidden" name="contest_id" value="<?= $selectedContestId ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Question Prompt</label>
                <textarea name="question_text" rows="2" required placeholder="e.g. What will be the output of the following code snippet?"
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-indigo-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Code Snippet (Optional)</label>
                <textarea name="code_snippet" rows="5" placeholder="Paste source code here if applicable..."
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-mono text-xs focus:ring-1 focus:ring-indigo-500"></textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Option A</label>
                    <input type="text" name="option_a" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Option B</label>
                    <input type="text" name="option_b" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Option C</label>
                    <input type="text" name="option_c" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Option D</label>
                    <input type="text" name="option_d" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Correct Option</label>
                <select name="correct_option" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-emerald-400 text-sm font-bold">
                    <option value="A">Option A</option>
                    <option value="B">Option B</option>
                    <option value="C">Option C</option>
                    <option value="D">Option D</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Explanation (Optional)</label>
                <textarea name="explanation" rows="2" placeholder="Explain the output or logic..."
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:ring-1 focus:ring-indigo-500"></textarea>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('addQModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md">Save Question</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Question Modal -->
<div id="editQModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Edit Contest Question</h3>
            <button onclick="document.getElementById('editQModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="coding_contest.php?contest=<?= $selectedContestId ?>" class="space-y-4">
            <input type="hidden" name="action" value="edit_question">
            <input type="hidden" name="question_id" id="editQId">
            <input type="hidden" name="contest_id" value="<?= $selectedContestId ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Question Prompt</label>
                <textarea name="question_text" id="editQText" rows="2" required
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-indigo-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Code Snippet (Optional)</label>
                <textarea name="code_snippet" id="editQCode" rows="5"
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-mono text-xs focus:ring-1 focus:ring-indigo-500"></textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Option A</label>
                    <input type="text" name="option_a" id="editQOptA" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Option B</label>
                    <input type="text" name="option_b" id="editQOptB" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Option C</label>
                    <input type="text" name="option_c" id="editQOptC" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Option D</label>
                    <input type="text" name="option_d" id="editQOptD" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Correct Option</label>
                <select name="correct_option" id="editQCorrect" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-emerald-400 text-sm font-bold">
                    <option value="A">Option A</option>
                    <option value="B">Option B</option>
                    <option value="C">Option C</option>
                    <option value="D">Option D</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Explanation</label>
                <textarea name="explanation" id="editQExp" rows="2"
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:ring-1 focus:ring-indigo-500"></textarea>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('editQModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md">Update Question</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditContestQ(q) {
    document.getElementById('editQId').value = q.id;
    document.getElementById('editQText').value = q.question_text;
    document.getElementById('editQCode').value = q.code_snippet || '';
    document.getElementById('editQOptA').value = q.option_a;
    document.getElementById('editQOptB').value = q.option_b;
    document.getElementById('editQOptC').value = q.option_c;
    document.getElementById('editQOptD').value = q.option_d;
    document.getElementById('editQCorrect').value = q.correct_option;
    document.getElementById('editQExp').value = q.explanation || '';
    document.getElementById('editQModal').classList.remove('hidden');
}
</script>

<?php renderAdminFooter(); ?>
