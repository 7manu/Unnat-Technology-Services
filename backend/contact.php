<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.html', true, 303);
    exit;
}

// Quietly accept bot submissions that fill the hidden honeypot field.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    header('Location: ../index.php', true, 303);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$mobile = trim((string) ($_POST['mobile'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$question = trim((string) ($_POST['question'] ?? ''));
$details = trim((string) ($_POST['details'] ?? ''));
$returnTo = (string) ($_POST['return_to'] ?? 'contact.html');
$allowedReturns = [
    'index.php#contact' => ['page' => 'index.php', 'fragment' => 'contact'],
    'index.html#contact' => ['page' => 'index.php', 'fragment' => 'contact'],
    'contact.html#contact-form' => ['page' => 'contact.html', 'fragment' => 'contact-form'],
    'contact.php#contact-form' => ['page' => 'contact.html', 'fragment' => 'contact-form'],
];
$returnLocation = $allowedReturns[$returnTo] ?? ['page' => 'contact.html', 'fragment' => 'contact-form'];

function inquiryRedirect(array $location, string $status): string
{
    return sprintf('../%s?inquiry=%s#%s', $location['page'], rawurlencode($status), $location['fragment']);
}

$isValid = $name !== ''
    && mb_strlen($name) <= 25
    && preg_match('/^[0-9]{10}$/', $mobile)
    && ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL))
    && mb_strlen($email) <= 50
    && $question !== ''
    && mb_strlen($question) <= 150
    && mb_strlen($details) <= 500;

if (!$isValid) {
    header('Location: ' . inquiryRedirect($returnLocation, 'invalid'), true, 303);
    exit;
}

try {
    require __DIR__ . '/_conn.php';
    $statement = $conn->prepare(
        'INSERT INTO `query` (`name`, `mobile`, `email`, `question`, `details`, `status`) VALUES (?, ?, ?, ?, ?, 0)'
    );
    $statement->bind_param('sssss', $name, $mobile, $email, $question, $details);
    $statement->execute();
    $statement->close();

    header('Location: ' . inquiryRedirect($returnLocation, 'sent'), true, 303);
    exit;
} catch (Throwable $exception) {
    error_log('UTS inquiry could not be stored: ' . $exception->getMessage());
    header('Location: ../contact.html?inquiry=error#contact-form', true, 303);
    exit;
}
