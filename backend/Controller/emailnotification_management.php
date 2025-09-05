<?php

namespace App\Controller;

// Create a new instance of the database connection
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';

use Database\dbconnection;
use App\Controller\QueryBuilder;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$database = new dbconnection();
$db = $database->getConnection();

class emailnotification_management
{
    private $request_table = 'request_table';
    private $attachment_table = 'attachment_table';
    private $requeststatus_table = 'requeststatus_table';
    private $email_table = 'email_table';
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getEmailSenderDetails($section)
    {
        $query = "SELECT emailadd FROM " . $this->email_table . " WHERE department = :section";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':section', $section);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAttachments($control_number)
    {
        $query = "SELECT item_attachment FROM " . $this->attachment_table . " WHERE control_number = :control_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':control_number', $control_number, \PDO::PARAM_STR); // Usually control_number is a string
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getEmails($section)
    {
        $params = [];
        $builder = QueryBuilder::getEmailReceipient($section);
        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBCC($section)
    {
        $params = [];
        $builder = QueryBuilder::getBCC($section);
        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function SendEmailupdateStatus($data, $message, $subject)
    {
        $mail = new PHPMailer(true);

        $mainrecipient = $data['address_section'];
        $bccrecipient  = $data['bcc_Section'];
        $recipient     = $this->getEmails($mainrecipient);
        $bcc           = $this->getBCC($bccrecipient);

        // Require at least one recipient
        if (empty($recipient)) {
            return false;
        }

        try {
            // Server settings
            $mail->SMTPDebug  = 0; // Debug output
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'];
            $mail->Password   = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
            $mail->Port       = $_ENV['SMTP_PORT'];

            $mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']);

            // Add TO addresses
            foreach ($recipient as $recip) {
                if (isset($recip['emailadd']) && filter_var($recip['emailadd'], FILTER_VALIDATE_EMAIL)) {
                    $mail->addAddress($recip['emailadd']);
                }
            }

            // Add BCC addresses (optional)
            if (!empty($bcc)) {
                foreach ($bcc as $rbcc) {
                    if (isset($rbcc['emailadd']) && filter_var($rbcc['emailadd'], FILTER_VALIDATE_EMAIL)) {
                        $mail->addBCC($rbcc['emailadd']);
                    }
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function SendEmailNotification($recipients, $cc, $bcc, $subject, $message, $section, $control_number)
    {

        // Validate recipients
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = $_ENV['SMTP_HOST'];                     // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = $_ENV['SMTP_USERNAME'];                    // SMTP username
            $mail->Password   = $_ENV['SMTP_PASSWORD'];                                     // SMTP password    
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'];         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = $_ENV['SMTP_PORT'];                                    // TCP port to connect to
            // TCP port to connect to
            //Recipients
            $mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']); // Set the sender's email and name

            // Add BCC if needed
            foreach ($recipients as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->addBCC($email); // Add BCC recipient
                } else {
                    // Handle invalid email address
                    return false;
                }
            }

            $attachments = $this->getAttachments($control_number);
            foreach ($attachments as $index => $fileData) {
                if (empty($fileData)) {
                    continue;
                }

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($fileData);

                // Assign a default filename since it's not stored in the DB
                $filename = "attachment_" . ($index + 1) . ".jpeg";  // Change .bin to expected file type if possible

                $mail->addStringAttachment($fileData, $filename, 'base64', $mimeType);
            }

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);
            $mail->send();

            // Email sent successfully
            return [
                'success' => true,
                'message' => 'Email Sent!'
            ];
        } catch (Exception $e) {
            // Handle error
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function AutoEmailNotification($data, $targetEmails) 
    {
        
    }
}
