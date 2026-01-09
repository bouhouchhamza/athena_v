<?php

require_once __DIR__ . '/../repositories/TacheRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../entities/Tache.php';
require_once __DIR__ . '/LogService.php';

class TacheService {
    private TacheRepository $tacheRepository;
    private UserRepository $userRepository;
    private SprintRepository $sprintRepository;
    private LogService $logService;
    
    public function __construct() {
        $this->tacheRepository = new TacheRepository();
        $this->userRepository = new UserRepository();
        $this->sprintRepository = new SprintRepository();
        $this->logService = LogService::getInstance();
    }
    
    public function createTask(int $userId, int $sprintId, string $titre, ?string $description = null): Tache {
        // Verify sprint exists
        $sprint = $this->sprintRepository->findById($sprintId);
        if (!$sprint) {
            throw new Exception("Sprint not found");
        }
        
        // Create task
        $tache = new Tache($sprintId, $titre, $description, 'a_faire', null, $userId);
        
        if (!$this->tacheRepository->save($tache)) {
            throw new Exception("Failed to create task");
        }
        
        // Get the ID of the inserted task
        $lastInsertId = Database::getInstance()->lastInsertId();
        $tache->setId($lastInsertId);
        
        // Log the action
        $this->logService->logTaskCreated($userId, $tache->getId(), $titre);
        
        return $tache;
    }
    
    public function assignTask(int $userId, int $taskId, int $assigneeId): Tache {
        $tache = $this->tacheRepository->findById($taskId);
        if (!$tache) {
            throw new Exception("Task not found");
        }
        
        // Verify assignee exists
        $assignee = $this->userRepository->findById($assigneeId);
        if (!$assignee) {
            throw new Exception("Assignee not found");
        }
        
        // Check permissions (admin/chef can assign any task, member can only assign their own tasks)
        if (!$this->canUserManageTask($userId, $tache)) {
            throw new Exception("Permission denied");
        }
        
        $tache->assigner($assigneeId);
        
        if (!$this->tacheRepository->update($tache)) {
            throw new Exception("Failed to assign task");
        }
        
        // Log the action
        $this->logService->logTaskAssigned($userId, $tache->getId(), $tache->getTitre(), $assigneeId);
        
        return $tache;
    }
    
    public function completeTask(int $userId, int $taskId): Tache {
        $tache = $this->tacheRepository->findById($taskId);
        if (!$tache) {
            throw new Exception("Task not found");
        }
        
        // Check permissions (admin/chef can complete any task, member can only complete their assigned tasks)
        if (!$this->canUserCompleteTask($userId, $tache)) {
            throw new Exception("Permission denied");
        }
        
        $tache->completer();
        
        if (!$this->tacheRepository->update($tache)) {
            throw new Exception("Failed to complete task");
        }
        
        // Log the action
        $this->logService->logTaskCompleted($userId, $tache->getId(), $tache->getTitre());
        
        return $tache;
    }
    
    public function updateTask(int $userId, int $taskId, string $titre, ?string $description = null): Tache {
        $tache = $this->tacheRepository->findById($taskId);
        if (!$tache) {
            throw new Exception("Task not found");
        }
        
        // Check permissions
        if (!$this->canUserManageTask($userId, $tache)) {
            throw new Exception("Permission denied");
        }
        
        $tache->setTitre($titre);
        $tache->setDescription($description);
        
        if (!$this->tacheRepository->update($tache)) {
            throw new Exception("Failed to update task");
        }
        
        // Log the action
        $this->logService->logTaskUpdated($userId, $tache->getId(), $tache->getTitre());
        
        return $tache;
    }
    
    public function deleteTask(int $userId, int $taskId): bool {
        $tache = $this->tacheRepository->findById($taskId);
        if (!$tache) {
            throw new Exception("Task not found");
        }
        
        // Check permissions (only admin/chef can delete tasks)
        if (!$this->canUserManageTask($userId, $tache)) {
            throw new Exception("Permission denied");
        }
        
        // Log the action before deletion
        $this->logService->logTaskDeleted($userId, $tache->getId(), $tache->getTitre());
        
        return $this->tacheRepository->delete($taskId);
    }
    
    public function getTaskById(int $taskId): ?Tache {
        try {
            return $this->tacheRepository->findById($taskId);
        } catch (Exception $e) {
            throw new Exception("Failed to get task: " . $e->getMessage());
        }
    }
    
    public function getTasksBySprint(int $sprintId): array {
        try {
            return $this->tacheRepository->findBySprintId($sprintId);
        } catch (Exception $e) {
            throw new Exception("Failed to get tasks by sprint: " . $e->getMessage());
        }
    }
    
    public function getTasksByAssignee(int $assigneeId): array {
        try {
            return $this->tacheRepository->findByAssignee($assigneeId);
        } catch (Exception $e) {
            throw new Exception("Failed to get tasks by assignee: " . $e->getMessage());
        }
    }
    
    private function canUserManageTask(int $userId, Tache $tache): bool {
        // In a real implementation, you would check user role from database
        // For now, we'll assume user 1 is admin, user 2 is chef
        if ($userId === 1 || $userId === 2) {
            return true; // admin or chef
        }
        
        // Members can only manage their own tasks
        return $tache->getCreatedBy() === $userId || $tache->getAssigneA() === $userId;
    }
    
    private function canUserCompleteTask(int $userId, Tache $tache): bool {
        // Admin and chef can complete any task
        if ($userId === 1 || $userId === 2) {
            return true;
        }
        
        // Members can only complete tasks assigned to them
        return $tache->getAssigneA() === $userId;
    }
}
