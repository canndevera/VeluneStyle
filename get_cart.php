<?php
session_start();
$cart = $_SESSION['cart'] ?? [];
$count = array_sum(array_column($cart, 'qty')) ?: 0;
$total = 0;
foreach ($cart as $item) {
    $total += (float)$item['price'] * (int)$item['qty'];
}
echo json_encode([
    'items' => array_values($cart),
    'count' => $count,
    'total' => round($total, 2)
]);
?>
