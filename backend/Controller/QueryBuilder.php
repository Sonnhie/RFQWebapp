<?php

namespace App\Controller;


class QueryBuilder
{

    private static $request_table = 'request_table';
    private static $attachment_table = 'attachment_table';
    private static $email_table = 'email_table';
    private static $requestLogs_table = 'request_logs_table';
    private static $comparison_table = 'comparison_table';
    private static $delivery_table = 'delivery_table';
    private static $notification_table = "notification_table";
    private static $currency_table = "currency_table";
    private static $upload_path = "department_paths";
    private static $signature_table = "signature_table";


    public static function insertNewRequest()
    {
        return "INSERT INTO " . self::$request_table . " 
                        (control_number, item_name, item_description, item_purpose, item_quantity, item_uom, item_status, item_remarks, item_requestor, item_section) 
                        VALUES (:control_number, :item_name, :item_description, :item_purpose, :item_quantity, :item_uom, :item_status, :item_remarks, :item_requestor, :item_section)";
    }

    public static function getLatestControlNumber()
    {
        return "select control_number, created_at 
                    from " . self::$request_table . "
                    where DATE_FORMAT(created_at, '%Y%m') = :current_month
                    order by created_at desc 
                    limit 1";
    }

    public static function insertNewAttachment()
    {
        return "INSERT INTO " . self::$attachment_table . " (control_number, item_name, item_attachment) 
                    VALUES (:control_number, :item_name, :item_attachment)";
    }

    public static function updateAttachment()
    {
        return "UPDATE " . self::$attachment_table . " 
                    SET item_name = :item_name, item_attachment = :item_attachment 
                    WHERE id = :id";
    }

    public static function insertNewLogs()
    {
        return "INSERT INTO " . self::$requestLogs_table . " 
                    (control_number, status, remarks) 
                    VALUES (:control_number, :item_status, :item_remarks)";
    }

    public static function fetchItemsbySection($filters, $params)
    {
        $query = "SELECT * FROM " . self::$request_table . " where 1=1";

        if (!empty($filters['section'])) {
            $query .= " AND item_section = :item_section";
            $params[':item_section'] = $filters['section'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND item_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[':from'] = $filters['from'];
            $params[':to'] = $filters['to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (item_name LIKE :search OR item_description LIKE :search OR control_number LIKE :search
                OR item_requestor LIKE :search OR item_section LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function CountAllRequests($filters)
    {
        $params = [];

        $query = "SELECT COUNT(*) as total FROM " . self::$request_table . " WHERE 1=1 ";

        if (!empty($filters['section'])) {
            $query .= " AND item_section = :item_section";
            $params[':item_section'] = $filters['section'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND item_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[':from'] = $filters['from'];
            $params[':to'] = $filters['to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (item_name LIKE :search OR item_description LIKE :search OR control_number LIKE :search
                OR item_requestor LIKE :search OR item_section LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function CountAllRequestsByControlNumber($filters)
    {
        $params = [];

        $query = "SELECT COUNT(distinct control_number) as total FROM " . self::$request_table . " WHERE 1=1 ";


        if (!empty($filters['section'])) {
            $query .= " AND item_section = :item_section";
            $params[':item_section'] = $filters['section'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND item_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[':from'] = $filters['from'];
            $params[':to'] = $filters['to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (item_name LIKE :search OR item_description LIKE :search OR control_number LIKE :search
                OR item_requestor LIKE :search OR item_section LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function getAttachment()
    {
        return "SELECT item_attachment FROM " . self::$attachment_table . " WHERE id = :id";
    }

    public static function updateItem()
    {
        return "UPDATE " . self::$request_table . " 
                    SET item_name = :item_name, item_description = :item_description, item_purpose = :item_purpose, item_quantity = :item_quantity, item_uom = :item_uom  
                    WHERE id = :id";
    }

    public static function deleteRequest()
    {
        return "DELETE FROM " . self::$request_table . " WHERE id = :id";
    }

    public static function deleteAllRequest()
    {
        return "DELETE FROM " . self::$request_table . " WHERE control_number = :id";
    }

    public static function deleteAttachment()
    {
        return "DELETE FROM " . self::$attachment_table . " WHERE id = :id";
    }

    public static function getSingleRequest()
    {
        return "SELECT * FROM " . self::$request_table . " WHERE control_number = :CONTROL_NUMBER";
    }

    public static function getComparisonCount($filters)
    {
        $params = [];

        $query = "SELECT COUNT(distinct control_number) as total FROM " . self::$request_table . " WHERE 1=1";

        if (!empty($filters['section'])) {
            $query .= " AND item_section = :item_section";
            $params[':item_section'] = $filters['section'];
        }

        if (!empty($filters['control_number'])) {
            $query .= " AND control_number = :control_number";
            $params[':control_number'] = $filters['search'];
        }

        $query .= " AND item_status  = 'Comparison For Approval'";

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function getRequestForSectionApproval($filters, $params)
    {

        $query = "SELECT DISTINCT control_number, item_section, item_status, item_remarks, created_at 
            FROM " . self::$request_table . " WHERE 1=1";

        if ($filters['section'] == 'Procurement' && $filters['role'] == 'Manager') {
            $query .= " AND item_section = 'Procurement' OR item_section LIKE '%2nd Process%' OR item_section = 'Injection'";
        }

        if ($filters['section'] != 'Procurement' && $filters['role'] == 'Section-Approver') {
            $query .= " AND item_section = :item_section";
            $params[':item_section'] = $filters['section'];
        }

        if ($filters['section'] == 'Procurement' && $filters['role'] == 'Verifier-Approver') {
            $query .= " AND item_section = 'Procurement'";
        }

        if (!empty($filters['status'])) {
            $query .= " AND item_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[':from'] = $filters['from'];
            $params[':to'] = $filters['to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (item_name LIKE :search OR item_description LIKE :search OR control_number LIKE :search
                OR item_requestor LIKE :search OR item_section LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $query .= " AND item_remarks = 'For Section head approval'";

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function getRequestForSectionApprovalCount($filters)
    {
        $params = [];
        $query = "SELECT COUNT(distinct control_number) as total from " . self::$request_table . " WHERE 1=1";

        if ($filters['section'] == 'Procurement' && $filters['role'] == 'Manager') {
            $query .= " AND item_section = 'Procurement' OR item_section LIKE '%2nd Process%' OR item_section = 'Injection'";
        }

        if ($filters['section'] != 'Procurement' && $filters['role'] == 'Section-Approver') {
            $query .= " AND item_section = :item_section";
            $params[':item_section'] = $filters['section'];
        }

        if ($filters['section'] == 'Procurement' && $filters['role'] == 'Verifier-Approver') {
            $query .= " AND item_section = 'Procurement'";
        }

        if (!empty($filters['status'])) {
            $query .= " AND item_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[':from'] = $filters['from'];
            $params[':to'] = $filters['to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (item_name LIKE :search OR item_description LIKE :search OR control_number LIKE :search
                OR item_requestor LIKE :search OR item_section LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $query .= " AND item_remarks = 'For Section head approval'";

        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function UpdateRequestStatus()
    {
        return "UPDATE " . self::$request_table . " 
                    SET item_remarks = :item_remarks, item_status = :item_status  
                    WHERE control_number = :control_number";
    }

    public static function fetchRequestForverification($filters, $params)
    {
        $query = "SELECT * FROM " . self::$request_table . " where 1=1";

        if (!empty($filters['remarks'])) {
            $statuses = $filters['remarks'];

            switch ($statuses) {
                case 'For Procurement Verification':
                    $query .= " AND item_remarks = :item_remarks";
                    $params[':item_remarks'] = $filters['remarks'];
                    break;
                case 'For Section head approval':
                    $query .= " AND item_remarks = :item_remarks";
                    $params[':item_remarks'] = $filters['remarks'];
                    break;
            }
        }


        if (!empty($filters['status'])) {
            $query .= " AND item_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[':from'] = $filters['from'];
            $params[':to'] = $filters['to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (item_name LIKE :search OR item_description LIKE :search OR control_number LIKE :search
                OR item_requestor LIKE :search OR item_section LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function CountRequestForverification($filters)
    {
        $params = [];

        $query = "SELECT COUNT(distinct control_number) as total FROM " . self::$request_table . " WHERE 1=1 ";

        if (!empty($filters['remarks'])) {
            $statuses = $filters['remarks'];

            switch ($statuses) {
                case 'For Procurement Verification':
                    $query .= " AND item_remarks = :item_remarks";
                    $params[':item_remarks'] = $filters['remarks'];
                    break;
                case 'For Section head approval':
                    $query .= " AND item_remarks = :item_remarks";
                    $params[':item_remarks'] = $filters['remarks'];
                    break;
            }
        }

        if (!empty($filters['status'])) {
            $query .= " AND item_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[':from'] = $filters['from'];
            $params[':to'] = $filters['to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (item_name LIKE :search OR item_description LIKE :search OR control_number LIKE :search
                OR item_requestor LIKE :search OR item_section LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function getTotalVerifiedCount($filters)
    {
        $params = [];

        $query = "SELECT COUNT(distinct control_number) as total from " . self::$request_table . " where 1=1";

        if (!empty($filters['dateFrom']) && $filters['dateTo']) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[":from"] = $filters["dateFrom"];
            $params[":to"] = $filters["dateTo"];
        }

        if (!empty($filters['searchValue'])) {
            $query .= " AND (control_number LIKE :search OR item_status LIKE :search OR item_section LIKE :search)";
            $params[":search"] = '%' . $filters["searchValue"] . '%';
        }

        $query .= " AND item_remarks = 'For Quotation'";

        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function getVerifiedRequest($filters, $params)
    {

        $query = "SELECT control_number, item_remarks, item_status, item_section, created_at from "
            . self::$request_table . " WHERE 1=1";

        if (!empty($filters['dateFrom']) && $filters['dateTo']) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[":from"] = $filters["dateFrom"];
            $params[":to"] = $filters["dateTo"];
        }

        if (!empty($filters['searchValue'])) {
            $query .= " AND (control_number LIKE :search OR item_status LIKE :search)";
            $params[":search"] = '%' . $filters["searchValue"] . '%';
        }

        $query .= " AND item_remarks = 'For Quotation' GROUP BY control_number ASC";

        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function CheckDuplicateComparison()
    {
        return "SELECT COUNT(*) FROM " . self::$comparison_table .
            " WHERE control_number = :control_number AND item_name = :item_name AND supplier_name = :supplier_name";
    }

    public static function InserNewComparison()
    {
        return "INSERT INTO " . self::$comparison_table . " 
                    (control_number, item_name, item_quantity, item_description, item_uom, payment_terms, delivery_time, supplier_name, supplier_price, currency, supplier_discount, total_price) 
                    VALUES (:control_number, :item_name, :item_quantity, :item_description, :item_uom, :payment_terms, :delivery_time, :supplier_name, :supplier_price, :currency, :supplier_discount, :total_price)";
    }

    public static function getTotalComparisonCount($filters, $section)
    {
        $params = [];

        $query = "SELECT COUNT(distinct control_number) as total from " . self::$request_table . " where 1=1";

        if ($section != 'Procurement') {
            $query .= " AND item_section = :section";
            $params[":section"] = $section;
        }

        if (!empty($filters['dateFrom']) && $filters['dateTo']) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[":from"] = $filters["dateFrom"];
            $params[":to"] = $filters["dateTo"];
        }

        if (!empty($filters['searchValue'])) {
            $query .= " AND (control_number LIKE :search OR item_status LIKE :search OR item_section LIKE :search)";
            $params[":search"] = '%' . $filters["searchValue"] . '%';
        }

        if ($filters['role'] == 'Verifier') {
            $query .= " AND (item_remarks = 'Comparison Created' 
                                OR item_remarks LIKE '%Comparison Approved by:%' OR item_remarks LIKE '%Disapproved by:%' OR item_remarks LIKE '%Comparison Acknowledge by:%' OR item_remarks LIKE '%Comparison Verified by:%')";
        }

        if ($filters["role"] == "Verifier-Approver") {
            $query .= " AND item_remarks = 'Comparison Created'";
        }

        if ($filters["role"] == "Manager") {
            $query .= " AND item_remarks = 'Comparison Verified by: Melanie Gancayco'";
        }

        if ($filters["role"] == "Requestor") {
            $query .= " AND (item_remarks = 'Comparison Acknowledge by: Wilfredo Arante' OR item_remarks LIKE '%Disapproved by:%' OR item_remarks LIKE '%Comparison Approved by:%')";
        }

        if ($filters["role"] == "Section-Approver") {
            $query .= " AND (item_remarks = 'Comparison Acknowledge by: Wilfredo Arante' OR item_remarks LIKE '%Disapproved by:%' OR item_remarks LIKE '%Comparison Approved by:%')";
        }


        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function getComparisonRequest($filters, $section, $params)
    {

        $query = "SELECT distinct control_number, item_remarks, item_requestor, item_status, item_section, created_at from "
            . self::$request_table . " WHERE 1=1";

        if ($section != 'Procurement') {
            $query .= " AND item_section = :section";
            $params[":section"] = $section;
        }

        if (!empty($filters['dateFrom']) && $filters['dateTo']) {
            $query .= " AND created_at BETWEEN :from AND :to";
            $params[":from"] = $filters["dateFrom"];
            $params[":to"] = $filters["dateTo"];
        }

        if (!empty($filters['searchValue'])) {
            $query .= " AND (control_number LIKE :search OR item_status LIKE :search OR item_section LIKE :search)";
            $params[":search"] = '%' . $filters["searchValue"] . '%';
        }

        if ($filters['role'] == 'Verifier') {
            $query .= " AND (item_remarks = 'Comparison Created' 
                                OR item_remarks LIKE '%Comparison Approved by:%' OR item_remarks LIKE '%Disapproved by:%' OR item_remarks LIKE '%Comparison Acknowledge by:%' OR item_remarks LIKE '%Comparison Verified by:%')";
        }

        if ($filters["role"] == "Verifier-Approver") {
            $query .= " AND item_remarks = 'Comparison Created'";
        }

        if ($filters["role"] == "Manager") {
            $query .= " AND item_remarks = 'Comparison Verified by: Melanie Gancayco'";
        }

        if ($filters["role"] == "Requestor") {
            $query .= " AND (item_remarks = 'Comparison Acknowledge by: Wilfredo Arante' OR item_remarks LIKE '%Disapproved by:%' OR item_remarks LIKE '%Comparison Approved by:%')";
        }

        if ($filters["role"] == "Section-Approver") {
            $query .= " AND (item_remarks = 'Comparison Acknowledge by: Wilfredo Arante' OR item_remarks LIKE '%Disapproved by:%' OR item_remarks LIKE '%Comparison Approved by:%')";
        }

        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function getComparisonResponse()
    {
        return "SELECT * FROM " . self::$comparison_table . " 
                    WHERE control_number = :control_number 
                    ORDER BY item_name ASC, supplier_name ASC";
    }

    public static function updateComparison()
    {
        return "UPDATE " . self::$comparison_table . " SET supplier_name = :supplier_name, supplier_price = :supplier_price,
                    supplier_discount = :item_discount, total_price = :item_total where id = :supplier_id";
    }


    public static function getTotalStatusCount($data)
    {
        $params = [];
        $query = "
                SELECT 
                    MONTH(latest.created_at) AS month,
                    SUM(CASE WHEN latest.item_status = 'Completed' THEN 1 ELSE 0 END) AS Completed,
                    SUM(CASE WHEN latest.item_status = 'Pending' THEN 1 ELSE 0 END) AS Pending,
                    SUM(CASE WHEN latest.item_status = 'Hold' THEN 1 ELSE 0 END) AS Hold
                FROM (
                    SELECT 
                        control_number, 
                        item_section,
                        MAX(created_at) AS created_at,
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(item_status ORDER BY created_at DESC),
                            ',',
                            1
                        ) AS item_status
                    FROM request_table
                    WHERE YEAR(created_at) = :current_year
                    GROUP BY control_number, item_section
                ) AS latest
                WHERE 1=1
            ";
        $params[":current_year"] = $data['current_year'];

        // Role condition
        if ($data['section'] != "Procurement") {
            $query .= " AND latest.item_section = :item_section";
            $params[":item_section"] = $data['section'];
        }

        // Final grouping and ordering
        $query .= "
                GROUP BY MONTH(latest.created_at)
                ORDER BY month
            ";

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function getTotalStatusCountofMonth($data)
    {
        $params = [];
        $query = "
                SELECT 
                    MONTH(latest.created_at) AS month,
                    SUM(CASE WHEN latest.item_status = 'Completed' THEN 1 ELSE 0 END) AS Completed,
                    SUM(CASE WHEN latest.item_status = 'Pending' THEN 1 ELSE 0 END) AS Pending,
                    SUM(CASE WHEN latest.item_status = 'Hold' THEN 1 ELSE 0 END) AS Hold
                FROM (
                    SELECT 
                        control_number, 
                        item_section,
                        MAX(created_at) AS created_at,
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(item_status ORDER BY created_at DESC),
                            ',',
                            1
                        ) AS item_status
                    FROM request_table
                    WHERE YEAR(created_at) = :current_year
                    AND MONTH(created_at) = :current_month
                    GROUP BY control_number, item_section
                ) AS latest
                WHERE 1=1
            ";

        $params[":current_year"] = date('Y');
        $params[":current_month"] = date('n'); // numeric month without leading zero

        // Role condition
        if ($data['section'] != "Procurement") {
            $query .= " AND latest.item_section = :item_section";
            $params[":item_section"] = $data['section'];
        }

        // Final grouping and ordering
        $query .= "
                GROUP BY MONTH(latest.created_at)
                ORDER BY month
            ";

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function getLatestRequest($section)
    {
        $params = [];
        $query = "SELECT * from " . self::$request_table . " where 1=1";

        if ($section != "Procurement") {
            $query .= " AND item_section = :item_section";
            $params[":item_section"] = $section;
        }

        $query .= " AND created_at
                    >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY created_at DESC";

        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function getEmailReceipient($section)
    {
        $params = [];
        $query = "select emailadd from " . self::$email_table . " where 1=1";

        if (!empty($section)) {
            $query .= " AND department = :department ";
            $params[":department"] = $section;
        }

        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function getBCC($section)
    {
        $params = [];
        $query = "select emailadd from " . self::$email_table . " where 1=1";

        if (!empty($section)) {
            $query .= " AND department = :department";
            $params[":department"] = $section;
        }

        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function getTimeline($controlnumber)
    {
        $params = [];
        $query = "select status, remarks, DATE(update_at) as date, TIME(update_at) as time 
                          from " . self::$requestLogs_table . " where 1=1";

        if (!empty($controlnumber)) {
            $query .= " AND control_number = :control_number  ORDER BY update_at ASC";
            $params[":control_number"] = $controlnumber;
        }

        return [
            "query" => $query,
            "params" => $params
        ];
    }

    public static function getsuggesstion($input)
    {
        $params = [];
        $query = "SELECT Distinct control_number from " . self::$requestLogs_table . " where 1=1";
        if (!empty($input)) {
            $query .= " AND control_number LIKE :input limit 10";
            $params[":input"] = '%' . $input . '%';
        }
        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function UpdateExchangeRate($rate)
    {
        $params = [];

        $query =  "UPDATE " . self::$currency_table . " SET currency_value = :currency_value WHERE currency_id = :currency_id";
        $params[':currency_id'] = $rate['currency_id'];
        $params[':currency_value'] = $rate['currency_value'];

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    public static function GetExchangeRate()
    {
        return "SELECT * FROM " . self::$currency_table . " WHERE currency_id = :currency_id";
    }

    public static function GetUploadPath(){
        return "SELECT upload_path from " . self::$upload_path . " WHERE department_name = :department LIMIT 1";
    }

    public static function InsertSignature(){
        return "INSERT INTO " . self::$signature_table . " (requestor_name, section, signature_path, printed_name, role) 
        VALUES (:requestor_name, :section, :signature_path, :printed_name, :role)";
    }

    public static function GetUserESign(){
        return "SELECT signature_path FROM " . self::$signature_table . " WHERE requestor_name = :requestor_name AND section = :section";
    }

    public static function UpdateEsign(){
        return "UPDATE " . self::$signature_table . " SET signature_path = :signature_path WHERE requestor_name = :requestor_name AND section = :section";
    }

    public static function getDeptEsign(){
        return "SELECT * FROM " . self::$signature_table . " WHERE section = :section";
    }

}
