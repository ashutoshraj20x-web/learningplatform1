<?php
// api/get_practicals.php - Returns practicals and experiment details
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(['error' => 'Database connection failed'], 500);
}

try {
    $stmt = $pdo->query("SELECT * FROM practicals ORDER BY order_num ASC, id ASC");
    $practicals = $stmt->fetchAll();

    $stmtExp = $pdo->query("SELECT * FROM practical_experiments ORDER BY practical_id ASC, order_num ASC, id ASC");
    $allExp = $stmtExp->fetchAll();

    $expByPractical = [];
    foreach ($allExp as $exp) {
        $pId = $exp['practical_id'];
        $expByPractical[$pId][] = [
            'id' => (int)$exp['id'],
            'title' => $exp['title'],
            'pdf' => $exp['pdf_url'] ?? '',
            'code' => $exp['code_content'] ?? ''
        ];
    }

    $result = [];
    foreach ($practicals as $p) {
        $pId = $p['id'];
        $result[] = [
            'id' => (int)$pId,
            'subject' => $p['subject_name'],
            'language' => $p['language'],
            'type' => $p['type'],
            'experiments' => $expByPractical[$pId] ?? []
        ];
    }

    jsonResponse($result);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
