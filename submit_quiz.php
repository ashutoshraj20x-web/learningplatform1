<?php
// api/submit_quiz.php - Validates submitted answers and calculates score
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(['error' => 'Database connection failed'], 500);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['type']) || empty($input['id'])) {
    jsonResponse(['error' => 'Invalid submission data'], 400);
}

$type = $input['type']; // 'test' or 'contest'
$quizId = (int)$input['id'];
$userAnswers = $input['answers'] ?? []; // Map of question_id => 'A'|'B'|'C'|'D'

try {
    if ($type === 'test') {
        $stmt = $pdo->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation 
                               FROM test_questions 
                               WHERE test_series_id = ? 
                               ORDER BY id ASC");
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT id, question_text, code_snippet, option_a, option_b, option_c, option_d, correct_option, explanation 
                               FROM contest_questions 
                               WHERE contest_id = ? 
                               ORDER BY id ASC");
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();
    }

    $totalQuestions = count($questions);
    $correctCount = 0;
    $incorrectCount = 0;
    $unansweredCount = 0;
    $results = [];

    foreach ($questions as $q) {
        $qId = $q['id'];
        $submitted = isset($userAnswers[$qId]) ? strtoupper(trim($userAnswers[$qId])) : null;
        $correct = strtoupper(trim($q['correct_option']));
        $isCorrect = false;

        if ($submitted === null || $submitted === '') {
            $unansweredCount++;
        } elseif ($submitted === $correct) {
            $correctCount++;
            $isCorrect = true;
        } else {
            $incorrectCount++;
        }

        $results[] = [
            'id' => $qId,
            'question' => $q['question_text'],
            'code_snippet' => $q['code_snippet'] ?? null,
            'options' => [
                'A' => $q['option_a'],
                'B' => $q['option_b'],
                'C' => $q['option_c'],
                'D' => $q['option_d'],
            ],
            'user_answer' => $submitted,
            'correct_answer' => $correct,
            'is_correct' => $isCorrect,
            'explanation' => $q['explanation'] ?? ''
        ];
    }

    $scorePercentage = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;

    jsonResponse([
        'success' => true,
        'quiz_id' => $quizId,
        'type' => $type,
        'total_questions' => $totalQuestions,
        'correct_count' => $correctCount,
        'incorrect_count' => $incorrectCount,
        'unanswered_count' => $unansweredCount,
        'score_percentage' => $scorePercentage,
        'details' => $results
    ]);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
