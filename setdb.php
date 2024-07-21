<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Configuration du Panel</title>
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
    </style>
</head>
<body>
<div class="container">
    <h1 id="config-title" class="mt-5 mb-4">Configuration du Panel</h1>

    <?php
    $configFilePath = 'config.php';

    if (!file_exists($configFilePath)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $host = $_POST['host'];
            $dbname = $_POST['dbname'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $smtpHost = $_POST['smtp_host'];
            $smtpPort = $_POST['smtp_port'];
            $smtpUsername = $_POST['smtp_username'];
            $smtpPassword = $_POST['smtp_password'];
            $smtpFrom = $_POST['smtp_from'];

            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                $configContent = <<<EOT
<?php

\$databaseConfig = [
    'host' => '$host',
    'dbname' => '$dbname',
    'username' => '$username',
    'password' => '$password',
];

\$smtpConfig = [
    'host' => '$smtpHost',
    'port' => $smtpPort,
    'username' => '$smtpUsername',
    'password' => '$smtpPassword',
    'from' => '$smtpFrom',
];

?>
EOT;
                file_put_contents($configFilePath, $configContent);
                $sqlFile = 'utils/panel.sql';
                $sqlCommands = file_get_contents($sqlFile);
                $pdo->exec($sqlCommands);
                header('Location: account/register');
                exit();
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger" role="alert">';
                echo "Erreur de connexion à la base de données : " . $e->getMessage();
                echo '</div>';
            }
        } else {
            echo '<form class="form-container" method="post">';
            echo '<h2>Configuration de la base de données</h2>';
            echo '<div class="form-group">';
            echo '<label for="host">Hôte:</label>';
            echo '<input type="text" class="form-control" name="host" required>';
            echo '</div>';
            echo '<div class="form-group">';
            echo '<label for="dbname">Nom de la base de données:</label>';
            echo '<input type="text" class="form-control" name="dbname" required>';
            echo '</div>';
            echo '<div class="form-group">';
            echo '<label for="username">Nom d utilisateur:</label>';
            echo '<input type="text" class="form-control" name="username" required>';
            echo '</div>';
            echo '<div class="form-group">';
            echo '<label for="password">Mot de passe:</label>';
            echo '<input type="password" class="form-control" name="password">';
            echo '</div>';
            echo '<h2 class="mt-4">Configuration SMTP</h2>';
            echo '<div class="form-group">';
            echo '<label for="smtp_host">Hôte SMTP:</label>';
            echo '<input type="text" class="form-control" name="smtp_host" required>';
            echo '</div>';
            echo '<div class="form-group">';
            echo '<label for="smtp_port">Port SMTP:</label>';
            echo '<input type="number" class="form-control" name="smtp_port" required>';
            echo '</div>';
            echo '<div class="form-group">';
            echo '<label for="smtp_username">Nom d utilisateur SMTP:</label>';
            echo '<input type="text" class="form-control" name="smtp_username" required>';
            echo '</div>';
            echo '<div class="form-group">';
            echo '<label for="smtp_password">Mot de passe SMTP:</label>';
            echo '<input type="password" class="form-control" name="smtp_password" required>';
            echo '</div>';
            echo '<div class="form-group">';
            echo '<label for="smtp_from">Adresse e-mail d expédition:</label>';
            echo '<input type="email" class="form-control" name="smtp_from" required>';
            echo '</div>';
            echo '<button type="submit" class="btn btn-primary mt-3">Enregistrer</button>';
            echo '</form>';
            exit;
        }
    } elseif (file_exists($configFilePath)) {
        header('Location: account/connexion');
        exit();
    }
    ?>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
