<?php
    // Include the database connection

use GuzzleHttp\Psr7\Query;

    include_once '../../database/dbconnection.php';
    // Include the query builder
    include_once '../Controller/queryBuilder.php';

    $database = new DBConnection();
    $db = $database->getConnection();

    class ComparisonManagement{

        private $conn;

        public function __construct($db) {
            $this->conn = $db;
        }
        
        public function fetchComparisonByControlNumber($filters, $page = 1, $perPage = 10) {
            $params = [];

            // Build SELECT query
            $builder = QueryBuilder::getComparison($filters, $params);

            $query = $builder['query'];
            $params = $builder['params'];

            // Order by latest
            $query .= " ORDER BY created_at DESC";

            // Pagination
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);

            // Bind values
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function countComparison($filters) {
            $params = [];

            // Instead of COUNT(*), use COUNT(DISTINCT control_number)
            $builder = QueryBuilder::getComparisonCount($filters, $params);

            $query = $builder['query'];
            $params = $builder['params'];

            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        }
    }
?>