<?php
    // Include the user model
    include_once './backend/Model/usermodel.php';
    // Include the database connection
    include_once './database/dbconnection.php';
    // Create a new instance of the database connection
    require __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $database = new DBConnection();
    $db = $database->getConnection();

    class EmailManagement{
        private $request_table = 'request_table';
        private $attachment_table = 'attachment_table';
        private $requeststatus_table = 'requeststatus_table';
        private $email_table = 'email_table';
        private $conn;

        public function __construct($db){
            $this->conn = $db;
        }

        public function getEmailSenderDetails($section){
            $query = "SELECT emailadd FROM " . $this->email_table . " WHERE department = :section";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':section', $section);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function getAttachments($content_id) {
            $query = "SELECT item_attachment FROM " . $this->attachment_table . " WHERE content_id = :content_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        public function SendEmailNotification($recipients, $cc, $bcc, $subject, $message, $section, $control_number){

            // Get the email sender details based on the section
            $emailDetails = $this->getEmailSenderDetails($section);
            if ($emailDetails) {
                $email = $emailDetails['emailadd'];
            } else {
                // If no email found for the section, return false
                return false;
            }

            // Validate recipients
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->SMTPDebug = 3;                                       // Enable verbose debug output
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = getenv('SMTP_HOST');                     // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = getenv('SMTP_USERNAME');                    // SMTP username
                $mail->Password   = getenv('SMTP_PASSWORD');                                     // SMTP password    
                $mail->SMTPSecure = getenv('SMTP_SECURE');         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                $mail->Port       = getenv('SMTP_PORT');                                    // TCP port to connect to
                                                // TCP port to connect to
                //Recipients
                $mail->setFrom(getenv('FROM_EMAIL'), getenv('FROM_NAME')); // Set the sender's email and name

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
        
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
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
                return true;

            } catch (Exception $e) {
                // Handle error
                return false;
            }
        }
    }
?>