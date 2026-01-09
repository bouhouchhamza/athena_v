<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/Projet.php';
class ProjetRepository {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function save(Projet $projet): bool {
        $sql = "INSERT INTO projets (nom, description, date_debut, date_fin, statut, created_by, created_at) 
                VALUES (:nom, :description, :date_debut, :date_fin, :statut, :created_by, :created_at)";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nom' => $projet->getNom(),
                ':description' => $projet->getDescription(),
                ':date_debut' => $projet->getDateDebut() ? $projet->getDateDebut()->format('Y-m-d') : null,
                ':date_fin' => $projet->getDateFin() ? $projet->getDateFin()->format('Y-m-d') : null,
                ':statut' => $projet->getStatut(),
                ':created_by' => $projet->getCreatedBy(),
                ':created_at' => $projet->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to save project: " . $e->getMessage());
        }
    }
    public function findById(int $id): ?Projet {
        $sql = "SELECT * FROM projets WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            return $this->hydrateProjet($data);
        } catch (PDOException $e) {
            throw new Exception("Failed to find project: " . $e->getMessage());
        }
    }
    public function findAll(): array {
        $sql = "SELECT * FROM projets ORDER BY created_at DESC";
        try {
            $stmt = $this->db->query($sql);
            $projets = [];
            while ($data = $stmt->fetch()) {
                $projets[] = $this->hydrateProjet($data);
            }
            return $projets;
        } catch (PDOException $e) {
            throw new Exception("Failed to find all projects: " . $e->getMessage());
        }
    }
    public function update(Projet $projet): bool {
        $sql = "UPDATE projets 
                SET nom = :nom, description = :description, date_debut = :date_debut, 
                    date_fin = :date_fin, statut = :statut 
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $projet->getId(),
                ':nom' => $projet->getNom(),
                ':description' => $projet->getDescription(),
                ':date_debut' => $projet->getDateDebut() ? $projet->getDateDebut()->format('Y-m-d') : null,
                ':date_fin' => $projet->getDateFin() ? $projet->getDateFin()->format('Y-m-d') : null,
                ':statut' => $projet->getStatut()
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to update project: " . $e->getMessage());
        }
    }
    public function delete(int $id): bool {
        $sql = "DELETE FROM projets WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Failed to delete project: " . $e->getMessage());
        }
    }
    private function hydrateProjet(array $data): Projet {
        $projet = new Projet(
            $data['nom'],
            $data['description'],
            $data['date_debut'] ? new DateTime($data['date_debut']) : null,
            $data['date_fin'] ? new DateTime($data['date_fin']) : null,
            $data['statut'],
            $data['created_by']
        );
        $projet->setId($data['id']);
        $projet->setCreatedAt(new DateTime($data['created_at']));
        return $projet;
    }
}
