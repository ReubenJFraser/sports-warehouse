<?php
// admin/run_function.php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Only POST requests allowed.']);
    exit;
}

$action = $_POST['action'] ?? null;

if (!$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing action.']);
    exit;
}

// Define allowed functions
$allowed = [
    'deploy_cloudways',
    // You can add more actions here in future
];

if (!in_array($action, $allowed)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Action not permitted.']);
    exit;
}

// Execute the function
switch ($action) {
    case 'deploy_cloudways':
        // Shell out to your helper script. You could replace this with a custom script if needed.
        $cmd = 'powershell.exe -ExecutionPolicy Bypass -Command "Set-Location C:\\laragon\\www\\sports-warehouse-home-page; git add .; git commit -m \"Auto deploy from button\"; git push"';
        exec($cmd, $output, $code);

        echo json_encode([
            'success' => ($code === 0),
            'output' => $output,
            'exit_code' => $code,
        ]);
        exit;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown error.']);
        exit;
}



