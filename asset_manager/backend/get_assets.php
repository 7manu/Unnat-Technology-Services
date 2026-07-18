<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit("Unauthorized");
}
include '_conn.php';

$search = $_GET['q'] ?? '';
$sort = $_GET['sort'] ?? 'latest';
$search = "%$search%";

$order = "ORDER BY id DESC"; // default

switch ($sort) {
    case "oldest": $order = "ORDER BY id ASC"; break;
    case "low-high": $order = "ORDER BY value_per_month ASC"; break;
    case "high-low": $order = "ORDER BY value_per_month DESC"; break;
    case "az": $order = "ORDER BY name ASC"; break;
    case "za": $order = "ORDER BY name DESC"; break;
}

$min = $_GET['min'] ?? null;
$max = $_GET['max'] ?? null;
$dateFrom = $_GET['dateFrom'] ?? null;
$dateTo = $_GET['dateTo'] ?? null;

$query = "SELECT id, name, value_per_month, start_date, details FROM assets WHERE name LIKE ?";
$params = ["s", &$search];

// Add optional filters
if ($min !== null && $min !== "") {
    $query .= " AND value_per_month >= ?";
    $params[0] .= "d";
    $params[] = &$min;
}
if ($max !== null && $max !== "") {
    $query .= " AND value_per_month <= ?";
    $params[0] .= "d";
    $params[] = &$max;
}
if ($dateFrom !== null && $dateFrom !== "") {
    $query .= " AND start_date >= ?";
    $params[0] .= "s";
    $params[] = &$dateFrom;
}
if ($dateTo !== null && $dateTo !== "") {
    $query .= " AND start_date <= ?";
    $params[0] .= "s";
    $params[] = &$dateTo;
}

$query .= " $order LIMIT 50";

$stmt = $conn->prepare($query);
call_user_func_array([$stmt, 'bind_param'], $params);
$stmt->execute();
$result = $stmt->get_result();


// Output HTML
if ($result->num_rows > 0) {
    echo '<table class="table table-bordered table-striped">';
    echo '<thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Asset Name</th>
                <th>Monthly Value (₹)</th>
                <th>Start Date</th>
                <th>Details</th>
                <th>Action</th>
            </tr>
          </thead><tbody>';
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>₹" . number_format($row['value_per_month'], 2) . "</td>
                <td>" . htmlspecialchars($row['start_date']) . "</td>
                <td>" . nl2br(htmlspecialchars($row['details'])) . "</td>
                <td>
                    <button class='btn btn-sm btn-warning me-1 editBtn' data-id='{$row['id']}'>Edit</button>
                    <button class='btn btn-sm btn-danger deleteBtn' data-id='{$row['id']}'>Delete</button>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
} else {
    echo '<div class="alert alert-info">No assets found.</div>';
}
?>
