<?php
// api/unlock.php - API สำหรับปลดล็อค/รับคำขอปลดล็อค
header('Content-Type: application/json');
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true) ?? [];
$db = getDB();

// ===== GET: Client ดึงสถานะ (ว่าปลดล็อคหรือยัง) =====
if ($method === 'GET') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $computer = $db->prepare("SELECT * FROM computers WHERE ip_address = ?")->execute([$ip])
                ? ($stmt = $db->prepare("SELECT * FROM computers WHERE ip_address = ?")) && $stmt->execute([$ip]) ? $stmt->fetch() : null
                : null;
    $stmt = $db->prepare("SELECT * FROM computers WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $computer = $stmt->fetch();

    if (!$computer) {
        echo json_encode(['status' => 'unknown', 'message' => 'Computer not registered']);
        exit;
    }

    // อัปเดต last_seen
    $db->prepare("UPDATE computers SET last_seen = NOW() WHERE id = ?")->execute([$computer['id']]);

    echo json_encode([
        'status'      => $computer['status'],
        'computer_id' => $computer['id'],
        'name'        => $computer['name'],
    ]);
    exit;
}

// ===== POST =====
if ($method === 'POST') {
    $action = $data['action'] ?? '';

    // Client ส่งคำขอปลดล็อค
    if ($action === 'request_unlock') {
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $db->prepare("SELECT * FROM computers WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $computer = $stmt->fetch();

        if (!$computer) {
            echo json_encode(['success' => false, 'message' => 'Computer not found']);
            exit;
        }

        // อัปเดตสถานะเป็น locked
        $db->prepare("UPDATE computers SET status='locked', last_seen=NOW() WHERE id=?")->execute([$computer['id']]);

        // เช็คว่ามีคำขอ pending อยู่แล้วไหม
        $stmt = $db->prepare("SELECT id FROM unlock_requests WHERE computer_id=? AND status='pending'");
        $stmt->execute([$computer['id']]);
        if (!$stmt->fetch()) {
            $db->prepare("INSERT INTO unlock_requests (computer_id) VALUES (?)")->execute([$computer['id']]);
        }

        echo json_encode(['success' => true, 'message' => 'Request sent']);
        exit;
    }

    // Admin ปลดล็อคโดยตรง หรือตอบคำขอ
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // ตอบคำขอ unlock
    if ($action === 'approved' || $action === 'rejected') {
        $req_id = (int)($data['request_id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM unlock_requests WHERE id=?");
        $stmt->execute([$req_id]);
        $req = $stmt->fetch();

        if ($req) {
            $db->prepare("UPDATE unlock_requests SET status=?, processed_at=NOW(), processed_by=? WHERE id=?")
               ->execute([$action, $_SESSION['admin_id'], $req_id]);

            if ($action === 'approved') {
                // เริ่ม session
                $db->prepare("UPDATE computers SET status='unlocked' WHERE id=?")->execute([$req['computer_id']]);
                $db->prepare("INSERT INTO sessions (computer_id, unlocked_by) VALUES (?,?)")
                   ->execute([$req['computer_id'], $_SESSION['admin_id']]);
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // ปลดล็อคตรง
    if ($action === 'direct_unlock') {
        $cid = (int)($data['computer_id'] ?? 0);
        $db->prepare("UPDATE computers SET status='unlocked' WHERE id=?")->execute([$cid]);
        $db->prepare("INSERT INTO sessions (computer_id, unlocked_by) VALUES (?,?)")
           ->execute([$cid, $_SESSION['admin_id']]);
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
