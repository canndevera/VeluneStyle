<?php
session_start();
require_once 'products.php';

$data = $_POST;
if (empty($data)) {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
}

$id = isset($data['id']) ? (int)$data['id'] : 0;
$qty = isset($data['qty']) ? max(1, (int)$data['qty']) : 1;

$product = findProduct($id);
if (!$product) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'Producto no encontrado']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id] = [
        'id' => $id,
        'name' => $product['name'],
        'price' => $product['price'],
        'qty' => $qty,
        'img' => $product['img']
    ];
} else {
    $_SESSION['cart'][$id]['qty'] += $qty;
}

$cart = $_SESSION['cart'] ?? [];
$count = array_sum(array_column($cart, 'qty')) ?: 0;
$total = 0;
foreach ($cart as $item) {
    $total += (float)$item['price'] * (int)$item['qty'];
}

echo json_encode([
    'ok' => true,
    'count' => $count,
    'cartCount' => $count,
    'total' => round($total, 2)
]);
?>
