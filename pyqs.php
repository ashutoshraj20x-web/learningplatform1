<?php
// admin/pyqs.php - Manage Subject-wise & Year-wise PYQ PDFs
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.");
}

$selectedSubject = $_GET['subject'] ?? 'os';

// Handle Actions (Add, Upload/Replace, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_pyq') {
        $subjectId = $_POST['subject_id'];
        $year = (int)$_POST['year'];
        $pdfUrl = '';

        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $upload = handlePdfUpload($_FILES['pdf_file'], 'pyqs');
            if ($upload['success']) {
                $pdfUrl = $upload['url'];
            }
        } elseif (!empty($_POST['custom_url'])) {
            $pdfUrl = trim($_POST['custom_url']);
        } else {
            $pdfUrl = "pdfs/pyqs/{$subjectId}-{$year}.pdf";
        }

        if (!empty($subjectId) && $year >= 2000) {
            $stmt = $pdo->prepare("INSERT INTO pyqs (subject_id, year, pdf_url) VALUES (?, ?, ?)");
            $stmt->execute([$subjectId, $year, $pdfUrl]);
            setFlash('success', "PYQ for year {$year} added successfully!");
        } else {
            setFlash('error', 'Please enter a valid year.');
        }
        header("Location: pyqs.php?subject={$selectedSubject}");
        exit;
    }

    if ($action === 'upload_pyq_pdf') {
        $pyqId = (int)$_POST['pyq_id'];
        $pdfUrl = '';

        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $upload = handlePdfUpload($_FILES['pdf_file'], 'pyqs');
            if ($upload['success']) {
                $pdfUrl = $upload['url'];
            } else {
                setFlash('error', 'Upload failed: ' . $upload['error']);
                header("Location: pyqs.php?subject={$selectedSubject}");
                exit;
            }
        } elseif (!empty($_POST['custom_url'])) {
            $pdfUrl = trim($_POST['custom_url']);
        }

        if ($pyqId > 0 && !empty($pdfUrl)) {
            $stmt = $pdo->prepare("UPDATE pyqs SET pdf_url = ? WHERE id = ?");
            $stmt->execute([$pdfUrl, $pyqId]);
            setFlash('success', 'PYQ PDF updated successfully!');
        }
        header("Location: pyqs.php?subject={$selectedSubject}");
        exit;
    }

    if ($action === 'delete_pyq') {
        $pyqId = (int)$_POST['pyq_id'];
        if ($pyqId > 0) {
            $stmt = $pdo->prepare("DELETE FROM pyqs WHERE id = ?");
            $stmt->execute([$pyqId]);
            setFlash('success', 'PYQ deleted successfully.');
        }
        header("Location: pyqs.php?subject={$selectedSubject}");
        exit;
    }
}

// Fetch subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY order_num ASC")->fetchAll();

// Fetch PYQs for selected subject
$stmtPyqs = $pdo->prepare("SELECT * FROM pyqs WHERE subject_id = ? ORDER BY year DESC");
$stmtPyqs->execute([$selectedSubject]);
$pyqs = $stmtPyqs->fetchAll();

renderAdminHeader('Previous Year Questions Management', 'pyqs', 'PYQs');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Previous Year Questions (PYQs)</h2>
            <p class="text-xs text-slate-400 mt-1">Upload and manage subject-wise PYQ exam papers (2021 – 2026).</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-lg shadow-violet-600/20 transition">
            <span>+</span> Add New PYQ Year
        </button>
    </div>

    <!-- Subject Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        <?php foreach ($subjects as $s): ?>
            <a href="pyqs.php?subject=<?= urlencode($s['id']) ?>" 
               class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $selectedSubject === $s['id'] ? 'bg-violet-600 text-white shadow-md shadow-violet-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white hover:bg-slate-800' ?>">
                <?= htmlspecialchars($s['short_code']) ?> &bull; <?= htmlspecialchars($s['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- PYQs Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php if (empty($pyqs)): ?>
            <div class="col-span-full bg-slate-900 rounded-3xl border border-slate-800 p-8 text-center text-slate-500 italic text-xs">
                No PYQs added for this subject yet. Click "+ Add New PYQ Year" above.
            </div>
        <?php else: ?>
            <?php foreach ($pyqs as $pq): 
                $pdfUrl = $pq['pdf_url'];
            ?>
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-sm flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex justify-between items-center">
                            <span class="inline-flex px-3 py-1 rounded-full bg-violet-500/10 text-violet-400 text-xs font-black">
                                Year <?= $pq['year'] ?>
                            </span>
                            <span class="text-xs font-semibold text-slate-500">PDF Document</span>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-white mt-3">PYQ Paper &bull; <?= $pq['year'] ?></h3>
                        <p class="text-xs text-slate-400 font-mono mt-2 truncate bg-slate-950 p-2.5 rounded-xl border border-slate-800" title="<?= htmlspecialchars($pdfUrl) ?>">
                            📄 <?= htmlspecialchars($pdfUrl) ?>
                        </p>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-800 flex flex-col gap-2">
                        <div class="flex gap-2">
                            <a href="../<?= htmlspecialchars($pdfUrl) ?>" target="_blank"
                               class="flex-1 text-center py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition border border-slate-700">
                                👁 View PDF
                            </a>
                            <button onclick="openUploadPyq(<?= $pq['id'] ?>, <?= $pq['year'] ?>, '<?= addslashes(htmlspecialchars($pq['pdf_url'])) ?>')"
                                    class="flex-1 py-2 px-3 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-md shadow-violet-600/20 transition">
                                📤 Replace
                            </button>
                        </div>
                        <form method="POST" action="pyqs.php?subject=<?= urlencode($selectedSubject) ?>" onsubmit="return confirm('Delete this year PYQ?');">
                            <input type="hidden" name="action" value="delete_pyq">
                            <input type="hidden" name="pyq_id" value="<?= $pq['id'] ?>">
                            <button type="submit" class="w-full text-center text-xs text-rose-400 hover:text-rose-300 font-medium py-1">
                                Delete PYQ
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add PYQ Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Add PYQ for <?= htmlspecialchars($selectedSubject) ?></h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="pyqs.php?subject=<?= urlencode($selectedSubject) ?>" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add_pyq">
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($selectedSubject) ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Exam Year</label>
                <input type="number" name="year" value="<?= date('Y') ?>" min="2010" max="2040" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-violet-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Upload PDF File</label>
                <input type="file" name="pdf_file" accept="application/pdf"
                       class="w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-violet-500/10 file:text-violet-400 hover:file:bg-violet-500/20 cursor-pointer border border-slate-700 rounded-xl p-2 bg-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">OR Custom Path / URL</label>
                <input type="text" name="custom_url" placeholder="pdfs/pyqs/<?= htmlspecialchars($selectedSubject) ?>-2026.pdf"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-violet-500">
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-sm shadow-md">Add PYQ</button>
            </div>
        </form>
    </div>
</div>

<!-- Replace PYQ Modal -->
<div id="replaceModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white" id="replaceModalTitle">Replace PYQ PDF</h3>
            <button onclick="document.getElementById('replaceModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="pyqs.php?subject=<?= urlencode($selectedSubject) ?>" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="upload_pyq_pdf">
            <input type="hidden" name="pyq_id" id="replacePyqId">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Choose New PDF File</label>
                <input type="file" name="pdf_file" accept="application/pdf"
                       class="w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-violet-500/10 file:text-violet-400 hover:file:bg-violet-500/20 cursor-pointer border border-slate-700 rounded-xl p-2 bg-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">OR Custom Path / URL</label>
                <input type="text" name="custom_url" id="replaceCustomUrl"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-violet-500">
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('replaceModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-sm shadow-md">Save PDF</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadPyq(pyqId, year, currentUrl) {
    document.getElementById('replacePyqId').value = pyqId;
    document.getElementById('replaceModalTitle').innerText = 'Replace PYQ for Year ' + year;
    document.getElementById('replaceCustomUrl').value = currentUrl;
    document.getElementById('replaceModal').classList.remove('hidden');
}
</script>

<?php renderAdminFooter(); ?>
