<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../repositories/TacheRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/TacheService.php';

$auth = new Auth();
$auth->requireLogin();

$sprintId = Helpers::getGet('sprint_id');
if (!$sprintId) {
    Helpers::flash('error', 'Sprint ID is required');
    Helpers::redirect('projets.php');
}

$tacheRepository = new TacheRepository();
$sprintRepository = new SprintRepository();
$userRepository = new UserRepository();
$tacheService = new TacheService();

$taches = [];
$sprint = null;
$users = [];
$error = '';

try {
    $sprint = $sprintRepository->findById($sprintId);
    if (!$sprint) {
        Helpers::flash('error', 'Sprint not found');
        Helpers::redirect('projets.php');
    }
    
    $taches = $tacheRepository->findBySprintId($sprintId);
    $users = $userRepository->findAll();
} catch (Exception $e) {
    $error = 'Failed to load tasks: ' . $e->getMessage();
}

$user = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - <?php echo htmlspecialchars($sprint->getNom()); ?> - Scrum Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        h1 { color: #333; margin: 0; }
        .breadcrumb { color: #6c757d; font-size: 14px; margin-top: 5px; }
        .breadcrumb a { color: #007bff; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .nav { display: flex; gap: 15px; }
        .nav a { color: #007bff; text-decoration: none; padding: 8px 16px; border-radius: 4px; transition: background 0.3s; }
        .nav a:hover { background: #e9ecef; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .error { color: #dc3545; margin-bottom: 20px; padding: 10px; background: #f8d7da; border-radius: 4px; }
        .success { color: #155724; margin-bottom: 20px; padding: 10px; background: #d4edda; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; color: #495057; }
        tr:hover { background: #f8f9fa; }
        .status-badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-a_faire { background: #f8d7da; color: #721c24; }
        .status-en_cours { background: #fff3cd; color: #856404; }
        .status-termine { background: #d4edda; color: #155724; }
        .actions { display: flex; gap: 5px; flex-wrap: wrap; }
        .empty { text-align: center; padding: 40px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Tasks</h1>
                <div class="breadcrumb">
                    <a href="projets.php">Projects</a> → 
                    <a href="sprints.php?projet_id=<?php echo $sprint->getProjetId(); ?>">Sprints</a> → 
                    <?php echo htmlspecialchars($sprint->getNom()); ?>
                </div>
            </div>
            <div class="nav">
                <span>Welcome, <?php echo htmlspecialchars($user->getUsername()); ?> (<?php echo htmlspecialchars($user->getRole()); ?>)</span>
                <a href="sprints.php?projet_id=<?php echo $sprint->getProjetId(); ?>">← Back to Sprints</a>
                <a href="admin_dashboard.php">Dashboard</a>
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
        
        <div style="margin-bottom: 20px;">
            <a href="create_tache.php?sprint_id=<?php echo $sprintId; ?>" class="btn btn-success">+ Create New Task</a>
            <a href="sprints.php?projet_id=<?php echo $sprint->getProjetId(); ?>" class="btn btn-secondary">← Back to Sprints</a>
        </div>
        
        <?php if (empty($taches)): ?>
            <div class="empty">
                <p>No tasks found for this sprint.</p>
                <p><a href="create_tache.php?sprint_id=<?php echo $sprintId; ?>">Create your first task</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($taches as $tache): ?>
                        <tr>
                            <td><?php echo $tache->getId(); ?></td>
                            <td><?php echo htmlspecialchars($tache->getTitre()); ?></td>
                            <td><?php echo htmlspecialchars($tache->getDescription() ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $tache->getStatut(); ?>">
                                    <?php echo htmlspecialchars($tache->getStatut()); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $assignee = null;
                                foreach ($users as $u) {
                                    if ($u->getId() === $tache->getAssigneA()) {
                                        $assignee = $u;
                                        break;
                                    }
                                }
                                echo $assignee ? htmlspecialchars($assignee->getUsername()) : 'Unassigned';
                                ?>
                            </td>
                            <td>
                                <?php 
                                $creator = null;
                                foreach ($users as $u) {
                                    if ($u->getId() === $tache->getCreatedBy()) {
                                        $creator = $u;
                                        break;
                                    }
                                }
                                echo $creator ? htmlspecialchars($creator->getUsername()) : 'Unknown';
                                ?>
                            </td>
                            <td class="actions">
                                <?php if ($user->canManageEverything() || $tache->getAssigneA() === $user->getId()): ?>
                                    <?php if ($tache->getStatut() !== 'termine'): ?>
                                        <a href="create_tache.php?action=complete&id=<?php echo $tache->getId(); ?>&sprint_id=<?php echo $sprintId; ?>" class="btn btn-success btn-sm">Complete</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if ($user->canManageEverything()): ?>
                                    <a href="create_tache.php?action=assign&id=<?php echo $tache->getId(); ?>&sprint_id=<?php echo $sprintId; ?>" class="btn btn-warning btn-sm">Assign</a>
                                <?php endif; ?>
                                
                                <a href="reclamations.php?task_id=<?php echo $tache->getId(); ?>" class="btn btn-danger btn-sm">Report Issue</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
