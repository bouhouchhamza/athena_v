<?php

class Tache {
    private ?int $id;
    private int $sprintId;
    private string $titre;
    private ?string $description;
    private string $statut;
    private ?int $assigneA;
    private int $createdBy;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    
    public function __construct(
        int $sprintId,
        string $titre,
        ?string $description = null,
        string $statut = 'a_faire',
        ?int $assigneA = null,
        int $createdBy,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->sprintId = $sprintId;
        $this->titre = $titre;
        $this->description = $description;
        $this->statut = $statut;
        $this->assigneA = $assigneA;
        $this->createdBy = $createdBy;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }
    
    // Getters
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getSprintId(): int {
        return $this->sprintId;
    }
    
    public function getTitre(): string {
        return $this->titre;
    }
    
    public function getDescription(): ?string {
        return $this->description;
    }
    
    public function getStatut(): string {
        return $this->statut;
    }
    
    public function getAssigneA(): ?int {
        return $this->assigneA;
    }
    
    public function getCreatedBy(): int {
        return $this->createdBy;
    }
    
    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): DateTime {
        return $this->updatedAt;
    }
    
    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }
    
    public function setSprintId(int $sprintId): void {
        $this->sprintId = $sprintId;
    }
    
    public function setTitre(string $titre): void {
        $this->titre = $titre;
    }
    
    public function setDescription(?string $description): void {
        $this->description = $description;
    }
    
    public function setStatut(string $statut): void {
        $this->statut = $statut;
        $this->updatedAt = new DateTime();
    }
    
    public function setAssigneA(?int $assigneA): void {
        $this->assigneA = $assigneA;
        $this->updatedAt = new DateTime();
    }
    
    public function setCreatedBy(int $createdBy): void {
        $this->createdBy = $createdBy;
    }
    
    public function isAFaire(): bool {
        return $this->statut === 'a_faire';
    }
    
    public function isEnCours(): bool {
        return $this->statut === 'en_cours';
    }
    
    public function isTermine(): bool {
        return $this->statut === 'termine';
    }
    
    public function assigner(int $userId): void {
        $this->assigneA = $userId;
        $this->updatedAt = new DateTime();
    }
    
    public function completer(): void {
        $this->statut = 'termine';
        $this->updatedAt = new DateTime();
    }
}
