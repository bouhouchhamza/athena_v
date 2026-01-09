<?php
class Log {
    private ?int $id;
    private int $userId;
    private string $action;
    private string $entityType;
    private int $entityId;
    private ?string $description;
    private DateTime $createdAt;
    public function __construct(
        int $userId,
        string $action,
        string $entityType,
        int $entityId,
        ?string $description = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->description = $description;
        $this->createdAt = new DateTime();
    }
    public function getId(): ?int {
        return $this->id;
    }
    public function getUserId(): int {
        return $this->userId;
    }
    public function getAction(): string {
        return $this->action;
    }
    public function getEntityType(): string {
        return $this->entityType;
    }
    public function getEntityId(): int {
        return $this->entityId;
    }
    public function getDescription(): ?string {
        return $this->description;
    }
    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }
    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }
    public function setAction(string $action): void {
        $this->action = $action;
    }
    public function setEntityType(string $entityType): void {
        $this->entityType = $entityType;
    }
    public function setEntityId(int $entityId): void {
        $this->entityId = $entityId;
    }
    public function setDescription(?string $description): void {
        $this->description = $description;
    }
    public function setCreatedAt(DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }
    public const PROJECT_CREATED = 'PROJECT_CREATED';
    public const PROJECT_UPDATED = 'PROJECT_UPDATED';
    public const PROJECT_DELETED = 'PROJECT_DELETED';
    public const SPRINT_CREATED = 'SPRINT_CREATED';
    public const SPRINT_UPDATED = 'SPRINT_UPDATED';
    public const SPRINT_DELETED = 'SPRINT_DELETED';
    public const TASK_CREATED = 'TASK_CREATED';
    public const TASK_ASSIGNED = 'TASK_ASSIGNED';
    public const TASK_COMPLETED = 'TASK_COMPLETED';
    public const TASK_UPDATED = 'TASK_UPDATED';
    public const TASK_DELETED = 'TASK_DELETED';
    public const USER_LOGIN = 'USER_LOGIN';
    public const RECLAMATION_CREATED = 'RECLAMATION_CREATED';
    public const RECLAMATION_RESOLVED = 'RECLAMATION_RESOLVED';
}
