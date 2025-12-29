<?php
require_once __DIR__ . '/../DataBaseManagement/DB.php';
header('Content-Type: application/json; charset=utf-8');

$db = DB::get();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$id = (int)($data['id'] ?? 0);
$qty = max(1, (int)($data['qty'] ?? 1));

if ($id <= 0) {
  echo json_encode(["success" => false, "error" => "Missing id"]);
  exit;
}

$db->begin_transaction();

try {
  // Bloquear fila
  $stmt = $db->prepare("SELECT available_stock FROM products WHERE product_id = ? FOR UPDATE");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();

  if (!$row || (int)$row['available_stock'] < $qty) {
    throw new Exception("Not enough stock");
  }

  // Reservar: available -= qty, reserved += qty
  $upd = $db->prepare("
    UPDATE products
    SET available_stock = available_stock - ?,
        reserved_stock = reserved_stock + ?
    WHERE product_id = ?
  ");
  $upd->bind_param("iii", $qty, $qty, $id);
  $upd->execute();
  $upd->close();

  $db->commit();
  echo json_encode(["success" => true, "reserved" => $qty]);

} catch (Exception $e) {
  $db->rollback();
  echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
