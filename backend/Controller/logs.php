<?php
    namespace App\Controller;

    use Database\dbconnection;
    use App\Controller\QueryBuilder;
    $database = new dbconnection();
    $db = $database->getConnection();

    class logs{

        private $conn;

        public function __construct($db) {
            $this->conn = $db;
        }
        
        //Function to create request logs
        public function CreateRequestLogs($data){
            $query = QueryBuilder::insertNewLogs();
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':control_number', $data['control_number']);
            $stmt->bindParam(':item_status', $data['requestor_status']);
            $stmt->bindParam(':item_remarks', $data['item_remarks']);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }
    }
?>