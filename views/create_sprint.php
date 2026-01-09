<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../services/SprintService.php';
require_once __DIR__ . '/../services/ProjetService.php';
require_once __DIR__ . '/../entities/Sprint.php';
$auth = new Auth();
$auth->requireLogin();
$user = $auth->getCurrentUser();
if (!$user->canManageEverything()) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Access denied';
    exit();
}
$projetId = Helpers::getGet('projet_id');
if (!$projetId) {
    Helpers::flash('error', 'Project ID is required');
    Helpers::redirect('projets.php');
}
$sprintService = new SprintService();
$projetService = new ProjetService();
$projet = null;
$errors = [];
try {
    $projet = $projetService->getProjetById($projetId);
    if (!$projet) {
        Helpers::flash('error', 'Project not found');
        Helpers::redirect('projets.php');
    }
} catch (Exception $e) {
    Helpers::flash('error', 'Failed to load project: ' . $e->getMessage());
    Helpers::redirect('projets.php');
}
if (Helpers::isPost()) {
    $nom = Helpers::sanitize(Helpers::getPost('nom'));
    $description = Helpers::sanitize(Helpers::getPost('description'));
    $dateDebut = Helpers::getPost('date_debut');
    $dateFin = Helpers::getPost('date_fin');
    $statut = Helpers::getPost('statut');
    $errors = Helpers::validateRequired($_POST, ['nom', 'statut']);
    if (empty($errors)) {
        try {
            $dateDebutObj = $dateDebut ? new DateTime($dateDebut) : null;
            $dateFinObj = $dateFin ? new DateTime($dateFin) : null;
            $sprintService->createSprint($user->getId(), $projetId, $nom, $description, $dateDebutObj, $dateFinObj);
            Helpers::flash('success', 'Sprint created successfully!');
            Helpers::redirect('sprints.php?projet_id=' . $projetId);
        } catch (Exception $e) {
            $errors['general'] = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sprint - <?php echo htmlspecialchars($projet->getNom()); ?> - Scrum Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { margin-bottom: 30px; }
        h1 { color: #333; margin: 0; }
        .project-info { color: #6c757d; font-size: 14px; margin-top: 5px; }
        .nav { margin-top: 15px; }
        .nav a { color: #007bff; text-decoration: none; margin-right: 15px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="date"], select, textarea { 
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create New Sprint</h1>
            <div class="project-info">Project: <?php echo htmlspecialchars($projet->getNom()); ?></div>
            <div class="nav">
                <a href="sprints.php?projet_id=<?php echo $projetId; ?>">← Back to Sprints</a>
                <a href="admin_dashboard.php">Dashboard</a>
            </div>
        </div>
        <?php if (isset($errors['general'])): ?>
            <div class="error-general"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="nom">Sprint Name:</label>
                <input type="text" id="nom" name="nom" required value="<?php echo Helpers::getPost('nom'); ?>">
                <?php if (isset($errors['nom'])): ?>
                    <div class="error"><?php echo $errors['nom']; ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo Helpers::getPost('description'); ?></textarea>
            </div>
            <div class="form-group">
                <label for="date_debut">Start Date:</label>
                <input type="date" id="date_debut" name="date_debut" value="<?php echo Helpers::getPost('date_debut'); ?>">
            </div>
            <div class="form-group">
                <label for="date_fin">End Date:</label>
                <input type="date" id="date_fin" name="date_fin" value="<?php echo Helpers::getPost('date_fin'); ?>">
            </div>
            <div class="form-group">
                <label for="statut">Status:</label>
                <select id="statut" name="statut" required>
                    <option value="">Select Status</option>
                    <option value="planifie" <?php echo Helpers::getPost('statut') === 'planifie' ? 'selected' : ''; ?>>Planifié</option>
                    <option value="en_cours" <?php echo Helpers::getPost('statut') === 'en_cours' ? 'selected' : ''; ?>>En Cours</option>
                    <option value="termine" <?php echo Helpers::getPost('statut') === 'termine' ? 'selected' : ''; ?>>Terminé</option>
                </select>
                <?php if (isset($errors['statut'])): ?>
                    <div class="error"><?php echo $errors['statut']; ?></div>
                <?php endif; ?>
            </div>
            <div>
                <button type="submit">Create Sprint</button>
                <a href="sprints.php?projet_id=<?php echo $projetId; ?>" class="btn-secondary" style="display: inline-block; padding: 12px 24px; text-decoration: none; background: #6c757d; color: white; border-radius: 4px;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
