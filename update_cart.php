<?php
session_start();
require_once 'products.php';

$data = $_POST;
if (empty($data)) {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
}

$id = isset($data['id']) ? (int)$data['id'] : 0;
$qty = isset($data['qty']) ? (int)$data['qty'] : null;

if (!isset($_SESSION['cart'][$id])) {
    echo json_encode(['ok'=>false,'error'=>'No existe en el carrito']);
    exit;
}

if ($qty === null) {
    unset($_SESSION['cart'][$id]);
} else {
    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
    } else {
        $_SESSION['cart'][$id]['qty'] = $qty;
    }
}

$cart = $_SESSION['cart'] ?? [];
$count = array_sum(array_column($cart, 'qty')) ?: 0;
$total = 0;
foreach ($cart as $item) {
    $total += (float)$item['price'] * (int)$item['qty'];
}
echo json_encode(['ok'=>true,'count'=>$count,'total'=>round($total,2)]);
?>
