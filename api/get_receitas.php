<?php
require_once '../connections/connections.php';

header('Content-Type: application/json');

$conn = new_db_connection();
$query = $_GET['query'] ?? '';
$search = '%' . ltrim($query, '@') . '%';

if (substr($query, 0, 1) === '@') {
    // 🔍 Pesquisa por utilizador
    $sql = "SELECT user_id AS id, username AS title, profile_image AS image_url FROM users WHERE username LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search);
} else {
    // 🔍 Pesquisa por receita ou ingrediente
    $sql = "
        SELECT DISTINCT r.recipe_id AS id, r.title, r.image_url
        FROM recipes r
        LEFT JOIN recipe_ingredients ri ON r.recipe_id = ri.ref_recipe_id
        LEFT JOIN ingredients i ON ri.ref_ingredient_id = i.ingredient_id
        WHERE r.ref_status_id = 2 AND (
            r.title LIKE ? OR i.name LIKE ?)
        LIMIT 10
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search, $search);
}

$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'image_url' => $row['image_url']
    ];
}

echo json_encode($items);
$stmt->close();
$conn->close();
