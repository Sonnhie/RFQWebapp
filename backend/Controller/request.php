<?php

namespace App\Controller;

require_once __DIR__ . '/../../vendor/autoload.php';


use Database\dbconnection;
use App\Controller\QueryBuilder;
use Exception;

$database = new dbconnection();
$db = $database->getConnection();

class request
{

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    //Function to create control number
    public function createRFQNumber()
    {

        $date = date('Ym');
        $currentMonth = date('Ym');

        $query = QueryBuilder::getLatestControlNumber();
        $lastId = 0;
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':current_month', $currentMonth, \PDO::PARAM_STR);
        $stmt->execute();
        $rowcount = $stmt->rowCount();

        if ($rowcount > 0) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $max_rfq_number = $result['control_number'];
            $lastId = isset($max_rfq_number) ?
                intval(substr($max_rfq_number, -4)) : 0;
            $newID = $lastId + 1;
            $controlID = "RFQ-" . $date . "-" . str_pad($newID, 4, "0", STR_PAD_LEFT);
        } else {
            $controlID = "RFQ-" . $date . "-0001";
        }
        return $controlID;
    }

    // Function to create a new request
    public function CreateNewRequest($data)
    {
        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'Empty or null data.'
            ];
        }

        try {
            $this->conn->beginTransaction();

            $query = QueryBuilder::insertNewRequest();
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':control_number', $data['control_number']);
            $stmt->bindParam(':item_name', $data['item_name']);
            $stmt->bindParam(':item_description', $data['item_description']);
            $stmt->bindParam(':item_purpose', $data['item_purpose']);
            $stmt->bindParam(':item_quantity', $data['item_quantity']);
            $stmt->bindParam(':item_uom', $data['item_unit']);
            $stmt->bindParam(':item_status', $data['requestor_status']);
            $stmt->bindParam(':item_remarks', $data['item_remarks']);
            $stmt->bindParam(':item_requestor', $data['requestor_name']);
            $stmt->bindParam(':item_section', $data['requestor_section']);

            if (!$stmt->execute()) {
                throw new \Exception('Creation failed');
            }

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Request created successfully.'
            ];
        } catch (\Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ];
        }
    }

    //Function to upload attachments
    public function UploadAttachment($files)
    {
        // $control_number = $this->createRFQNumber();
        $query = QueryBuilder::insertNewAttachment();
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':control_number', $files['control_number']);
        $stmt->bindParam(':item_name', $files['item_name']);
        $stmt->bindValue(':path_file', $files['path_file']);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Function to fetch all requests with pagination and filters
    public function fetchItemBySectionRequests($filters, $page = 1, $perPage = 10)
    {
        $params = [];

        // Build SELECT query
        $builder = QueryBuilder::fetchItemsbySection($filters, $params);

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

        $stmt->bindValue(':limit', (int)$perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Function to count all requests based on filters
    public function countAllRequests($filters)
    {
        $params = [];

        // Instead of COUNT(*), use COUNT(DISTINCT control_number)
        $builder = QueryBuilder::CountAllRequests($filters, $params);

        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    //Get attachment
    public function getAttachment($id, $control_number)
    {
        $query = QueryBuilder::getAttachment($id);
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT); // Use type hinting for safety
        $stmt->bindParam(':control_number', $control_number, \PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result && isset($result['path_file']) ? $result['path_file'] : null;
    }

    //Update Attachment
    public function updateAttachment($files)
    {
        $query = QueryBuilder::updateAttachment();
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $files['id']);
        $stmt->bindParam(':item_name', $files['item_name']);
        $stmt->bindValue(':path_file', $files['path_file']);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    //Delete Attachment
    public function deleteAttachment($id)
    {
        $query = QueryBuilder::deleteAttachment();
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT); // Use type hinting for safety
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    //Edit Items
    public function editItems($data)
    {
        $query = QueryBuilder::updateItem();
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':item_name', $data['item_name']);
        $stmt->bindParam(':item_description', $data['item_description']);
        $stmt->bindParam(':item_purpose', $data['item_purpose']);
        $stmt->bindParam(':item_quantity', $data['item_quantity']);
        $stmt->bindParam(':item_uom', $data['item_unit']);
        $stmt->bindParam(':id', $data['id']);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    //Delete Request
    public function deleteRequest($id)
    {
        try {
            // Start a transaction
            $this->conn->beginTransaction();

            // First delete query
            $query = QueryBuilder::deleteRequest();
            $stmt1 = $this->conn->prepare($query);
            $stmt1->bindParam(':id', $id);
            $stmt1->execute();

            // Second delete query
            $query2 = QueryBuilder::deleteAttachment();
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':id', $id);
            $stmt2->execute();

            // If both succeeded
            $this->conn->commit();
            return true;
        } catch (\PDOException $e) {
            // If something fails, rollback
            $this->conn->rollBack();
            error_log("Delete failed: " . $e->getMessage());
            return false;
        }
    }

    //fetch all requests by control number
    public function fetchAllRequestsByControlNumber($filters, $page = 1, $perPage = 10)
    {

        $params = [];
        $builder = QueryBuilder::fetchItemsbySection($filters, $params);
        $query = $builder['query'];
        $params = $builder['params'];
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
        $stmt->bindValue(':limit', (int)$perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //Count all requests by control number
    public function countAllRequestsByControlNumber($filters)
    {
        $params = [];
        $builder = QueryBuilder::CountAllRequestsByControlNumber($filters, $params);
        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);

        // Bind dynamic parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Function to fetch a single request by ID
    public function fetchRequestById($id)
    {
        $query = QueryBuilder::getSingleRequest();
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':CONTROL_NUMBER', $id); // Use type hinting for safety
        $stmt->execute();

        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }

    public function fetchForSectionApprovals($filter, $page = 1, $perPage = 10)
    {
        $params = [];
        $builder = QueryBuilder::getRequestForSectionApproval($filter, $params);
        $query = $builder['query'];
        $params = $builder['params'];

        $query .= ' ORDER BY created_at DESC';

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
        $stmt->bindValue(':limit', (int)$perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetchForSectionApprovalsCount($filters)
    {
        $params = [];
        $builder = QueryBuilder::getRequestForSectionApprovalCount($filters, $params);
        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);

        // Bind dynamic parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function UpdateRequestStatus($data)
    {
        $query = QueryBuilder::UpdateRequestStatus();
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':item_remarks', $data['item_remarks']);
        $stmt->bindValue(':item_status', $data['requestor_status']);
        $stmt->bindValue(':control_number', $data['control_number']);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }


    //fetch all requests by control number
    public function fetchVerificationRequestsByControlNumber($filters, $page = 1, $perPage = 10)
    {

        $params = [];
        $builder = QueryBuilder::fetchRequestForverification($filters, $params);
        $query = $builder['query'];
        $params = $builder['params'];
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
        $stmt->bindValue(':limit', (int)$perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //Count all requests by control number
    public function countVerificationRequestsByControlNumber($filters)
    {
        $params = [];
        $builder = QueryBuilder::CountRequestForverification($filters, $params);
        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);

        // Bind dynamic parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    //fetch all requests by control number
    public function fetchHistoryByControlNumber($filters, $page = 1, $perPage = 10)
    {

        $params = [];
        $builder = QueryBuilder::fetchRequestHistory($filters, $params);
        $query = $builder['query'];
        $params = $builder['params'];
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
        $stmt->bindValue(':limit', (int)$perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //Count all requests by control number
    public function countHistoryByControlNumber($filters)
    {
        $params = [];
        $builder = QueryBuilder::CountRequestHistory($filters, $params);
        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);

        // Bind dynamic parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function totalVerificationRequest($filters)
    {
        $params = [];
        $builder = QueryBuilder::getTotalVerifiedCount($filters);
        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getVerifiedData($filters, $page, $limit)
    {
        $params = [];
        $builder = QueryBuilder::getVerifiedRequest($filters, $params);
        $query = $builder['query'];
        $params = $builder['params'];
        $offset = ($page - 1) * $limit;
        $query .= " LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // Bind LIMIT and OFFSET (must be integers)
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function CreateComparison($data)
    {
        // Check if control number + item + supplier already exists
        $checkQuery = QueryBuilder::CheckDuplicateComparison();
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->execute([
            ':control_number' => $data['control_number'],
            ':item_name'      => $data['item_name'],
            ':supplier_name'  => $data['supplier_name']
        ]);

        $count = $stmt->fetchColumn();
        // var_dump($data);
        // exit;

        if ($count == 0) {
            $query = QueryBuilder::InserNewComparison();
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':control_number', $data['control_number']);
            $stmt->bindValue(':item_name', $data['item_name']);
            $stmt->bindValue(':item_quantity', $data['item_quantity']);
            $stmt->bindValue(':item_description', $data['item_description']);
            $stmt->bindValue(':item_uom', $data['item_unit']);
            $stmt->bindValue(':supplier_name', $data['supplier_name']);
            $stmt->bindValue(':supplier_price', $data['supplier_price']);
            $stmt->bindValue(':currency', $data['currency']);
            $stmt->bindValue(':supplier_discount', $data['supplier_discount']);
            $stmt->bindValue(':total_price', $data['total_price']);
            $stmt->bindValue(':payment_terms', $data['payment_days']);
            $stmt->bindValue(':delivery_time', $data['delivery_days']);
            $stmt->bindValue(':upload_path', $data['path']);


            $result = $stmt->execute();

            if ($result) {
                return true;
            } else {
                return false;
            }
        }

        // Record already exists
        return false;
    }

    public function getComparison($filters, $section, $page, $limit)
    {
        $params = [];
        $builder = QueryBuilder::getComparisonRequest($filters, $section, $params);
        $query = $builder['query'];
        $params = $builder['params'];
        $offset = ($page - 1) * $limit;
        $query .= " LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // Bind LIMIT and OFFSET (must be integers)
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countComparison($filters, $section)
    {
        $params = [];
        $builder = QueryBuilder::getTotalComparisonCount($filters, $section);
        $query = $builder['query'];
        $params = $builder['params'];

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function fetchComparisonByControlNumber($control_number)
    {
        // Build SELECT query
        $query = QueryBuilder::getComparisonResponse();

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':control_number', $control_number, \PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function UpdateComparison($data)
    {
        try {
            $query = QueryBuilder::updateComparison();
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':supplier_id', $data['id'], \PDO::PARAM_INT);
            $stmt->bindParam(':supplier_price', $data['supplier_price'], \PDO::PARAM_STR);
            $stmt->bindParam(':item_discount', $data['supplier_discount'], \PDO::PARAM_STR);
            $stmt->bindParam(':item_total', $data['total'], \PDO::PARAM_STR);
            $stmt->bindParam(':supplier_name', $data['supplier_name'], \PDO::PARAM_STR);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Comparison has been successfully updated.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update comparison data.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getRecentRequest($section)
    {
        $params = [];


        $builder = QueryBuilder::getLatestRequest($section);
        $query = $builder['query'];
        $params = $builder['params'];
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function GetTimeline($control_number)
    {
        $params = [];

        if (empty($control_number)) {
            return [
                'success' => false,
                'message' => 'Empty or null control number'
            ];
            exit;
        }

        try {
            $builder = QueryBuilder::getTimeline($control_number);
            $query = $builder['query'];
            $params = $builder['params'];
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            if (!$stmt->execute()) {
                throw new \Exception('Failed to execute query.');
            }

            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => 'Logs successfully fetch.',
                'logs' => $logs
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ];
        }
    }

    public function FetchControlNumber($input)
    {
        $params = [];
        try {
            $builder = QueryBuilder::getsuggesstion($input);
            $query = $builder['query'];
            $params = $builder['params'];
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                $data = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                return [
                    'success' => true,
                    'data' => $data,
                    'message' => 'Data fetch successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error in fetching data.'
                ];
            }
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ];
        }
    }

    public function updateExchangeRate($rate)
    {
        $params = [];
        try {
            $builder = QueryBuilder::UpdateExchangeRate($rate);
            $query = $builder['query'];
            $params = $builder['params'];
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Exchange Rate successully updated.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Update failed.'
                ];
            }
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Internal server error: ' . $e
            ];
        }
    }

    public function GetExchangerate($currency_id)
    {
        try {
            $query = QueryBuilder::GetExchangeRate();
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':currency_id', $currency_id);
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => throw new \Exception('Failed to execute query.')
                ];
            }

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Internal server error: ' . $e
            ];
        }
    }

    public function getEsign($data)
    {
        try {
            $query = QueryBuilder::GetUserESign();
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':requestor_name', $data['requestor_name']);
            $stmt->bindValue(':section', $data['section']);

            if (!$stmt->execute()) {
                return [
                    'message' => throw new \Exception . 'Faile to execute query.'
                ];
            }

            $row = $stmt->Fetch(\PDO::FETCH_ASSOC);
            return $row;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Internal server error: ' . $e
            ];
        }
    }

    public function getDeptEsign($section)
    {
        try {
            $query = QueryBuilder::getDeptEsign();
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':section', $section);

            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => throw new Exception . 'Failed to execute query.'
                ];
            }

            $row = $stmt->fetchALL(\PDO::FETCH_ASSOC);
            return $row;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Internal server error: ' . $e
            ];
        }
    }

    public function DeleteRequestByControlNumber($control_number)
    {
        try {
            $query = QueryBuilder::deleteAllRequest();
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $control_number);
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => throw new Exception . 'Failed to execute query.'
                ];
            } else {
                return [
                    'success' => true,
                    'message' => 'Request successfully deleted.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Internal server error: ' . $e
            ];
        }
    }
}
