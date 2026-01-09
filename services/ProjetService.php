<?php
require_once __DIR__ . '/../repositories/ProjetRepository.php';
require_once __DIR__ . '/../entities/Projet.php';
require_once __DIR__ . '/LogService.php';
class ProjetService {
    private ProjetRepository $projetRepository;
    private LogService $logService;
    public function __construct() {
        $this->projetRepository = new ProjetRepository();
        $this->logService = LogService::getInstance();
    }
    public function createProjet(int $userId, string $nom, ?string $description = null, ?DateTime $dateDebut = null, ?DateTime $dateFin = null): Projet {
        $projet = new Projet($nom, $description, $dateDebut, $dateFin, 'en_cours', $userId);
        if (!$this->projetRepository->save($projet)) {
            throw new Exception("Failed to create project");
        }
        $lastInsertId = Database::getInstance()->lastInsertId();
        $projet->setId($lastInsertId);
        $this->logService->logProjectCreated($userId, $projet->getId(), $nom);
        return $projet;
    }
    public function getAllProjets(): array {
        try {
            return $this->projetRepository->findAll();
        } catch (Exception $e) {
            throw new Exception("Failed to get projects: " . $e->getMessage());
        }
    }
    public function getProjetById(int $id): ?Projet {
        try {
            return $this->projetRepository->findById($id);
        } catch (Exception $e) {
            throw new Exception("Failed to get project: " . $e->getMessage());
        }
    }
    public function updateProjet(int $userId, int $projetId, string $nom, ?string $description = null, ?DateTime $dateDebut = null, ?DateTime $dateFin = null, string $statut = 'en_cours'): Projet {
        $projet = $this->projetRepository->findById($projetId);
        if (!$projet) {
            throw new Exception("Project not found");
        }
        $projet->setNom($nom);
        $projet->setDescription($description);
        $projet->setDateDebut($dateDebut);
        $projet->setDateFin($dateFin);
        $projet->setStatut($statut);
        $this->logService->logProjectUpdated($userId, $projetId, $nom);
        return $projet;
    }
    public function deleteProjet(int $userId, int $projetId): bool {
        $projet = $this->projetRepository->findById($projetId);
        if (!$projet) {
            throw new Exception("Project not found");
        }
        $this->logService->logProjectDeleted($userId, $projetId, $projet->getNom());
        return true;
    }
}
