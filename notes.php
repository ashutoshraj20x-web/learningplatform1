<?php
// admin/notes.php - Manage Unit-wise PDF Notes
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.");
}

$selectedSubject = $_GET['subject'] ?? 'os';

// Handle Upload / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_notes_pdf') {
        $unitId = (int)$_POST['unit_id'];
        
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handlePdfUpload($_FILES['pdf_file'], 'notes');
            if ($uploadResult['success']) {
                $pdfPath = $uploadResult['url'];
                $stmt = $pdo->prepare("UPDATE units SET notes_pdf_url = ? WHERE id = ?");
                $stmt->execute([$pdfPath, $unitId]);
                setFlash('success', 'PDF Notes uploaded and linked successfully!');
            } else {
                setFlash('error', 'Upload failed: ' . $uploadResult['error']);
            }
        } elseif (!empty($_POST['custom_url'])) {
            $customUrl = trim($_POST['custom_url']);
            $stmt = $pdo->prepare("UPDATE units SET notes_pdf_url = ? WHERE id = ?");
            $stmt->execute([$customUrl, $unitId]);
            setFlash('success', 'PDF URL updated successfully!');
        } else {
            setFlash('error', 'Please select a PDF file or enter a valid URL.');
        }

        header("Location: notes.php?subject={$selectedSubject}");
        exit;
    }

    if ($action === 'clear_notes_pdf') {
        $unitId = (int)$_POST['unit_id'];
        if ($unitId > 0) {
            $stmt = $pdo->prepare("UPDATE units SET notes_pdf_url = '' WHERE id = ?");
            $stmt->execute([$unitId]);
            setFlash('success', 'PDF Notes unlinked from unit.');
        }
        header("Location: notes.php?subject={$selectedSubject}");
        exit;
    }
}

// Fetch subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY order_num ASC")->fetchAll();

// Fetch units for current subject
$stmtUnits = $pdo->prepare("SELECT * FROM units WHERE subject_id = ? ORDER BY unit_number ASC");
$stmtUnits->execute([$selectedSubject]);
$units = $stmtUnits->fetchAll();

renderAdminHeader('Unit Notes Management', 'notes', 'Notes');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Unit-wise Notes (PDF Uploads)</h2>
            <p class="text-xs text-slate-400 mt-1">Upload and manage official PDF study notes for each subject and unit.</p>
        </div>
    </div>

    <!-- Subject Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        <?php foreach ($subjects as $s): ?>
            <a href="notes.php?subject=<?= urlencode($s['id']) ?>" 
               class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $selectedSubject === $s['id'] ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white hover:bg-slate-800' ?>">
                <?= htmlspecialchars($s['short_code']) ?> &bull; <?= htmlspecialchars($s['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Units Notes Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($units as $u): 
            $hasPdf = !empty($u['notes_pdf_url']);
            $pdfUrl = $u['notes_pdf_url'] ?: "pdfs/notes/{$selectedSubject}-unit-{$u['unit_number']}.pdf";
        ?>
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-sm flex flex-col justify-between hover:border-slate-700 transition">
                <div>
                    <div class="flex justify-between items-center">
                        <span class="inline-flex px-2.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 text-xs font-black">Unit <?= $u['unit_number'] ?></span>
                        <span class="text-xs font-semibold <?= $hasPdf ? 'text-emerald-400' : 'text-slate-500' ?>">
                            <?= $hasPdf ? '✓ PDF Uploaded' : '• Default path' ?>
                        </span>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-white mt-3"><?= htmlspecialchars($u['title']) ?></h3>
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
                        <button onclick="openUploadModal(<?= $u['id'] ?>, 'Unit <?= $u['unit_number'] ?>: <?= addslashes(htmlspecialchars($u['title'])) ?>', '<?= addslashes(htmlspecialchars($u['notes_pdf_url'])) ?>')"
                                class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition">
                            📤 <?= $hasPdf ? 'Replace' : 'Upload' ?>
                        </button>
                    </div>

                    <?php if ($hasPdf): ?>
                        <form method="POST" action="notes.php?subject=<?= urlencode($selectedSubject) ?>" onsubmit="return confirm('Reset this unit to default PDF path?');" class="mt-1">
                            <input type="hidden" name="action" value="clear_notes_pdf">
                            <input type="hidden" name="unit_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="w-full text-center text-xs text-rose-400 hover:text-rose-300 font-medium py-1">
                                Reset to default path
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Upload Notes PDF Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white" id="uploadModalTitle">Upload Unit Notes PDF</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="notes.php?subject=<?= urlencode($selectedSubject) ?>" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="upload_notes_pdf">
            <input type="hidden" name="unit_id" id="uploadUnitId">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Choose PDF File from Computer</label>
                <input type="file" name="pdf_file" accept="application/pdf"
                       class="w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-500/10 file:text-emerald-400 hover:file:bg-emerald-500/20 cursor-pointer border border-slate-700 rounded-xl p-2 bg-slate-800">
                <small class="text-xs text-slate-500 mt-1 block">Maximum size: 50MB. Only PDF files supported.</small>
            </div>

            <div class="relative flex py-2 items-center">
                <div class="flex-grow border-t border-slate-800"></div>
                <span class="flex-shrink mx-4 text-xs font-bold uppercase text-slate-500">OR Enter Existing Path / URL</span>
                <div class="flex-grow border-t border-slate-800"></div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Custom Path or URL</label>
                <input type="text" name="custom_url" id="uploadCustomUrl" placeholder="e.g. pdfs/notes/os-unit-1.pdf or https://..."
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-emerald-500">
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-md">Save & Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal(unitId, title, currentUrl) {
    document.getElementById('uploadUnitId').value = unitId;
    document.getElementById('uploadModalTitle').innerText = 'Upload Notes: ' + title;
    document.getElementById('uploadCustomUrl').value = currentUrl;
    document.getElementById('uploadModal').classList.remove('hidden');
}
</script>

<?php renderAdminFooter(); ?>
