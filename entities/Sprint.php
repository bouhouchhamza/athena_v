<?php
class Sprint {
    private ?int $id;
    private int $projetId;
    private string $nom;
    private ?string $description;
    private ?DateTime $dateDebut;
    private ?DateTime $dateFin;
    private string $statut;
    private DateTime $createdAt;
    public function __construct(
        int $projetId,
        string $nom,
        ?string $description = null,
        ?DateTime $dateDebut = null,
        ?DateTime $dateFin = null,
        string $statut = 'planifie',
        ?int $id = null
    ) {
        $this->id = $id;
        $this->projetId = $projetId;
        $this->nom = $nom;
        $this->description = $description;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->statut = $statut;
        $this->createdAt = new DateTime();
    }
    public function getId(): ?int {
        return $this->id;
    }
    public function getProjetId(): int {
        return $this->projetId;
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
    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }
    public function setProjetId(int $projetId): void {
        $this->projetId = $projetId;
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
    public function setCreatedAt(DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }
    public function isPlanifie(): bool {
        return $this->statut === 'planifie';
    }
    public function isEnCours(): bool {
        return $this->statut === 'en_cours';
    }
    public function isTermine(): bool {
        return $this->statut === 'termine';
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
    public function start(): void {
        $this->statut = 'en_cours';
        if (!$this->dateDebut) {
            $this->dateDebut = new DateTime();
        }
    }
    public function complete(): void {
        $this->statut = 'termine';
        $this->dateFin = new DateTime();
    }
}
