<?php
/**
 * PHITSOL Email Configuration for GoDaddy Hosting
 * Centralized configuration file for all email settings
 */

// GoDaddy Email Server Configuration
define('SMTP_HOST', 'smtpout.secureserver.net');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'info@phitsol.com');
define('SMTP_PASSWORD', 'Kkcc2025#');
define('SMTP_SECURITY', 'ssl'); // ssl or tls

// Email Settings
define('FROM_EMAIL', 'info@phitsol.com');
define('FROM_NAME', 'PHITSOL Team');
define('REPLY_TO_EMAIL', 'info@phitsol.com');
define('REPLY_TO_NAME', 'PHITSOL Support');

// GoDaddy-specific SMTP Options
$GODADDY_SMTP_OPTIONS = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

// PHPMailer Configuration
define('MAIL_TIMEOUT', 60);
define('MAIL_CHARSET', 'UTF-8');
define('MAIL_DEBUG', false); // Set to true for debugging

/**
 * Get optimized PHPMailer configuration for GoDaddy hosting
 * @return array Configuration array
 */
function getGoDaddyMailConfig() {
    return array(
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'security' => SMTP_SECURITY,
        'timeout' => MAIL_TIMEOUT,
        'charset' => MAIL_CHARSET,
        'debug' => MAIL_DEBUG,
        'smtp_options' => $GODADDY_SMTP_OPTIONS
    );
}

/**
 * Configure PHPMailer instance for GoDaddy hosting
 * @param PHPMailer $mail PHPMailer instance
 * @return void
 */
function configureGoDaddyMail($mail) {
    $config = getGoDaddyMailConfig();
    
    // Basic SMTP settings
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $config['port'];
    
    // GoDaddy-specific options
    $mail->SMTPOptions = $config['smtp_options'];
    $mail->Timeout = $config['timeout'];
    $mail->SMTPKeepAlive = true;
    $mail->CharSet = $config['charset'];
    
    // Debug mode (only for testing)
    if ($config['debug']) {
        $mail->SMTPDebug = 2;
    }
    
    // Set default from address
    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addReplyTo(REPLY_TO_EMAIL, REPLY_TO_NAME);
}

/**
 * Send email using GoDaddy configuration
 * @param string $to Recipient email
 * @param string $toName Recipient name
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Alternative text body
 * @return bool Success status
 */
function sendGoDaddyEmail($to, $toName, $subject, $body, $altBody = '') {
    require_once 'phpmailer/Exception.php';
    require_once 'phpmailer/PHPMailer.php';
    require_once 'phpmailer/SMTP.php';
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        configureGoDaddyMail($mail);
        
        // Recipients
        $mail->addAddress($to, $toName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log('Email sending failed: ' . $e->getMessage());
        return false;
    }
}
?>
