<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/Log.php';
class LogRepository {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function save(Log $log): bool {
        $sql = "INSERT INTO logs (user_id, action, entity_type, entity_id, description, created_at) 
                VALUES (:user_id, :action, :entity_type, :entity_id, :description, :created_at)";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $log->getUserId(),
                ':action' => $log->getAction(),
                ':entity_type' => $log->getEntityType(),
                ':entity_id' => $log->getEntityId(),
                ':description' => $log->getDescription(),
                ':created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to save log: " . $e->getMessage());
        }
    }
    public function findById(int $id): ?Log {
        $sql = "SELECT * FROM logs WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            return $this->hydrateLog($data);
        } catch (PDOException $e) {
            throw new Exception("Failed to find log: " . $e->getMessage());
        }
    }
    public function findAll(): array {
        $sql = "SELECT * FROM logs ORDER BY created_at DESC";
        try {
            $stmt = $this->db->query($sql);
            $logs = [];
            while ($data = $stmt->fetch()) {
                $logs[] = $this->hydrateLog($data);
            }
            return $logs;
        } catch (PDOException $e) {
            throw new Exception("Failed to find all logs: " . $e->getMessage());
        }
    }
    public function findByUserId(int $userId): array {
        $sql = "SELECT * FROM logs WHERE user_id = :user_id ORDER BY created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $logs = [];
            while ($data = $stmt->fetch()) {
                $logs[] = $this->hydrateLog($data);
            }
            return $logs;
        } catch (PDOException $e) {
            throw new Exception("Failed to find logs by user: " . $e->getMessage());
        }
    }
    public function findByEntityType(string $entityType): array {
        $sql = "SELECT * FROM logs WHERE entity_type = :entity_type ORDER BY created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':entity_type' => $entityType]);
            $logs = [];
            while ($data = $stmt->fetch()) {
                $logs[] = $this->hydrateLog($data);
            }
            return $logs;
        } catch (PDOException $e) {
            throw new Exception("Failed to find logs by entity type: " . $e->getMessage());
        }
    }
    public function findRecent(int $limit = 50): array {
        $sql = "SELECT * FROM logs ORDER BY created_at DESC LIMIT :limit";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $logs = [];
            while ($data = $stmt->fetch()) {
                $logs[] = $this->hydrateLog($data);
            }
            return $logs;
        } catch (PDOException $e) {
            throw new Exception("Failed to find recent logs: " . $e->getMessage());
        }
    }
    private function hydrateLog(array $data): Log {
        $log = new Log(
            $data['user_id'],
            $data['action'],
            $data['entity_type'],
            $data['entity_id'],
            $data['description']
        );
        $log->setId($data['id']);
        $log->setCreatedAt(new DateTime($data['created_at']));
        return $log;
    }
}
