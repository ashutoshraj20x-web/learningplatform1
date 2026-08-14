<?php
// api/get_notes.php - Returns subjects and their unit-wise notes
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(['error' => 'Database connection failed'], 500);
}

try {
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY order_num ASC, id ASC");
    $subjects = $stmt->fetchAll();

    $stmtUnits = $pdo->query("SELECT * FROM units ORDER BY subject_id ASC, unit_number ASC");
    $units = $stmtUnits->fetchAll();

    $unitsBySubject = [];
    foreach ($units as $u) {
        $sId = $u['subject_id'];
        $uNum = $u['unit_number'];
        $pdfUrl = !empty($u['notes_pdf_url']) ? $u['notes_pdf_url'] : "pdfs/notes/{$sId}-unit-{$uNum}.pdf";

        $unitsBySubject[$sId][] = [
            'id' => $sId . '-u' . $uNum,
            'db_id' => $u['id'],
            'unit_number' => $uNum,
            'label' => 'Unit ' . $uNum,
            'title' => $u['title'],
            'notes' => 'Unit ' . $uNum . ' notes PDF',
            'pdf' => $pdfUrl
        ];
    }

    $result = [];
    foreach ($subjects as $s) {
        $sId = $s['id'];
        $result[] = [
            'id' => $sId,
            'short' => $s['short_code'],
            'name' => $s['name'],
            'units' => $unitsBySubject[$sId] ?? []
        ];
    }

    jsonResponse($result);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
