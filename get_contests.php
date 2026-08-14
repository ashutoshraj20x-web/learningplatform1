<?php
// api/get_contests.php - Returns list of Coding Contests or specific Contest Questions
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(['error' => 'Database connection failed'], 500);
}

try {
    $contestId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($contestId) {
        // Fetch specific contest details
        $stmt = $pdo->prepare("SELECT * FROM coding_contests WHERE id = ?");
        $stmt->execute([$contestId]);
        $contest = $stmt->fetch();

        if (!$contest) {
            jsonResponse(['error' => 'Coding contest not found'], 404);
        }

        // Fetch questions without revealing correct answer
        $qStmt = $pdo->prepare("SELECT id, question_text, code_snippet, option_a, option_b, option_c, option_d 
                                FROM contest_questions 
                                WHERE contest_id = ? 
                                ORDER BY id ASC");
        $qStmt->execute([$contestId]);
        $questions = $qStmt->fetchAll();

        $contest['questions'] = $questions;
        $contest['total_questions'] = count($questions);

        jsonResponse($contest);
    } else {
        // Fetch all contests
        $stmt = $pdo->query("SELECT c.*, COUNT(q.id) as question_count 
                             FROM coding_contests c 
                             LEFT JOIN contest_questions q ON c.id = q.contest_id 
                             GROUP BY c.id 
                             ORDER BY c.id ASC");
        $contests = $stmt->fetchAll();

        jsonResponse($contests);
    }

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
