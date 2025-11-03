<?php

namespace App\Utils;

class EmailService
{
    /**
     * Send support message reply notification to user
     */
    public static function sendSupportReplyNotification($userEmail, $userName, $subject, $adminReply, $originalMessage)
    {
        $to = $userEmail;
        $subject = "Re: " . $subject . " - PHITSOL Support";
        
        $message = self::getSupportReplyEmailTemplate($userName, $subject, $adminReply, $originalMessage);
        $headers = self::getEmailHeaders();
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get email template for support reply notification
     */
    private static function getSupportReplyEmailTemplate($userName, $subject, $adminReply, $originalMessage)
    {
        $template = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Support Reply - PHITSOL</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
        .message-box { background: white; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #007bff; }
        .reply-box { background: white; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #28a745; }
        .footer { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 0.9rem; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>PHITSOL Support</h2>
            <p>Your support inquiry has been answered</p>
        </div>
        
        <div class='content'>
            <p>Dear " . htmlspecialchars($userName) . ",</p>
            
            <p>Thank you for contacting PHITSOL support. We have reviewed your inquiry and provided a response below.</p>
            
            <h3>Your Original Message:</h3>
            <div class='message-box'>
                <strong>Subject:</strong> " . htmlspecialchars($subject) . "<br><br>
                " . nl2br(htmlspecialchars($originalMessage)) . "
            </div>
            
            <h3>Our Response:</h3>
            <div class='reply-box'>
                " . nl2br(htmlspecialchars($adminReply)) . "
            </div>
            
            <p>If you have any additional questions or need further assistance, please don't hesitate to contact us again through the Partners Portal.</p>
            
            <div style='text-align: center; margin: 20px 0;'>
                <a href='https://yourdomain.com/public/contact-support.php' class='btn'>Contact Support</a>
            </div>
            
            <p>Best regards,<br>
            <strong>PHITSOL Support Team</strong></p>
        </div>
        
        <div class='footer'>
            <p>This is an automated message from PHITSOL Partners Portal.<br>
            Please do not reply to this email. For support, visit our Partners Portal.</p>
        </div>
    </div>
</body>
</html>";
        
        return $template;
    }
    
    /**
     * Get email headers
     */
    private static function getEmailHeaders()
    {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: PHITSOL Support <noreply@phitsol.com>" . "\r\n";
        $headers .= "Reply-To: support@phitsol.com" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return $headers;
    }
    
    /**
     * Get base URL for the application
     */
    private static function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        return $protocol . $host . $path . '/';
    }


    
    /**
     * Get email template for document status notification
     */
    private static function getDocumentStatusEmailTemplate($userName, $status, $reviewedBy, $comments, $submittedDate)
    {
        $statusText = ucfirst($status);
        $statusColor = $status === 'approved' ? '#10b981' : ($status === 'rejected' ? '#ef4444' : '#f59e0b');
        $statusIcon = $status === 'approved' ? 'fas fa-check-circle' : ($status === 'rejected' ? 'fas fa-times-circle' : 'fas fa-clock');
        
        $template = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Document Status Update - PHITSOL</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
        .status-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {$statusColor}; }
        .status-header { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
        .status-icon { font-size: 24px; color: {$statusColor}; }
        .status-text { font-size: 18px; font-weight: bold; color: {$statusColor}; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .info-item { background: white; padding: 15px; border-radius: 6px; border: 1px solid #e9ecef; }
        .info-label { font-weight: bold; color: #6c757d; font-size: 0.9rem; margin-bottom: 5px; }
        .info-value { color: #333; }
        .comments-box { background: white; padding: 15px; border-radius: 6px; border: 1px solid #e9ecef; margin: 15px 0; }
        .footer { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 0.9rem; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>PHITSOL Partners Portal</h2>
            <p>Your document status has been updated</p>
        </div>
        
        <div class='content'>
            <p>Dear " . htmlspecialchars($userName) . ",</p>
            
            <p>Your application has been reviewed and the status has been updated.</p>
            
            <div class='status-box'>
                <div class='status-header'>
                    <i class='{$statusIcon} status-icon'></i>
                    <span class='status-text'>Status: {$statusText}</span>
                </div>
                
                <div class='info-grid'>
                    <div class='info-item'>
                        <div class='info-label'>Reviewed By</div>
                        <div class='info-value'>" . htmlspecialchars($reviewedBy) . "</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Review Date</div>
                        <div class='info-value'>" . date('F d, Y \a\t g:i A') . "</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Submitted Date</div>
                        <div class='info-value'>" . date('F d, Y', strtotime($submittedDate)) . "</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Application ID</div>
                        <div class='info-value'>PD-" . date('Ymd') . "</div>
                    </div>
                </div>
                
                " . (!empty($comments) ? "
                <div class='comments-box'>
                    <div class='info-label'>Review Comments</div>
                    <div class='info-value'>" . nl2br(htmlspecialchars($comments)) . "</div>
                </div>
                " : "") . "
            </div>
            
            " . ($status === 'approved' ? "
            <p><strong>Congratulations!</strong> Your application has been approved. You can now proceed with your partnership.</p>
            " : ($status === 'rejected' ? "
            <p>Your application requires some updates. Please review the comments above and resubmit your application.</p>
            " : "
            <p>Your documents are currently under review. We will notify you once the review is complete.</p>
            ")) . "
            
            <div style='text-align: center; margin: 20px 0;'>
                <a href='https://yourdomain.com/public/partners-dashboard.php' class='btn'>View Your Dashboard</a>
            </div>
            
            <p>If you have any questions, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            <strong>PHITSOL Partners Team</strong></p>
        </div>
        
        <div class='footer'>
            <p>This is an automated message from PHITSOL Partners Portal.<br>
            Please do not reply to this email. For support, visit our Partners Portal.</p>
        </div>
    </div>
</body>
</html>";
        
        return $template;
    }

    /**
     * Send a generic email
     */
    public static function sendEmail($to, $subject, $message)
    {
        $headers = self::getEmailHeaders();
        return mail($to, $subject, $message, $headers);
    }

    /**
     * Send welcome email to new users
     */
    public static function sendWelcomeEmail($userEmail, $userName)
    {
        $to = $userEmail;
        $subject = "Welcome to PHITSOL Partners Portal";
        
        $message = self::getWelcomeEmailTemplate($userName);
        $headers = self::getEmailHeaders();
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get welcome email template
     */
    private static function getWelcomeEmailTemplate($userName)
    {
        $template = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Welcome to PHITSOL</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
        .footer { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 0.9rem; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Welcome to PHITSOL</h2>
            <p>Your Partners Portal account is ready</p>
        </div>
        
        <div class='content'>
            <p>Dear " . htmlspecialchars($userName) . ",</p>
            
            <p>Welcome to PHITSOL Partners Portal! Your account has been successfully created and is ready for use.</p>
            
            <h3>What you can do next:</h3>
            <ul>
                <li>Complete your profile information</li>
                <li>Contact our support team for assistance</li>
                <li>Track your application status</li>
                <li>Contact our support team for assistance</li>
            </ul>
            
            <div style='text-align: center; margin: 20px 0;'>
                <a href='https://yourdomain.com/public/partners-dashboard.php' class='btn'>Access Your Dashboard</a>
            </div>
            
            <p>If you have any questions or need assistance, our support team is here to help.</p>
            
            <p>Best regards,<br>
            <strong>PHITSOL Team</strong></p>
        </div>
        
        <div class='footer'>
            <p>This is an automated message from PHITSOL Partners Portal.<br>
            Please do not reply to this email. For support, visit our Partners Portal.</p>
        </div>
    </div>
</body>
</html>";
        
        return $template;
    }
} 