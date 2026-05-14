<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../inc/hero/rationale.php';

header('Content-Type: application/json');

// NOTE: Keep auth/CSRF hardening aligned with existing admin authentication conventions.

function sw_hero_rationale_json_error(string $error, int $statusCode = 400): void
{
    http_response_code($statusCode);
    echo json_encode(['ok' => false, 'error' => $error], JSON_UNESCAPED_SLASHES);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $itemId = (int)($_GET['item_id'] ?? 0);
    if ($itemId <= 0) {
        sw_hero_rationale_json_error('Invalid item_id');
    }

    try {
        $rationale = sw_hero_rationale_fetch_active($pdo, $itemId);
        echo json_encode([
            'ok' => true,
            'item_id' => $itemId,
            'rationale' => $rationale,
        ], JSON_UNESCAPED_SLASHES);
        exit;
    } catch (Throwable $e) {
        sw_hero_rationale_json_error('Failed to read rationale', 500);
    }
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '', true);

    if (!is_array($payload)) {
        sw_hero_rationale_json_error('Invalid JSON payload');
    }

    if (!isset($payload['itemId']) && isset($payload['item_id'])) {
        $payload['itemId'] = $payload['item_id'];
    }

    try {
        $itemId = (int)($payload['itemId'] ?? 0);
        $rationaleId = sw_hero_rationale_save($pdo, $payload);

        echo json_encode([
            'ok' => true,
            'item_id' => $itemId,
            'rationale_id' => $rationaleId,
            'message' => 'Rationale saved',
        ], JSON_UNESCAPED_SLASHES);
        exit;
    } catch (InvalidArgumentException $e) {
        sw_hero_rationale_json_error($e->getMessage(), 400);
    } catch (Throwable $e) {
        sw_hero_rationale_json_error('Failed to save rationale', 500);
    }
}

sw_hero_rationale_json_error('Method not allowed', 405);
