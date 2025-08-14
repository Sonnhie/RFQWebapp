<?php
    ob_start();
    session_start();

    include_once '../Controller/comparison.php';
    include_once '../Controller/logs.php';
    include_once '../../database/dbconnection.php';

    $request = new ComparisonManagement($db);
    $logs = new Logs($db);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!empty($_POST['action']) && $_POST['action'] == 'get_comparison_items') {
                header('Content-Type: application/json');

                $control_number = $_POST['control_number'] ?? null;

                if (empty($control_number)) {
                    echo json_encode(['status' => 'error', 'message' => 'Control number is required']);
                    exit;
                }

                $result = $request->fetchComparisonByControlNumber($control_number);

                $groupedData = [];
                if ($result) {
                    foreach ($result as $row) {
                        $item_name = $row['item_name'];

                        if (!isset($groupedData[$item_name])) {
                            $groupedData[$item_name] = [
                                'control_number' => $row['control_number'],
                                'item_name' => $item_name,
                                'item_remarks' => $row['remarks'],
                                'item_quantity' => $row['item_quantity'],
                                'suppliers' => []
                            ];
                        }

                        $groupedData[$item_name]['suppliers'][] = [
                            'id' => $row['id'],
                            'supplier_name' => $row['supplier_name'],
                            'item_price' => $row['supplier_price'],
                            'item_discount' => $row['supplier_discount'],
                            'item_total' => $row['total_price']
                        ];
                    }

                    echo json_encode([
                        'status' => 'success',
                        'data' => array_values($groupedData) // Reset keys to make JSON array
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No comparison data found']);
                }
            }

            if (!empty($_POST['action']) && $_POST['action'] == 'get_comparison') {
                header('Content-Type: application/json');
        
                $section = $_POST['section'] ?? null;
                $filter = $_POST['filters'] ?? null;
                $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
                $perPage = 10;

                //Data Object
                $filters = [
                    'section' => $section,
                    'from' => $filter['from'] ?? null,
                    'to' => $filter['to'] ?? null,
                    'status' => $filter['status'] ?? null,
                    'search' => $filter['search'] ?? null
                ];
            
                // Fetch paginated result
                $result = $request->fetchComparisonByControlNumber($filters, $page, $perPage);
            
                // Optional: Fetch total count for pagination
                $totalResult = $request->countComparison($filters);
                // $totalCount = count($result);
            
                $data = [];
            
                if ($result) {

                    foreach ($result as $row) { 
                        $data[] = [
                            'id' => $row['id'],
                            'control_number' => $row['control_number'],
                            'item_name' => $row['item_name'],
                            'item_description' => $row['item_description'],
                            'item_quantity' => $row['item_quantity'],
                            'item_unit' => $row['item_uom'],
                            'item_purpose' => $row['item_purpose'],
                            'requestor_section' => $row['item_section'],
                            'requestor_name' => $row['item_requestor'],
                            'requestor_status' => $row['item_status'],
                            'item_section' => $row['item_section'],
                            'item_remarks' => $row['item_remarks'],
                            'created_at' => $row['created_at']
                        ];
                    }

                    echo json_encode([
                        'status' => 'success',
                        'data' => $data,
                        'total' => $totalResult,
                        'currentPage' => $page,
                        'perPage' => $perPage
                    ]);

                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No data found']);
                }
            }

    }

    if($_SERVER['REQUEST_METHOD'] == 'GET') {

    }
?>