<?php
session_start();
require_once '../connections/connections.php';
require_once '../components/cp_head.php';

$link = new_db_connection();
$ingredients_options = [];
if ($link) {
    $ingredients_query = "SELECT ingredient_id, name FROM ingredients ORDER BY name ASC";
    $ingredients_result = $link->query($ingredients_query);
    if ($ingredients_result) {
        while ($row = $ingredients_result->fetch_assoc()) {
            $ingredients_options[] = $row;
        }
    }
    $link->close();
}


$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';

include_once '../components/cp_intro_adicionar_receita.php';
?>