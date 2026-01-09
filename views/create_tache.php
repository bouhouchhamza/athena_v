<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../repositories/TacheRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/TacheService.php';
require_once __DIR__ . '/../entities/Tache.php';
$auth = new Auth();
$auth->requireLogin();
$sprintId = Helpers::getGet('sprint_id');
$action = Helpers::getGet('action');
$taskId = Helpers::getGet('id');
if (!$sprintId) {
    Helpers::flash('error', 'Sprint ID is required');
    Helpers::redirect('projets.php');
}
$tacheService = new TacheService();
$sprintRepository = new SprintRepository();
$userRepository = new UserRepository();
$sprint = null;
$tache = null;
$users = [];
$errors = [];
try {
    $sprint = $sprintRepository->findById($sprintId);
    if (!$sprint) {
        Helpers::flash('error', 'Sprint not found');
        Helpers::redirect('projets.php');
    }
    $users = $userRepository->findAll();
    if ($action && $taskId) {
        $tache = $tacheService->getTaskById($taskId);
        if (!$tache) {
            Helpers::flash('error', 'Task not found');
            Helpers::redirect('taches.php?sprint_id=' . $sprintId);
        }
        if ($action === 'complete') {
            try {
                $tacheService->completeTask($auth->getCurrentUser()->getId(), $taskId);
                Helpers::flash('success', 'Task completed successfully!');
                Helpers::redirect('taches.php?sprint_id=' . $sprintId);
            } catch (Exception $e) {
                $errors['general'] = $e->getMessage();
            }
        } elseif ($action === 'assign') {
            if (Helpers::isPost()) {
                $assigneeId = Helpers::getPost('assignee_id');
                if ($assigneeId) {
                    try {
                        $tacheService->assignTask($auth->getCurrentUser()->getId(), $taskId, $assigneeId);
                        Helpers::flash('success', 'Task assigned successfully!');
                        Helpers::redirect('taches.php?sprint_id=' . $sprintId);
                    } catch (Exception $e) {
                        $errors['general'] = $e->getMessage();
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    $errors['general'] = 'Error: ' . $e->getMessage();
}
if (Helpers::isPost() && !$action) {
    $titre = Helpers::sanitize(Helpers::getPost('titre'));
    $description = Helpers::sanitize(Helpers::getPost('description'));
    $assigneA = Helpers::getPost('assigne_a');
    $errors = Helpers::validateRequired($_POST, ['titre']);
    if (empty($errors)) {
        try {
            $tache = $tacheService->createTask($auth->getCurrentUser()->getId(), $sprintId, $titre, $description);
            if ($assigneA) {
                $tacheService->assignTask($auth->getCurrentUser()->getId(), $tache->getId(), $assigneA);
            }
            Helpers::flash('success', 'Task created successfully!');
            Helpers::redirect('taches.php?sprint_id=' . $sprintId);
        } catch (Exception $e) {
            $errors['general'] = 'Error: ' . $e->getMessage();
        }
    }
}
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action ? ucfirst($action) . ' Task' : 'Create Task'; ?> - <?php echo htmlspecialchars($sprint->getNom()); ?> - Scrum Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { margin-bottom: 30px; }
        h1 { color: #333; margin: 0; }
        .breadcrumb { color: #6c757d; font-size: 14px; margin-top: 5px; }
        .breadcrumb a { color: #007bff; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .nav { margin-top: 15px; }
        .nav a { color: #007bff; text-decoration: none; margin-right: 15px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], select, textarea { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; 
        }
        textarea { height: 100px; resize: vertical; }
        button { 
            padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 16px; margin-right: 10px; 
        }
        button:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
        .error { color: #dc3545; font-size: 14px; margin-top: 5px; }
        .error-general { color: #dc3545; margin-bottom: 20px; padding: 10px; background: #f8d7da; border-radius: 4px; }
        .task-info { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php 
                if ($action === 'assign') echo 'Assign Task';
                elseif ($action === 'complete') echo 'Complete Task';
                else echo 'Create New Task'; 
            ?></h1>
            <div class="breadcrumb">
                <a href="projets.php">Projects</a> → 
                <a href="sprints.php?projet_id=<?php echo $sprint->getProjetId(); ?>">Sprints</a> → 
                <a href="taches.php?sprint_id=<?php echo $sprintId; ?>">Tasks</a> → 
                <?php echo htmlspecialchars($sprint->getNom()); ?>
            </div>
            <div class="nav">
                <a href="taches.php?sprint_id=<?php echo $sprintId; ?>">← Back to Tasks</a>
                <a href="admin_dashboard.php">Dashboard</a>
            </div>
        </div>
        <?php if (isset($errors['general'])): ?>
            <div class="error-general"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        <?php if ($action === 'assign' && $tache): ?>
            <div class="task-info">
                <strong>Task:</strong> <?php echo htmlspecialchars($tache->getTitre()); ?><br>
                <strong>Current Status:</strong> <?php echo htmlspecialchars($tache->getStatut()); ?>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label for="assignee_id">Assign To:</label>
                    <select id="assignee_id" name="assignee_id" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u->getId(); ?>" <?php echo $tache->getAssigneA() === $u->getId() ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u->getUsername()); ?> (<?php echo htmlspecialchars($u->getRole()); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit">Assign Task</button>
                    <a href="taches.php?sprint_id=<?php echo $sprintId; ?>" class="btn-secondary" style="display: inline-block; padding: 12px 24px; text-decoration: none; background: #6c757d; color: white; border-radius: 4px;">Cancel</a>
                </div>
            </form>
        <?php elseif ($action === 'complete' && $tache): ?>
            <div class="task-info">
                <strong>Task:</strong> <?php echo htmlspecialchars($tache->getTitre()); ?><br>
                <strong>Description:</strong> <?php echo htmlspecialchars($tache->getDescription() ?? 'N/A'); ?><br>
                <strong>Current Status:</strong> <?php echo htmlspecialchars($tache->getStatut()); ?>
            </div>
            <form method="POST">
                <p>Are you sure you want to mark this task as completed?</p>
                <div>
                    <button type="submit">Complete Task</button>
                    <a href="taches.php?sprint_id=<?php echo $sprintId; ?>" class="btn-secondary" style="display: inline-block; padding: 12px 24px; text-decoration: none; background: #6c757d; color: white; border-radius: 4px;">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="titre">Task Title:</label>
                    <input type="text" id="titre" name="titre" required value="<?php echo Helpers::getPost('titre'); ?>">
                    <?php if (isset($errors['titre'])): ?>
                        <div class="error"><?php echo $errors['titre']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?php echo Helpers::getPost('description'); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="assigne_a">Assign To:</label>
                    <select id="assigne_a" name="assigne_a">
                        <option value="">Unassigned</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u->getId(); ?>" <?php echo Helpers::getPost('assigne_a') == $u->getId() ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u->getUsername()); ?> (<?php echo htmlspecialchars($u->getRole()); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit">Create Task</button>
                    <a href="taches.php?sprint_id=<?php echo $sprintId; ?>" class="btn-secondary" style="display: inline-block; padding: 12px 24px; text-decoration: none; background: #6c757d; color: white; border-radius: 4px;">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
