<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/User.php';

class UserRepository {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function save(User $user): bool {
        $sql = "INSERT INTO users (username, email, password_hash, role, created_at) 
                VALUES (:username, :email, :password_hash, :role, :created_at)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':username' => $user->getUsername(),
                ':email' => $user->getEmail(),
                ':password_hash' => $user->getPasswordHash(),
                ':role' => $user->getRole(),
                ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to save user: " . $e->getMessage());
        }
    }
    
    public function findById(int $id): ?User {
        $sql = "SELECT * FROM users WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            
            if (!$data) {
                return null;
            }
            
            return $this->hydrateUser($data);
        } catch (PDOException $e) {
            throw new Exception("Failed to find user: " . $e->getMessage());
        }
    }
    
    public function findByUsername(string $username): ?User {
        $sql = "SELECT * FROM users WHERE username = :username";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $username]);
            $data = $stmt->fetch();
            
            if (!$data) {
                return null;
            }
            
            return $this->hydrateUser($data);
        } catch (PDOException $e) {
            throw new Exception("Failed to find user by username: " . $e->getMessage());
        }
    }
    
    public function findByEmail(string $email): ?User {
        $sql = "SELECT * FROM users WHERE email = :email";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $data = $stmt->fetch();
            
            if (!$data) {
                return null;
            }
            
            return $this->hydrateUser($data);
        } catch (PDOException $e) {
            throw new Exception("Failed to find user by email: " . $e->getMessage());
        }
    }
    
    public function findAll(): array {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->query($sql);
            $users = [];
            
            while ($data = $stmt->fetch()) {
                $users[] = $this->hydrateUser($data);
            }
            
            return $users;
        } catch (PDOException $e) {
            throw new Exception("Failed to find all users: " . $e->getMessage());
        }
    }
    
    private function hydrateUser(array $data): User {
        $user = new User(
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['role']
        );
        
        $user->setId($data['id']);
        $user->setCreatedAt(new DateTime($data['created_at']));
        
        return $user;
    }
}
