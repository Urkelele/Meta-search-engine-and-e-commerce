<?php
require_once __DIR__ . '/../DataBaseManagement/DB.php';
header('Content-Type: application/json; charset=utf-8');

$db = DB::get();
$data = json_decode(file_get_contents('php://input'), true);

$items = $data['items'] ?? [];
if (!is_array($items) || count($items) === 0) {
  echo json_encode(["success" => false, "error" => "No items"]);
  exit;
}

$db->begin_transaction();

try {
  foreach ($items as $it) {
    $id = (int)($it['id'] ?? 0);
    $qty = max(1, (int)($it['qty'] ?? 1));  

    // bloquear producto y comprobar reserved
    $stmt = $db->prepare("SELECT reserved_stock FROM products WHERE product_id = ? FOR UPDATE");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || (int)$row['reserved_stock'] < $qty) {
      throw new Exception("Not enough reserved stock for product $id");
    }

    // consumir reserva (vendido)
    $upd = $db->prepare("
      UPDATE products
      SET reserved_stock = reserved_stock - ?
      WHERE product_id = ?
    ");
    $upd->bind_param("ii", $qty, $id);
    $upd->execute();
    $upd->close();
  }

  $db->commit();
  echo json_encode(["success" => true]);

} catch (Exception $e) {
  $db->rollback();
  echo json_encode(["success" => false, "error" => $e->getMessage()]);
}