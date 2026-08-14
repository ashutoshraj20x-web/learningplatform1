<?php
// api/get_pyqs.php - Returns subjects and their year-wise PYQs
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(['error' => 'Database connection failed'], 500);
}

try {
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY order_num ASC, id ASC");
    $subjects = $stmt->fetchAll();

    $stmtPyqs = $pdo->query("SELECT * FROM pyqs ORDER BY subject_id ASC, year ASC");
    $allPyqs = $stmtPyqs->fetchAll();

    $pyqsBySubject = [];
    foreach ($allPyqs as $pq) {
        $sId = $pq['subject_id'];
        $pyqsBySubject[$sId][] = [
            'id' => (int)$pq['id'],
            'year' => (int)$pq['year'],
            'pdf' => !empty($pq['pdf_url']) ? $pq['pdf_url'] : "pdfs/pyqs/{$sId}-{$pq['year']}.pdf"
        ];
    }

    $result = [];
    foreach ($subjects as $s) {
        $sId = $s['id'];
        $years = $pyqsBySubject[$sId] ?? [];
        
        // If empty in db, generate default years
        if (empty($years)) {
            $defaultYears = [2021, 2022, 2023, 2024, 2025, 2026];
            foreach ($defaultYears as $yr) {
                $years[] = [
                    'id' => 0,
                    'year' => $yr,
                    'pdf' => "pdfs/pyqs/{$sId}-{$yr}.pdf"
                ];
            }
        }

        $result[] = [
            'id' => $sId,
            'subject' => $s['name'],
            'years' => $years
        ];
    }

    jsonResponse($result);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
