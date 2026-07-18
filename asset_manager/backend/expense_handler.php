<?php
session_start();
header('Content-Type: application/json');
require '_conn.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action']);
    exit;
}

if ($action === 'fetch') {
    $month = $_GET['month'] ?? date('Y-m');
    $search = trim($_GET['search'] ?? '');

    $uid = $_SESSION['user']['id'];
    $data = [];
    $total = 0;

if ($search !== '') {
    $search = "%{$search}%";
    $stmt = $conn->prepare("SELECT id, description, amount, date FROM expenses WHERE user_id=? AND DATE_FORMAT(date, '%Y-%m')=? AND description LIKE ? ORDER BY date DESC, id DESC");
    $stmt->bind_param("iss", $uid, $month, $search);
} else {
    $stmt = $conn->prepare("SELECT id, description, amount, date FROM expenses WHERE user_id=? AND DATE_FORMAT(date, '%Y-%m')=? ORDER BY date DESC, id DESC");
    $stmt->bind_param("is", $uid, $month);
}

$stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
        $total += $row['amount'];
    }

    echo json_encode(['items' => $data, 'total' => $total]);

} elseif ($action === 'add') {

    if (!isset($_POST['description']) || !isset($_POST['amount'])) {
        echo json_encode(['error' => 'Missing fields']);
        exit;
    }

    $desc = trim($_POST['description']);
    $amt = floatval($_POST['amount']);
    $uid = $_SESSION['user']['id'];
    $now = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO expenses (user_id, description, amount, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $uid, $desc, $amt, $now);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Expense added']);
}
// DELETE handler
elseif ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    $uid = $_SESSION['user']['id'];

    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $uid);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Expense deleted']);
    
// EDIT handler
} elseif ($action === 'edit') {
    $id = $_POST['id'] ?? 0;
    $desc = trim($_POST['description'] ?? '');
    $amt = floatval($_POST['amount'] ?? 0);
    $uid = $_SESSION['user']['id'];

    $stmt = $conn->prepare("UPDATE expenses SET description=?, amount=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sdii", $desc, $amt, $id, $uid);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Expense updated']);
}

?>
