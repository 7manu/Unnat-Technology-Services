<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit("Unauthorized");
}
include '_conn.php';

$data = [
    'totalMonthly' => 0,
    'perDay' => 0,
    'mostProfitable' => ['name' => '-', 'value' => 0],
    'leastProfitable' => ['name' => '-', 'value' => 0]
];

// Total Monthly
$res = $conn->query("SELECT SUM(value_per_month) as total FROM assets");
$row = $res->fetch_assoc();
$data['totalMonthly'] = round($row['total'], 2);
$data['perDay'] = round($row['total'] / 30, 2);

// Most profitable
$res = $conn->query("SELECT name, value_per_month FROM assets ORDER BY value_per_month DESC LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $data['mostProfitable'] = ['name' => $row['name'], 'value' => round($row['value_per_month'], 2)];
}

// Least profitable
$res = $conn->query("SELECT name, value_per_month FROM assets ORDER BY value_per_month ASC LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $data['leastProfitable'] = ['name' => $row['name'], 'value' => round($row['value_per_month'], 2)];
}

echo json_encode($data);
?>
