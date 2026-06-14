<?php
// api/control.php - Admin สั่ง Shutdown เครื่อง Client
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$db = getDB();
$cid = (int)($data['computer_id'] ?? 0);
$cmd = $data['command'] ?? '';

$stmt = $db->prepare("SELECT * FROM computers WHERE id=?");
$stmt->execute([$cid]);
$computer = $stmt->fetch();

if (!$computer) {
    echo json_encode(['success' => false, 'message' => 'Computer not found']);
    exit;
}

if ($cmd === 'shutdown') {
    // บันทึกคำสั่ง shutdown ลง DB (Client จะ poll มาดู)
    $db->prepare("UPDATE computers SET status='offline' WHERE id=?")->execute([$cid]);
    // ในระบบจริงอาจใช้ WOL หรือ Agent บน Client
    // บันทึกคำสั่งลง command queue
    // ตัวอย่างนี้ใช้ flag ใน DB
    $db->prepare("INSERT INTO web_logs (computer_id, url, title) VALUES (?,'system://shutdown','[SYSTEM] Shutdown command issued')")
       ->execute([$cid]);
    echo json_encode(['success' => true, 'message' => "Shutdown command sent to {$computer['name']}"]);

} elseif ($cmd === 'lock') {
    $db->prepare("UPDATE computers SET status='locked' WHERE id=?")->execute([$cid]);
    echo json_encode(['success' => true]);

} else {
    echo json_encode(['success' => false, 'message' => 'Unknown command']);
}
