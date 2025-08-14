<?php
    // Include the user model
    include_once './backend/Model/usermodel.php';
    // Include the database connection
    include_once './database/dbconnection.php';
    // Include the query builder
    include_once './backend/Controller/queryBuilder.php';


    require __DIR__ . '/../../vendor/autoload.php';
    require __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    // require_once __DIR__ . '/../websocket/WebSocketNotifier.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use Dotenv\Dotenv;
    use WebSocket\WebSocketNotifier;

    // $notifier = new WebSocketNotifier("ws://localhost:8080");
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
   
    $database = new DBConnection();
    $db = $database->getConnection();
    
    namespace App\Controller;

    class RequestManagement{
        private $request_table = 'request_table';
        private $attachment_table = 'attachment_table';
        private $email_table = 'email_table';
        private $requestLogs_table = 'request_logs_table';
        private $comparison_table = 'comparison_table';
        private $delivery_table = 'delivery_table';
        private $notification_table = "notification_table";
        private $conn;  
        private $notifier;

        public function __construct($db) {
            $this->conn = $db;
        }


        public function CreateComparison($data){
            // Check if control number + item + supplier already exists
            $checkQuery = "SELECT COUNT(*) FROM {$this->comparison_table} 
                        WHERE control_number = :control_number AND item_name = :item_name AND supplier_name = :supplier_name";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->execute([
                ':control_number' => $data['control_number'],
                ':item_name'      => $data['item_name'],
                ':supplier_name'  => $data['supplier_name']
            ]);
            
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $query = "INSERT INTO {$this->comparison_table} 
                    (control_number, item_name, item_quantity, supplier_name, supplier_price, supplier_discount, total_price) 
                    VALUES (:control_number, :item_name, :item_quantity, :supplier_name, :supplier_price, :supplier_discount, :total_price)";
                $stmt = $this->conn->prepare($query);
                return $stmt->execute([
                    ':control_number'     => $data['control_number'],
                    ':item_name'          => $data['item_name'],
                    ':item_quantity'      => $data['item_quantity'],
                    ':supplier_name'      => $data['supplier_name'],
                    ':supplier_price'     => $data['supplier_price'],
                    ':supplier_discount'  => $data['supplier_discount'],
                    ':total_price'        => $data['total_price']
                ]);
            }

            // Record already exists
            return false;
        }

        public function updateComparisonRemarks($data) {
            $query = "UPDATE " . $this->comparison_table . " 
                    SET remarks = :remarks 
                    WHERE control_number = :control_number";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':remarks', $data['item_remarks']);
            $stmt->bindParam(':control_number', $data['control_number']);
            return $stmt->execute();
        }

        public function deleteComparison($id) {
            $query = "DELETE FROM " . $this->comparison_table . " WHERE control_number = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            return $stmt->execute();
        }

        public function fetchAllforApprovedByControlNumber($requestor_section, $from = null, $to = null, $status = null, $searchQuery = null, $page = 1, $perPage = 10) {
            $query = "SELECT * FROM " . $this->request_table;
            $conditions = [];
            $params = [];

            $conditions[] = "item_status = 'Pending'";
        
            // Section filter (if not Procurement)
            if ($requestor_section !== "Procurement") {
                $conditions[] = "item_section = :item_section";
                
                $params[':item_section'] = $requestor_section;
            }
        
            // Date range filter
            if (!empty($from) && !empty($to)) {
                $conditions[] = "created_at BETWEEN :from AND :to";
                $params[':from'] = $from;
                $params[':to'] = $to;
            }
        
            // Status filter
            if (!empty($status)) {
                $conditions[] = "item_status = :item_status";
                $params[':item_status'] = $status;
            }
        
            // Search filter
            if (!empty($searchQuery)) {
                $conditions[] = "(item_name LIKE :searchQuery OR item_description LIKE :searchQuery)";
                $params[':searchQuery'] = '%' . $searchQuery . '%';
            }
        
            // Apply WHERE clause
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
        
            // Order by newest first
            $query .= " Group by control_number  ORDER BY created_at DESC";
        
            // Pagination using LIMIT and OFFSET
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :limit OFFSET :offset";
        
            // Prepare and bind
            $stmt = $this->conn->prepare($query);
        
            // Bind dynamic parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        
            // Bind LIMIT and OFFSET (must be integers)
            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
            $stmt->execute();
        
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Function to count all requests
        public function countAllforApprovedByControlNumber($requestor_section, $from = null, $to = null, $status = null, $searchQuery = null) {
            $query = "SELECT COUNT(DISTINCT control_number) as total FROM " . $this->request_table;
            $conditions = [];
            $params = [];
        
            if ($requestor_section !== "Procurement") {
                $conditions[] = "item_section = :item_section";
                $conditions[] = "item_status = 'Pending'";
                $params[':item_section'] = $requestor_section;
            }
        
            if (!empty($from) && !empty($to)) {
                $conditions[] = "created_at BETWEEN :from AND :to";
                $params[':from'] = $from;
                $params[':to'] = $to;
            }
        
            if (!empty($status)) {
                $conditions[] = "item_status = :item_status";
                $params[':item_status'] = $status;
            }
        
            if (!empty($searchQuery)) {
                $conditions[] = "(item_name LIKE :searchQuery OR item_description LIKE :searchQuery)";
                $params[':searchQuery'] = '%' . $searchQuery . '%';
            }
        
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
        
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        }

        // Function to fetch a single request by ID
        public function fetchRequestById($id) {;
            $query = "SELECT * FROM " . $this->request_table . " WHERE control_number = :CONTROL_NUMBER";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':CONTROL_NUMBER', $id); // Use type hinting for safety
            $stmt->execute();
        
            return $stmt->fetchALL(PDO::FETCH_ASSOC);
        }


        // Function to get email sender details based on section
        public function getEmailSenderDetails($section){
            $query = "SELECT emailadd FROM " . $this->email_table . " WHERE department = :section";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':section', $section);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Function to get all attachments for a specific control number
        public function getAttachments($control_number) {
            $query = "SELECT item_attachment FROM " . $this->attachment_table . " WHERE control_number = :control_number";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':control_number', $control_number, PDO::PARAM_STR); // Usually control_number is a string
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        // Function to send email notification
        public function SendEmailNotification($recipients, $cc, $bcc, $subject, $message, $section, $control_number){

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

        // Function to send email status update
        public function SendEmailStatusUpdate($section, $subject, $message) {
            // Get email address of the section
            $emailDetails = $this->getEmailSenderDetails($section);
          
            if (empty($emailDetails)) {
                return false; // No email(s) found
            }
            
            // $emailDetails = ['Sonny.delrosario@nidec-instruments.com.ph', 'sonnyboy.delrosario@nidec.com'];
            // Validate recipients
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->SMTPDebug = 0;                                       // Enable verbose debug output
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = $_ENV['SMTP_HOST'];                     // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = $_ENV['SMTP_USERNAME'];                 // SMTP username
                $mail->Password   = $_ENV['SMTP_PASSWORD'];                 // SMTP password    
                $mail->SMTPSecure = $_ENV['SMTP_SECURE'];                   // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                $mail->Port       = $_ENV['SMTP_PORT'];                     // TCP port to connect to
                                                
                //Recipients
                $mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']); // Set the sender's email and name
                
               foreach ($emailDetails as $email) {
                    $emails = $email['emailadd']; // Assuming email is stored in 'emailadd' key
                    if (filter_var($emails, FILTER_VALIDATE_EMAIL)) {
                        $mail->addAddress($emails); // Add recipient
                        $hasvalidEmail = true; // Flag to check if at least one valid email is added
                    } else {
                        error_log("Invalid email skipped: " . $emails);
                    }
                }

                if (!isset($hasvalidEmail)) {
                    return false; // No valid email addresses found
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

        //Function to auto-sugggest
        public function FetchControlNumber($input){
            try{
                $query = "SELECT distinct control_number from  {$this->request_table} where control_number like :input limit 10";
                $stmt = $this->conn->prepare($query);
                $searchQuery = '%' . $input . '%';
                $stmt->bindParam(":input", $searchQuery, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    return [
                        'success' => true,
                        'data' => $data,
                        'message' => 'Data fetch successfully.'
                    ];
                }else{
                    return [
                        'success' => false,
                        'message' => 'Error in fetching data.'
                    ];
                }
            }catch(PDOException $e){
                return [
                    'success' => false,
                    'message' => 'Internal Server Error: ' . $e->getMessage()
                ];
            }
            
        }

        //Insert delivery details
        public function InsertDeliveryDetails($data){

            try{
                $query = "insert into {$this->delivery_table} (control_number, item_name, item_description, item_quantity, supplier_name, item_amount, delivery_date, item_status) 
                        values (:control_number, :item_name, :item_description, :item_quantity, :supplier_name, :item_amount, :delivery_date, :item_status)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':control_number', $data['control_number'], PDO::PARAM_STR);
                $stmt->bindParam(':item_name', $data['item_name'], PDO::PARAM_STR);
                $stmt->bindParam(':item_description', $data['item_description'], PDO::PARAM_STR);
                $stmt->bindParam(':item_quantity', $data['item_quantity'], PDO::PARAM_INT);
                $stmt->bindParam(':supplier_name', $data['supplier_name'], PDO::PARAM_STR);
                $stmt->bindParam(':item_amount', $data['item_amount']);
                $stmt->bindValue(':delivery_date', $data['delivery_date']);
                $stmt->bindParam(':item_status', $data['item_status'], PDO::PARAM_STR);
                // $stmt->bindParam(':item_remarks', $data['item_remarks'], PDO::PARAM_STR);
                
                if($stmt->execute()){
                    return [
                        'success' => true,
                        'message' => 'Delivery details has been successfuly saved.' . $data['delivery_date']
                    ];
                }else{
                    return [
                        'success' => false,
                        'message' => 'Data failed to insert.'
                    ];
                }
            }catch(PDOException $e){
                return [
                    'success' => false,
                    'message' => 'Internal server error: ' . $e->getMessage()
                ];
            }

        }

        public function FetchDeliveryData($searchquery, $page = null ,$perpage = null){
            $query = "select * from {$this->delivery_table} ";
            $params = [];

            if (!empty($searchquery)) {
                $query .= " where control_number like :searchquery";
                $params[':searchquery'] = '%' . $searchquery . '%';
            }

            $query .= " order by control_number ASC";

            // Pagination using LIMIT and OFFSET
            $offset = ($page - 1) * $perpage;
            $query .= " LIMIT :limit OFFSET :offset";

            // Prepare and bind
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            //Bind LIMIT and OFFSET (must be integers)
            $stmt->bindValue(':limit', (int)$perpage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function CountDeliveryData($searchquery = null){
            $query = "select count(*) as total from {$this->delivery_table} ";
            // $conditions = [];
            $params = [];

            if (!empty($searchquery)) {
                $query .= " where control_number like :searchquery";
                $params[':searchquery'] = '%' . $searchquery . '%';
            }

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        }

        public function DeliveryReceivedUpdate($id, $date, $status){

            if (empty($id) || empty($date) || empty($status)) {
                return [
                    'success' => false,
                    'message' => 'Empty or null parameters.'
                ];
                exit;
            }

            try{
                $query = "update {$this->delivery_table} set received_date = :received_date, item_status = :status where id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':received_date', $date, PDO::PARAM_STR);
                $stmt->bindParam(':status' , $status, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    return [
                        'success' => true,
                        'message' => 'Item has been received.'
                    ];
                }else{
                    return [
                        'success' => false,
                        'message' => 'Error receiving item.'
                    ];
                }
            }catch(PDOException $e){
                return [
                    'success' => false,
                    'message' => 'Internal Server Error: ' . $e->getMessage()
                ];
            }

        }

        public function DeleteDeliveryItem($id){

            try{
                $this->conn->beginTransaction();

                $query = "delete from {$this->delivery_table} where id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if (!$stmt->execute()) {
                    throw new Exception("Delete failed!.");              
                }

                $this->conn->commit();

                return [    
                        'success' => true,
                        'message' => 'Successfully deleted.'
                    ];
            }catch(Exception $e){

                $this->conn->rollback();

                return [
                    'success' => false,
                    'message' => 'Internal server error: ' . $e->getMessage()
                ];
            }
        }

        public function UpdateDeliveryDetails($data){
            if (empty($data)) {
                return [
                    'success' => false,
                    'message' => 'Empty or null data.'
                ];
                exit;
            }

            try{
                $this->conn->beginTransaction();
                $query = "update {$this->delivery_table} set supplier_name = :supplier_name, item_amount = :item_amount, delivery_date = :delivery_date 
                            where id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':supplier_name', $data['supplier_name'], PDO::PARAM_STR);
                $stmt->bindParam(':item_amount', $data['item_amount']);
                $stmt->bindValue(':delivery_date', $data['delivery_date'], PDO::PARAM_STR);
                $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
                
                if (!$stmt->execute()) {
                    throw new Exception('Update failed');
                }

                $this->conn->commit();
                return [
                    'success' => true,
                    'message' => 'Delivery detail successfully updated.'
                ];
            }catch(Exception $e){
                $this->conn->rollback();
                return [
                    'success' => false,
                    'message' => 'Internal server error: ' . $e->getMessage()
                ];
            }
        }

        public function AddRemarks($data){
            if(empty($data)){
                return [
                    'success' => false,
                    'message' => 'Empty or null remarks.'
                ];
                exit;
            }

            try{
                $this->conn->beginTransaction();
                $query = "update {$this->delivery_table} set item_remarks = :item_remarks where id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
                $stmt->bindParam(':item_remarks', $data['item_remarks'], PDO::PARAM_STR);

                if (!$stmt->execute()) {
                    throw new Exception('Adding remarks failed');
                }

                $this->conn->commit();
                return [
                    'success' => true,
                    'message' => 'Remarks successfully updated.'
                ];
            }catch(Exception $e){
                $this->conn->rollback();
                return [
                    'success' => true,
                    'message' => 'Internal server error: ' . $e->getMessage()
                ];
            }
        }

        public function GetTimeline($control_number){

            if (empty($control_number)) {
                return [
                    'success' => false,
                    'message' => 'Empty or null control number'
                ];
                exit;
            }

            try{
                $query = "select status, remarks, DATE(update_at) as date, TIME(update_at) as time 
                          from {$this->requestLogs_table} where control_number = :control_number ORDER BY update_at ASC";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':control_number' , $control_number, PDO::PARAM_STR);

                if (!$stmt->execute()) {
                    throw new Exception('Failed to execute query.');
                }

                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'message' => 'Logs successfully fetch.',
                    'logs' => $logs
                ];
                
            }catch(Exception $e){
                return [
                    'success' => false,
                    'message' => 'Internal server error: ' . $e->getMessage()
                ];
            }
        }

        public function InsertNotificationMessage($data){
            if (empty($data)) {
                return [
                    'success' => false,
                    'message' => 'Empty or null data.'
                ];
                exit;
            }
            
            try{
                $query = "INSERT INTO {$this->notification_table} (control_number, message, section) 
                          VALUES (:control_number, :message, :section)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':control_number', $data['control_number'], PDO::PARAM_STR);
                $stmt->bindParam(':message', $data['message'], PDO::PARAM_STR);
                $stmt->bindParam(':section', $data['section'], PDO::PARAM_STR);

                if ($stmt->execute()) {
                    return [
                        'success' => true,
                        'message' => 'Notification message successfully inserted.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to insert notification message.'
                    ];
                }
            }catch(Exception $e){
                return [
                    'success' => false,
                    'message' => 'Internal server error: ' . $e->getMessage()
                ];
            }
            
        }

        public function getNotifications($section){

            try{
                $query = "SELECT * FROM {$this->notification_table} ";

                if ($section !== 'Procurement') {
                    $query .= "WHERE section = :section ";
                }

                // Add ORDER BY clause to sort by created_at in descending order
                $query .= "ORDER BY created_at DESC";
                
                // Prepare the statement    
                $stmt = $this->conn->prepare($query);

                if ($section !== 'Procurement') {
                    $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                }

                if (!$stmt->execute()) {
                    throw new Exception('Failed to execute query.');
                }

                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'message' => 'Notifications successfully fetched.',
                    'notifications' => $notifications
                ];
            }catch(Exception $e){
                return [
                    'success' => false,
                    'message' => 'Internal server error: ' . $e->getMessage()
                ];
            }
        }
    }
?>