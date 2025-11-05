<?php
// Configuration
define('CONTACT_EMAIL', 'flo.lonjarret@gmail.com'); // Email de réception
define('SITE_NAME', 'JS Industrie');
define('FROM_EMAIL', 'flo.lonjarret@gmail.com');

// Headers de sécurité
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Fonction de nettoyage des données
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fonction de validation d'email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer l'email
$email = isset($_POST['email']) ? clean_input($_POST['email']) : '';

// Protection anti-spam (honeypot)
$honeypot = isset($_POST['url']) ? $_POST['url'] : '';
if (!empty($honeypot)) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Inscription réussie']);
    exit;
}

// Validation de l'email
$errors = [];

if (empty($email)) {
    $errors[] = 'L\'email est requis';
} elseif (!validate_email($email)) {
    $errors[] = 'L\'email n\'est pas valide';
}

// Si des erreurs existent
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Sauvegarder dans un fichier CSV (ou base de données)
$csv_file = 'newsletter_subscribers.csv';
$file_exists = file_exists($csv_file);

// Vérifier si l'email existe déjà
if ($file_exists) {
    $existing_emails = file($csv_file, FILE_IGNORE_NEW_LINES);
    foreach ($existing_emails as $line) {
        $data = str_getcsv($line);
        if (isset($data[0]) && $data[0] === $email) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Vous êtes déjà inscrit à notre newsletter !'
            ]);
            exit;
        }
    }
}

// Ajouter l'email au fichier CSV
$fp = fopen($csv_file, 'a');
if ($fp) {
    fputcsv($fp, [$email, date('Y-m-d H:i:s')]);
    fclose($fp);

    // Envoyer une notification à l'administrateur
    $admin_subject = '[' . SITE_NAME . '] Nouvelle inscription newsletter';
    $admin_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #111827 0%, #1E5BA8 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>📧 Nouvelle inscription newsletter</h2>
            </div>
            <div class='content'>
                <p><strong>Email :</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Date :</strong> " . date('d/m/Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $admin_headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . SITE_NAME . ' <' . FROM_EMAIL . '>',
        'X-Mailer: PHP/' . phpversion()
    ];

    mail(CONTACT_EMAIL, $admin_subject, $admin_body, implode("\r\n", $admin_headers));

    // Envoyer un email de bienvenue
    $welcome_subject = 'Bienvenue dans la newsletter ' . SITE_NAME;
    $welcome_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #111827 0%, #1E5BA8 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
            .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #FFA500 0%, #ff8c00 100%); color: white; text-decoration: none; border-radius: 25px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🎉 Bienvenue !</h2>
            </div>
            <div class='content'>
                <p>Merci de vous être inscrit à la newsletter de <strong>" . SITE_NAME . "</strong> !</p>
                <p>Vous recevrez désormais nos dernières actualités, conseils d'experts et informations sur nos réalisations directement dans votre boîte mail.</p>
                <p>Nous sommes ravis de vous compter parmi nos abonnés !</p>
                <p style='text-align: center;'>
                    <a href='https://www.js-industrie.fr' class='button'>Visiter notre site</a>
                </p>
                <p>À très bientôt,<br>L'équipe " . SITE_NAME . "</p>
                <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
                <p style='font-size: 12px; color: #666;'>Si vous ne souhaitez plus recevoir nos emails, contactez-nous à contact@js-industrie.fr</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $welcome_headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . SITE_NAME . ' <' . FROM_EMAIL . '>',
        'X-Mailer: PHP/' . phpversion()
    ];

    mail($email, $welcome_subject, $welcome_body, implode("\r\n", $welcome_headers));

    // Réponse de succès
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie ! Vérifiez votre boîte mail pour confirmer.'
    ]);
} else {
    // Erreur
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue. Veuillez réessayer.'
    ]);
}
?>
