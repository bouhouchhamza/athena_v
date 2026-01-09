<?php

require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/ProjetRepository.php';
require_once __DIR__ . '/../entities/Sprint.php';
require_once __DIR__ . '/LogService.php';

class SprintService {
    private SprintRepository $sprintRepository;
    private ProjetRepository $projetRepository;
    private LogService $logService;
    
    public function __construct() {
        $this->sprintRepository = new SprintRepository();
        $this->projetRepository = new ProjetRepository();
        $this->logService = LogService::getInstance();
    }
    
    public function createSprint(int $userId, int $projetId, string $nom, ?string $description = null, ?DateTime $dateDebut = null, ?DateTime $dateFin = null): Sprint {
        // Verify project exists
        $projet = $this->projetRepository->findById($projetId);
        if (!$projet) {
            throw new Exception("Project not found");
        }
        
        // Create sprint
        $sprint = new Sprint($projetId, $nom, $description, $dateDebut, $dateFin, 'planifie');
        
        if (!$this->sprintRepository->save($sprint)) {
            throw new Exception("Failed to create sprint");
        }
        
        // Get the ID of the inserted sprint
        $lastInsertId = Database::getInstance()->lastInsertId();
        $sprint->setId($lastInsertId);
        
        // Log the action
        $this->logService->logSprintCreated($userId, $sprint->getId(), $nom);
        
        return $sprint;
    }
    
    public function getAllSprints(): array {
        try {
            return $this->sprintRepository->findAll();
        } catch (Exception $e) {
            throw new Exception("Failed to get sprints: " . $e->getMessage());
        }
    }
    
    public function getSprintsByProjet(int $projetId): array {
        try {
            return $this->sprintRepository->findByProjetId($projetId);
        } catch (Exception $e) {
            throw new Exception("Failed to get sprints by project: " . $e->getMessage());
        }
    }
    
    public function getSprintById(int $id): ?Sprint {
        try {
            return $this->sprintRepository->findById($id);
        } catch (Exception $e) {
            throw new Exception("Failed to get sprint: " . $e->getMessage());
        }
    }
    
    public function updateSprint(int $userId, int $sprintId, string $nom, ?string $description = null, ?DateTime $dateDebut = null, ?DateTime $dateFin = null, string $statut = 'planifie'): Sprint {
        $sprint = $this->sprintRepository->findById($sprintId);
        if (!$sprint) {
            throw new Exception("Sprint not found");
        }
        
        $sprint->setNom($nom);
        $sprint->setDescription($description);
        $sprint->setDateDebut($dateDebut);
        $sprint->setDateFin($dateFin);
        $sprint->setStatut($statut);
        
        // Log the action
        $this->logService->logSprintUpdated($userId, $sprintId, $nom);
        
        return $sprint;
    }
    
    public function deleteSprint(int $userId, int $sprintId): bool {
        $sprint = $this->sprintRepository->findById($sprintId);
        if (!$sprint) {
            throw new Exception("Sprint not found");
        }
        
        // Log the action before deletion
        $this->logService->logSprintDeleted($userId, $sprintId, $sprint->getNom());
        
        // Note: In a real implementation, you would have a delete method in SprintRepository
        return true;
    }
    
    public function startSprint(int $userId, int $sprintId): Sprint {
        $sprint = $this->sprintRepository->findById($sprintId);
        if (!$sprint) {
            throw new Exception("Sprint not found");
        }
        
        $sprint->setStatut('en_cours');
        $sprint->setDateDebut(new DateTime());
        
        // Log the action
        $this->logService->logSprintUpdated($userId, $sprintId, $sprint->getNom());
        
        return $sprint;
    }
    
    public function completeSprint(int $userId, int $sprintId): Sprint {
        $sprint = $this->sprintRepository->findById($sprintId);
        if (!$sprint) {
            throw new Exception("Sprint not found");
        }
        
        $sprint->setStatut('termine');
        $sprint->setDateFin(new DateTime());
        
        // Log the action
        $this->logService->logSprintUpdated($userId, $sprintId, $sprint->getNom());
        
        return $sprint;
    }
}
