<?php
require_once __DIR__ . '/../config/config.php';

if (!defined('SMTP_CONNECT_TIMEOUT')) {
    define('SMTP_CONNECT_TIMEOUT', 4);
}
if (!defined('SMTP_READ_TIMEOUT')) {
    define('SMTP_READ_TIMEOUT', 6);
}

class EmailService {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $this->host = MAIL_HOST;
        $this->port = MAIL_PORT;
        $this->username = MAIL_USERNAME;
        $this->password = str_replace(' ', '', MAIL_PASSWORD);
        $this->fromEmail = MAIL_FROM;
        $this->fromName = MAIL_FROM_NAME;
    }

    public function sendOTP($recipientEmail, $otp, $recipientName = '') {
        $subject = 'Your OTP for Email Verification - ' . APP_NAME;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px; background-color: #f9f9f9; margin-top: 10px; }
                .otp-box { background-color: #007bff; color: white; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . APP_NAME . "</h1>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($recipientName) . ",</p>
                    <p>Thank you for registering with " . APP_NAME . ". Please use the following OTP to verify your email address:</p>
                    <div class='otp-box'>$otp</div>
                    <p>This OTP is valid for the next 5 minutes. Do not share this code with anyone.</p>
                    <p>If you did not request this OTP, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - Digital Paper Library</p>
                    <p>&copy; 2026. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendWalletTopUpOTP($recipientEmail, $otp, $recipientName = '') {
        $subject = 'Your OTP for Wallet Top Up - ' . APP_NAME;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4f1bf1; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px; background-color: #f9f9f9; margin-top: 10px; }
                .otp-box { background-color: #4f1bf1; color: white; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . APP_NAME . "</h1>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($recipientName) . ",</p>
                    <p>Use the following OTP to confirm your wallet top up:</p>
                    <div class='otp-box'>$otp</div>
                    <p>This OTP is valid for the next 5 minutes. Do not share this code with anyone.</p>
                    <p>If you did not request this, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - Digital Paper Library</p>
                    <p>&copy; 2026. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    // Sends OTP for password reset (forgot password).
    public function sendPasswordResetOTP($recipientEmail, $otp, $recipientName = '') {
        $subject = 'Your OTP for Password Reset - ' . APP_NAME;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px; background-color: #f9f9f9; margin-top: 10px; }
                .otp-box { background-color: #dc3545; color: white; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . APP_NAME . "</h1>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($recipientName) . ",</p>
                    <p>We received a request to reset your password. Use the following OTP to reset your password:</p>
                    <div class='otp-box'>$otp</div>
                    <p>This OTP is valid for the next 5 minutes. Do not share this code with anyone.</p>
                    <p>If you did not request this OTP, you can ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - Digital Paper Library</p>
                    <p>&copy; 2026. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendWelcome($recipientEmail, $recipientName = '') {
        $subject = 'Welcome to ' . APP_NAME;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px; background-color: #f9f9f9; margin-top: 10px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to " . APP_NAME . "</h1>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($recipientName) . ",</p>
                    <p>Your account has been successfully created and verified! You can now log in and start browsing our library.</p>
                    <p>Features you can enjoy:</p>
                    <ul>
                        <li>Browse thousands of books</li>
                        <li>Rent or buy books online</li>
                        <li>Borrow physical copies from our library</li>
                        <li>Rate and review books</li>
                        <li>Manage your wallet</li>
                    </ul>
                    <p>Happy Reading!</p>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - Digital Paper Library</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendRentConfirmation($recipientEmail, $recipientName, $bookName, $dueDate, $amount) {
        $subject = 'Rent Confirmation - ' . $bookName;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0066cc; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px; background-color: #f9f9f9; margin-top: 10px; }
                .details { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Rent Confirmation</h1>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($recipientName) . ",</p>
                    <p>Your book rental has been confirmed!</p>
                    <div class='details'>
                        <p><strong>Book:</strong> " . htmlspecialchars($bookName) . "</p>
                        <p><strong>Due Date:</strong> $dueDate</p>
                        <p><strong>Amount Paid:</strong> $" . htmlspecialchars((string)$amount) . "</p>
                    </div>
                    <p>Please ensure to return the book before the due date to avoid fines.</p>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . "</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendBookPurchaseConfirmation($recipientEmail, $recipientName, $bookName, $amountPaidCents, $walletBalanceCents) {
        $subject = 'Purchase Confirmation - ' . $bookName;
        $amountPaid = '$' . number_format(((int)$amountPaidCents) / 100, 2);
        $walletBalance = '$' . number_format(((int)$walletBalanceCents) / 100, 2);

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #1f8b4c; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px; background-color: #f9f9f9; margin-top: 10px; }
                .details { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; background: #fff; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Book Purchase Confirmed</h1>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($recipientName) . ",</p>
                    <p>Your digital book purchase has been completed successfully.</p>
                    <div class='details'>
                        <p><strong>Book:</strong> " . htmlspecialchars($bookName) . "</p>
                        <p><strong>Amount Paid:</strong> " . htmlspecialchars($amountPaid) . "</p>
                        <p><strong>Wallet Balance:</strong> " . htmlspecialchars($walletBalance) . "</p>
                    </div>
                    <p>You can start reading the book now from your My Books section.</p>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . "</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendFineNotification($recipientEmail, $recipientName, $bookName, $fineAmount) {
        $subject = 'Fine Notification - Overdue Book: ' . $bookName;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px; background-color: #f9f9f9; margin-top: 10px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Fine Notification</h1>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($recipientName) . ",</p>
                    <p>You have an overdue book:</p>
                    <p><strong>Book:</strong> " . htmlspecialchars($bookName) . "</p>
                    <p><strong>Fine Amount:</strong> $" . htmlspecialchars((string)$fineAmount) . "</p>
                    <p>Please return the book or pay the fine through your account.</p>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . "</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendMembershipActivation($recipientEmail, $recipientName, $planName, $expiryDate) {
        $subject = 'Membership Activated - ' . APP_NAME;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; padding: 40px 20px; text-align: center; }
                .content { padding: 30px; line-height: 1.6; color: #374151; }
                .plan-badge { display: inline-block; padding: 8px 16px; background: #f3f4f6; border-radius: 20px; font-weight: bold; color: #4f46e5; margin: 10px 0; }
                .details { background: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e5e7eb; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #9ca3af; background: #f9fafb; }
                .button { display: inline-block; padding: 12px 24px; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin:0;'>Welcome to Premium!</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p>Great news! Your membership has been successfully activated. You now have full access to our premium digital library features.</p>
                    <div class='details'>
                        <p style='margin:0;'><strong>Active Plan:</strong></p>
                        <div class='plan-badge'>" . htmlspecialchars($planName) . "</div>
                        <p style='margin:10px 0 0 0;'><strong>Valid Until:</strong> " . date('F j, Y', strtotime($expiryDate)) . "</p>
                    </div>
                    <p>Start exploring our vast collection of books and exclusive resources today.</p>
                    <center><a href='" . APP_URL . "/public/index.php?page=dashboard' class='button'>Go to Dashboard</a></center>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - The Future of Digital Reading</p>
                    <p>&copy; 2026 " . APP_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendMembershipExpiryWarning($recipientEmail, $recipientName, $planName, $expiryDate, $daysLeft) {
        $subject = 'Action Required: Your Membership is Expiring Soon - ' . APP_NAME;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fff5f5; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); color: white; padding: 40px 20px; text-align: center; }
                .content { padding: 30px; line-height: 1.6; color: #374151; }
                .warning-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #9ca3af; background: #f9fafb; }
                .button { display: inline-block; padding: 12px 24px; background: #ef4444; color: white !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin:0;'>Membership Expiring Soon</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p>Your <strong>" . htmlspecialchars($planName) . "</strong> membership is set to expire in <strong>$daysLeft days</strong>.</p>
                    <div class='warning-box'>
                        <p style='margin:0;'><strong>Expiry Date:</strong> " . date('F j, Y', strtotime($expiryDate)) . "</p>
                        <p style='margin:10px 0 0 0;'>Don't lose your access to premium books and features! Renew now to keep enjoying " . APP_NAME . " without interruption.</p>
                    </div>
                    <center><a href='" . APP_URL . "/public/index.php?page=membership' class='button'>Renew Membership</a></center>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - The Future of Digital Reading</p>
                    <p>&copy; 2026 " . APP_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendMembershipDeactivation($recipientEmail, $recipientName, $planName) {
        $subject = 'Membership Deactivated - ' . APP_NAME;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
                .header { background: #4b5563; color: white; padding: 40px 20px; text-align: center; }
                .content { padding: 30px; line-height: 1.6; color: #374151; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #9ca3af; background: #f9fafb; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin:0;'>Membership Deactivated</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p>This email confirms that your <strong>" . htmlspecialchars($planName) . "</strong> membership has been deactivated. Your premium access has ended.</p>
                    <p>If you have any questions or would like to rejoin, you can visit your dashboard at any time.</p>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - The Future of Digital Reading</p>
                    <p>&copy; 2026 " . APP_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendMembershipExpired($recipientEmail, $recipientName, $planName) {
        $subject = 'Your Membership Has Expired - ' . APP_NAME;

        $message = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
                .header { background: linear-gradient(135deg, #4b5563 0%, #1f2937 100%); color: white; padding: 40px 20px; text-align: center; }
                .content { padding: 30px; line-height: 1.6; color: #374151; }
                .info-box { background: #f9fafb; border-left: 4px solid #4b5563; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #9ca3af; background: #f9fafb; }
                .button { display: inline-block; padding: 12px 24px; background: #6366f1; color: white !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin:0;'>Membership Expired</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p>This is to inform you that your <strong>" . htmlspecialchars($planName) . "</strong> membership has expired, and your premium access has ended.</p>
                    <div class='info-box'>
                        <p style='margin:0;'><strong>Access Restricted:</strong></p>
                        <p style='margin:10px 0 0 0;'>Books and digital content unlocked via your membership have been removed from your digital library. To restore access and continue reading, simply renew your membership.</p>
                    </div>
                    <center><a href='" . APP_URL . "/public/index.php?page=membership' class='button'>Renew Membership Now</a></center>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - The Future of Digital Reading</p>
                    <p>&copy; 2026 " . APP_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    public function sendWalletTopUpConfirmation($recipientEmail, $recipientName, $amountCents, $newBalanceCents, $method) {
        $subject = 'Wallet Top Up Confirmed - ' . APP_NAME;
        $amountStr = '$' . number_format(((int)$amountCents) / 100, 2);
        $balanceStr = '$' . number_format(((int)$newBalanceCents) / 100, 2);

        $message = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 40px 20px; text-align: center; }
                .content { padding: 30px; line-height: 1.6; color: #374151; }
                .amount-badge { display: inline-block; padding: 12px 24px; background: #ecfdf5; border: 1px solid #10b981; border-radius: 12px; font-weight: 800; font-size: 28px; color: #059669; margin: 15px 0; }
                .details { background: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e5e7eb; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #9ca3af; background: #f9fafb; }
                .button { display: inline-block; padding: 12px 24px; background: #10b981; color: white !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin:0; font-size: 24px;'>Top Up Successful!</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p>We are pleased to confirm that your wallet top up request has been completed successfully.</p>
                    <center>
                        <div class='amount-badge'>$amountStr</div>
                    </center>
                    <div class='details'>
                        <table style='width:100%; border-collapse:collapse;'>
                            <tr style='border-bottom:1px solid #e5e7eb;'>
                                <td style='padding:10px 0; font-weight:bold; color:#6b7280;'>Payment Method:</td>
                                <td style='padding:10px 0; text-align:right; font-weight:bold; color:#374151;'>" . htmlspecialchars($method) . "</td>
                            </tr>
                            <tr>
                                <td style='padding:10px 0; font-weight:bold; color:#6b7280;'>New Wallet Balance:</td>
                                <td style='padding:10px 0; text-align:right; font-weight:bold; color:#059669; font-size:16px;'>$balanceStr</td>
                            </tr>
                        </table>
                    </div>
                    <p>You can now use your updated balance to purchase books, rent digital materials, or upgrade your membership!</p>
                    <center><a href='" . APP_URL . "/public/index.php?page=dashboard' class='button'>Go to Dashboard</a></center>
                </div>
                <div class='footer'>
                    <p>" . APP_NAME . " - The Future of Digital Reading</p>
                    <p>&copy; 2026 " . APP_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($recipientEmail, $subject, $message, $recipientName);
    }

    private function send($recipientEmail, $subject, $message, $recipientName = '') {
        try {
            $transportHost = $this->host;
            if ((int)$this->port === 465) {
                $transportHost = 'ssl://' . $this->host;
            }

            $socket = @fsockopen($transportHost, (int)$this->port, $errno, $errstr, SMTP_CONNECT_TIMEOUT);

            if (!$socket) {
                error_log("SMTP connect failed: $errno $errstr");
                return false;
            }
            stream_set_timeout($socket, SMTP_READ_TIMEOUT);

            $read = function() use ($socket) {
                $response = '';
                while (($line = fgets($socket, 515)) !== false) {
                    $response .= $line;
                    // SMTP multiline responses use "250-" until the last line, which uses "250 "
                    if (strlen($line) < 4 || $line[3] === ' ') {
                        break;
                    }
                }
                return $response;
            };
            $expect = function($response, $codes, $step) {
                $code = substr(trim($response), 0, 3);
                if (!in_array($code, $codes, true)) {
                    error_log("SMTP $step failed: " . trim($response));
                    return false;
                }
                return true;
            };

            $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';

            $response = $read();
            if (!$expect($response, ['220'], 'greeting')) {
                fclose($socket);
                return false;
            }

            fputs($socket, "EHLO " . $serverName . "\r\n");
            $response = $read();
            if (!$expect($response, ['250'], 'ehlo')) {
                fclose($socket);
                return false;
            }

            if ((int)$this->port !== 465) {
                fputs($socket, "STARTTLS\r\n");
                $response = $read();
                if (!$expect($response, ['220'], 'starttls')) {
                    fclose($socket);
                    return false;
                }

                $cryptoMethod = defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')
                    ? STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                    : STREAM_CRYPTO_METHOD_TLS_CLIENT;
                if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                    error_log('SMTP TLS negotiation failed');
                    fclose($socket);
                    return false;
                }

                fputs($socket, "EHLO " . $serverName . "\r\n");
                $response = $read();
                if (!$expect($response, ['250'], 'ehlo-after-starttls')) {
                    fclose($socket);
                    return false;
                }
            }

            fputs($socket, "AUTH LOGIN\r\n");
            $response = $read();
            if (!$expect($response, ['334'], 'auth-login')) {
                fclose($socket);
                return false;
            }
            fputs($socket, base64_encode($this->username) . "\r\n");
            $response = $read();
            if (!$expect($response, ['334'], 'auth-username')) {
                fclose($socket);
                return false;
            }
            fputs($socket, base64_encode($this->password) . "\r\n");
            $response = $read();
            if (!$expect($response, ['235'], 'auth-password')) {
                fclose($socket);
                return false;
            }

            fputs($socket, "MAIL FROM: <" . $this->fromEmail . ">\r\n");
            $response = $read();
            if (!$expect($response, ['250'], 'mail-from')) {
                fclose($socket);
                return false;
            }

            fputs($socket, "RCPT TO: <$recipientEmail>\r\n");
            $response = $read();
            if (!$expect($response, ['250', '251'], 'rcpt-to')) {
                fclose($socket);
                return false;
            }

            fputs($socket, "DATA\r\n");
            $response = $read();
            if (!$expect($response, ['354'], 'data')) {
                fclose($socket);
                return false;
            }

            $emailContent = "To: " . (empty($recipientName) ? $recipientEmail : $recipientName . " <$recipientEmail>") . "\r\n";
            $emailContent .= "From: " . $this->fromName . " <" . $this->fromEmail . ">\r\n";
            $emailContent .= "Subject: $subject\r\n";
            $emailContent .= "MIME-Version: 1.0\r\n";
            $emailContent .= "Content-type: text/html; charset=UTF-8\r\n\r\n";
            $emailContent .= $message . "\r\n.\r\n";

            fputs($socket, $emailContent);
            $response = $read();
            if (!$expect($response, ['250'], 'send-data')) {
                fclose($socket);
                return false;
            }

            fputs($socket, "QUIT\r\n");
            fclose($socket);

            return true;
        } catch (Exception $e) {
            error_log('SMTP exception: ' . $e->getMessage());
            return false;
        }
    }

    public static function generateOTP($length = 6) {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9);
        }
        return $otp;
    }
}
?>

