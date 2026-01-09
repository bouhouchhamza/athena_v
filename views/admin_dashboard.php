<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/ProjetRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/TacheRepository.php';
require_once __DIR__ . '/../repositories/ReclamationRepository.php';
require_once __DIR__ . '/../services/LogService.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$isAdmin = $user->canManageEverything();

// Initialize repositories
$userRepository = new UserRepository();
$projetRepository = new ProjetRepository();
$sprintRepository = new SprintRepository();
$tacheRepository = new TacheRepository();
$reclamationRepository = new ReclamationRepository();
$logService = LogService::getInstance();

// Get statistics
$stats = [
    'total_users' => 0,
    'total_projects' => 0,
    'total_sprints' => 0,
    'total_tasks' => 0,
    'total_reclamations' => 0,
    'open_reclamations' => 0,
    'completed_tasks' => 0
];

$recentLogs = [];
$error = '';

try {
    $stats['total_users'] = count($userRepository->findAll());
    $stats['total_projects'] = count($projetRepository->findAll());
    $stats['total_sprints'] = count($sprintRepository->findAll());
    $stats['total_tasks'] = count($tacheRepository->findAll());
    $stats['total_reclamations'] = count($reclamationRepository->findAll());
    $stats['open_reclamations'] = count($reclamationRepository->findOpen());
    
    // Count completed tasks
    $allTasks = $tacheRepository->findAll();
    foreach ($allTasks as $task) {
        if ($task->isTermine()) {
            $stats['completed_tasks']++;
        }
    }
    
    // Get recent logs (only for admins)
    if ($isAdmin) {
        $recentLogs = $logService->getRecentLogs(10);
    }
} catch (Exception $e) {
    $error = 'Failed to load dashboard data: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Scrum Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding: 20px; background: white; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin: 0; }
        .nav { display: flex; gap: 15px; }
        .nav a { color: #007bff; text-decoration: none; padding: 8px 16px; border-radius: 4px; transition: background 0.3s; }
        .nav a:hover { background: #e9ecef; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #007bff; margin-bottom: 5px; }
        .stat-label { color: #6c757d; font-size: 14px; }
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card h2 { color: #333; margin-top: 0; margin-bottom: 20px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 10px; margin-bottom: 10px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .error { color: #dc3545; margin-bottom: 20px; padding: 10px; background: #f8d7da; border-radius: 4px; }
        .success { color: #155724; margin-bottom: 20px; padding: 10px; background: #d4edda; border-radius: 4px; }
        .log-item { padding: 10px; border-bottom: 1px solid #eee; }
        .log-item:last-child { border-bottom: none; }
        .log-time { color: #6c757d; font-size: 12px; }
        .log-action { font-weight: bold; color: #495057; }
        .log-description { color: #6c757d; font-size: 14px; margin-top: 2px; }
        .quick-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .welcome { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .welcome h2 { margin: 0 0 10px 0; }
        .welcome p { margin: 0; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Scrum Management Dashboard</h1>
            </div>
            <div class="nav">
                <span>Welcome, <?php echo htmlspecialchars($user->getUsername()); ?> (<?php echo htmlspecialchars($user->getRole()); ?>)</span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php $success = Helpers::getFlash('success'); ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="welcome">
            <h2>Welcome back, <?php echo htmlspecialchars($user->getUsername()); ?>!</h2>
            <p>Here's an overview of your Scrum project management system.</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_projects']; ?></div>
                <div class="stat-label">Total Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_sprints']; ?></div>
                <div class="stat-label">Total Sprints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_tasks']; ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed_tasks']; ?></div>
                <div class="stat-label">Completed Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_reclamations']; ?></div>
                <div class="stat-label">Total Reclamations</div>
            </div>
            <?php if ($isAdmin): ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['open_reclamations']; ?></div>
                    <div class="stat-label">Open Reclamations</div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="content-grid">
            <!-- Quick Actions -->
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="projets.php" class="btn">View Projects</a>
                    <?php if ($isAdmin): ?>
                        <a href="create_projet.php" class="btn btn-success">Create Project</a>
                        <a href="create_sprint.php?projet_id=1" class="btn btn-success">Add Sprint</a>
                        <a href="register.php" class="btn btn-warning">Add User</a>
                    <?php endif; ?>
                    <a href="reclamations.php" class="btn btn-danger">
                        Reclamations 
                        <?php if ($isAdmin && $stats['open_reclamations'] > 0): ?>
                            (<?php echo $stats['open_reclamations']; ?> open)
                        <?php endif; ?>
                    </a>
                </div>
                
                <?php if ($isAdmin): ?>
                    <h3 style="margin-top: 30px; margin-bottom: 15px;">System Overview</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li>📊 <?php echo $stats['total_projects']; ?> projects active</li>
                        <li>🏃 <?php echo $stats['total_sprints']; ?> sprints running</li>
                        <li>✅ <?php echo $stats['completed_tasks']; ?> tasks completed</li>
                        <li>⚠️ <?php echo $stats['open_reclamations']; ?> issues to resolve</li>
                    </ul>
                <?php else: ?>
                    <h3 style="margin-top: 30px; margin-bottom: 15px;">Your Tasks</h3>
                    <p>View and manage your assigned tasks from the projects page.</p>
                <?php endif; ?>
            </div>
            
            <!-- Recent Activity (Admin Only) -->
            <?php if ($isAdmin): ?>
                <div class="card">
                    <h2>Recent Activity</h2>
                    <?php if (empty($recentLogs)): ?>
                        <p style="color: #6c757d;">No recent activity found.</p>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="log-item">
                                <div class="log-time"><?php echo $log->getCreatedAt()->format('Y-m-d H:i:s'); ?></div>
                                <div class="log-action"><?php echo htmlspecialchars($log->getAction()); ?></div>
                                <div class="log-description"><?php echo htmlspecialchars($log->getDescription() ?? ''); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
