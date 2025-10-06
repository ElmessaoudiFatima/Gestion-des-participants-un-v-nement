<?php
session_start();

// Configuration de la base de donnees
define('DB_HOST', 'localhost');
define('DB_NAME', 'campus_events');
define('DB_USER', 'root');
define('DB_PASS', '');

//  reCAPTCHA
define('RECAPTCHA_SITE_KEY', '6LcGIt8rAAAAAIRNJXJE2gL6truEsteLcJWM4YJb'); 
define('RECAPTCHA_SECRET_KEY', '6LcGIt8rAAAAAI8YKl6peMkTMGftBMxs-DnkhJ5d'); 

//  email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '0theentirepopulationoftexas0@gmail.com');
define('SMTP_PASS', 'vzkn jdjs jtta cdnp');
define('FROM_EMAIL', 'noreply@campusevent.com');
define('FROM_NAME', 'Campus Event');

// Inclure PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $filiere = $_POST['filiere'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // Validation
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($prenom)) $errors[] = "Le prénom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
    if (empty($date_naissance)) $errors[] = "La date de naissance est requise";
    if (empty($filiere)) $errors[] = "La filière est requise";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if (strlen($password) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";

    //reCAPTCHA
    if (empty($recaptcha_response)) {
        $errors[] = "Veuillez compléter le reCAPTCHA";
    } else {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_data = [
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($recaptcha_data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($recaptcha_url, false, $context);
        $result_json = json_decode($result);

        if (!$result_json->success) {
            $errors[] = "Vérification reCAPTCHA échouée";
        }
    }

    if (empty($errors)) {
        try {
            // Connexion a la base de données
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // verifier si l email existe 
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé";
            } else {
                // generation du token du verification
                $verification_token = bin2hex(random_bytes(32));
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insertion dans la BD
               $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, date_naissance, filiere, password, verification_token, is_verified, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'participant', NOW())");
$stmt->execute([$nom, $prenom, $email, $date_naissance, $filiere, $hashed_password, $verification_token]);

                // Envoi de l'email de verification
            $verification_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
    . "://" . $_SERVER['HTTP_HOST']
    . "/Gestion-des-participants-un-v-nement/public/verify.php?token=" . urlencode($verification_token);

                
                $mail = new PHPMailer(true);
                
                try {
                    // Configuration SMTP
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USER;
                    $mail->Password = SMTP_PASS;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = SMTP_PORT;
                    $mail->CharSet = 'UTF-8';

                    // Destinataires
                    $mail->setFrom(FROM_EMAIL, FROM_NAME);
                    $mail->addAddress($email, $prenom . ' ' . $nom);

                    // Contenu
                    $mail->isHTML(true);
                    $mail->Subject = "Vérification de votre compte Campus Event";
                    
                    $message = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; }
                            .container { max-width: 600px; margin: 0 auto; background: white; }
                            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; text-align: center; }
                            .header h1 { margin: 0; font-size: 32px; }
                            .content { padding: 40px; }
                            .button { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                            .footer { background: #f9f9f9; padding: 20px; text-align: center; color: #666; font-size: 12px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>Campus Event</h1>
                            </div>
                            <div class='content'>
                                <h2>Bonjour " . htmlspecialchars($prenom) . " " . htmlspecialchars($nom) . " !</h2>
                                <p>Merci de vous être inscrit sur <strong>Campus Event</strong> !</p>
                                <p>Pour activer votre compte et profiter de toutes nos fonctionnalités, veuillez cliquer sur le bouton ci-dessous :</p>
                                <div style='text-align: center;'>
                                    <a href='" . $verification_link . "' class='button'>Verifier mon compte</a>
                                </div>
                                <p>Ou copiez ce lien dans votre navigateur :</p>
                                <p style='word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . $verification_link . "</p>
                                <p><strong>Important :</strong> Ce lien expirera dans 24 heures.</p>
                                <p>Si vous n'avez pas créé de compte, ignorez simplement cet email.</p>
                            </div>
                            <div class='footer'>
                                <p>© 2025 Campus Event. Tous droits réservés.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    $mail->Body = $message;
                    $mail->AltBody = "Bonjour " . $prenom . " " . $nom . "!\n\nMerci de vous être inscrit sur Campus Event !\n\nPour activer votre compte, veuillez cliquer sur le lien suivant : " . $verification_link . "\n\nCe lien expirera dans 24 heures.\n\nSi vous n'avez pas créé de compte, ignorez simplement cet email.";

                    $mail->send();
                    $success = "Inscription réussie ! Un email de vérification a été envoyé à votre adresse.";
                    $_POST = [];
                } catch (Exception $e) {
                    $errors[] = "Erreur lors de l'envoi de l'email de vérification : " . $mail->ErrorInfo;
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Event - Inscription</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <!-- CSS personnalisé -->
  <link rel="stylesheet" href="../assets/styles.css">
<script src="../assets/script.js"></script>
</head>
<body>
    <div class="modal-wrapper">
        <div class="registration-card">
            <div class="card-header-custom">
                <button class="close-btn" onclick="handleClose()">&times;</button>
                <div class="logo-title">
                    <h1>Campus</h1>
                    <h2>Event</h2>
                    <p class="subtitle">Creer votre compte maintenant</p>
                </div>
            </div>

            <div class="card-body-custom">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-custom">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Erreur !</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-custom">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong><?php echo htmlspecialchars($success); ?></strong>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating input-group-custom">
                                <i class="bi bi-person-fill input-icon"></i>
                                <input type="text" class="form-control" id="nom" name="nom" placeholder="Nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required>
                                <label for="nom">Nom</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating input-group-custom">
                                <i class="bi bi-person-fill input-icon"></i>
                                <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Prénom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" required>
                                <label for="prenom">Prénom</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating input-group-custom">
                                <i class="bi bi-envelope-fill input-icon"></i>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                <label for="email">Email</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating input-group-custom">
                                <i class="bi bi-calendar-fill input-icon"></i>
                                <input type="date" class="form-control" id="date_naissance" name="date_naissance" placeholder="Date de naissance" value="<?php echo htmlspecialchars($_POST['date_naissance'] ?? ''); ?>" required>
                                <label for="date_naissance">Date de naissance</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating input-group-custom">
                        <i class="bi bi-mortarboard-fill input-icon"></i>
                        <select class="form-control" id="filiere" name="filiere" required>
                            <option value="">Selectionnez votre filiere</option>
                            <option value=" Génie Informatique" <?php echo (($_POST['filiere'] ?? '') === 'Informatique') ? 'selected' : ''; ?>>Informatique</option>
                            <option value="Génie Civil" <?php echo (($_POST['filiere'] ?? '') === 'Génie Civil') ? 'selected' : ''; ?>>Génie Civil</option>
                            <option value="Génie Mecatronique " <?php echo (($_POST['filiere'] ?? '') === 'Génie Mecatronique') ? 'selected' : ''; ?>>Génie Mecatronique</option>
                            <option value="Big Data & AI" <?php echo (($_POST['filiere'] ?? '') === 'Big Data & AI') ? 'selected' : ''; ?>>Big Data & AI</option>
                            <option value="Supply Chain Management" <?php echo (($_POST['filiere'] ?? '') === 'Supply Chain Management') ? 'selected' : ''; ?>>Supply Chain Management</option>
                            <option value="Génie Télecommunication et réseaux" <?php echo (($_POST['filiere'] ?? '') === 'Génie Télecommunication et réseaux') ? 'selected' : ''; ?>>Génie Télecommunication et réseaux</option>
                            <option value="Cybersecurité" <?php echo (($_POST['filiere'] ?? '') === 'Cybersecurité') ? 'selected' : ''; ?>>Cybersecurité</option>
                             <option value="2AP" <?php echo (($_POST['filiere'] ?? '') === '2AP') ? 'selected' : ''; ?>>2AP</option>
                            <option value="Je suis externe" <?php echo (($_POST['filiere'] ?? '') === 'Je suis externe') ? 'selected' : ''; ?>>Je suis externe</option>
                        </select>
                        <label for="filiere">Filiere</label>
                    </div>

<div class="form-floating input-group-custom">
    <i class="bi bi-lock-fill input-icon"></i>
    <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
    <label for="password">Mot de passe</label>

</div>
<div class="strength-meter" id="strengthMeter"></div>

<div class="form-floating input-group-custom">
    <i class="bi bi-lock-fill input-icon"></i>
    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmer" required>
    <label for="confirm_password">Confirmer le mot de passe</label>
   
</div>

                    <div class="login-link">
                        Vous avez deja un compte? <a href="login.php">Connectez-vous</a>
                    </div>

                    <div class="recaptcha-wrapper">
                        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                    </div>

                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle me-2"></i>S'inscrire
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  
</body>
</html>