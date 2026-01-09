<?php

class User {
    private ?int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private string $role;
    private DateTime $createdAt;
    
    public function __construct(
        string $username,
        string $email,
        string $passwordHash,
        string $role = 'membre',
        ?int $id = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->createdAt = new DateTime();
    }
    
    // Getters
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getUsername(): string {
        return $this->username;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
    
    public function getPasswordHash(): string {
        return $this->passwordHash;
    }
    
    public function getRole(): string {
        return $this->role;
    }
    
    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }
    
    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }
    
    public function setUsername(string $username): void {
        $this->username = $username;
    }
    
    public function setEmail(string $email): void {
        $this->email = $email;
    }
    
    public function setPasswordHash(string $passwordHash): void {
        $this->passwordHash = $passwordHash;
    }
    
    public function setRole(string $role): void {
        $this->role = $role;
    }
    
    public function setCreatedAt(DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }
    
    public function isAdmin(): bool {
        return $this->role === 'admin';
    }
    
    public function isChef(): bool {
        return $this->role === 'chef';
    }
    
    public function isMembre(): bool {
        return $this->role === 'membre';
    }
    
    public function canManageEverything(): bool {
        return $this->isAdmin() || $this->isChef();
    }
}
