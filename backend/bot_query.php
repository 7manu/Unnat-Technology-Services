<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function botResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    botResponse(['success' => false, 'error' => 'This endpoint accepts POST requests only.'], 405);
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

// Honeypot field for automated spam submissions.
if (trim((string) ($payload['website'] ?? '')) !== '') {
    botResponse(['success' => true, 'message' => 'Your inquiry has been received.']);
}

$lastSubmission = (int) ($_SESSION['last_bot_query_at'] ?? 0);
if ($lastSubmission > 0 && time() - $lastSubmission < 15) {
    botResponse(['success' => false, 'error' => 'Please wait a moment before sending another inquiry.'], 429);
}

$name = trim((string) ($payload['name'] ?? ''));
$mobile = preg_replace('/\D+/', '', (string) ($payload['mobile'] ?? ''));
$project = trim((string) ($payload['project'] ?? ''));
$query = trim((string) ($payload['query'] ?? ''));

$allowedProjects = ['Web Platform', 'Business Software', 'Automation', 'AI Solution', 'Mobile App', 'Other'];
$isValid = $name !== ''
    && mb_strlen($name) <= 25
    && preg_match('/^[0-9]{10}$/', $mobile)
    && in_array($project, $allowedProjects, true)
    && mb_strlen($query) >= 10
    && mb_strlen($query) <= 420;

if (!$isValid) {
    botResponse(['success' => false, 'error' => 'Please provide a valid name, 10-digit mobile number, project type and query.'], 422);
}

$question = sprintf('[%s] %s', $project, mb_substr($query, 0, 115));
$details = mb_substr(
    "Submitted through the UTS AI Assistant.\nProject type: {$project}\nCustomer query: {$query}",
    0,
    500
);

try {
    require __DIR__ . '/_conn.php';
    $email = '';
    $statement = $conn->prepare(
        'INSERT INTO `query` (`name`, `mobile`, `email`, `question`, `details`, `status`) VALUES (?, ?, ?, ?, ?, 0)'
    );
    $statement->bind_param('sssss', $name, $mobile, $email, $question, $details);
    $statement->execute();
    $reference = (int) $statement->insert_id;
    $statement->close();
    $_SESSION['last_bot_query_at'] = time();

    botResponse([
        'success' => true,
        'message' => 'Your project inquiry has been raised successfully.',
        'reference' => $reference,
    ], 201);
} catch (Throwable $exception) {
    error_log('UTS assistant inquiry error: ' . $exception->getMessage());
    botResponse([
        'success' => false,
        'error' => 'The assistant could not save your inquiry. Please use WhatsApp or the contact form instead.',
    ], 500);
}
