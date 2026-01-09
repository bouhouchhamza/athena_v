<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/Reclamation.php';

class ReclamationRepository {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function save(Reclamation $reclamation): bool {
        $sql = "INSERT INTO reclamations (task_id, user_id, description, statut, created_at, resolved_at) 
                VALUES (:task_id, :user_id, :description, :statut, :created_at, :resolved_at)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':task_id' => $reclamation->getTaskId(),
                ':user_id' => $reclamation->getUserId(),
                ':description' => $reclamation->getDescription(),
                ':statut' => $reclamation->getStatut(),
                ':created_at' => $reclamation->getCreatedAt()->format('Y-m-d H:i:s'),
                ':resolved_at' => $reclamation->getResolvedAt() ? $reclamation->getResolvedAt()->format('Y-m-d H:i:s') : null
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to save reclamation: " . $e->getMessage());
        }
    }
    
    public function update(Reclamation $reclamation): bool {
        $sql = "UPDATE reclamations 
                SET task_id = :task_id, user_id = :user_id, description = :description, 
                    statut = :statut, resolved_at = :resolved_at 
                WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $reclamation->getId(),
                ':task_id' => $reclamation->getTaskId(),
                ':user_id' => $reclamation->getUserId(),
                ':description' => $reclamation->getDescription(),
                ':statut' => $reclamation->getStatut(),
                ':resolved_at' => $reclamation->getResolvedAt() ? $reclamation->getResolvedAt()->format('Y-m-d H:i:s') : null
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to update reclamation: " . $e->getMessage());
        }
    }
    
    public function findById(int $id): ?Reclamation {
        $sql = "SELECT * FROM reclamations WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            
            if (!$data) {
                return null;
            }
            
            return $this->hydrateReclamation($data);
        } catch (PDOException $e) {
            throw new Exception("Failed to find reclamation: " . $e->getMessage());
        }
    }
    
    public function findAll(): array {
        $sql = "SELECT * FROM reclamations ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->query($sql);
            $reclamations = [];
            
            while ($data = $stmt->fetch()) {
                $reclamations[] = $this->hydrateReclamation($data);
            }
            
            return $reclamations;
        } catch (PDOException $e) {
            throw new Exception("Failed to find all reclamations: " . $e->getMessage());
        }
    }
    
    public function findByTaskId(int $taskId): array {
        $sql = "SELECT * FROM reclamations WHERE task_id = :task_id ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':task_id' => $taskId]);
            $reclamations = [];
            
            while ($data = $stmt->fetch()) {
                $reclamations[] = $this->hydrateReclamation($data);
            }
            
            return $reclamations;
        } catch (PDOException $e) {
            throw new Exception("Failed to find reclamations by task: " . $e->getMessage());
        }
    }
    
    public function findByUserId(int $userId): array {
        $sql = "SELECT * FROM reclamations WHERE user_id = :user_id ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $reclamations = [];
            
            while ($data = $stmt->fetch()) {
                $reclamations[] = $this->hydrateReclamation($data);
            }
            
            return $reclamations;
        } catch (PDOException $e) {
            throw new Exception("Failed to find reclamations by user: " . $e->getMessage());
        }
    }
    
    public function findByStatut(string $statut): array {
        $sql = "SELECT * FROM reclamations WHERE statut = :statut ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':statut' => $statut]);
            $reclamations = [];
            
            while ($data = $stmt->fetch()) {
                $reclamations[] = $this->hydrateReclamation($data);
            }
            
            return $reclamations;
        } catch (PDOException $e) {
            throw new Exception("Failed to find reclamations by status: " . $e->getMessage());
        }
    }
    
    public function findOpen(): array {
        return $this->findByStatut('open');
    }
    
    public function findResolved(): array {
        return $this->findByStatut('resolved');
    }
    
    public function delete(int $id): bool {
        $sql = "DELETE FROM reclamations WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Failed to delete reclamation: " . $e->getMessage());
        }
    }
    
    private function hydrateReclamation(array $data): Reclamation {
        $reclamation = new Reclamation(
            $data['task_id'],
            $data['user_id'],
            $data['description'],
            $data['statut']
        );
        
        $reclamation->setId($data['id']);
        $reclamation->setCreatedAt(new DateTime($data['created_at']));
        
        if ($data['resolved_at']) {
            $reclamation->setResolvedAt(new DateTime($data['resolved_at']));
        }
        
        return $reclamation;
    }
}
