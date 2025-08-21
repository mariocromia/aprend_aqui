<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Simple file-based storage for camera scenes
$dataFile = 'data/camera_scenes.json';

// Ensure data directory exists
if (!is_dir('data')) {
    mkdir('data', 0755, true);
}

// Initialize file if it doesn't exist
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // Get all scenes or specific scene
        $scenes = json_decode(file_get_contents($dataFile), true);
        
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $scene = array_filter($scenes, fn($s) => $s['id'] == $id);
            echo json_encode(array_values($scene)[0] ?? null);
        } else {
            echo json_encode($scenes);
        }
        break;

    case 'POST':
        // Save new scene or update existing
        $scenes = json_decode(file_get_contents($dataFile), true);
        
        if (!isset($input['name']) || !isset($input['waypoints'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields: name, waypoints']);
            break;
        }
        
        $scene = [
            'id' => $input['id'] ?? uniqid(),
            'name' => $input['name'],
            'waypoints' => $input['waypoints'],
            'created_at' => $input['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Check if updating existing scene
        $existingIndex = array_search($scene['id'], array_column($scenes, 'id'));
        
        if ($existingIndex !== false) {
            $scenes[$existingIndex] = $scene;
        } else {
            $scenes[] = $scene;
        }
        
        file_put_contents($dataFile, json_encode($scenes, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'scene' => $scene]);
        break;

    case 'DELETE':
        // Delete scene
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Scene ID required']);
            break;
        }
        
        $scenes = json_decode(file_get_contents($dataFile), true);
        $id = $_GET['id'];
        $scenes = array_filter($scenes, fn($s) => $s['id'] != $id);
        
        file_put_contents($dataFile, json_encode(array_values($scenes), JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>