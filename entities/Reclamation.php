<?php
class Reclamation {
    private ?int $id;
    private int $taskId;
    private int $userId;
    private string $description;
    private string $statut;
    private DateTime $createdAt;
    private ?DateTime $resolvedAt;
    public function __construct(
        int $taskId,
        int $userId,
        string $description,
        string $statut = 'open',
        ?int $id = null
    ) {
        $this->id = $id;
        $this->taskId = $taskId;
        $this->userId = $userId;
        $this->description = $description;
        $this->statut = $statut;
        $this->createdAt = new DateTime();
        $this->resolvedAt = null;
    }
    public function getId(): ?int {
        return $this->id;
    }
    public function getTaskId(): int {
        return $this->taskId;
    }
    public function getUserId(): int {
        return $this->userId;
    }
    public function getDescription(): string {
        return $this->description;
    }
    public function getStatut(): string {
        return $this->statut;
    }
    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }
    public function getResolvedAt(): ?DateTime {
        return $this->resolvedAt;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }
    public function setTaskId(int $taskId): void {
        $this->taskId = $taskId;
    }
    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }
    public function setDescription(string $description): void {
        $this->description = $description;
    }
    public function setStatut(string $statut): void {
        $this->statut = $statut;
    }
    public function setResolvedAt(?DateTime $resolvedAt): void {
        $this->resolvedAt = $resolvedAt;
    }
    public function setCreatedAt(DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }
    public function isOpen(): bool {
        return $this->statut === 'open';
    }
    public function isResolved(): bool {
        return $this->statut === 'resolved';
    }
    public function resoudre(): void {
        $this->statut = 'resolved';
        $this->resolvedAt = new DateTime();
    }
}
