<?php
    ob_start();
    session_start();
    
    include_once './database/dbconnection.php';
    include_once './backend/Controller/user_management.php';
    include_once './backend/Controller/dashboard_management.php';
    include_once './backend/Controller/request_management.php';
    include_once './backend/Model/usermodel.php';
    require_once __DIR__ . '/./backend/websocket/WebSocketNotifier.php';

    use WebSocket\WebSocketNotifier;
    $user_management = new UserManagement($db);
    $request_management = new RequestManagement($db);
    $dashboard_management = new DashboardManagement($db);
    $notifier = new WebSocketNotifier("ws://192.168.101.195:8080");

    // Login Action
    if (!empty($_POST['action']) && $_POST['action'] === 'login') {
        header('Content-Type: application/json');

        $username = isset($_POST['username']) ? $_POST['username'] : null;
        $password = isset($_POST['password']) ? $_POST['password'] : null;
        $machine_token = isset($_POST['machine_token']) ? $_POST['machine_token'] : null;

        $result = $user_management->authenticate($username, $password, $machine_token);

        echo json_encode([
            'status'  => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ]);
    }

    //Create New Request
    if (!empty($_POST['action']) && $_POST['action'] == 'create_request') {
        header('Content-Type: application/json');

        $responses = [];
        $success_count = 0;
        $error_count = 0;

        // Variables
        $item_name = $_POST['item_name'] ?? null;
        $item_description = $_POST['item_description'] ?? null;
        $item_quantity = $_POST['item_quantity'] ?? null;
        $item_unit = $_POST['item_unit'] ?? null;
        $item_purpose = $_POST['item_purpose'] ?? null;
        $requestor_section = $_POST['requestor_section'] ?? null;
        $requestor_name = $_POST['requestor_name'] ?? null;
        $item_remarks = $_POST['remarks'] ?? null;
        $requestor_status = 'Pending';
        $item_attachment = $_FILES['item-attachment'] ?? null;

        // Validate
        if (empty($item_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Item name is required']);
            exit;
        }

        if (empty($_FILES['item-attachment']['tmp_name']) || count($_FILES['item-attachment']['tmp_name']) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'File upload is required']);
            exit;
        }

        $fileContents = [];
        foreach ($_FILES['item-attachment']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['item-attachment']['error'][$key] === UPLOAD_ERR_OK) {
                $fileContents[$key] = file_get_contents($tmpName);
            } else {
                $fileContents[$key] = null;
            }
        }

        $control_number = $request_management->createRFQNumber();

        // Create initial logs
        $request_management->CreateRequestLogs([
            'control_number' => $control_number,
            'requestor_status' => $requestor_status,
            'item_remarks' => 'Created Request'
        ]);

        // Loop through items
        foreach ($item_name as $key => $name) {
            $data = [
                'control_number' => $control_number,
                'item_name' => $name ?? null,
                'item_description' => $item_description[$key] ?? null,
                'item_quantity' => $item_quantity[$key] ?? null,
                'item_unit' => $item_unit[$key] ?? null,
                'item_purpose' => $item_purpose[$key] ?? null,
                'requestor_section' => $requestor_section ?? null,
                'requestor_status' => $requestor_status,
                'item_remarks' => $item_remarks,
                'requestor_name' => $requestor_name,
                'item_attachment' => $fileContents[$key] ?? null
            ];

            $request_management->UploadAttachment($data);
            $response = $request_management->CreateNewRequest($data);

            if (isset($response['success']) && $response['success']) {
                $success_count++;
            } else {
                $error_count++;
            }

            $responses[] = $response['message'] ?? 'Unknown result';
        }

        // Send back summary response
        if ($success_count > 0 && $error_count === 0) {
            // Notify WebSocket clients about the new request
            $notifier->send(
                'broadcast',
                [
                    'control_number' => $control_number,
                    'item_requestor' => $requestor_name,
                    'item_section' => $requestor_section,
                    'date' => date('Y-m-d H:i:s'),
                    'message' => "New request created with Control Number: $control_number"
                ],
                'Procurement' // Target section for the notification
            );

            echo json_encode(['status' => 'success', 'message' => "All $success_count items submitted successfully."]);
        } elseif ($success_count > 0 && $error_count > 0) {
            echo json_encode(['status' => 'partial', 'message' => "$success_count succeeded, $error_count failed.", 'details' => $responses]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Failed to submit all items.", 'details' => $responses]);
        }
    }

    //Fetch Items
    if (!empty($_POST['action']) && $_POST['action'] == 'get_items') {
        header('Content-Type: application/json');
    
        $section = $_POST['section'] ?? null;
        $filters = $_POST['filters'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $status = $filters['status'] ?? null;
        $query = $filters['search'] ?? null;
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $perPage = 10;
    
        // Fetch paginated result
        $result = $request_management->fetchAllRequests($section, $from, $to, $status, $query, $page, $perPage);
    
        // Optional: Fetch total count for pagination
        $totalResult = $request_management->countAllRequests($section, $from, $to, $status, $query);
    
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
                    'item_remarks' => $row['item_remarks'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
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

    //Fetch Items
    if (!empty($_POST['action']) && $_POST['action'] == 'get_itemsbycontrolnumber') {
        header('Content-Type: application/json');
    
        $section = $_POST['section'] ?? null;
        $filters = $_POST['filters'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $status = $filters['status'] ?? null;
        $query = $filters['search'] ?? null;
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $perPage = 10;
    
        // Fetch paginated result
        $result = $request_management->fetchAllRequestsByControlNumber($section, $from, $to, $status, $query, $page, $perPage);
    
        // Optional: Fetch total count for pagination
        $totalResult = $request_management->countAllRequestsByControlNumber($section, $from, $to, $status, $query);
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

    //Fetch Items
    if (!empty($_POST['action']) && $_POST['action'] == 'get_single_items') {
        header('Content-Type: application/json');
    
        $control_number = $_POST['control_number'] ?? null;

    
        // Fetch paginated result
        $result = $request_management->fetchRequestById($control_number);
    
    
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
                    'item_remarks' => $row['item_remarks'],
                    'created_at' => $row['created_at']
                ];
            }
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No data found']);
        }
    }
    
    //Action for items (Update, Delete, View attachments)
    if(!empty($_POST['action']) && $_POST['action'] == 'delete_item'){
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $result = $request_management->deleteRequest($id);
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Request deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete request']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'edit_request') {
        header('Content-Type: application/json');

        $id = isset($_POST['item_id']) ? $_POST['item_id'] : null;
        $item_name = isset($_POST['item_name']) ? $_POST['item_name'] : null;
        $item_description = isset($_POST['item_description']) ? $_POST['item_description'] : null;
        $item_purpose = isset($_POST['item_purpose']) ? $_POST['item_purpose'] : null;
        $item_quantity = isset($_POST['item_quantity']) ? $_POST['item_quantity'] : null;
        $item_unit = isset($_POST['item_unit']) ? $_POST['item_unit'] : null;
        $item_purpose = isset($_POST['item_purpose']) ? $_POST['item_purpose'] : null;

        $data = [
            'id' => $id,
            'item_name' => $item_name,
            'item_description' => $item_description,
            'item_quantity' => $item_quantity,
            'item_unit' => $item_unit,
            'item_purpose' => $item_purpose
        ];

        $result = $request_management->editItems($data);
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
        }
        
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'get_item_details') {
        header('Content-Type: application/json');
    
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $filePath = $request_management->getAttachment($id); // this already returns the file path
    
        if (empty($filePath)){
            echo json_encode(['status' => 'error', 'message' => 'File not found']);
            exit;
        }else{
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($filePath);
            $encodedFile = base64_encode($filePath);
    
            $data = [
                'file_type' => $mimeType,
                'file_content' => $encodedFile
            ];

            echo json_encode(['status' => 'success', 'data' => $data]);
        }
    }
    
    if (!empty($_POST['action']) && $_POST['action'] == 'get_chart_data') {
        header('Content-Type: application/json');
    
        $year = isset($_POST['year']) ? $_POST['year'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : 'Procurement'; // Default to 'Procurement' if not set
    
        if ($year) {
            $chartData = $dashboard_management->getChartData($year, $section);

            // Initialize months with zeroes (1-based index)
            $approvedData = array_fill(1, 12, 0);
            $pendingData  = array_fill(1, 12, 0);
            $rejectedData = array_fill(1, 12, 0);

            foreach ($chartData as $row) {
                $month = (int)$row['month'];

                // ✅ Accumulate counts instead of overwriting
                $approvedData[$month] += (int)$row['approved'];
                $pendingData[$month]  += (int)$row['pending'];
                $rejectedData[$month] += (int)$row['rejected'];
            }

            // ✅ Output JSON only once after loop
            echo json_encode([
                'status' => 'success',
                'approved' => array_values($approvedData),
                'pending'  => array_values($pendingData),
                'rejected' => array_values($rejectedData)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Year is required']);
        }
    }
    
    if (!empty($_POST['action']) && $_POST['action'] == 'get_summary_overview') {
        header('Content-Type: application/json');
    
        $year = isset($_POST['year']) ? $_POST['year'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : 'Procurement'; // Default to 'Procurement' if not set
    
        if ($year) {
            $summaryData = $dashboard_management->getTotalCountPerStatus($year, $section);
            echo json_encode(
                $summaryData
            );
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Year is required']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'sectionapprove_request') {
        header('Content-Type: application/json');

        $id = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;

        $data = [
            'control_number' => $id,
            'item_remarks' => $remarks,
            'requestor_status' => $status
        ];

        $result = $request_management->UpdateRequestStatus($data);
        $resultLogs = $request_management->CreateRequestLogs($data);
        if ($result && $resultLogs) {
            echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'sectiondecline_request') {
        header('Content-Type: application/json');

        $id = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;

        $data = [
            'control_number' => $id,
            'item_remarks' => $remarks,
            'requestor_status' => $status
        ];

        $result = $request_management->UpdateRequestStatus($data);
        $resultLogs = $request_management->CreateRequestLogs($data);
        if ($result && $resultLogs) {
            echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'verify_item') {
        header('Content-Type: application/json');

        $id = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : null;

        $data = [
            'control_number' => $id,
            'item_remarks' => $remarks,
            'requestor_status' => $status
        ];

        $subject = "Request for Quotation - {$id}";
        $body = "<p>Dear {$section} Team,</p>
                <p>The Procurement Department has verified the request from the {$section} department (Control No: <strong>{$id}</strong>).</p><br>
                <p>We're now proceeding with the quotation process.</p>
                <p>Best regards,</p>
                <p>Procurement Department</p>";

        $result = $request_management->UpdateRequestStatus($data);
        $resultLogs = $request_management->CreateRequestLogs($data);
        if ($result && $resultLogs) {
            $emailupdate = $request_management->SendEmailStatusUpdate($section, $subject, $body);
            echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'hold_item') {
        header('Content-Type: application/json');

        $id = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : null;

        $data = [
            'control_number' => $id,
            'item_remarks' => $remarks,
            'requestor_status' => $status
        ];

        $subject = "Request for Quotation - {$id}";
        $body = "<p>Dear {$section} Team,</p>
                <p>The request of {$section} department with control number <strong>{$id}</strong> has been put on hold.</p>
                <p>Remarks: {$remarks}</p>
                <p>Please take the necessary actions.</p>
                <p>Best regards,</p>
                <p>Nidec Instruments Philippines Corporation</p>";

       
        $result = $request_management->UpdateRequestStatus($data);
        $resultLogs = $request_management->CreateRequestLogs($data);

        if ($result && $resultLogs) {
            $emailupdate = $request_management->SendEmailStatusUpdate($section, $subject, $body);
            echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
        }
    }

    if(!empty($_POST['action']) && $_POST['action'] == 'send_email_to_supplier'){
        header('Content-Type: application/json');
        $recipients = $_POST['recipients'] ?? [];
        $ccs = $_POST['ccs'] ?? [];
        $bccs = $_POST['bccs'] ?? [];
        $controlNumber = $_POST['control_number'] ?? 'N/A';
        $section = $_POST['section'] ?? 'Procurement';

        $itemDetails = $request_management->fetchRequestById($controlNumber);

        $tableHtml = "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                    <thead style='background-color: #333; color: #fff;'>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>UOM</th>
                    </tr>
                    </thead>
                    <tbody>";
        foreach($itemDetails as $row){
                    $tableHtml .= "
                    <tr></tr>
                        <td>{$row['item_name']}</td>
                        <td>{$row['item_description']}</td>
                        <td>{$row['item_quantity']}</td>
                        <td>{$row['item_uom']}</td>
                    ";
                }
                $tableHtml .= "</tbody>";
                $tableHtml .= "</table>";

                $subject = "Request for Quotation";
                $body = "<p>Dear Supplier,</p>
                    <p>I hope this email finds you well. We would like to request a quotation for the following items:</p>" 
                    . $tableHtml . 
                    "<p>Please provide us with the following details in your quotation:</p>
                    <ul>
                        <li>Unit price and total price</li>
                        <li>Payment terms</li>
                        <li>Lead time and delivery schedule</li>
                        <li>Availability of stock</li>
                        <li>Warranty (if applicable)</li>
                    </ul>
                    <p>Should you require any further information, please feel free to reach out.</p>
                    
                    <p>Looking forward to your prompt response.<br>
                        Kindly see the attached file for reference.</p>

                    <p>Best regards,</p>
                    <p>Nidec Instruments Philippines Corporation</p>

                    <p><strong>Note:</strong> This is an auto-generated email. Please do not reply directly to this email. Kindly send your response to <a href='mailto:regine.guellena@nidec.com'>regine.guellena@nidec.com</a>.</p>";

        $mailSent = $request_management->SendEmailNotification($recipients, $ccs, $bccs, $subject, $body, $section, $controlNumber);
        if ($mailSent) {
            echo json_encode(['status' => 'success', 'message' => 'Email sent successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send email']);
        } 
    }

    //Fetch Items
    if (!empty($_POST['action']) && $_POST['action'] == 'get_items_for_comparison') {
        header('Content-Type: application/json');
    
        $control_number = $_POST['control_number'] ?? null;

    
        // Fetch paginated result
        $result = $request_management->fetchRequestById($control_number);
    
    
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
                    'item_remarks' => $row['item_remarks'],
                    'created_at' => $row['created_at']
                ];
            }
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No data found']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'create_comparison') {
        header('Content-Type: application/json');

        $success_count = 0;
        $error_count = 0;

        $control_number = $_POST['control_number'] ?? null;
        $item_names = $_POST['item_name'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
        $supplier_names = $_POST['supplier_name'] ?? [];
        $supplier_prices = $_POST['item_price'] ?? [];
        $supplier_discounts = $_POST['item_discount'] ?? [];
        $total_prices = $_POST['item_total'] ?? [];
        $section = $_POST['section'] ?? null;

        if (empty($control_number)) {
            echo json_encode(['status' => 'error', 'message' => 'Control number is required']);
            exit;
        }

        foreach ($item_names as $key => $item_name) {
            $quantity = $quantities[$key] ?? null;

            $itemSuppliers = $supplier_names[$key] ?? [];
            $itemPrices = $supplier_prices[$key] ?? [];
            $itemDiscounts = $supplier_discounts[$key] ?? [];
            $itemTotals = $total_prices[$key] ?? [];

            foreach ($itemSuppliers as $index => $supplier_name) {
                $data = [
                    'control_number'   => $control_number,
                    'item_name'        => $item_name,
                    'supplier_name'    => $supplier_name ?? null,
                    'item_quantity'    => $quantity,
                    'supplier_price'       => $itemPrices[$index] ?? null,
                    'supplier_discount'    => $itemDiscounts[$index] ?? null,
                    'total_price'       => $itemTotals[$index] ?? null
                ];

                $response = $request_management->CreateComparison($data);

                if ($response) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        $data = [
                    'control_number' => $control_number,
                    'item_remarks' => 'Comparison created',
                    'requestor_status' => 'Pending'
                ];

        $subject = "Request for Quotation - {$control_number}";
        $body = "<p>Dear {$section} Team,</p>
                <p>The comparison for the <strong>{$section}</strong> department with control number <strong>{$control_number}</strong> has been successfully created.</p>
                <p>Remarks: Comparison created</p>
                <p>Please review the comparison details and take the necessary actions.</p>
                <p>Best regards,</p>
                <p>Nidec Instruments Philippines Corporation</p>";


        $result = $request_management->UpdateRequestStatus($data);
        $resultLogs = $request_management->CreateRequestLogs($data);

        if ($success_count > 0) {
            $emailupdate = $request_management->SendEmailStatusUpdate($section, $subject, $body);
            echo json_encode(['status' => 'success', 'message' => 'Comparison created successfully']);
        } elseif ($error_count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create comparison: Duplicate entries or missing data']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No data processed']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'get_comparison_items') {
        header('Content-Type: application/json');

        $control_number = $_POST['control_number'] ?? null;

        if (empty($control_number)) {
            echo json_encode(['status' => 'error', 'message' => 'Control number is required']);
            exit;
        }

        $result = $request_management->fetchComparisonByControlNumber($control_number);

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

    if (!empty($_POST['action']) && $_POST['action'] == 'delete_comparison') {
        header('Content-Type: application/json');

        $id = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $result = $request_management->deleteComparison($id);
        $data = [
                    'control_number' => $id,
                    'item_remarks' => 'Comparison deleted',
                    'requestor_status' => 'Pending'
                ];

        $resultLogs = $request_management->CreateRequestLogs($data);

        if ($result && $resultLogs) {
            echo json_encode(['status' => 'success', 'message' => 'Comparison item deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete comparison item']);
        }
    }
    
    if (!empty($_POST['action']) && $_POST['action'] == 'approve_comparison') {
        header('Content-Type: application/json');

        $control_number = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : null;

        if (empty($control_number)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            exit;
        }

        $data = [
            'control_number' => $control_number,
            'requestor_status' => $status,
            'item_remarks' => 'Comparison approved'
        ];

        $resultLogs = $request_management->CreateRequestLogs($data);
        $result = $request_management->UpdateRequestStatus($data);
        $comparisonResult = $request_management->updateComparisonRemarks($data);

        $subject = "Request for Quotation - {$control_number}";
        $body = "<p>Dear Procurement Team,</p>
                <p>The comparison for the <strong>{$section}</strong> department with control number <strong>{$control_number}</strong> has been approved by the requestor.</p>
                <p>Remarks: Approved</p>
                <p>Please proceed with the next steps in the procurement process.</p>
                <p>Best regards,</p>
                <p>Nidec Instruments Philippines Corporation</p>";

        if ($result && $resultLogs && $comparisonResult) {
            $emailupdate = $request_management->SendEmailStatusUpdate($section, $subject, $body);
            echo json_encode(['status' => 'success', 'data' => $result]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'hold_comparison') {
        header('Content-Type: application/json');
        $control_number = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : null;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;
        $data = [
            'control_number' => $control_number,
            'requestor_status' => $status,
            'item_remarks' => $remarks
        ];
        if (empty($control_number)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            exit;
        }
        $resultLogs = $request_management->CreateRequestLogs($data);
        $result = $request_management->UpdateRequestStatus($data);
        $comparisonResult = $request_management->updateComparisonRemarks($data);
        $subject = "Request for Quotation - {$control_number}";
        $body = "<p>Dear Procurement Team,</p>
                <p>The comparison for the <strong>{$section}</strong> department with control number <strong>{$control_number}</strong> has been declined by the requestor.</p>
                <p>Remarks: {$remarks}</p>
                <p>Please review the comparison details and take the necessary actions.</p>
                <p>Best regards,</p>
                <p>Nidec Instruments Philippines Corporation</p>";
        if ($result && $resultLogs && $comparisonResult) {
            $emailupdate = $request_management->SendEmailStatusUpdate($section, $subject, $body);
            echo json_encode(['status' => 'success', 'data' => $result]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'getsuggestion') {
        header('Content-Type: application/json');
        $query = isset($_POST['query']) ? $_POST['query'] : null;

        $result = $request_management->FetchControlNumber($query);

        echo json_encode([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => $result['data']
        ]);
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'LoadItems') {
        header('Content-Type: application/json');
        $control_number = isset($_POST['control_number']) ? $_POST['control_number'] : null;

        $result = $request_management->fetchRequestById($control_number);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Data fetch successfully.',
                'data' => $result
            ]);
        }else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retreive data.',
            ]);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'save_delivery') {

        $control_number = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $item_name = isset($_POST['item_name']) ? $_POST['item_name'] : null;
        $item_description = isset($_POST['item_description']) ? $_POST['item_description'] : null;
        $item_quantity = isset($_POST['item_quantity']) ? $_POST['item_quantity'] : null;
        $supplier = isset($_POST['supplier_name']) ? $_POST['supplier_name'] : null;
        $total_amount = isset($_POST['amount']) ? $_POST['amount'] : null;
        $delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : null;
        $status = 'Pending';
        $remarks = '';

        $data = [];
        $success_count = 0;
        $error_count = 0;

        foreach($item_name as $key => $item_names){
            $data = [
                'control_number' => $control_number,
                'item_name' => $item_names,
                'item_description' => $item_description[$key] ?? null,
                'item_quantity' => $item_quantity[$key] ?? null,
                'supplier_name' => $supplier[$key] ?? null,
                'item_amount' => $total_amount[$key] ?? null,
                'delivery_date' => $delivery_date[$key] ?? null,
                'item_status' => $status ?? null,
                'item_remarks' => $remarks[$key] ?? null
            ];

            $result = $request_management->InsertDeliveryDetails($data);

            if ($result['success']) {
                $success_count++;
            }else{
                $error_count++;
            }
        }

        $dataLogs = [
                    'control_number' => $control_number,
                    'requestor_status' => 'Completed',
                    'item_remarks' => 'For Delivery.'
                ];

        $request_management->CreateRequestLogs($dataLogs);

        if ($success_count > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => $result['message']
            ]);
        }else{
            echo json_encode([
                'status' => 'error',
                'message' => $result['message']
            ]);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'LoadTable') {
        header('Content-Type: application/json');
        $search_query = isset($_POST['search_query']) ? $_POST['search_query'] : null;
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $perpage = 10;

        $result = $request_management->FetchDeliveryData($search_query, $page, $perpage);
        $resultcount = $request_management->CountDeliveryData($search_query);
        // $resultcount = count($result);
        $data = [];

        if (!empty($result)) {
            foreach($result as $row){
                $data [] = [
                    'id' => $row['id'],
                    'control_number' => $row['control_number'],
                    'item_name' => $row['item_name'],
                    'item_quantity' => $row['item_quantity'],
                    'supplier_name' => $row['supplier_name'],
                    'item_amount' => $row['item_amount'],
                    'item_status' => $row['item_status'],
                    'item_remarks' => $row['item_remarks'],
                    'delivery_date' => $row['delivery_date'],
                    'received_date' => $row['received_date']
                ];
            }
            echo json_encode([
                'status' => 'success',
                'message' => 'Data successfully fetch.',
                'data' => $data,
                'total' => $resultcount,
                'currentPage' => $page,
                'perPage' => $perpage
            ]);

        }else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve data.'
            ]);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'received') {
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $control_number = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $date = date('Y-m-d H:i:s');
        $status = 'Completed';

        $result = $request_management->DeliveryReceivedUpdate($id, $date, $status);

        // $dataLogs = [
        //             'control_number' => $control_number,
        //             'requestor_status' => 'Completed',
        //             'item_remarks' => 'Delivered.'
        //         ];

        // $request_management->CreateRequestLogs($dataLogs);

            echo json_encode([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message']
            ]);
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'delete') {
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? $_POST['id'] : null;
        // $date = date('Y-m-d H:i:s');
        // $status = 'Completed';

        $result = $request_management->DeleteDeliveryItem($id);


        echo json_encode([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message']
            ]);

    }

    if (!empty($_POST['action'] && $_POST['action'] == 'editdelivery')) {
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $supplier_name = isset($_POST['supplier_name']) ? $_POST['supplier_name'] : null;
        $item_amount = isset($_POST['item_amount']) ? $_POST['item_amount'] : null;
        $delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : null;


        $data = [];

        $data = [
            'id' => $id,
            'supplier_name' => $supplier_name,
            'item_amount' => $item_amount,
            'delivery_date' => $delivery_date
        ];

        $result = $request_management->UpdateDeliveryDetails($data);

        echo json_encode([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'] . $data['delivery_date']
        ]);

    }

    if (!empty($_POST['action']) && $_POST['action'] == 'addremarks') {
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;

        $data = [];

        $data = [
            'id' => $id,
            'item_remarks' => $remarks
        ];

        $result = $request_management->AddRemarks($data);

        echo json_encode([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ]);
    }

    if(!empty($_POST['action']) && $_POST['action'] == 'gettimeline'){
        header('Content-Type: application/json');

        $id = isset($_POST['control_number']) ? $_POST['control_number'] : null;

        $response = $request_management->GetTimeline($id);

        echo json_encode([
            'status' => $response['success'] ? 'success' : 'error',
            'message' => $response['message'],
            'logs' => $response['logs']
        ]);
    }
?>  