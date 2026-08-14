<?php
// api/get_tests.php - Returns list of Test Series or specific Test Questions
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(['error' => 'Database connection failed'], 500);
}

try {
    $testId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($testId) {
        // Fetch specific test details
        $stmt = $pdo->prepare("SELECT t.*, s.name as subject_name, s.short_code 
                               FROM test_series t 
                               LEFT JOIN subjects s ON t.subject_id = s.id 
                               WHERE t.id = ?");
        $stmt->execute([$testId]);
        $test = $stmt->fetch();

        if (!$test) {
            jsonResponse(['error' => 'Test series not found'], 404);
        }

        // Fetch questions without revealing correct answer
        $qStmt = $pdo->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d 
                                FROM test_questions 
                                WHERE test_series_id = ? 
                                ORDER BY id ASC");
        $qStmt->execute([$testId]);
        $questions = $qStmt->fetchAll();

        $test['questions'] = $questions;
        $test['total_questions'] = count($questions);

        jsonResponse($test);
    } else {
        // Fetch all test series
        $stmt = $pdo->query("SELECT t.*, s.name as subject_name, s.short_code, 
                                    COUNT(q.id) as question_count 
                             FROM test_series t 
                             LEFT JOIN subjects s ON t.subject_id = s.id 
                             LEFT JOIN test_questions q ON t.id = q.test_series_id 
                             GROUP BY t.id 
                             ORDER BY t.id ASC");
        $tests = $stmt->fetchAll();

        jsonResponse($tests);
    }

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
