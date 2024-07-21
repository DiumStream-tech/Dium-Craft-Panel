<?php
session_start();

// Inclure le fichier de configuration
require_once '../config.php';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . $databaseConfig['host'] . ";dbname=" . $databaseConfig['dbname'] . ";charset=utf8mb4", $databaseConfig['username'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$errors = [];
$success = false;
$consoleError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse e-mail invalide.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Générer un token unique
            $token = bin2hex(random_bytes(32));
            
            // Mettre à jour le token dans la base de données
            $updateStmt = $pdo->prepare("UPDATE users SET token = :token WHERE id = :id");
            $updateStmt->execute([
                'token' => $token,
                'id' => $user['id']
            ]);

            // Préparer l'e-mail
            $to = $email;
            $subject = "Réinitialisation de votre mot de passe";
            $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/account/reset_password.php?token=" . $token;
            $message = "Bonjour,\n\nVous avez demandé une réinitialisation de votre mot de passe. Cliquez sur le lien suivant pour réinitialiser votre mot de passe :\n\n$reset_link\n\nSi vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail.";

            // En-têtes de l'e-mail
            $headers = "From: " . $smtpConfig['from'] . "\r\n";
            $headers .= "Reply-To: " . $smtpConfig['from'] . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // Configuration SMTP
            ini_set('SMTP', $smtpConfig['host']);
            ini_set('smtp_port', $smtpConfig['port']);
            ini_set('sendmail_from', $smtpConfig['from']);

            // Envoyer l'e-mail
            if (mail($to, $subject, $message, $headers)) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de l'envoi de l'e-mail. Veuillez réessayer.";
                $consoleError = "Erreur SMTP : Impossible d'envoyer l'e-mail. Vérifiez la configuration SMTP.";
            }
        } else {
            $errors[] = "Aucun compte associé à cette adresse e-mail.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mot de passe oublié</title>
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
                        <h2 class="card-title">Mot de passe oublié</h2>
                        <?php if ($success) : ?>
                            <div class="alert alert-success">
                                Un e-mail avec les instructions pour réinitialiser votre mot de passe a été envoyé à votre adresse e-mail.
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = '/';
                                }, 5000);
                            </script>
                        <?php elseif (!empty($errors)) : ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error) : ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse e-mail</label>
                                <input type="email" name="email" id="email" class="form-control" required>
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
    <?php if (!empty($consoleError)) : ?>
    <script>
        console.error("<?php echo $consoleError; ?>");
    </script>
    <?php endif; ?>
</body>
</html>
