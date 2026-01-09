<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

$auth = new Auth();
$errors = [];

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    Helpers::redirect('admin_dashboard.php');
}

// Handle registration form submission
if (Helpers::isPost()) {
    $username = Helpers::sanitize(Helpers::getPost('username'));
    $email = Helpers::sanitizeEmail(Helpers::getPost('email'));
    $password = Helpers::getPost('password');
    $confirmPassword = Helpers::getPost('confirm_password');
    $role = Helpers::getPost('role');
    
    // Validation
    $errors = Helpers::validateRequired($_POST, ['username', 'email', 'password', 'confirm_password', 'role']);
    
    if (empty($errors)) {
        if (!Helpers::validateEmail($email)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            try {
                $userRepository = new UserRepository();
                
                // Check if username or email already exists
                if ($userRepository->findByUsername($username)) {
                    $errors['username'] = 'Username already exists';
                }
                
                if ($userRepository->findByEmail($email)) {
                    $errors['email'] = 'Email already exists';
                }
                
                if (empty($errors)) {
                    // Create new user
                    $hashedPassword = Auth::hashPassword($password);
                    $user = new User($username, $email, $hashedPassword, $role);
                    
                    if ($userRepository->save($user)) {
                        Helpers::flash('success', 'Registration successful! Please login.');
                        Helpers::redirect('login.php');
                    } else {
                        $errors['general'] = 'Registration failed';
                    }
                }
            } catch (Exception $e) {
                $errors['general'] = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Scrum Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="password"], input[type="email"], select, textarea { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; 
        }
        button { 
            width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 16px; 
        }
        button:hover { background: #218838; }
        .error { color: #dc3545; font-size: 14px; margin-top: 5px; }
        .error-general { color: #dc3545; margin-bottom: 20px; padding: 10px; background: #f8d7da; border-radius: 4px; }
        .link { text-align: center; margin-top: 20px; }
        .link a { color: #007bff; text-decoration: none; }
        .link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        
        <?php if (isset($errors['general'])): ?>
            <div class="error-general"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required value="<?php echo Helpers::getPost('username'); ?>">
                <?php if (isset($errors['username'])): ?>
                    <div class="error"><?php echo $errors['username']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?php echo Helpers::getPost('email'); ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="error"><?php echo $errors['confirm_password']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="membre" <?php echo Helpers::getPost('role') === 'membre' ? 'selected' : ''; ?>>Membre</option>
                    <option value="chef" <?php echo Helpers::getPost('role') === 'chef' ? 'selected' : ''; ?>>Chef</option>
                    <option value="admin" <?php echo Helpers::getPost('role') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <div class="error"><?php echo $errors['role']; ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <div class="link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
