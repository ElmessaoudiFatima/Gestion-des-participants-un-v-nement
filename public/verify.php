<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'campus_events');
define('DB_USER', 'root');
define('DB_PASS', '');

$message = '';
$success = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Rechercher dans la table utilisateurs
        $stmt = $pdo->prepare("SELECT id, email, nom, prenom, is_verified, created_at FROM utilisateurs WHERE verification_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['is_verified']) {
                $message = "Ce compte a déjà été vérifié. Vous pouvez vous connecter.";
                $success = true;
            } else {
                // Vérifier l'expiration du token 
                $created = new DateTime($user['created_at']);
                $now = new DateTime();
                $diff = $now->diff($created);
                $hours = ($diff->days * 24) + $diff->h;

                if ($hours > 24) {
                    $message = "Ce lien de vérification a expiré. Veuillez vous réinscrire.";
                } else {
                    // Activer le compte
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET is_verified = 1, verification_token = NULL WHERE id = ?");
                    $stmt->execute([$user['id']]);

                    $message = "Félicitations ! Votre compte a été vérifié avec succès. Vous pouvez maintenant vous connecter.";
                    $success = true;
                }
            }
        } else {
            $message = "Token de vérification invalide ou expiré.";
        }
    } catch (PDOException $e) {
        $message = "Erreur technique : " . $e->getMessage();
    }
} else {
    $message = "Token de vérification manquant.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du compte - Campus Event</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/script.js"></script>
</head>
<body>
    <div class="bg-circle bg-circle-1"></div>
    <div class="bg-circle bg-circle-2"></div>
    <div class="bg-circle bg-circle-3"></div>

    <div class="verification-container">
        <div class="verification-card">
            <div class="card-header-custom">
                <div class="logo-title">
                    <h1><i class="bi bi-shield-check"></i> Campus Event</h1>
                </div>
            </div>

            <div class="card-body-custom">
                <div class="status-icon <?php echo $success ? 'success-icon' : 'error-icon'; ?>">
                    <?php if ($success): ?>
                        <i class="bi bi-check-lg"></i>
                    <?php else: ?>
                        <i class="bi bi-x-lg"></i>
                    <?php endif; ?>
                </div>

                <div class="decorative-line"></div>

                <h2 class="status-message">
                    <?php echo $success ? 'Vérification Réussie !' : 'Erreur de Vérification'; ?>
                </h2>

                <p class="status-description">
                    <?php echo htmlspecialchars($message); ?>
                </p>

                <div class="button-group">
                    <?php if ($success): ?>
                        <a href="login.php" class="btn-custom btn-primary-custom">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter maintenant
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn-custom btn-primary-custom">
                            <i class="bi bi-arrow-clockwise me-2"></i>Réessayer
                        </a>
                        <a href="index.php" class="btn-custom btn-secondary-custom">
                            <i class="bi bi-house me-2"></i>Retour à l'accueil
                        </a>
                    <?php endif; ?>
                </div>

                <div class="mt-5">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Besoin d'aide? <a href="support.php" style="color: #667eea;">Contactez le support</a>
                    </small>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <small style="color: rgba(255, 255, 255, 0.8);">
                © 2025 Campus Event. Tous droits réservés.
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>