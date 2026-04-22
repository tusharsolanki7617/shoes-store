<?php
/**
 * Email Service Class
 * Environment-aware email sending
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailerType;
    
    public function __construct() {
        // Auto-select mailer type based on environment
        // Force SMTP if configured, regardless of environment
        $this->mailerType = !empty(SMTP_USER) ? 'smtp' : (ENVIRONMENT === 'development' ? 'mail' : 'smtp');
    }
    
    /**
     * Send email
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $fromName Sender name
     * @return bool Success status
     */
    public function send($to, $subject, $body, $fromName = null) {
        $fromName = $fromName ?? SITE_NAME;
        $fromEmail = SITE_EMAIL;
        
        if ($this->mailerType === 'smtp') {
            return $this->sendViaSMTP($to, $subject, $body, $fromEmail, $fromName);
        } else {
            return $this->sendViaMail($to, $subject, $body, $fromEmail, $fromName);
        }
    }
    
    /**
     * Send via PHP mail() function
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $body Body
     * @param string $fromEmail From email
     * @param string $fromName From name
     * @return bool Success
     */
    private function sendViaMail($to, $subject, $body, $fromEmail, $fromName) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        
        $result = @mail($to, $subject, $body, $headers);
        
        // If in development and mail fails, log to file for testing
        if (!$result && ENVIRONMENT === 'development') {
            $logDir = __DIR__ . '/../logs';
            if (!file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/email_log.txt';
            $logEntry = "\n" . str_repeat('=', 80) . "\n";
            $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $logEntry .= "To: $to\n";
            $logEntry .= "Subject: $subject\n";
            $logEntry .= "Body:\n" . strip_tags($body) . "\n";
            $logEntry .= str_repeat('=', 80) . "\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            return true; // Return true to prevent blocking user registration
        }
        
        return $result;
    }
    
    /**
     * Send via SMTP (placeholder for PHPMailer integration)
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $body Body
     * @param string $fromEmail From email
     * @param string $fromName From name
     * @return bool Success
     */
    private function sendViaSMTP($to, $subject, $body, $fromEmail, $fromName) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            // Gmail requires From to match the authenticated account
            // Use SMTP_USER as From, display the site name as the sender name
            $mail->setFrom(SMTP_USER, $fromName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Get email template wrapper
     * @param string $content Email content
     * @return string Full HTML email
     */
    public function getTemplate($content) {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; line-height: 1.6; color: #111; background-color: #f5f5f5; margin: 0; padding: 0; }
                .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; padding: 0; }
                .header { padding: 40px 40px 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #111; }
                .content { padding: 20px 40px 40px; }
                .button { display: inline-block; padding: 15px 30px; background: #111111; color: #ffffff !important; text-decoration: none; border-radius: 4px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-size: 14px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; color: #999; font-size: 12px; background: #fafafa; border-top: 1px solid #eee; }
                .footer a { color: #555; text-decoration: none; }
                h2 { font-size: 20px; font-weight: 800; margin-bottom: 20px; text-transform: uppercase; }
                p { margin-bottom: 15px; color: #333; font-size: 16px; }
            </style>
        </head>
        <body>
            <div class="wrapper">
                <div class="header">
                    <h1>' . SITE_NAME . '</h1>
                </div>
                <div class="content">
                    ' . $content . '
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
    
    /**
     * Send activation email
     * @param string $to Recipient email
     * @param string $token Activation token
     * @return bool Success
     */
    public function sendActivationEmail($to, $token) {
        $activationLink = BASE_URL . 'user/activate.php?token=' . $token;
        $content = '
            <h2>Welcome to the Team</h2>
            <p>You\'re almost there. To start shopping the latest drops, verify your email address.</p>
            <center><a href="' . $activationLink . '" class="button">Verify Email</a></center>
            <p style="font-size: 12px; color: #777; margin-top: 30px;">If the button doesn\'t work, copy this link:<br>' . $activationLink . '</p>
        ';
        
        return $this->send($to, 'Verify Your Account - ' . SITE_NAME, $this->getTemplate($content));
    }
    
    /**
     * Send password reset OTP email
     * @param string $to Recipient email
     * @param string $otp OTP code
     * @return bool Success
     */
    public function sendPasswordResetEmail($to, $otp) {
        $content = '
            <h2>Reset Password</h2>
            <p>We received a request to reset your password. Use the code below to proceed:</p>
            <div style="background: #f5f5f5; padding: 20px; text-align: center; margin: 30px 0;">
                <h1 style="font-size: 40px; margin: 0; letter-spacing: 10px; color: #111;">' . $otp . '</h1>
            </div>
            <p>This code expires in 15 minutes.</p>
            <p style="font-size: 12px; color: #777;">If you didn\'t ask for this, you can ignore this email.</p>
        ';
        
        return $this->send($to, 'Reset Your Password', $this->getTemplate($content));
    }
    
    /**
     * Send order confirmation email
     * @param string $to Recipient email
     * @param array $orderData Order details
     * @return bool Success
     */
    public function sendOrderConfirmation($to, $orderData) {
        $content = '
            <h2>Order Confirmed</h2>
            <p>Your order <strong>#' . $orderData['order_number'] . '</strong> has been placed successfully.</p>
            
            <div style="border-top: 2px solid #111; border-bottom: 2px solid #111; padding: 20px 0; margin: 20px 0;">
                <table width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td align="left" style="font-weight: bold;">TOTAL PAYMENT</td>
                        <td align="right" style="font-weight: bold; font-size: 18px;">' . formatPrice($orderData['total']) . '</td>
                    </tr>
                </table>
            </div>

            <p>We\'ll notify you once it ships.</p>
            <center><a href="' . BASE_URL . 'user/orders.php" class="button">View Order</a></center>
        ';
        
        return $this->send($to, 'Order Confirmation #' . $orderData['order_number'], $this->getTemplate($content));
    }
    
    /**
     * Send contact form notification
     * @param array $formData Contact form data
     * @return bool Success
     */
    public function sendContactNotification($formData) {
        $content = '
            <h2>New Message</h2>
            <p><strong>From:</strong> ' . e($formData['name']) . ' (' . e($formData['email']) . ')</p>
            <p><strong>Subject:</strong> ' . e($formData['subject']) . '</p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            <p style="white-space: pre-line;">' . e($formData['message']) . '</p>
        ';
        
        return $this->send(ADMIN_EMAIL, 'New Contact: ' . e($formData['subject']), $this->getTemplate($content));
    }
}
