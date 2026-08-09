<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function expenseResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function validExpenseDate(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

if (!isset($_SESSION['user']['id'])) {
    expenseResponse(['success' => false, 'error' => 'Your session has expired. Please sign in again.'], 401);
}

$csrfToken = (string) ($_GET['csrf_token'] ?? $_POST['csrf_token'] ?? '');
$storedToken = (string) ($_SESSION['expense_csrf'] ?? '');
if ($storedToken === '' || !hash_equals($storedToken, $csrfToken)) {
    expenseResponse(['success' => false, 'error' => 'The request could not be verified. Refresh the page and try again.'], 403);
}

$action = (string) ($_GET['action'] ?? $_POST['action'] ?? '');
$allowedActions = ['fetch', 'add', 'edit', 'delete'];
if (!in_array($action, $allowedActions, true)) {
    expenseResponse(['success' => false, 'error' => 'Unsupported expense action.'], 400);
}

if ($action !== 'fetch' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    expenseResponse(['success' => false, 'error' => 'This action requires a POST request.'], 405);
}

try {
    require __DIR__ . '/_conn.php';

    // Existing installations did not include an expense migration. Creating the
    // table here makes the module usable immediately and is safe on later calls.
    $conn->query(
        "CREATE TABLE IF NOT EXISTS `expenses` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `description` VARCHAR(255) NOT NULL,
            `amount` DECIMAL(12,2) NOT NULL,
            `date` DATE NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_expenses_user_date` (`user_id`, `date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $userId = (int) $_SESSION['user']['id'];

    if ($action === 'fetch') {
        $month = (string) ($_GET['month'] ?? date('Y-m'));
        $search = trim((string) ($_GET['search'] ?? ''));

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) || mb_strlen($search) > 100) {
            expenseResponse(['success' => false, 'error' => 'Choose a valid month and search term.'], 422);
        }

        $monthStart = $month . '-01';
        $monthEnd = (new DateTimeImmutable($monthStart))->modify('first day of next month')->format('Y-m-d');

        if ($search !== '') {
            $searchTerm = '%' . $search . '%';
            $statement = $conn->prepare(
                'SELECT `id`, `description`, `amount`, `date` FROM `expenses`
                 WHERE `user_id` = ? AND `date` >= ? AND `date` < ? AND `description` LIKE ?
                 ORDER BY `date` DESC, `id` DESC'
            );
            $statement->bind_param('isss', $userId, $monthStart, $monthEnd, $searchTerm);
        } else {
            $statement = $conn->prepare(
                'SELECT `id`, `description`, `amount`, `date` FROM `expenses`
                 WHERE `user_id` = ? AND `date` >= ? AND `date` < ?
                 ORDER BY `date` DESC, `id` DESC'
            );
            $statement->bind_param('iss', $userId, $monthStart, $monthEnd);
        }

        $statement->execute();
        $statement->bind_result($id, $description, $amount, $date);
        $items = [];
        $total = 0.0;
        while ($statement->fetch()) {
            $numericAmount = (float) $amount;
            $items[] = [
                'id' => (int) $id,
                'description' => (string) $description,
                'amount' => number_format($numericAmount, 2, '.', ''),
                'date' => (string) $date,
            ];
            $total += $numericAmount;
        }
        $statement->close();

        expenseResponse([
            'success' => true,
            'items' => $items,
            'summary' => [
                'total' => round($total, 2),
                'count' => count($items),
                'average' => count($items) > 0 ? round($total / count($items), 2) : 0,
            ],
        ]);
    }

    if ($action === 'add') {
        $description = trim((string) ($_POST['description'] ?? ''));
        $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $date = (string) ($_POST['date'] ?? '');

        if ($description === '' || mb_strlen($description) > 255 || $amount === false || $amount <= 0 || $amount > 9999999999.99 || !validExpenseDate($date)) {
            expenseResponse(['success' => false, 'error' => 'Enter a description, positive amount and valid date.'], 422);
        }

        $statement = $conn->prepare('INSERT INTO `expenses` (`user_id`, `description`, `amount`, `date`) VALUES (?, ?, ?, ?)');
        $statement->bind_param('isds', $userId, $description, $amount, $date);
        $statement->execute();
        $newId = $statement->insert_id;
        $statement->close();

        expenseResponse(['success' => true, 'message' => 'Expense added.', 'id' => $newId], 201);
    }

    $expenseId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$expenseId) {
        expenseResponse(['success' => false, 'error' => 'Choose a valid expense record.'], 422);
    }

    if ($action === 'delete') {
        $statement = $conn->prepare('DELETE FROM `expenses` WHERE `id` = ? AND `user_id` = ?');
        $statement->bind_param('ii', $expenseId, $userId);
        $statement->execute();
        $changed = $statement->affected_rows;
        $statement->close();

        if ($changed !== 1) {
            expenseResponse(['success' => false, 'error' => 'Expense not found or already deleted.'], 404);
        }
        expenseResponse(['success' => true, 'message' => 'Expense deleted.']);
    }

    $description = trim((string) ($_POST['description'] ?? ''));
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $date = (string) ($_POST['date'] ?? '');

    if ($description === '' || mb_strlen($description) > 255 || $amount === false || $amount <= 0 || $amount > 9999999999.99 || !validExpenseDate($date)) {
        expenseResponse(['success' => false, 'error' => 'Enter a description, positive amount and valid date.'], 422);
    }

    $statement = $conn->prepare('UPDATE `expenses` SET `description` = ?, `amount` = ?, `date` = ? WHERE `id` = ? AND `user_id` = ?');
    $statement->bind_param('sdsii', $description, $amount, $date, $expenseId, $userId);
    $statement->execute();
    $matched = $statement->affected_rows;
    $statement->close();

    expenseResponse(['success' => true, 'message' => $matched === 1 ? 'Expense updated.' : 'No changes were needed.']);
} catch (Throwable $exception) {
    error_log('Expense module error: ' . $exception->getMessage());
    expenseResponse([
        'success' => false,
        'error' => 'The expense service is unavailable. Confirm that the database user can create and update tables.',
    ], 500);
}
