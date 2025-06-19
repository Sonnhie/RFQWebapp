<?php
    // Include the user model
    include_once './backend/Model/usermodel.php';
    // Include the database connection
    include_once './database/dbconnection.php';
    // Create a new instance of the database connection
    $database = new DBConnection();
    $db = $database->getConnection();

    class DashboardManagement{
        private $request_table = 'request_table';
        private $attachment_table = 'attachment_table';
        private $requeststatus_table = 'requeststatus_table';
        private $conn;

        public function __construct($db){
            $this->conn = $db;
        }

        public function getChartData($year, $section){
            $sql = "
                SELECT 
                    MONTH(latest.created_at) AS month,
                    SUM(CASE WHEN latest.item_status = 'Approved' THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN latest.item_status = 'Pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN latest.item_status = 'Rejected' THEN 1 ELSE 0 END) AS rejected
                FROM (
                    SELECT 
                        control_number,
                        MAX(created_at) as created_at,
                        -- Prefer latest status per control_number
                        SUBSTRING_INDEX(GROUP_CONCAT(item_status ORDER BY created_at DESC), ',', 1) AS item_status
                    FROM {$this->request_table}
                    WHERE YEAR(created_at) = :year
            ";

            if ($section !== 'Procurement') {
                $sql .= " AND item_section = :section";
            }

            $sql .= "
                    GROUP BY control_number
                ) AS latest
                GROUP BY MONTH(latest.created_at)
                ORDER BY MONTH(latest.created_at)
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':year', $year);

            if ($section !== 'Procurement') {
                $stmt->bindParam(':section', $section);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        }

        
       public function getTotalCountPerStatus($year, $section){
            $sql = "
                SELECT 
                    status_summary.item_status, 
                    COUNT(*) AS total_count
                FROM (
                    SELECT 
                        control_number,
                        MAX(created_at) AS created_at,
                        SUBSTRING_INDEX(GROUP_CONCAT(item_status ORDER BY created_at DESC), ',', 1) AS item_status
                    FROM {$this->request_table}
                    WHERE YEAR(created_at) = :year
            ";

            if ($section !== 'Procurement') {
                $sql .= " AND item_section = :section";
            }

            $sql .= "
                    GROUP BY control_number
                ) AS status_summary
                GROUP BY status_summary.item_status
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':year', $year);

            if ($section !== 'Procurement') {
                $stmt->bindParam(':section', $section);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

    }
?>