<?php

namespace App\Controller;

use Database\DBConnection;
use App\Controller\QueryBuilder;

$database = new DBConnection();
$db = $database->getConnection();

class dashboard_management
{
    private $request_table = 'request_table';
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getChartData($data)
    {
        try {
            $params = [];
            $builder = QueryBuilder::getTotalStatusCount($data);
            $query = $builder['query'];
            $params = $builder['params'];
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $datas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'message' => 'Successfully data received.',
                'data' => $datas
            ];
        } catch (\PDOException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getPieChartData($data)
    {
        try {
            $params = [];
            $builder = QueryBuilder::getTotalStatusCountofMonth($data);
            $query = $builder['query'];
            $params = $builder['params'];
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'message' => 'Successfully data received.',
                'data' => $data
            ];
        } catch (\PDOException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getTotalCountPerStatus($year, $section)
    {
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
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }
}
