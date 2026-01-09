<?php

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../entities/User.php';
require_once __DIR__ . '/LogService.php';

class AuthService {
    private UserRepository $userRepository;
    private LogService $logService;
    
    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->logService = LogService::getInstance();
    }
    
    public function register(string $username, string $email, string $password, string $role = 'membre'): User {
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters");
        }
        
        // Check if user already exists
        if ($this->userRepository->findByUsername($username)) {
            throw new Exception("Username already exists");
        }
        
        if ($this->userRepository->findByEmail($email)) {
            throw new Exception("Email already exists");
        }
        
        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($username, $email, $hashedPassword, $role);
        
        if (!$this->userRepository->save($user)) {
            throw new Exception("Failed to register user");
        }
        
        return $user;
    }
    
    public function login(string $username, string $password): ?User {
        $user = $this->userRepository->findByUsername($username);
        
        if (!$user || !password_verify($password, $user->getPasswordHash())) {
            return null;
        }
        
        // Log the login
        $this->logService->logUserLogin($user->getId(), $user->getUsername());
        
        return $user;
    }
    
    public function getAllUsers(): array {
        try {
            return $this->userRepository->findAll();
        } catch (Exception $e) {
            throw new Exception("Failed to get users: " . $e->getMessage());
        }
    }
    
    public function getUserById(int $id): ?User {
        try {
            return $this->userRepository->findById($id);
        } catch (Exception $e) {
            throw new Exception("Failed to get user: " . $e->getMessage());
        }
    }
    
    public function updateUser(int $userId, string $username, string $email, string $role): User {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new Exception("User not found");
        }
        
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRole($role);
        
        // Note: In a real implementation, you would have an update method in UserRepository
        return $user;
    }
    
    public function deleteUser(int $userId): bool {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // Note: In a real implementation, you would have a delete method in UserRepository
        return true;
    }
    
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
