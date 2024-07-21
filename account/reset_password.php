<?php
session_start();

// Informations de connexion à la base de données
$host = '109.234.166.35';
$dbname = 'sc1mifa5051_launcher';
$username = 'sc1mifa5051_launcher';
$password = 'DiumCraft2023@';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (strlen($newPassword) < 8) {
        $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = "Les nouveaux mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE token = :token");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if ($user) {
            $updateStmt = $pdo->prepare("UPDATE users SET password = :password, token = NULL WHERE id = :id");
            $updateStmt->execute([
                'password' => $hashedPassword,
                'id' => $user['id']
            ]);
            $success = true;
        } else {
            $errors[] = "Token invalide ou expiré.";
        }
    }
} else {
    if (!isset($_GET['token'])) {
        die("Token manquant.");
    }
    $token = $_GET['token'];
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Réinitialiser le mot de passe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .card {
            background-color: #1e1e1e;
            border: none;
        }
        .form-control {
            background-color: #333;
            border: 1px solid #444;
            color: #fff;
        }
        .form-control:focus {
            background-color: #333;
            color: #fff;
        }
        .btn-primary {
            background-color: #6200ea;
            border: none;
        }
        .btn-primary:hover {
            background-color: #3700b3;
        }
        .alert {
            background-color: #333;
            border: 1px solid #444;
        }
        .alert-danger {
            color: #ff4081;
        }
        .alert-success {
            color: #00e676;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Réinitialiser le mot de passe</h2>
                        <?php if ($success) : ?>
                            <div class="alert alert-success">
                                Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant <a href="connexion.php">vous connecter</a>.
                            </div>
                        <?php elseif (!empty($errors)) : ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error) : ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
