<?php

require_once __DIR__ . '/../repositories/LogRepository.php';
require_once __DIR__ . '/../entities/Log.php';

class LogService {
    private static ?LogService $instance = null;
    private LogRepository $logRepository;
    
    private function __construct() {
        $this->logRepository = new LogRepository();
    }
    
    public static function getInstance(): LogService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log(int $userId, string $action, string $entityType, int $entityId, ?string $description = null): bool {
        try {
            $log = new Log($userId, $action, $entityType, $entityId, $description);
            return $this->logRepository->save($log);
        } catch (Exception $e) {
            // Silently fail logging to not break main functionality
            error_log("Logging failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function logProjectCreated(int $userId, int $projectId, string $projectName): bool {
        return $this->log($userId, Log::PROJECT_CREATED, 'project', $projectId, "Project '{$projectName}' created");
    }
    
    public function logProjectUpdated(int $userId, int $projectId, string $projectName): bool {
        return $this->log($userId, Log::PROJECT_UPDATED, 'project', $projectId, "Project '{$projectName}' updated");
    }
    
    public function logProjectDeleted(int $userId, int $projectId, string $projectName): bool {
        return $this->log($userId, Log::PROJECT_DELETED, 'project', $projectId, "Project '{$projectName}' deleted");
    }
    
    public function logSprintCreated(int $userId, int $sprintId, string $sprintName): bool {
        return $this->log($userId, Log::SPRINT_CREATED, 'sprint', $sprintId, "Sprint '{$sprintName}' created");
    }
    
    public function logSprintUpdated(int $userId, int $sprintId, string $sprintName): bool {
        return $this->log($userId, Log::SPRINT_UPDATED, 'sprint', $sprintId, "Sprint '{$sprintName}' updated");
    }
    
    public function logSprintDeleted(int $userId, int $sprintId, string $sprintName): bool {
        return $this->log($userId, Log::SPRINT_DELETED, 'sprint', $sprintId, "Sprint '{$sprintName}' deleted");
    }
    
    public function logTaskCreated(int $userId, int $taskId, string $taskTitle): bool {
        return $this->log($userId, Log::TASK_CREATED, 'task', $taskId, "Task '{$taskTitle}' created");
    }
    
    public function logTaskAssigned(int $userId, int $taskId, string $taskTitle, int $assignedUserId): bool {
        return $this->log($userId, Log::TASK_ASSIGNED, 'task', $taskId, "Task '{$taskTitle}' assigned to user {$assignedUserId}");
    }
    
    public function logTaskCompleted(int $userId, int $taskId, string $taskTitle): bool {
        return $this->log($userId, Log::TASK_COMPLETED, 'task', $taskId, "Task '{$taskTitle}' completed");
    }
    
    public function logTaskUpdated(int $userId, int $taskId, string $taskTitle): bool {
        return $this->log($userId, Log::TASK_UPDATED, 'task', $taskId, "Task '{$taskTitle}' updated");
    }
    
    public function logTaskDeleted(int $userId, int $taskId, string $taskTitle): bool {
        return $this->log($userId, Log::TASK_DELETED, 'task', $taskId, "Task '{$taskTitle}' deleted");
    }
    
    public function logUserLogin(int $userId, string $username): bool {
        return $this->log($userId, Log::USER_LOGIN, 'user', $userId, "User '{$username}' logged in");
    }
    
    public function logReclamationCreated(int $userId, int $reclamationId, int $taskId): bool {
        return $this->log($userId, Log::RECLAMATION_CREATED, 'reclamation', $reclamationId, "Reclamation created for task {$taskId}");
    }
    
    public function logReclamationResolved(int $userId, int $reclamationId, int $taskId): bool {
        return $this->log($userId, Log::RECLAMATION_RESOLVED, 'reclamation', $reclamationId, "Reclamation for task {$taskId} resolved");
    }
    
    public function getRecentLogs(int $limit = 50): array {
        try {
            return $this->logRepository->findRecent($limit);
        } catch (Exception $e) {
            error_log("Failed to get recent logs: " . $e->getMessage());
            return [];
        }
    }
    
    public function getLogsByUser(int $userId): array {
        try {
            return $this->logRepository->findByUserId($userId);
        } catch (Exception $e) {
            error_log("Failed to get logs by user: " . $e->getMessage());
            return [];
        }
    }
    
    public function getLogsByEntityType(string $entityType): array {
        try {
            return $this->logRepository->findByEntityType($entityType);
        } catch (Exception $e) {
            error_log("Failed to get logs by entity type: " . $e->getMessage());
            return [];
        }
    }
}
