<?php
// Configuration
define('CONTACT_EMAIL', 'contact@js-industrie.fr'); // Email de réception
define('SITE_NAME', 'JS Industrie');
define('FROM_EMAIL', 'noreply@js-industrie.fr'); // Email d'envoi

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

// Récupérer les données du formulaire
$nom = isset($_POST['nom']) ? clean_input($_POST['nom']) : '';
$entreprise = isset($_POST['entreprise']) ? clean_input($_POST['entreprise']) : '';
$email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
$telephone = isset($_POST['telephone']) ? clean_input($_POST['telephone']) : '';
$type_projet = isset($_POST['type_projet']) ? clean_input($_POST['type_projet']) : '';
$message = isset($_POST['message']) ? clean_input($_POST['message']) : '';

// Protection anti-spam simple (honeypot)
$honeypot = isset($_POST['website']) ? $_POST['website'] : '';
if (!empty($honeypot)) {
    // Si le champ honeypot est rempli, c'est un bot
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès']);
    exit;
}

// Validation des champs requis
$errors = [];

if (empty($nom)) {
    $errors[] = 'Le nom est requis';
}

if (empty($email)) {
    $errors[] = 'L\'email est requis';
} elseif (!validate_email($email)) {
    $errors[] = 'L\'email n\'est pas valide';
}

if (empty($type_projet)) {
    $errors[] = 'Le type de projet est requis';
}

if (empty($message)) {
    $errors[] = 'Le message est requis';
} elseif (strlen($message) < 10) {
    $errors[] = 'Le message doit contenir au moins 10 caractères';
}

// Si des erreurs existent, les retourner
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors), 'errors' => $errors]);
    exit;
}

// Préparer l'email
$subject = '[' . SITE_NAME . '] Nouvelle demande de devis - ' . $type_projet;

$email_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #111827 0%, #1E5BA8 100%); color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .field { margin-bottom: 15px; padding: 10px; background: white; border-left: 4px solid #1E5BA8; }
        .label { font-weight: bold; color: #1E5BA8; }
        .value { margin-top: 5px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>✉️ Nouvelle demande de devis</h2>
            <p>" . SITE_NAME . "</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>👤 Nom complet :</div>
                <div class='value'>" . htmlspecialchars($nom) . "</div>
            </div>

            " . (!empty($entreprise) ? "
            <div class='field'>
                <div class='label'>🏢 Entreprise :</div>
                <div class='value'>" . htmlspecialchars($entreprise) . "</div>
            </div>
            " : "") . "

            <div class='field'>
                <div class='label'>📧 Email :</div>
                <div class='value'><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></div>
            </div>

            " . (!empty($telephone) ? "
            <div class='field'>
                <div class='label'>📞 Téléphone :</div>
                <div class='value'><a href='tel:" . htmlspecialchars($telephone) . "'>" . htmlspecialchars($telephone) . "</a></div>
            </div>
            " : "") . "

            <div class='field'>
                <div class='label'>🔧 Type de projet :</div>
                <div class='value'>" . htmlspecialchars($type_projet) . "</div>
            </div>

            <div class='field'>
                <div class='label'>💬 Message :</div>
                <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
            </div>
        </div>
        <div class='footer'>
            <p>Ce message a été envoyé depuis le formulaire de contact de " . SITE_NAME . "</p>
            <p>Date : " . date('d/m/Y H:i:s') . "</p>
        </div>
    </div>
</body>
</html>
";

// Headers de l'email
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . SITE_NAME . ' <' . FROM_EMAIL . '>',
    'Reply-To: ' . $nom . ' <' . $email . '>',
    'X-Mailer: PHP/' . phpversion()
];

// Envoyer l'email
$mail_sent = mail(CONTACT_EMAIL, $subject, $email_body, implode("\r\n", $headers));

if ($mail_sent) {
    // Email de confirmation au client
    $client_subject = 'Confirmation de votre demande - ' . SITE_NAME;
    $client_body = "
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
                <h2>✅ Demande bien reçue</h2>
            </div>
            <div class='content'>
                <p>Bonjour " . htmlspecialchars($nom) . ",</p>
                <p>Nous avons bien reçu votre demande de devis concernant : <strong>" . htmlspecialchars($type_projet) . "</strong></p>
                <p>Notre équipe va l'examiner et vous répondra dans les plus brefs délais.</p>
                <p>À très bientôt,<br>L'équipe " . SITE_NAME . "</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $client_headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . SITE_NAME . ' <' . FROM_EMAIL . '>',
        'X-Mailer: PHP/' . phpversion()
    ];

    mail($email, $client_subject, $client_body, implode("\r\n", $client_headers));

    // Réponse de succès
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Votre demande a été envoyée avec succès ! Nous vous recontacterons rapidement.'
    ]);
} else {
    // Erreur d'envoi
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'envoi. Veuillez réessayer ou nous contacter directement par email.'
    ]);
}
?>
