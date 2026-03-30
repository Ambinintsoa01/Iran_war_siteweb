<?php
session_start();

// Si déjà connecté, rediriger vers dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Inclusion du fichier de configuration
    require_once 'includes/config.php';

    try {
        // Connexion DB
        $pdo = getPDO();

        $stmt = $pdo->prepare("SELECT user_id, password FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['password'] === $password) { 
            $_SESSION['user_id'] = $user['user_id'];
            header('Location: admin/dashboard.php');
            exit();
        } else {
            error_log("User not found for email: " . $email);
            $message = 'Email ou mot de passe incorrect.' . $email . " - " . $password;
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        $message = 'Erreur de base de données.';
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Backoffice</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Connexion Backoffice</h1>
            <?php if ($message): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" value="admin@gmail.com" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe:</label>
                    <input type="password" id="password" name="password" value="admin!@#123" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
        </div>
    </div>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>