<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../services/ProjetService.php';
$auth = new Auth();
$auth->requireLogin();
$projetService = new ProjetService();
$projets = [];
$error = '';
try {
    $projets = $projetService->getAllProjets();
} catch (Exception $e) {
    $error = 'Failed to load projects: ' . $e->getMessage();
}
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - Scrum Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        h1 { color: #333; margin: 0; }
        .nav { display: flex; gap: 15px; }
        .nav a { color: #007bff; text-decoration: none; padding: 8px 16px; border-radius: 4px; transition: background 0.3s; }
        .nav a:hover { background: #e9ecef; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .error { color: #dc3545; margin-bottom: 20px; padding: 10px; background: #f8d7da; border-radius: 4px; }
        .success { color: #155724; margin-bottom: 20px; padding: 10px; background: #d4edda; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; color: #495057; }
        tr:hover { background: #f8f9fa; }
        .status-badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-en_cours { background: #cce5ff; color: #004085; }
        .status-termine { background: #d4edda; color: #155724; }
        .status-en_attente { background: #fff3cd; color: #856404; }
        .actions { display: flex; gap: 5px; }
        .actions a { padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .empty { text-align: center; padding: 40px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Projects</h1>
            <div class="nav">
                <span>Welcome, <?php echo htmlspecialchars($user->getUsername()); ?> (<?php echo htmlspecialchars($user->getRole()); ?>)</span>
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
            <a href="create_projet.php" class="btn btn-success">+ Create New Project</a>
        </div>
        <?php if (empty($projets)): ?>
            <div class="empty">
                <p>No projects found.</p>
                <p><a href="create_projet.php">Create your first project</a></p>
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
                    <?php foreach ($projets as $projet): ?>
                        <tr>
                            <td><?php echo $projet->getId(); ?></td>
                            <td><?php echo htmlspecialchars($projet->getNom()); ?></td>
                            <td><?php echo htmlspecialchars($projet->getDescription() ?? 'N/A'); ?></td>
                            <td><?php echo $projet->getDateDebut() ? $projet->getDateDebut()->format('Y-m-d') : 'N/A'; ?></td>
                            <td><?php echo $projet->getDateFin() ? $projet->getDateFin()->format('Y-m-d') : 'N/A'; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $projet->getStatut(); ?>">
                                    <?php echo htmlspecialchars($projet->getStatut()); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="sprints.php?projet_id=<?php echo $projet->getId(); ?>" class="btn btn-warning">View Sprints</a>
                                <?php if ($user->canManageEverything()): ?>
                                    <a href="create_projet.php?id=<?php echo $projet->getId(); ?>" class="btn">Edit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
