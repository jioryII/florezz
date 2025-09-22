<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        try {

            $stmt = $pdo->query("SELECT * FROM notes ORDER BY created_at DESC");
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            

            $formattedNotes = array_map(function($note) {
                return [
                    'id' => intval($note['id']),
                    'text' => $note['text'],
                    'timestamp' => date('d/m/Y H:i', strtotime($note['created_at'])),
                    'color' => $note['color'],
                    'rotation' => intval($note['rotation'])
                ];
            }, $notes);
            
            echo json_encode($formattedNotes);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener notas: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        try {
            // Agregar nueva nota
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['text']) || empty(trim($input['text']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Texto requerido']);
                exit;
            }
            
            $colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7', 'color-8'];
            $color = $colors[array_rand($colors)];
            $rotation = rand(-8, 8);
            
            $stmt = $pdo->prepare("INSERT INTO notes (text, color, rotation) VALUES (?, ?, ?)");
            $stmt->execute([trim($input['text']), $color, $rotation]);
            
            $newId = $pdo->lastInsertId();
            
            echo json_encode([
                'id' => intval($newId),
                'text' => trim($input['text']),
                'timestamp' => date('d/m/Y H:i'),
                'color' => $color,
                'rotation' => $rotation
            ]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear nota: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        try {

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
            $result = $stmt->execute([$input['id']]);
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al eliminar']);
            }
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar nota: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>