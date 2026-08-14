<?php
// admin/practicals.php - Manage Practicals, Lab Codes, and DE Lab PDFs
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.");
}

$selectedLabId = isset($_GET['lab']) ? (int)$_GET['lab'] : 1;

// Handle Actions (Add/Edit Experiment, Upload DE PDF, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_experiment') {
        $labId = (int)$_POST['lab_id'];
        $title = trim($_POST['title']);
        $code = $_POST['code_content'] ?? '';
        $order = (int)($_POST['order_num'] ?? 1);
        $pdfUrl = '';

        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $upload = handlePdfUpload($_FILES['pdf_file'], 'practicals');
            if ($upload['success']) {
                $pdfUrl = $upload['url'];
            }
        } elseif (!empty($_POST['pdf_url'])) {
            $pdfUrl = trim($_POST['pdf_url']);
        }

        if (!empty($title) && $labId > 0) {
            $stmt = $pdo->prepare("INSERT INTO practical_experiments (practical_id, title, pdf_url, code_content, order_num) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$labId, $title, $pdfUrl, $code, $order]);
            setFlash('success', 'New lab experiment added successfully!');
        } else {
            setFlash('error', 'Please enter experiment title.');
        }
        header("Location: practicals.php?lab={$selectedLabId}");
        exit;
    }

    if ($action === 'edit_experiment') {
        $expId = (int)$_POST['exp_id'];
        $title = trim($_POST['title']);
        $code = $_POST['code_content'] ?? '';
        $order = (int)($_POST['order_num'] ?? 1);
        $pdfUrl = trim($_POST['pdf_url'] ?? '');

        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $upload = handlePdfUpload($_FILES['pdf_file'], 'practicals');
            if ($upload['success']) {
                $pdfUrl = $upload['url'];
            }
        }

        if ($expId > 0 && !empty($title)) {
            $stmt = $pdo->prepare("UPDATE practical_experiments SET title = ?, pdf_url = ?, code_content = ?, order_num = ? WHERE id = ?");
            $stmt->execute([$title, $pdfUrl, $code, $order, $expId]);
            setFlash('success', 'Lab experiment updated successfully!');
        }
        header("Location: practicals.php?lab={$selectedLabId}");
        exit;
    }

    if ($action === 'delete_experiment') {
        $expId = (int)$_POST['exp_id'];
        if ($expId > 0) {
            $stmt = $pdo->prepare("DELETE FROM practical_experiments WHERE id = ?");
            $stmt->execute([$expId]);
            setFlash('success', 'Experiment deleted.');
        }
        header("Location: practicals.php?lab={$selectedLabId}");
        exit;
    }
}

// Fetch all practical subjects
$labs = $pdo->query("SELECT * FROM practicals ORDER BY order_num ASC")->fetchAll();

// Get current lab details
$stmtCurLab = $pdo->prepare("SELECT * FROM practicals WHERE id = ?");
$stmtCurLab->execute([$selectedLabId]);
$curLab = $stmtCurLab->fetch() ?: ($labs[0] ?? null);

// Fetch experiments for current lab
$experiments = [];
if ($curLab) {
    $stmtExp = $pdo->prepare("SELECT * FROM practical_experiments WHERE practical_id = ? ORDER BY order_num ASC, id ASC");
    $stmtExp->execute([$curLab['id']]);
    $experiments = $stmtExp->fetchAll();
}

renderAdminHeader('Practicals & Lab Experiments', 'practicals', 'Practicals');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Practicals & Lab Experiments</h2>
            <p class="text-xs text-slate-400 mt-1">Manage practical experiment code snippets for Java, C & DSA, or upload experiment PDFs for DE Lab.</p>
        </div>
        <?php if ($curLab && $curLab['type'] !== 'contact'): ?>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/20 transition">
                <span>+</span> Add Experiment to <?= htmlspecialchars($curLab['language']) ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- Lab Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        <?php foreach ($labs as $lab): ?>
            <a href="practicals.php?lab=<?= $lab['id'] ?>" 
               class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $selectedLabId == $lab['id'] ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white hover:bg-slate-800' ?>">
                <?= htmlspecialchars($lab['subject_name']) ?> <span class="opacity-75">(<?= htmlspecialchars($lab['language']) ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($curLab['type'] === 'contact'): ?>
        <div class="bg-slate-900 rounded-3xl border border-slate-800 p-8 text-center max-w-2xl mx-auto shadow-sm">
            <span class="w-16 h-16 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center font-bold text-2xl mx-auto mb-4">💼</span>
            <h3 class="text-xl font-black text-white"><?= htmlspecialchars($curLab['subject_name']) ?></h3>
            <p class="text-xs text-slate-400 mt-2 leading-relaxed">This module is reserved for student internship inquiries and redirects students directly to the Contact section on the main platform.</p>
            <div class="mt-6 inline-flex px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 text-xs font-bold">
                Status: Active &amp; Linked to Contact Us
            </div>
        </div>
    <?php else: ?>
        <div class="bg-slate-900 rounded-3xl border border-slate-800 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white"><?= htmlspecialchars($curLab['subject_name']) ?></h3>
                    <p class="text-xs text-slate-400 mt-0.5">Type: <b class="uppercase text-sky-400"><?= htmlspecialchars($curLab['type']) ?></b> &bull; Total Experiments: <b><?= count($experiments) ?></b></p>
                </div>
            </div>

            <?php if (empty($experiments)): ?>
                <p class="text-center text-xs text-slate-500 py-8 italic">No experiments added for this lab yet. Click "+ Add Experiment" to create one.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($experiments as $idx => $exp): ?>
                        <div class="border border-slate-800 rounded-2xl p-4 bg-slate-950/60 hover:border-slate-700 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 font-black text-xs flex items-center justify-center">
                                        <?= $exp['order_num'] ?>
                                    </span>
                                    <h4 class="font-bold text-white text-xs sm:text-sm"><?= htmlspecialchars($exp['title']) ?></h4>
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php if (!empty($exp['pdf_url'])): ?>
                                        <a href="../<?= htmlspecialchars($exp['pdf_url']) ?>" target="_blank"
                                           class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs hover:bg-slate-700 transition">
                                            📄 View PDF
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="openEditExp(<?= htmlspecialchars(json_encode($exp)) ?>)"
                                            class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs hover:bg-slate-700 transition">
                                        Edit
                                    </button>
                                    <form method="POST" action="practicals.php?lab=<?= $selectedLabId ?>" onsubmit="return confirm('Delete this experiment?');">
                                        <input type="hidden" name="action" value="delete_experiment">
                                        <input type="hidden" name="exp_id" value="<?= $exp['id'] ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 font-bold text-xs hover:bg-rose-500/20 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <?php if (!empty($exp['code_content'])): ?>
                                <div class="mt-3 bg-slate-900 text-slate-200 rounded-xl p-3.5 text-xs font-mono overflow-x-auto max-h-36 border border-slate-800">
                                    <pre><code><?= htmlspecialchars($exp['code_content']) ?></code></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Experiment Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Add Experiment: <?= htmlspecialchars($curLab['subject_name']) ?></h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="practicals.php?lab=<?= $selectedLabId ?>" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add_experiment">
            <input type="hidden" name="lab_id" value="<?= $curLab['id'] ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Experiment Title</label>
                <input type="text" name="title" required placeholder="e.g. Experiment 1: Singly Linked List Implementation"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Order Number</label>
                <input type="number" name="order_num" value="<?= count($experiments) + 1 ?>" min="1" max="99" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-amber-500">
            </div>

            <?php if ($curLab['type'] === 'pdf'): ?>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Upload Experiment PDF</label>
                    <input type="file" name="pdf_file" accept="application/pdf"
                           class="w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500/10 file:text-amber-400 hover:file:bg-amber-500/20 cursor-pointer border border-slate-700 rounded-xl p-2 bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">OR PDF Path</label>
                    <input type="text" name="pdf_url" placeholder="pdfs/digital-electronics-experiment-1.pdf"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-amber-500">
                </div>
            <?php else: ?>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Experiment Source Code</label>
                    <textarea name="code_content" rows="8" placeholder="Paste C, Java, or SQL source code here..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-mono text-xs focus:ring-1 focus:ring-amber-500"></textarea>
                </div>
            <?php endif; ?>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm shadow-md">Save Experiment</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Experiment Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-black text-white">Edit Lab Experiment</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold flex items-center justify-center">✕</button>
        </div>
        <form method="POST" action="practicals.php?lab=<?= $selectedLabId ?>" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="edit_experiment">
            <input type="hidden" name="exp_id" id="editExpId">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Experiment Title</label>
                <input type="text" name="title" id="editExpTitle" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Order Number</label>
                <input type="number" name="order_num" id="editExpOrder" min="1" max="99" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-amber-500">
            </div>

            <?php if ($curLab['type'] === 'pdf'): ?>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Upload New PDF (Optional)</label>
                    <input type="file" name="pdf_file" accept="application/pdf"
                           class="w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500/10 file:text-amber-400 hover:file:bg-amber-500/20 cursor-pointer border border-slate-700 rounded-xl p-2 bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">PDF Path / URL</label>
                    <input type="text" name="pdf_url" id="editExpPdf"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:ring-1 focus:ring-amber-500">
                </div>
            <?php else: ?>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Experiment Source Code</label>
                    <textarea name="code_content" id="editExpCode" rows="8"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-mono text-xs focus:ring-1 focus:ring-amber-500"></textarea>
                </div>
            <?php endif; ?>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm shadow-md">Update Experiment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditExp(data) {
    document.getElementById('editExpId').value = data.id;
    document.getElementById('editExpTitle').value = data.title;
    document.getElementById('editExpOrder').value = data.order_num;
    if (document.getElementById('editExpPdf')) {
        document.getElementById('editExpPdf').value = data.pdf_url || '';
    }
    if (document.getElementById('editExpCode')) {
        document.getElementById('editExpCode').value = data.code_content || '';
    }
    document.getElementById('editModal').classList.remove('hidden');
}
</script>

<?php renderAdminFooter(); ?>
