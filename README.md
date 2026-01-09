# Scrum Project Management Application

A simple PHP OOP application for managing Scrum projects without frameworks.

## 📁 Project Structure

```
config/
  database.php        # Database configuration
core/
  Database.php        # Singleton PDO Database connection
entities/
  User.php            # User entity
  Projet.php          # Project entity
  Sprint.php          # Sprint entity
  Tache.php           # Task entity
  Reclamation.php     # Reclamation entity
  Log.php             # Log entity
services/
  AuthService.php     # Authentication service
  ProjetService.php   # Project service
  SprintService.php   # Sprint service
  TacheService.php    # Task service
  ReclamationService.php # Reclamation service
  LogService.php      # Logging service
repositories/
  UserRepository.php  # User repository
  ProjetRepository.php # Project repository
  SprintRepository.php # Sprint repository
  TacheRepository.php # Task repository
  ReclamationRepository.php # Reclamation repository
  LogRepository.php   # Log repository
utils/
  Auth.php            # Authentication helper
  Helpers.php         # Utility functions
views/
  projets.php         # Projects view
  sprints.php         # Sprints view
  taches.php          # Tasks view
  reclamations.php    # Reclamations view
  admin_dashboard.php # Admin dashboard
```

## 🗄️ Database Schema

### Core Tables
- **users**: User management with roles (admin, chef, membre)
- **projets**: Project management
- **sprints**: Sprint management linked to projects
- **taches**: Task management linked to sprints

### Important Tables
- **reclamations**: Task issue reporting
  - `id`, `task_id`, `user_id`, `description`, `status` (open/resolved), `created_at`, `resolved_at`
  
- **logs**: Activity logging
  - `id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `created_at`

## 🔧 Installation

1. Import the database schema:
```sql
mysql -u root -p < database_schema.sql
```

2. Configure database settings in `config/database.php`

3. Set up web server to point to project root

## 📋 Key Features

### Role-Based Permissions
- **Admin**: Can manage everything
- **Chef**: Can manage projects, sprints, tasks
- **Membre**: Can only manage their own tasks

### Logging System
Critical actions are automatically logged:
- Project CRUD operations
- Sprint CRUD operations  
- Task creation, assignment, completion
- User logins
- Reclamation creation/resolution

Example usage:
```php
$logService = LogService::getInstance();
$logService->logTaskCreated($userId, $taskId, "Task title");
```

### Reclamation System
Simple issue reporting for tasks:
- Members can report problems on tasks
- Admins can view and resolve reclamations
- Status tracking (open/resolved)

Example usage:
```php
$reclamationService = new ReclamationService();
$reclamation = $reclamationService->createReclamation($userId, $taskId, "Issue description");
```

## 🎯 Simple Code Examples

### Log Entity + Repository + Service

```php
// Log Entity
$log = new Log($userId, 'TASK_CREATED', 'task', $taskId, "Task created");

// Log Repository
$logRepository = new LogRepository();
$logRepository->save($log);

// Log Service (recommended way)
LogService::getInstance()->logTaskCreated($userId, $taskId, "Task title");
```

### Reclamation Entity + Service

```php
// Create reclamation
$reclamationService = new ReclamationService();
$reclamation = $reclamationService->createReclamation($userId, $taskId, "Problem description");

// Resolve reclamation
$reclamationService->resolveReclamation($adminId, $reclamationId);
```

### Logging Inside TacheService

```php
public function createTask(int $userId, int $sprintId, string $titre): Tache {
    $tache = new Tache($sprintId, $titre, null, 'a_faire', null, $userId);
    
    if ($this->tacheRepository->save($tache)) {
        // Log the action
        $this->logService->logTaskCreated($userId, $tache->getId(), $titre);
        return $tache;
    }
    
    throw new Exception("Failed to create task");
}
```

## 🔐 Default Login

- Username: `admin`
- Password: `admin123`

## 🚀 Getting Started

1. Create database and import schema
2. Update database configuration
3. Access application via web server
4. Login with default admin account
5. Create users, projects, sprints, and tasks

The application follows a simple Repository → Service → View pattern with clean separation of concerns and beginner-friendly code.
