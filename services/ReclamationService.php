<?php
require_once __DIR__ . '/../repositories/ReclamationRepository.php';
require_once __DIR__ . '/../repositories/TacheRepository.php';
require_once __DIR__ . '/../entities/Reclamation.php';
require_once __DIR__ . '/LogService.php';
class ReclamationService {
    private ReclamationRepository $reclamationRepository;
    private TacheRepository $tacheRepository;
    private LogService $logService;
    public function __construct() {
        $this->reclamationRepository = new ReclamationRepository();
        $this->tacheRepository = new TacheRepository();
        $this->logService = LogService::getInstance();
    }
    public function createReclamation(int $userId, int $taskId, string $description): Reclamation {
        $task = $this->tacheRepository->findById($taskId);
        if (!$task) {
            throw new Exception("Task not found");
        }
        $reclamation = new Reclamation($taskId, $userId, $description);
        if (!$this->reclamationRepository->save($reclamation)) {
            throw new Exception("Failed to create reclamation");
        }
        $lastInsertId = Database::getInstance()->lastInsertId();
        $reclamation->setId($lastInsertId);
        $this->logService->logReclamationCreated($userId, $reclamation->getId(), $taskId);
        return $reclamation;
    }
    public function resolveReclamation(int $adminId, int $reclamationId): Reclamation {
        $reclamation = $this->reclamationRepository->findById($reclamationId);
        if (!$reclamation) {
            throw new Exception("Reclamation not found");
        }
        if ($reclamation->isResolved()) {
            throw new Exception("Reclamation is already resolved");
        }
        $reclamation->resoudre();
        if (!$this->reclamationRepository->update($reclamation)) {
            throw new Exception("Failed to resolve reclamation");
        }
        $this->logService->logReclamationResolved($adminId, $reclamation->getId(), $reclamation->getTaskId());
        return $reclamation;
    }
    public function getAllReclamations(): array {
        try {
            return $this->reclamationRepository->findAll();
        } catch (Exception $e) {
            throw new Exception("Failed to get reclamations: " . $e->getMessage());
        }
    }
    public function getOpenReclamations(): array {
        try {
            return $this->reclamationRepository->findOpen();
        } catch (Exception $e) {
            throw new Exception("Failed to get open reclamations: " . $e->getMessage());
        }
    }
    public function getResolvedReclamations(): array {
        try {
            return $this->reclamationRepository->findResolved();
        } catch (Exception $e) {
            throw new Exception("Failed to get resolved reclamations: " . $e->getMessage());
        }
    }
    public function getReclamationsByTask(int $taskId): array {
        try {
            return $this->reclamationRepository->findByTaskId($taskId);
        } catch (Exception $e) {
            throw new Exception("Failed to get reclamations by task: " . $e->getMessage());
        }
    }
    public function getReclamationsByUser(int $userId): array {
        try {
            return $this->reclamationRepository->findByUserId($userId);
        } catch (Exception $e) {
            throw new Exception("Failed to get reclamations by user: " . $e->getMessage());
        }
    }
    public function getReclamationById(int $id): ?Reclamation {
        try {
            return $this->reclamationRepository->findById($id);
        } catch (Exception $e) {
            throw new Exception("Failed to get reclamation: " . $e->getMessage());
        }
    }
    public function deleteReclamation(int $adminId, int $reclamationId): bool {
        $reclamation = $this->reclamationRepository->findById($reclamationId);
        if (!$reclamation) {
            throw new Exception("Reclamation not found");
        }
        try {
            return $this->reclamationRepository->delete($reclamationId);
        } catch (Exception $e) {
            throw new Exception("Failed to delete reclamation: " . $e->getMessage());
        }
    }
    public function canUserCreateReclamation(int $userId, int $taskId): bool {
        try {
            $task = $this->tacheRepository->findById($taskId);
            if (!$task) {
                return false;
            }
            return $task->getAssigneA() === $userId || true; 
        } catch (Exception $e) {
            return false;
        }
    }
    public function canUserResolveReclamation(int $userId): bool {
        return $userId === 1;
    }
}
