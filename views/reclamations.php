<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../repositories/ReclamationRepository.php';
require_once __DIR__ . '/../repositories/TacheRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/ReclamationService.php';
$auth = new Auth();
$auth->requireLogin();
$reclamationService = new ReclamationService();
$reclamationRepository = new ReclamationRepository();
$tacheRepository = new TacheRepository();
$userRepository = new UserRepository();
$reclamations = [];
$task = null;
$error = '';
$showCreateForm = false;
$user = $auth->getCurrentUser();
$isAdmin = $user->canManageEverything();
$taskId = Helpers::getGet('task_id');
if ($taskId) {
    $task = $tacheRepository->findById($taskId);
    if ($task) {
        $showCreateForm = true;
    }
}
if (Helpers::isPost() && Helpers::getPost('action') === 'create') {
    $description = Helpers::sanitize(Helpers::getPost('description'));
    $taskId = Helpers::getPost('task_id');
    if (empty($description)) {
        $error = 'Description is required';
    } elseif (empty($taskId)) {
        $error = 'Task ID is required';
    } else {
        try {
            $reclamationService->createReclamation($user->getId(), $taskId, $description);
            Helpers::flash('success', 'Reclamation created successfully!');
            Helpers::redirect('reclamations.php');
        } catch (Exception $e) {
            $error = 'Failed to create reclamation: ' . $e->getMessage();
        }
    }
}
if (Helpers::isPost() && Helpers::getPost('action') === 'resolve') {
    $reclamationId = Helpers::getPost('reclamation_id');
    if ($reclamationId) {
        try {
            $reclamationService->resolveReclamation($user->getId(), $reclamationId);
            Helpers::flash('success', 'Reclamation resolved successfully!');
        } catch (Exception $e) {
            $error = 'Failed to resolve reclamation: ' . $e->getMessage();
        }
    }
}
try {
    if ($isAdmin) {
        $reclamations = $reclamationService->getAllReclamations();
    } else {
        $reclamations = $reclamationService->getReclamationsByUser($user->getId());
    }
} catch (Exception $e) {
    $error = 'Failed to load reclamations: ' . $e->getMessage();
}
$allTasks = [];
try {
    $allTasks = $tacheRepository->findAll();
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamations - Scrum Management</title>
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
        .form-container { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        select, textarea { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; 
        }
        textarea { height: 80px; resize: vertical; }
        button { 
            padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 14px; 
        }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; color: #495057; }
        tr:hover { background: #f8f9fa; }
        .status-badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-open { background: #f8d7da; color: #721c24; }
        .status-resolved { background: #d4edda; color: #155724; }
        .actions { display: flex; gap: 5px; }
        .actions a { padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .empty { text-align: center; padding: 40px; color: #6c757d; }
        .task-info { background: #e9ecef; padding: 10px; border-radius: 3px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reclamations</h1>
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
        <!-- Create Reclamation Form -->
        <?php if ($showCreateForm || !$isAdmin): ?>
            <div class="form-container">
                <h3><?php echo $task ? 'Report Issue for Task' : 'Create New Reclamation'; ?></h3>
                <?php if ($task): ?>
                    <div class="task-info">
                        <strong>Task:</strong> <?php echo htmlspecialchars($task->getTitre()); ?><br>
                        <strong>ID:</strong> <?php echo $task->getId(); ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <?php if (!$task): ?>
                        <div class="form-group">
                            <label for="task_id">Select Task:</label>
                            <select id="task_id" name="task_id" required>
                                <option value="">Select a task</option>
                                <?php foreach ($allTasks as $t): ?>
                                    <option value="<?php echo $t->getId(); ?>" <?php echo Helpers::getPost('task_id') == $t->getId() ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($t->getTitre()); ?> (ID: <?php echo $t->getId(); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="task_id" value="<?php echo $task->getId(); ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="description">Issue Description:</label>
                        <textarea id="description" name="description" required placeholder="Describe the issue or problem with this task..."><?php echo Helpers::getPost('description'); ?></textarea>
                    </div>
                    <button type="submit">Submit Reclamation</button>
                </form>
            </div>
        <?php endif; ?>
        <!-- Reclamations List -->
        <?php if (empty($reclamations)): ?>
            <div class="empty">
                <p>No reclamations found.</p>
                <?php if ($isAdmin): ?>
                    <p>Members can create reclamations from the task list.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Task</th>
                        <th>Description</th>
                        <th>Reported By</th>
                        <th>Status</th>
                        <th>Created</th>
                        <?php if ($isAdmin): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reclamations as $reclamation): ?>
                        <tr>
                            <td><?php echo $reclamation->getId(); ?></td>
                            <td>
                                <a href="taches.php?sprint_id=<?php echo $reclamation->getTaskId(); ?>" style="color: #007bff; text-decoration: none;">
                                    Task #<?php echo $reclamation->getTaskId(); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($reclamation->getDescription()); ?></td>
                            <td><?php echo htmlspecialchars($reclamation->getUserId()); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $reclamation->getStatut(); ?>">
                                    <?php echo htmlspecialchars($reclamation->getStatut()); ?>
                                </span>
                            </td>
                            <td><?php echo $reclamation->getCreatedAt()->format('Y-m-d H:i'); ?></td>
                            <?php if ($isAdmin): ?>
                                <td class="actions">
                                    <?php if ($reclamation->isOpen()): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="resolve">
                                            <input type="hidden" name="reclamation_id" value="<?php echo $reclamation->getId(); ?>">
                                            <button type="submit" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Resolve</button>
                                        </form>
                                    <?php else: ?>
                                        <small>Resolved: <?php echo $reclamation->getResolvedAt()->format('Y-m-d H:i'); ?></small>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
