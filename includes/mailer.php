<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../config/mail.php';

function send_reset_email($to_email, $to_name, $reset_link) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = 'U-SIIRS — Password Reset Request';
        $mail->Body    = '
            <div style="font-family:Arial,sans-serif; max-width:520px; margin:auto; padding:32px; border:1px solid #eee; border-radius:8px;">
                <h2 style="color:#1a1a2e;">Password Reset Request</h2>
                <p>Hi <strong>' . htmlspecialchars($to_name) . '</strong>,</p>
                <p>We received a request to reset your U-SIIRS password.</p>
                <p>Click the button below to set a new password.</p>
                <p style="text-align:center; margin:32px 0;">
                    <a href="' . $reset_link . '"
                       style="background:#4f46e5; color:#fff; padding:12px 28px; border-radius:6px; text-decoration:none; font-weight:bold;">
                        Reset My Password
                    </a>
                </p>
                <p style="color:#666; font-size:0.9rem;">
                    This link expires in <strong>1 hour</strong>.<br>
                    If you did not request this, ignore this email.
                </p>
                <hr style="border:none; border-top:1px solid #eee; margin:24px 0;">
                <p style="color:#999; font-size:0.8rem; text-align:center;">
                    U-SIIRS — UTeM Issue &amp; Incident Reporting System
                </p>
            </div>
        ';
        $mail->AltBody = 'Reset your U-SIIRS password here: ' . $reset_link . "\n\nThis link expires in 1 hour.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}
