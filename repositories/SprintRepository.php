<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/Sprint.php';
class SprintRepository {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function save(Sprint $sprint): bool {
        $sql = "INSERT INTO sprints (projet_id, nom, description, date_debut, date_fin, statut, created_at) 
                VALUES (:projet_id, :nom, :description, :date_debut, :date_fin, :statut, :created_at)";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':projet_id' => $sprint->getProjetId(),
                ':nom' => $sprint->getNom(),
                ':description' => $sprint->getDescription(),
                ':date_debut' => $sprint->getDateDebut() ? $sprint->getDateDebut()->format('Y-m-d') : null,
                ':date_fin' => $sprint->getDateFin() ? $sprint->getDateFin()->format('Y-m-d') : null,
                ':statut' => $sprint->getStatut(),
                ':created_at' => $sprint->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to save sprint: " . $e->getMessage());
        }
    }
    public function findById(int $id): ?Sprint {
        $sql = "SELECT * FROM sprints WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            return $this->hydrateSprint($data);
        } catch (PDOException $e) {
            throw new Exception("Failed to find sprint: " . $e->getMessage());
        }
    }
    public function findByProjetId(int $projetId): array {
        $sql = "SELECT * FROM sprints WHERE projet_id = :projet_id ORDER BY created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':projet_id' => $projetId]);
            $sprints = [];
            while ($data = $stmt->fetch()) {
                $sprints[] = $this->hydrateSprint($data);
            }
            return $sprints;
        } catch (PDOException $e) {
            throw new Exception("Failed to find sprints by project: " . $e->getMessage());
        }
    }
    public function findAll(): array {
        $sql = "SELECT * FROM sprints ORDER BY created_at DESC";
        try {
            $stmt = $this->db->query($sql);
            $sprints = [];
            while ($data = $stmt->fetch()) {
                $sprints[] = $this->hydrateSprint($data);
            }
            return $sprints;
        } catch (PDOException $e) {
            throw new Exception("Failed to find all sprints: " . $e->getMessage());
        }
    }
    public function update(Sprint $sprint): bool {
        $sql = "UPDATE sprints 
                SET projet_id = :projet_id, nom = :nom, description = :description, 
                    date_debut = :date_debut, date_fin = :date_fin, statut = :statut 
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $sprint->getId(),
                ':projet_id' => $sprint->getProjetId(),
                ':nom' => $sprint->getNom(),
                ':description' => $sprint->getDescription(),
                ':date_debut' => $sprint->getDateDebut() ? $sprint->getDateDebut()->format('Y-m-d') : null,
                ':date_fin' => $sprint->getDateFin() ? $sprint->getDateFin()->format('Y-m-d') : null,
                ':statut' => $sprint->getStatut()
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to update sprint: " . $e->getMessage());
        }
    }
    public function delete(int $id): bool {
        $sql = "DELETE FROM sprints WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Failed to delete sprint: " . $e->getMessage());
        }
    }
    private function hydrateSprint(array $data): Sprint {
        $sprint = new Sprint(
            $data['projet_id'],
            $data['nom'],
            $data['description'],
            $data['date_debut'] ? new DateTime($data['date_debut']) : null,
            $data['date_fin'] ? new DateTime($data['date_fin']) : null,
            $data['statut']
        );
        $sprint->setId($data['id']);
        $sprint->setCreatedAt(new DateTime($data['created_at']));
        return $sprint;
    }
}
