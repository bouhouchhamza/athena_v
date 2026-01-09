<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/Tache.php';

class TacheRepository {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function save(Tache $tache): bool {
        $sql = "INSERT INTO taches (sprint_id, titre, description, statut, assigne_a, created_by, created_at, updated_at) 
                VALUES (:sprint_id, :titre, :description, :statut, :assigne_a, :created_by, :created_at, :updated_at)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':sprint_id' => $tache->getSprintId(),
                ':titre' => $tache->getTitre(),
                ':description' => $tache->getDescription(),
                ':statut' => $tache->getStatut(),
                ':assigne_a' => $tache->getAssigneA(),
                ':created_by' => $tache->getCreatedBy(),
                ':created_at' => $tache->getCreatedAt()->format('Y-m-d H:i:s'),
                ':updated_at' => $tache->getUpdatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to save task: " . $e->getMessage());
        }
    }
    
    public function update(Tache $tache): bool {
        $sql = "UPDATE taches 
                SET sprint_id = :sprint_id, titre = :titre, description = :description, 
                    statut = :statut, assigne_a = :assigne_a, updated_at = :updated_at 
                WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $tache->getId(),
                ':sprint_id' => $tache->getSprintId(),
                ':titre' => $tache->getTitre(),
                ':description' => $tache->getDescription(),
                ':statut' => $tache->getStatut(),
                ':assigne_a' => $tache->getAssigneA(),
                ':updated_at' => $tache->getUpdatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to update task: " . $e->getMessage());
        }
    }
    
    public function findById(int $id): ?Tache {
        $sql = "SELECT * FROM taches WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            
            if (!$data) {
                return null;
            }
            
            return $this->hydrateTache($data);
        } catch (PDOException $e) {
            throw new Exception("Failed to find task: " . $e->getMessage());
        }
    }
    
    public function findBySprintId(int $sprintId): array {
        $sql = "SELECT * FROM taches WHERE sprint_id = :sprint_id ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sprint_id' => $sprintId]);
            $taches = [];
            
            while ($data = $stmt->fetch()) {
                $taches[] = $this->hydrateTache($data);
            }
            
            return $taches;
        } catch (PDOException $e) {
            throw new Exception("Failed to find tasks by sprint: " . $e->getMessage());
        }
    }
    
    public function findByAssignee(int $assigneeId): array {
        $sql = "SELECT * FROM taches WHERE assigne_a = :assigne_a ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':assigne_a' => $assigneeId]);
            $taches = [];
            
            while ($data = $stmt->fetch()) {
                $taches[] = $this->hydrateTache($data);
            }
            
            return $taches;
        } catch (PDOException $e) {
            throw new Exception("Failed to find tasks by assignee: " . $e->getMessage());
        }
    }
    
    public function findAll(): array {
        $sql = "SELECT * FROM taches ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->query($sql);
            $taches = [];
            
            while ($data = $stmt->fetch()) {
                $taches[] = $this->hydrateTache($data);
            }
            
            return $taches;
        } catch (PDOException $e) {
            throw new Exception("Failed to find all tasks: " . $e->getMessage());
        }
    }
    
    public function delete(int $id): bool {
        $sql = "DELETE FROM taches WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Failed to delete task: " . $e->getMessage());
        }
    }
    
    private function hydrateTache(array $data): Tache {
        $tache = new Tache(
            $data['sprint_id'],
            $data['titre'],
            $data['description'],
            $data['statut'],
            $data['assigne_a'],
            $data['created_by']
        );
        
        $tache->setId($data['id']);
        $tache->setCreatedAt(new DateTime($data['created_at']));
        $tache->setUpdatedAt(new DateTime($data['updated_at']));
        
        return $tache;
    }
}
