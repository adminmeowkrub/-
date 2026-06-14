<?php
// api/log.php - Client ส่ง Log มาเก็บ
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$ip = $_SERVER['REMOTE_ADDR'];
$db = getDB();

// หา computer จาก IP
$stmt = $db->prepare("SELECT * FROM computers WHERE ip_address = ?");
$stmt->execute([$ip]);
$computer = $stmt->fetch();

if (!$computer) {
    echo json_encode(['success' => false, 'message' => 'Unknown computer']);
    exit;
}

$cid = $computer['id'];
$type = $data['type'] ?? '';

// หา session ปัจจุบัน
$stmt = $db->prepare("SELECT id FROM sessions WHERE computer_id=? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1");
$stmt->execute([$cid]);
$session = $stmt->fetch();
$sid = $session ? $session['id'] : null;

if ($type === 'web') {
    // บันทึก web log
    $url   = substr($data['url'] ?? '', 0, 500);
    $title = substr($data['title'] ?? '', 0, 200);
    if ($url) {
        $db->prepare("INSERT INTO web_logs (computer_id, session_id, url, title) VALUES (?,?,?,?)")
           ->execute([$cid, $sid, $url, $title]);
    }
    echo json_encode(['success' => true]);

} elseif ($type === 'app_start') {
    // บันทึกแอปเปิด
    $app  = substr($data['app_name'] ?? '', 0, 200);
    $proc = substr($data['process_name'] ?? '', 0, 100);
    if ($app) {
        $db->prepare("INSERT INTO app_logs (computer_id, session_id, app_name, process_name) VALUES (?,?,?,?)")
           ->execute([$cid, $sid, $app, $proc]);
        echo json_encode(['success' => true, 'log_id' => $db->lastInsertId()]);
    } else {
        echo json_encode(['success' => false]);
    }

} elseif ($type === 'app_end') {
    // บันทึกแอปปิด
    $log_id = (int)($data['log_id'] ?? 0);
    $db->prepare("UPDATE app_logs SET ended_at=NOW() WHERE id=? AND computer_id=?")->execute([$log_id, $cid]);
    echo json_encode(['success' => true]);

} elseif ($type === 'heartbeat') {
    // Client บอกว่ายังออนไลน์
    $db->prepare("UPDATE computers SET last_seen=NOW(), status=IF(status='offline','idle',status) WHERE id=?")
       ->execute([$cid]);
    echo json_encode(['success' => true]);

} elseif ($type === 'shutdown') {
    // Client แจ้งว่ากำลัง shutdown - ปิด session
    if ($sid) {
        $db->prepare("UPDATE sessions SET ended_at=NOW(), duration_minutes=TIMESTAMPDIFF(MINUTE,started_at,NOW()) WHERE id=?")
           ->execute([$sid]);
    }
    $db->prepare("UPDATE computers SET status='offline' WHERE id=?")->execute([$cid]);
    echo json_encode(['success' => true]);

} else {
    echo json_encode(['success' => false, 'message' => 'Unknown log type']);
}
