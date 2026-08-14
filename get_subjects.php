<?php
// api/get_subjects.php - Returns subjects, units, and lectures
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

if (!$pdo) {
    // Fallback if DB not connected yet
    jsonResponse(['error' => 'Database connection failed'], 500);
}

try {
    // Fetch all subjects
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY order_num ASC, id ASC");
    $subjects = $stmt->fetchAll();

    // Fetch all units
    $stmtUnits = $pdo->query("SELECT * FROM units ORDER BY subject_id ASC, unit_number ASC");
    $allUnits = $stmtUnits->fetchAll();

    // Fetch all lectures
    $stmtLectures = $pdo->query("SELECT * FROM lectures ORDER BY unit_id ASC, order_num ASC, id ASC");
    $allLectures = $stmtLectures->fetchAll();

    // Group lectures by unit_id
    $lecturesByUnit = [];
    foreach ($allLectures as $lec) {
        $lecturesByUnit[$lec['unit_id']][] = [
            'id' => (string)$lec['id'],
            'title' => $lec['lecture_title'],
            'youtube' => $lec['youtube_url'],
            'order' => (int)$lec['order_num']
        ];
    }

    // Group units by subject_id
    $unitsBySubject = [];
    foreach ($allUnits as $unit) {
        $uId = $unit['id'];
        $sId = $unit['subject_id'];
        $uNum = $unit['unit_number'];

        $videos = $lecturesByUnit[$uId] ?? [];
        // If no videos yet, provide default fallback structure
        if (empty($videos)) {
            $videos = [
                ['id' => $sId . '-u' . $uNum . '-v1', 'title' => $unit['title'] . ' — Introduction', 'youtube' => 'https://www.youtube.com/embed/dQw4w9WgXcQ']
            ];
        }

        $unitsBySubject[$sId][] = [
            'id' => $sId . '-u' . $uNum,
            'db_id' => $uId,
            'unit_number' => $uNum,
            'label' => 'Unit ' . $uNum,
            'title' => $unit['title'],
            'notes' => 'Unit ' . $uNum . ' notes PDF',
            'pdf' => !empty($unit['notes_pdf_url']) ? $unit['notes_pdf_url'] : "pdfs/notes/{$sId}-unit-{$uNum}.pdf",
            'videos' => $videos
        ];
    }

    // Build final formatted array
    $result = [];
    foreach ($subjects as $subj) {
        $sId = $subj['id'];
        $result[] = [
            'id' => $sId,
            'short' => $subj['short_code'],
            'name' => $subj['name'],
            'units' => $unitsBySubject[$sId] ?? []
        ];
    }

    jsonResponse($result);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
