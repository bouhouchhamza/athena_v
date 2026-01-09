<?php

class Projet {
    private ?int $id;
    private string $nom;
    private ?string $description;
    private ?DateTime $dateDebut;
    private ?DateTime $dateFin;
    private string $statut;
    private ?int $createdBy;
    private DateTime $createdAt;
    
    public function __construct(
        string $nom,
        ?string $description = null,
        ?DateTime $dateDebut = null,
        ?DateTime $dateFin = null,
        string $statut = 'en_cours',
        ?int $createdBy = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->statut = $statut;
        $this->createdBy = $createdBy;
        $this->createdAt = new DateTime();
    }
    
    // Getters
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getNom(): string {
        return $this->nom;
    }
    
    public function getDescription(): ?string {
        return $this->description;
    }
    
    public function getDateDebut(): ?DateTime {
        return $this->dateDebut;
    }
    
    public function getDateFin(): ?DateTime {
        return $this->dateFin;
    }
    
    public function getStatut(): string {
        return $this->statut;
    }
    
    public function getCreatedBy(): ?int {
        return $this->createdBy;
    }
    
    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }
    
    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }
    
    public function setNom(string $nom): void {
        $this->nom = $nom;
    }
    
    public function setDescription(?string $description): void {
        $this->description = $description;
    }
    
    public function setDateDebut(?DateTime $dateDebut): void {
        $this->dateDebut = $dateDebut;
    }
    
    public function setDateFin(?DateTime $dateFin): void {
        $this->dateFin = $dateFin;
    }
    
    public function setStatut(string $statut): void {
        $this->statut = $statut;
    }
    
    public function setCreatedBy(?int $createdBy): void {
        $this->createdBy = $createdBy;
    }
    
    public function setCreatedAt(DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }
    
    public function isEnCours(): bool {
        return $this->statut === 'en_cours';
    }
    
    public function isTermine(): bool {
        return $this->statut === 'termine';
    }
    
    public function isEnAttente(): bool {
        return $this->statut === 'en_attente';
    }
    
    public function getDuration(): ?int {
        if ($this->dateDebut && $this->dateFin) {
            return $this->dateDebut->diff($this->dateFin)->days;
        }
        return null;
    }
    
    public function isOverdue(): bool {
        return $this->dateFin && $this->dateFin < new DateTime() && !$this->isTermine();
    }
}
