<?php
declare(strict_types=1);

namespace Mcp\Util;

use PHPMailer\PHPMailer\PHPMailer;
use Exception;
use Mcp\TemplateBuilder;

class SmtpClient
{

    private PHPMailer $mailer;

    public function __construct(string $host, int $port, string $username, string $password)
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->Username = $username;
        $mailer->Password = $password;
        $mailer->SMTPAuth = true;
        $mailer->SMTPSecure = $port == 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer = $mailer;
    }

    public function sendHtml(string $fromAddr, string $fromName, string $to, string $subject, TemplateBuilder $tpl): bool
    {
        try {
            $this->mailer->setFrom($fromAddr, $fromName);
            $this->mailer->addAddress($to);
        } catch (Exception $e) {
            error_log('Failed to prepare mail client (from: '.$fromAddr.', to: '.$to.')');
            return false;
        }

        $this->mailer->isHTML(true);
        $this->mailer->Subject = $subject;
        ob_start();
        $tpl->render();
        $tplOut = ob_end_clean();
        $this->mailer->Body = $tplOut;
        $this->mailer->AltBody = $this::htmlToPlain($tplOut);

        try {
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log('Could not send email: '.$this->mailer->ErrorInfo);
            return false;
        }
    }

    private static function htmlToPlain($message): string
    {
        $messageNew = str_replace('<br/>', "\n", $message);
        $messageNew = strip_tags(preg_replace('/<a href="(.*)">(.*)<\\/a>/', "$2: $1", $messageNew));
        return $messageNew;
    }

}
