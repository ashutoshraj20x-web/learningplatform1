<?php
// admin/test_series.php - Manage Subject-wise Test Series & Objective Questions
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.");
}

$selectedTestId = isset($_GET['test']) ? (int)$_GET['test'] : null;

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_test') {
        $subjectId = $_POST['subject_id'];
        $title = trim($_POST['title']);
        $duration = (int)($_POST['duration_minutes'] ?? 15);
        $marks = (int)($_POST['total_marks'] ?? 10);
        $desc = trim($_POST['description'] ?? '');

        if (!empty($subjectId) && !empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO test_series (subject_id, title, duration_minutes, total_marks, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$subjectId, $title, $duration, $marks, $desc]);
            $newId = $pdo->lastInsertId();
            setFlash('success', 'New test series created successfully!');
            header("Location: test_series.php?test={$newId}");
            exit;
        } else {
            setFlash('error', 'Please fill in required test details.');
        }
        header("Location: test_series.php");
        exit;
    }

    if ($action === 'add_question') {
        $testId = (int)$_POST['test_id'];
        $qText = trim($_POST['question_text']);
        $optA = trim($_POST['option_a']);
        $optB = trim($_POST['option_b']);
        $optC = trim($_POST['option_c']);
        $optD = trim($_POST['option_d']);
        $correct = strtoupper(trim($_POST['correct_option']));
        $exp = trim($_POST['explanation'] ?? '');

        if ($testId > 0 && !empty($qText) && !empty($optA) && !empty($optB) && in_array($correct, ['A', 'B', 'C', 'D'])) {
            $stmt = $pdo->prepare("INSERT INTO test_questions (test_series_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$testId, $qText, $optA, $optB, $optC, $optD, $correct, $exp]);
            setFlash('success', 'Question added successfully!');
        } else {
            setFlash('error', 'Please fill in all options and select a valid correct answer.');
        }
        header("Location: test_series.php?test={$testId}");
        exit;
    }

    if ($action === 'edit_question') {
        $qId = (int)$_POST['question_id'];
        $testId = (int)$_POST['test_id'];
        $qText = trim($_POST['question_text']);
        $optA = trim($_POST['option_a']);
        $optB = trim($_POST['option_b']);
        $optC = trim($_POST['option_c']);
        $optD = trim($_POST['option_d']);
        $correct = strtoupper(trim($_POST['correct_option']));
        $exp = trim($_POST['explanation'] ?? '');

        if ($qId > 0 && !empty($qText) && in_array($correct, ['A', 'B', 'C', 'D'])) {
            $stmt = $pdo->prepare("UPDATE test_questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ?, explanation = ? WHERE id = ?");
            $stmt->execute([$qText, $optA, $optB, $optC, $optD, $correct, $exp, $qId]);
            setFlash('success', 'Question updated successfully!');
        }
        header("Location: test_series.php?test={$testId}");
        exit;
    }

    if ($action === 'delete_question') {
        $qId = (int)$_POST['question_id'];
        $testId = (int)$_POST['test_id'];
        if ($qId > 0) {
            $stmt = $pdo->prepare("DELETE FROM test_questions WHERE id = ?");
            $stmt->execute([$qId]);
            setFlash('success', 'Question deleted.');
        }
        header("Location: test_series.php?test={$testId}");
        exit;
    }

    if ($action === 'delete_test') {
        $testId = (int)$_POST['test_id'];
        if ($testId > 0) {
            $stmt = $pdo->prepare("DELETE FROM test_series WHERE id = ?");
            $stmt->execute([$testId]);
            setFlash('success', 'Test series deleted.');
        }
        header("Location: test_series.php");
        exit;
    }
}

// Fetch all subjects for dropdown
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY order_num ASC")->fetchAll();

// Fetch all test series
$tests = $pdo->query("SELECT t.*, s.name as subject_name, s.short_code, COUNT(q.id) as question_count 
                      FROM test_series t 
                      LEFT JOIN subjects s ON t.subject_id = s.id 
                      LEFT JOIN test_questions q ON t.id = q.test_series_id 
                      GROUP BY t.id 
                      ORDER BY t.id ASC")->fetchAll();

if (!$selectedTestId && !empty($tests)) {
    $selectedTestId = $tests[0]['id'];
}

// Fetch active test details & its questions
$activeTest = null;
$questions = [];
if ($selectedTestId) {
    foreach ($tests as $t) {
        if ($t['id'] == $selectedTestId) {
            $activeTest = $t;
            break;
        }
    }
    $stmtQ = $pdo->prepare("SELECT * FROM test_questions WHERE test_series_id = ? ORDER BY id ASC");
    $stmtQ->execute([$selectedTestId]);
    $questions = $stmtQ->fetchAll();
}

renderAdminHeader('Test Series Management', 'test_series', 'Test Series');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Subject-wise Test Series &amp; MCQs</h2>
            <p class="text-xs text-slate-400 mt-1">Create subject mock tests and manage multiple choice objective questions with explanations.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="document.getElementById('createTestModal').classList.remove('hidden')" 
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs shadow-sm transition">
                + Create New Test
            </button>
            <?php if ($activeTest): ?>
                <button onclick="document.getElementById('addQModal').classList.remove('hidden')" 
                        class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-lg shadow-orange-600/20 transition">
                    + Add Question
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Test Series Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        <?php foreach ($tests as $t): ?>
            <a href="test_series.php?test=<?= $t['id'] ?>" 
               class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $selectedTestId == $t['id'] ? 'bg-orange-600 text-white shadow-md shadow-orange-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white hover:bg-slate-800' ?>">
                <?= htmlspecialchars($t['short_code'] ?? 'Test') ?>: <?= htmlspecialchars($t['title']) ?> 
                <span class="ml-1 opacity-80">(<?= $t['question_count'] ?> Qs)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($activeTest): ?>
        <div class="bg-slate-900 rounded-3xl border border-slate-800 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-3 mb-6">
                <div>
                    <span class="inline-flex px-3 py-1 rounded-full bg-orange-500/10 text-orange-400 text-xs font-black mb-1">
                        <?= htmlspecialchars($activeTest['subject_name'] ?? 'General') ?>
                    </span>
                    <h3 class="text-base sm:text-lg font-black text-white"><?= htmlspecialchars($activeTest['title']) ?></h3>
                    <p class="text-xs text-slate-400 mt-1">Duration: <b><?= $activeTest['duration_minutes'] ?> mins</b> &bull; Questions: <b><?= count($questions) ?></b></p>
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="test_series.php" onsubmit="return confirm('Delete this entire test series and all its questions?');">
                        <input type="hidden" name="action" value="delete_test">
                        <input type="hidden" name="test_id" value="<?= $activeTest['id'] ?>">
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 font-bold text-xs hover:bg-rose-500/20 transition">
                            Delete Test Series
                        </button>
                    </form>
                </div>
            </div>

            <!-- Questions List -->
            <?php if (empty($questions)): ?>
                <div class="text-center py-10 text-slate-500">
                    <p class="text-xs italic mb-3">No questions added to this test series yet.</p>
                    <button onclick="document.getElementById('addQModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-orange-600 text-white font-bold text-xs">
                        + Add First Question
                    </button>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($questions as $idx => $q): ?>
                        <div class="border border-slate-800 rounded-2xl p-5 bg-slate-950/60 hover:border-slate-700 transition">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-orange-500/10 text-orange-400 font-black text-xs flex items-center justify-center shrink-0 mt-0.5">
                                        <?= $idx + 1 ?>
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-white text-xs sm:text-sm"><?= nl2br(htmlspecialchars($q['question_text'])) ?></h4>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button onclick="openEditQ(<?= htmlspecialchars(json_encode($q)) ?>)"
                                            class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs hover:bg-slate-700">
                                        Edit
                                    </button>
                                    <form method="POST" action="test_series.php?test=<?= $activeTest['id'] ?>" onsubmit="return confirm('Delete this question?');">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                        <input type="hidden" name="test_id" value="<?= $activeTest['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 font-bold text-xs hover:bg-rose-500/20">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

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
                                    <b class="text-amber-400">💡 Explanation:</b> <?= htmlspecialchars($q['explanation']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Test Modal -->
<div id="createTestModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Create New Test Series</h3>
            <button onclick="document.getElementById('createTestModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="test_series.php" class="space-y-4">
            <input type="hidden" name="action" value="create_test">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Subject</label>
                <select name="subject_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= $s['short_code'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Test Title</label>
                <input type="text" name="title" required placeholder="e.g. Operating System Mid-Term Practice Test"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Duration (Mins)</label>
                    <input type="number" name="duration_minutes" value="15" min="1" max="180" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Total Marks</label>
                    <input type="number" name="total_marks" value="10" min="1" max="500" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description of topics covered..."
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs"></textarea>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('createTestModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm shadow-md">Create Test</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Question Modal -->
<div id="addQModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Add Question to Test Series</h3>
            <button onclick="document.getElementById('addQModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="test_series.php?test=<?= $selectedTestId ?>" class="space-y-4">
            <input type="hidden" name="action" value="add_question">
            <input type="hidden" name="test_id" value="<?= $selectedTestId ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Question Text</label>
                <textarea name="question_text" rows="3" required placeholder="Type multiple choice question here..."
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-orange-500"></textarea>
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
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Answer Explanation (Optional)</label>
                <textarea name="explanation" rows="2" placeholder="Explain why the answer is correct..."
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:ring-1 focus:ring-orange-500"></textarea>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('addQModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm shadow-md">Save Question</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Question Modal -->
<div id="editQModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Edit Question</h3>
            <button onclick="document.getElementById('editQModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="test_series.php?test=<?= $selectedTestId ?>" class="space-y-4">
            <input type="hidden" name="action" value="edit_question">
            <input type="hidden" name="question_id" id="editQId">
            <input type="hidden" name="test_id" value="<?= $selectedTestId ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Question Text</label>
                <textarea name="question_text" id="editQText" rows="3" required
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-orange-500"></textarea>
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
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:ring-1 focus:ring-orange-500"></textarea>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('editQModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm shadow-md">Update Question</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditQ(q) {
    document.getElementById('editQId').value = q.id;
    document.getElementById('editQText').value = q.question_text;
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
