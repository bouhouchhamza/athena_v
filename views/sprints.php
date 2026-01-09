<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../services/SprintService.php';
require_once __DIR__ . '/../services/ProjetService.php';
$auth = new Auth();
$auth->requireLogin();
$projetId = Helpers::getGet('projet_id');
if (!$projetId) {
    Helpers::flash('error', 'Project ID is required');
    Helpers::redirect('projets.php');
}
$sprintService = new SprintService();
$projetService = new ProjetService();
$sprints = [];
$projet = null;
$error = '';
try {
    $projet = $projetService->getProjetById($projetId);
    if (!$projet) {
        Helpers::flash('error', 'Project not found');
        Helpers::redirect('projets.php');
    }
    $sprints = $sprintService->getSprintsByProjet($projetId);
} catch (Exception $e) {
    $error = 'Failed to load sprints: ' . $e->getMessage();
}
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprints - <?php echo htmlspecialchars($projet->getNom()); ?> - Scrum Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        h1 { color: #333; margin: 0; }
        .project-info { color: #6c757d; font-size: 14px; margin-top: 5px; }
        .nav { display: flex; gap: 15px; }
        .nav a { color: #007bff; text-decoration: none; padding: 8px 16px; border-radius: 4px; transition: background 0.3s; }
        .nav a:hover { background: #e9ecef; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
        .error { color: #dc3545; margin-bottom: 20px; padding: 10px; background: #f8d7da; border-radius: 4px; }
        .success { color: #155724; margin-bottom: 20px; padding: 10px; background: #d4edda; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; color: #495057; }
        tr:hover { background: #f8f9fa; }
        .status-badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-planifie { background: #cce5ff; color: #004085; }
        .status-en_cours { background: #fff3cd; color: #856404; }
        .status-termine { background: #d4edda; color: #155724; }
        .actions { display: flex; gap: 5px; }
        .actions a { padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .empty { text-align: center; padding: 40px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Sprints</h1>
                <div class="project-info">Project: <?php echo htmlspecialchars($projet->getNom()); ?></div>
            </div>
            <div class="nav">
                <span>Welcome, <?php echo htmlspecialchars($user->getUsername()); ?> (<?php echo htmlspecialchars($user->getRole()); ?>)</span>
                <a href="projets.php">← Back to Projects</a>
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
            <a href="create_sprint.php?projet_id=<?php echo $projetId; ?>" class="btn btn-success">+ Create New Sprint</a>
            <a href="projets.php" class="btn btn-secondary">← Back to Projects</a>
        </div>
        <?php if (empty($sprints)): ?>
            <div class="empty">
                <p>No sprints found for this project.</p>
                <p><a href="create_sprint.php?projet_id=<?php echo $projetId; ?>">Create your first sprint</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sprints as $sprint): ?>
                        <tr>
                            <td><?php echo $sprint->getId(); ?></td>
                            <td><?php echo htmlspecialchars($sprint->getNom()); ?></td>
                            <td><?php echo htmlspecialchars($sprint->getDescription() ?? 'N/A'); ?></td>
                            <td><?php echo $sprint->getDateDebut() ? $sprint->getDateDebut()->format('Y-m-d') : 'N/A'; ?></td>
                            <td><?php echo $sprint->getDateFin() ? $sprint->getDateFin()->format('Y-m-d') : 'N/A'; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $sprint->getStatut(); ?>">
                                    <?php echo htmlspecialchars($sprint->getStatut()); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="taches.php?sprint_id=<?php echo $sprint->getId(); ?>" class="btn btn-warning">View Tasks</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
