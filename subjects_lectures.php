<?php
// admin/subjects_lectures.php - Manage Unit-wise Lectures
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.");
}

$selectedSubject = $_GET['subject'] ?? 'os';
$selectedUnit = isset($_GET['unit']) ? (int)$_GET['unit'] : null;

// Handle Actions (Add, Edit, Delete Lecture)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_lecture') {
        $unitId = (int)$_POST['unit_id'];
        $title = trim($_POST['title']);
        $youtube = trim($_POST['youtube_url']);
        $order = (int)($_POST['order_num'] ?? 1);

        if (strpos($youtube, 'watch?v=') !== false) {
            $youtube = preg_replace('/watch\?v=([a-zA-Z0-9_-]+)/', 'embed/$1', $youtube);
        } elseif (strpos($youtube, 'youtu.be/') !== false) {
            $youtube = preg_replace('/youtu\.be\/([a-zA-Z0-9_-]+)/', 'www.youtube.com/embed/$1', $youtube);
            if (strpos($youtube, 'http') === false) {
                $youtube = 'https://' . $youtube;
            }
        }

        if (!empty($title) && !empty($youtube) && $unitId > 0) {
            $stmt = $pdo->prepare("INSERT INTO lectures (unit_id, lecture_title, youtube_url, order_num) VALUES (?, ?, ?, ?)");
            $stmt->execute([$unitId, $title, $youtube, $order]);
            setFlash('success', 'New lecture video added successfully!');
        } else {
            setFlash('error', 'Please fill in all lecture fields.');
        }
        header("Location: subjects_lectures.php?subject={$selectedSubject}" . ($selectedUnit ? "&unit={$selectedUnit}" : ''));
        exit;
    }

    if ($action === 'edit_lecture') {
        $lecId = (int)$_POST['lecture_id'];
        $title = trim($_POST['title']);
        $youtube = trim($_POST['youtube_url']);
        $order = (int)($_POST['order_num'] ?? 1);

        if (strpos($youtube, 'watch?v=') !== false) {
            $youtube = preg_replace('/watch\?v=([a-zA-Z0-9_-]+)/', 'embed/$1', $youtube);
        } elseif (strpos($youtube, 'youtu.be/') !== false) {
            $youtube = preg_replace('/youtu\.be\/([a-zA-Z0-9_-]+)/', 'www.youtube.com/embed/$1', $youtube);
            if (strpos($youtube, 'http') === false) {
                $youtube = 'https://' . $youtube;
            }
        }

        if ($lecId > 0 && !empty($title) && !empty($youtube)) {
            $stmt = $pdo->prepare("UPDATE lectures SET lecture_title = ?, youtube_url = ?, order_num = ? WHERE id = ?");
            $stmt->execute([$title, $youtube, $order, $lecId]);
            setFlash('success', 'Lecture updated successfully!');
        }
        header("Location: subjects_lectures.php?subject={$selectedSubject}" . ($selectedUnit ? "&unit={$selectedUnit}" : ''));
        exit;
    }

    if ($action === 'delete_lecture') {
        $lecId = (int)$_POST['lecture_id'];
        if ($lecId > 0) {
            $stmt = $pdo->prepare("DELETE FROM lectures WHERE id = ?");
            $stmt->execute([$lecId]);
            setFlash('success', 'Lecture deleted successfully!');
        }
        header("Location: subjects_lectures.php?subject={$selectedSubject}" . ($selectedUnit ? "&unit={$selectedUnit}" : ''));
        exit;
    }
}

// Fetch all subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY order_num ASC")->fetchAll();

// Fetch units for current subject
$stmtUnits = $pdo->prepare("SELECT * FROM units WHERE subject_id = ? ORDER BY unit_number ASC");
$stmtUnits->execute([$selectedSubject]);
$units = $stmtUnits->fetchAll();

// Fetch lectures for all units in this subject
$unitIds = array_column($units, 'id');
$lecturesByUnit = [];
if (!empty($unitIds)) {
    $inClause = implode(',', array_fill(0, count($unitIds), '?'));
    $stmtLectures = $pdo->prepare("SELECT * FROM lectures WHERE unit_id IN ($inClause) ORDER BY order_num ASC, id ASC");
    $stmtLectures->execute($unitIds);
    $allLectures = $stmtLectures->fetchAll();
    foreach ($allLectures as $lec) {
        $lecturesByUnit[$lec['unit_id']][] = $lec;
    }
}

renderAdminHeader('Unit Lectures Management', 'lectures', 'Lectures');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Unit-wise Video Lectures</h2>
            <p class="text-xs text-slate-400 mt-1">Manage YouTube video lecture links, titles, and ordering under each course unit.</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-lg shadow-sky-600/20 transition">
            <span>+</span> Add Video Lecture
        </button>
    </div>

    <!-- Subject Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        <?php foreach ($subjects as $s): ?>
            <a href="subjects_lectures.php?subject=<?= urlencode($s['id']) ?>" 
               class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $selectedSubject === $s['id'] ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white hover:bg-slate-800' ?>">
                <?= htmlspecialchars($s['short_code']) ?> &bull; <?= htmlspecialchars($s['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Units & Lectures List -->
    <div class="space-y-5">
        <?php foreach ($units as $u): 
            $unitLecs = $lecturesByUnit[$u['id']] ?? [];
        ?>
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-2">
                    <div>
                        <span class="inline-flex px-2.5 py-0.5 rounded-md bg-sky-500/10 text-sky-400 text-xs font-black">Unit <?= $u['unit_number'] ?></span>
                        <h3 class="text-sm sm:text-base font-bold text-white mt-1"><?= htmlspecialchars($u['title']) ?></h3>
                    </div>
                    <button onclick="openAddForUnit(<?= $u['id'] ?>, 'Unit <?= $u['unit_number'] ?> — <?= addslashes(htmlspecialchars($u['title'])) ?>')"
                            class="text-xs font-bold text-sky-400 hover:text-sky-300 self-start sm:self-auto px-3 py-1.5 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 transition">
                        + Add Lecture to Unit <?= $u['unit_number'] ?>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <?php if (empty($unitLecs)): ?>
                        <p class="text-xs text-slate-500 italic py-2">No video lectures added for this unit yet.</p>
                    <?php else: ?>
                        <?php foreach ($unitLecs as $idx => $lec): ?>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 gap-3">
                                <div class="flex items-start gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5"><?= $lec['order_num'] ?></span>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-bold text-white truncate"><?= htmlspecialchars($lec['lecture_title']) ?></h4>
                                        <a href="<?= htmlspecialchars($lec['youtube_url']) ?>" target="_blank" class="text-xs text-sky-400 hover:underline truncate block mt-0.5 font-mono">
                                            <?= htmlspecialchars($lec['youtube_url']) ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                    <button onclick="openEditModal(<?= $lec['id'] ?>, '<?= addslashes(htmlspecialchars($lec['lecture_title'])) ?>', '<?= addslashes(htmlspecialchars($lec['youtube_url'])) ?>', <?= $lec['order_num'] ?>)"
                                            class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 font-bold text-xs transition">
                                        Edit
                                    </button>
                                    <form method="POST" action="subjects_lectures.php?subject=<?= urlencode($selectedSubject) ?>" onsubmit="return confirm('Are you sure you want to delete this lecture?');">
                                        <input type="hidden" name="action" value="delete_lecture">
                                        <input type="hidden" name="lecture_id" value="<?= $lec['id'] ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 hover:bg-rose-500/20 font-bold text-xs transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Lecture Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white" id="addModalTitle">Add Video Lecture</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="subjects_lectures.php?subject=<?= urlencode($selectedSubject) ?>" class="space-y-4">
            <input type="hidden" name="action" value="add_lecture">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Target Unit</label>
                <select name="unit_id" id="addUnitSelect" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm font-medium focus:ring-1 focus:ring-sky-500">
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>">Unit <?= $u['unit_number'] ?>: <?= htmlspecialchars($u['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Lecture Title</label>
                <input type="text" name="title" required placeholder="e.g. Introduction to Process Scheduling"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">YouTube URL / Embed Link</label>
                <input type="text" name="youtube_url" required placeholder="https://www.youtube.com/watch?v=... or embed url"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
                <small class="text-xs text-slate-500 mt-1 block">Standard YouTube links will automatically be converted to embed format.</small>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Order Number</label>
                <input type="number" name="order_num" value="1" min="1" max="99" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
            </div>
            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md">Save Lecture</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Lecture Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Edit Lecture</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="subjects_lectures.php?subject=<?= urlencode($selectedSubject) ?>" class="space-y-4">
            <input type="hidden" name="action" value="edit_lecture">
            <input type="hidden" name="lecture_id" id="editLectureId">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Lecture Title</label>
                <input type="text" name="title" id="editLectureTitle" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">YouTube URL / Embed Link</label>
                <input type="text" name="youtube_url" id="editLectureUrl" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Order Number</label>
                <input type="number" name="order_num" id="editLectureOrder" min="1" max="99" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-sky-500">
            </div>
            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md">Update Lecture</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddForUnit(unitId, unitTitle) {
    document.getElementById('addUnitSelect').value = unitId;
    document.getElementById('addModalTitle').innerText = 'Add Lecture to ' + unitTitle;
    document.getElementById('addModal').classList.remove('hidden');
}

function openEditModal(id, title, url, order) {
    document.getElementById('editLectureId').value = id;
    document.getElementById('editLectureTitle').value = title;
    document.getElementById('editLectureUrl').value = url;
    document.getElementById('editLectureOrder').value = order;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>

<?php renderAdminFooter(); ?>
