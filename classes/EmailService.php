<?php
require_once __DIR__ . '/../config/config.php';

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
                    <p>" . APP_NAME . " - Digital Library Management System</p>
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
                    <p>" . APP_NAME . " - Digital Library Management System</p>
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
                    <p>" . APP_NAME . " - Digital Library Management System</p>
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
                    <p>" . APP_NAME . " - Digital Library Management System</p>
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

    private function send($recipientEmail, $subject, $message, $recipientName = '') {
        try {
            $transportHost = $this->host;
            if ((int)$this->port === 465) {
                $transportHost = 'ssl://' . $this->host;
            }

            $socket = @fsockopen($transportHost, (int)$this->port, $errno, $errstr, 15);

            if (!$socket) {
                error_log("SMTP connect failed: $errno $errstr");
                return false;
            }

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

