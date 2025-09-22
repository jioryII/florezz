<?php
require_once 'config.php';

class NotesSyncServer {
    private $pdo;
    private $lastCheck;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->lastCheck = time();
    }
    
    public function checkForUpdates() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM notes WHERE created_at > FROM_UNIXTIME(" . $this->lastCheck . ")");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $this->lastCheck = time();
                return true;
            }
            return false;
        } catch(Exception $e) {
            error_log("Error checking for updates: " . $e->getMessage());
            return false;
        }
    }
    
    public function getRecentNotes($since = null) {
        try {
            if ($since) {
                $stmt = $this->pdo->prepare("SELECT * FROM notes WHERE created_at > FROM_UNIXTIME(?) ORDER BY created_at DESC");
                $stmt->execute([$since]);
            } else {
                $stmt = $this->pdo->query("SELECT * FROM notes ORDER BY created_at DESC LIMIT 10");
            }
            
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($note) {
                return [
                    'id' => intval($note['id']),
                    'text' => $note['text'],
                    'timestamp' => date('d/m/Y H:i', strtotime($note['created_at'])),
                    'color' => $note['color'],
                    'rotation' => intval($note['rotation'])
                ];
            }, $notes);
        } catch(Exception $e) {
            error_log("Error getting recent notes: " . $e->getMessage());
            return [];
        }
    }
    
    public function getLastUpdate() {
        return $this->lastCheck;
    }
}

if (isset($_GET['check_updates'])) {
    $syncServer = new NotesSyncServer($pdo);
    $hasUpdates = $syncServer->checkForUpdates();
    
    header('Content-Type: application/json');
    echo json_encode(['hasUpdates' => $hasUpdates]);
}


if (isset($_GET['recent_notes'])) {
    $syncServer = new NotesSyncServer($pdo);
    $since = isset($_GET['since']) ? intval($_GET['since']) : null;
    $notes = $syncServer->getRecentNotes($since);
    
    header('Content-Type: application/json');
    echo json_encode($notes);
}
?>