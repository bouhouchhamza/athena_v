<?php

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/LogService.php';

class Auth {
    private static ?User $currentUser = null;
    private UserRepository $userRepository;
    
    public function __construct() {
        $this->userRepository = new UserRepository();
    }
    
    public function login(string $username, string $password): ?User {
        $user = $this->userRepository->findByUsername($username);
        
        if (!$user || !password_verify($password, $user->getPasswordHash())) {
            return null;
        }
        
        self::$currentUser = $user;
        $_SESSION['user_id'] = $user->getId();
        
        // Log the login
        $logService = LogService::getInstance();
        $logService->logUserLogin($user->getId(), $user->getUsername());
        
        return $user;
    }
    
    public function logout(): void {
        self::$currentUser = null;
        unset($_SESSION['user_id']);
        session_destroy();
    }
    
    public function getCurrentUser(): ?User {
        if (self::$currentUser === null && isset($_SESSION['user_id'])) {
            self::$currentUser = $this->userRepository->findById($_SESSION['user_id']);
        }
        
        return self::$currentUser;
    }
    
    public function isLoggedIn(): bool {
        return $this->getCurrentUser() !== null;
    }
    
    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public function requireRole(string $role): void {
        $user = $this->getCurrentUser();
        if (!$user || $user->getRole() !== $role) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Access denied';
            exit();
        }
    }
    
    public function requireAdmin(): void {
        $user = $this->getCurrentUser();
        if (!$user || !$user->isAdmin()) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Admin access required';
            exit();
        }
    }
    
    public function requireAdminOrChef(): void {
        $user = $this->getCurrentUser();
        if (!$user || (!$user->isAdmin() && !$user->isChef())) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Admin or Chef access required';
            exit();
        }
    }
    
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
