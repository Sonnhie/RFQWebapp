<?php
    ob_start();
    session_start();
    require __DIR__ . '/../../vendor/autoload.php';


    use Database\dbconnection;
    use App\Controller\request;
    use App\Controller\dashboard_management;
    use App\Controller\emailnotification_management;
    use App\Controller\logs;
    use App\Controller\user_management;

    $database = new dbconnection();
    $db = $database->getConnection();
    $request = new request($db);
    $logs = new logs($db);
    $autoemail = new emailnotification_management($db);
    $dashboard_management = new dashboard_management($db);
    $user_management = new user_management($db);


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        if (!empty($_POST['action']) && $_POST['action'] == 'create_request') {
            header('Content-Type: application/json');

            $responses = [];
            $success_count = 0;
            $error_count = 0;

            // Variables
            $control_number = $request->createRFQNumber();
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
            $maxFileSize = 400 * 1024 * 1024; // 2MB in bytes
            $fileSize = $_FILES['item-attachment']['size'][0] ?? 0;
            $fileContents = [];
            foreach ($_FILES['item-attachment']['tmp_name'] as $key => $tmpName) {
                if ($fileSize > $maxFileSize) {
                    echo json_encode(['status' => 'error', 'message' => 'File size is ' . $fileSize . ' exceeds 40MB']);
                    exit;
                }
                if ($_FILES['item-attachment']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileContents[$key] = file_get_contents($tmpName);

                } else {
                    $fileContents[$key] = null;
                }
            }

            // Create initial logs
            $logs->CreateRequestLogs([
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

                $request->UploadAttachment($data);
                $response = $request->CreateNewRequest($data);

                if (isset($response['success']) && $response['success']) {
                    $success_count++;
                } else {
                    $error_count++;
                }

                $responses[] = $response['message'] ?? 'Unknown result';
            }

            // Send back summary response
            if ($success_count > 0 && $error_count === 0) {
                // Create a new request log entry for the successful submission
                $notification = [
                    'control_number' => $control_number,
                    'message' => "New request created by " . $requestor_section . " with Control Number: " . $control_number,
                    'section' => $requestor_section,
                ];

                // $result = $request_management->InsertNotificationMessage($notification);

                // // Notify WebSocket clients about the new request
                // $notifier->send(
                //     'broadcast',
                //     [
                //         'control_number' => $control_number,
                //         'item_requestor' => $requestor_name,
                //         'item_section' => $requestor_section,
                //         'date' => date('Y-m-d H:i:s'),
                //         'message' => "New request created with Control Number: $control_number"
                //     ],
                //     $requestor_section // Target section for the notification
                // );

                echo json_encode(['status' => 'success', 'message' => "All $success_count items submitted successfully."]);
            } elseif ($success_count > 0 && $error_count > 0) {
                echo json_encode(['status' => 'partial', 'message' => "$success_count succeeded, $error_count failed.", 'details' => $responses]);
            } else {
                echo json_encode(['status' => 'error', 'message' => "Failed to submit all items.", 'details' => $responses]);
            }            
        }

        if (!empty($_POST['action']) && $_POST['action'] == 'get_items') {
            header('Content-Type: application/json');
            
            //Page setup
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $perPage = 10;
            
            // Filters
            $filter = $_POST['filters'] ?? null;
            $filters = [
                    'section' => $_POST['section'] ?? null,
                    'from' => $filter['from'] ?? null,
                    'to' => $filter['to'] ?? null,
                    'status' => $filter['status'] ?? null,
                    'search' => $filter['search'] ?? null,

            ];

            // Fetch paginated result
            $result = $request->fetchItemBySectionRequests($filters, $page, $perPage);
        
            // Optional: Fetch total count for pagination
            $totalResult = $request->CountAllRequests($filters);
        
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

        if (!empty($_POST['action']) && $_POST['action'] == 'get_item_details') {
            header('Content-Type: application/json');
        
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $filePath = $request->getAttachment($id); // this already returns the file path
        
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

        if (!empty($_POST['action']) && $_POST['action'] == 'edit_request') {
            header('Content-Type: application/json');

            $id = isset($_POST['item_id']) ? $_POST['item_id'] : null;
            $item_name = isset($_POST['item_name']) ? $_POST['item_name'] : null;
            $item_description = isset($_POST['item_description']) ? $_POST['item_description'] : null;
            $item_purpose = isset($_POST['item_purpose']) ? $_POST['item_purpose'] : null;
            $item_quantity = isset($_POST['item_quantity']) ? $_POST['item_quantity'] : null;
            $item_unit = isset($_POST['item_unit']) ? $_POST['item_unit'] : null;
            $item_purpose = isset($_POST['item_purpose']) ? $_POST['item_purpose'] : null;

            $maxFileSize = 2 * 1024 * 1024; // 2MB in bytes

            if (isset($_FILES['item_attachment']) && $_FILES['item_attachment']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['item_attachment']['size'] > $maxFileSize) {
                    echo json_encode(['status' => 'error', 'message' => 'File size exceeds 40MB']);
                    exit;
                }

                $fileContents = file_get_contents($_FILES['item_attachment']['tmp_name']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File upload error']);
                exit;
            }


            $data = [
                'id' => $id,
                'item_name' => $item_name,
                'item_description' => $item_description,
                'item_quantity' => $item_quantity,
                'item_unit' => $item_unit,
                'item_purpose' => $item_purpose,
                'item_attachment' => $fileContents ? $fileContents : null
            ];

            $request->updateAttachment($data);
            $result = $request->editItems($data);

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
            }
            
        }

        if(!empty($_POST['action']) && $_POST['action'] == 'delete_item'){
            header('Content-Type: application/json');
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $result = $request->deleteRequest($id);
            $deleteAttachment = $request->deleteAttachment($id);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Request deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete request']);
            }
        }

        if (!empty($_POST['action']) && $_POST['action'] == 'get_itemsbycontrolnumber') {
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
                'search' => $filter['search'] ?? null,
                'remarks' => $filter['remarks'] ?? null
            ];
            
            if ($filters['remarks'] == 'For Procurement Verification') {
               $result = $request->fetchVerificationRequestsByControlNumber($filters, $page, $perPage);
               $totalResult = $request->countVerificationRequestsByControlNumber($filters, $page, $perPage);
            }else{
                // Fetch paginated result
                $result = $request->fetchAllRequestsByControlNumber($filters, $page, $perPage);
            
                // Optional: Fetch total count for pagination
                $totalResult = $request->countAllRequestsByControlNumber($filters);
                // $totalCount = count($result);
            }

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

        if (!empty($_POST['action']) && $_POST['action'] == 'get_single_items') {
            header('Content-Type: application/json');
        
            $control_number = $_POST['control_number'] ?? null;
        
            // Fetch paginated result
            $result = $request->fetchRequestById($control_number);
        
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

        if (!empty($_POST['action']) && $_POST['action'] == 'get_sectionapproval') {
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
                'search' => $filter['search'] ?? null,
                'role' => $filter['role'] ?? null
            ];
        
            // Fetch paginated result
            $result = $request->fetchForSectionApprovals($filters, $page, $perPage);
        
            // Optional: Fetch total count for pagination
            $totalResult = $request->fetchForSectionApprovalsCount($filters);
            // $totalCount = count($result);
        
            $data = [];
        
            if ($result) {

                foreach ($result as $row) { 
                    $data[] = [
                        // 'id' => $row['id'],
                        'control_number' => $row['control_number'],
                        // 'item_name' => $row['item_name'],
                        // 'item_description' => $row['item_description'],
                        // 'item_quantity' => $row['item_quantity'],
                        // 'item_unit' => $row['item_uom'],
                        // 'item_purpose' => $row['item_purpose'],
                        'requestor_section' => $row['item_section'],
                        // 'requestor_name' => $row['item_requestor'],
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

        if (!empty($_POST['action']) && $_POST['action'] == 'update_request') {
            header('Content-Type: application/json');

            $section = $_POST['section'] ?? null;
            $main = $_POST['main'] ?? null;

            $data = [
                'control_number'   => $_POST['control_number'] ?? null,
                'item_remarks'     => $_POST['remarks'] ?? null,
                'requestor_status' => $_POST['status'] ?? null,
                'section'          => $section
            ];

            $emailData = [
                'address_section' => $main,
                'bcc_Section' => $section
            ];
            
            
            // $subject = "Request for Quotation - {$data['control_number']}";
            $subject = "tEST EMAIL";
            $message = "<p>Dear {$main} Team,</p>
                    <p>This is to inform you that the request for the <strong>{$main}</strong> department with control number <strong>{$data['control_number']}</strong> has been updated.</p>
                    <p>Current Status: <strong>{$data['requestor_status']}</strong></p>
                    <p>Remarks: {$data['item_remarks']}</p>
                    <p>Please review the details and take the necessary actions if required.</p>
                    <p>Best regards,</p>
                    <p>Nidec Instruments Philippines Corporation</p>";
       

            $result = $request->UpdateRequestStatus($data);
            $resultLogs = $logs->CreateRequestLogs($data);
            if ($result && $resultLogs) {
                $emailnotif = $autoemail->SendEmailupdateStatus($emailData, $message, $subject);
                echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
            }
        }

        if (!empty($_POST['action']) && $_POST['action'] == 'getData') {
            header('Content-Type: application/json');

            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

            $perPage = $_POST['limit'] ?? 10;
            $section = $_POST['section'] ?? null;
            $filters = $_POST['filters'] ?? null;
            $filterData = [
                'dateFrom' => $filters['$dateFrom'] ?? null,
                'dateTo' => $filters['$dateTo'] ?? null,
                'searchValue' => $filters['$searchValue'] ?? null
            ];

            //Count total request
            $totalRequest = $request->totalVerificationRequest($filterData);
            //Get data
            $getData = $request->getVerifiedData($filterData, $page, $perPage);

            $data = [];

            foreach($getData as $row){
                $data[] = [
                    'control_number' => $row['control_number'] ?? null,
                    'item_status' => $row['item_status'] ?? null,
                    'item_remarks' => $row['item_remarks'] ?? null,
                    'item_section' => $row['item_section'] ?? null,
                    'created_at' => $row['created_at'] ?? null
                ];
            }

            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'total' => $totalRequest,
                'currentPage' => $page,
                'perPage' => $perPage
            ]);
        }

        if(!empty($_POST['action']) && $_POST['action'] == 'send_email_to_supplier'){
            header('Content-Type: application/json');
            $recipients = $_POST['recipients'] ?? [];
            $ccs = $_POST['ccs'] ?? [];
            $bccs = $_POST['bccs'] ?? [];
            $controlNumber = $_POST['control_number'] ?? 'N/A';
            $section = $_POST['section'] ?? 'Procurement';

            $itemDetails = $request->fetchRequestById($controlNumber);

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

            $mailSent = $autoemail->SendEmailNotification($recipients, $ccs, $bccs, $subject, $body, $section, $controlNumber);
            if ($mailSent) {
                echo json_encode(['status' => 'success', 'message' => 'Email sent successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send email']);
            } 
        }

        if (!empty($_POST['action']) && $_POST['action'] == 'get_items_for_comparison') {
            header('Content-Type: application/json');
        
            $control_number = $_POST['control_number'] ?? null;

        
            // Fetch paginated result
            $result = $request->fetchRequestById($control_number);
        
        
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
            $bcc = $_POST['bccsection'] ?? null;
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
                        'control_number'        => $control_number,
                        'item_name'             => $item_name,
                        'supplier_name'         => $supplier_name ?? null,
                        'item_quantity'         => $quantity,
                        'supplier_price'        => $itemPrices[$index] ?? null,
                        'supplier_discount'     => $itemDiscounts[$index] ?? null,
                        'total_price'           => $itemTotals[$index] ?? null
                    ];

                    $response = $request->CreateComparison($data);

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
            $emailData = [
                'address_section' => $section,
                'bcc_Section' => $bcc
            ];

            

            $result = $request->UpdateRequestStatus($data);
            $resultLogs = $logs->CreateRequestLogs($data);
            
            if ($success_count > 0) {
                $emailnotif = $autoemail->SendEmailupdateStatus($emailData, $body, $subject);
                echo json_encode(['status' => 'success' , 'message' => 'Comparison created successfully']);
            } elseif ($error_count > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create comparison: Duplicate entries or missing data']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No data processed']);
            }
        }

        if (!empty($_POST['action']) && $_POST['action'] == 'getcomparisonData') {
            header('Content-Type: application/json');

            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

            $perPage = $_POST['limit'] ?? 10;
            $section = $_POST['section'] ?? null;
            $filters = $_POST['filters'] ?? null;
            
            $filterData = [
                'dateFrom' => $filters['dateFrom'] ?? null,
                'dateTo' => $filters['dateTo'] ?? null,
                'role' => $filters['role'] ?? null,
                'searchValue' => $filters['searchValue'] ?? null
            ];

            //Count total request
            $totalRequest = $request->countComparison($filterData, $section);
            //Get data
            $getData = $request->getComparison($filterData, $section, $page, $perPage);

            $data = [];

            foreach($getData as $row){
                $data[] = [
                    'control_number' => $row['control_number'] ?? null,
                    'item_status' => $row['item_status'] ?? null,
                    'item_remarks' => $row['item_remarks'] ?? null,
                    'item_section' => $row['item_section'] ?? null,
                    'created_at' => $row['created_at'] ?? null
                ];
            }

            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'total' => $totalRequest,
                'currentPage' => $page,
                'perPage' => $perPage
            ]);
        }

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

        if (!empty($_POST['action']) && $_POST['action'] == 'Edit_Comparison') {
            header('Content-Type: application/json');

            $supplier_ids = $_POST['supplier_id'] ?? null;
            $supplier_names = $_POST['supplier_name'] ?? null;
            $item_prices = $_POST['item_price'] ?? null;
            $item_discounts = $_POST['item_discount'] ?? null;
            $item_totals = $_POST['item_total'] ?? null;
            $section = $_POST['section'] ?? null;
            $main = $_POST['main'] ?? null;
            $control_number = $_POST['control_number'] ?? null;
            if (!isset($supplier_ids) || !is_array($supplier_ids) || count(array_filter($supplier_ids)) === 0) {
                echo json_encode(['status'=> 'error','message'=> 'empty id']);
                exit;
            }


            $responses = [];
            $success_count = 0;
            $error_count = 0;
            $message = '';

            try {
                foreach ($supplier_ids as $index => $supplier_id) {
                    $data = [
                        'id' => $supplier_id,
                        'supplier_name' => $supplier_names[$index] ?? '',
                        'supplier_price' => $item_prices[$index] ?? 0,
                        'supplier_discount' => $item_discounts[$index] ?? 0,
                        'total' => $item_totals[$index] ?? 0
                    ];

                    $result = $request->UpdateComparison($data);

                    if ($result['success']) {
                        $success_count++;
                        $message = $result['message'];
                    } else {
                        $error_count++;
                        $message = $result['message'];
                    }
                }

               $emailData = [
                'address_section' => $main,
                'bcc_Section' => $section
                ];             
                // $subject = "Request for Quotation - {$control_number}";
                $subject = "tEST EMAIL";
                $message = "<p>Dear {$main} Team,</p>
                        <p>This is to inform you that the request for the <strong>{$main}</strong> department with control number <strong>{$control_number}</strong> has been updated.</p>
                        <p>Current Status: <strong>Completed</strong></p>
                        <p>Remarks: Update Comparison</p>
                        <p>Please review the details and take the necessary actions if required.</p>
                        <p>Best regards,</p>
                        <p>Nidec Instruments Philippines Corporation</p>";
            
                if ($success_count > 0) {
                     $emailnotif = $autoemail->SendEmailupdateStatus($emailData, $message, $subject);
                    echo json_encode([
                        'status' => 'success',
                        'message' => $message
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }

        if (!empty($_POST['action']) && $_POST['action'] == 'get_chart_data') {
            header('Content-Type: application/json');
            
            $data = $_POST['data'] ?? null;
            $year = $data['year'] ?? null;
            $section = isset($data['section']) ? $data['section'] : 'Procurement'; // Default to 'Procurement' if not set

            $data = [
                'current_year'=> $year,
                'section'=> $section,
                'role'=> $data['role'] ?? null
            ];
        
            if (!empty($year)) {
                $chartData = $dashboard_management->getChartData($data);
                $dataChart = $chartData['data'];


                // Initialize months with zeroes (1-based index)
                $approvedData = array_fill(1, 12, 0);
                $pendingData  = array_fill(1, 12, 0);
                $rejectedData = array_fill(1, 12, 0);

                foreach ($dataChart as $row) {
                    $month = (int)$row['month'];

                    // ✅ Accumulate counts instead of overwriting
                    $approvedData[$month] += (int)$row['Completed'];
                    $pendingData[$month]  += (int)$row['Pending'];
                    $rejectedData[$month] += (int)$row['Hold'];
                }

                // ✅ Output JSON only once after loop
                echo json_encode([
                    'status' => 'success',
                    'Completed' => array_values($approvedData),
                    'Pending'  => array_values($pendingData),
                    'Rejected' => array_values($rejectedData)
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Year is required']);
            }
        }

        if (!empty($_POST['action']) && $_POST['action'] == 'get_piechart_data') {
            header('Content-Type: application/json');
            
            $data = $_POST['data'] ?? null;
            $year = $data['year'] ?? null;
            $section = isset($data['section']) ? $data['section'] : 'Procurement'; // Default to 'Procurement' if not set

            $data = [
                'current_year'=> $year,
                'section'=> $section,
                'role'=> $data['role'] ?? null
            ];
        
            if (!empty($year)) {
                $chartData = $dashboard_management->getPieChartData($data);


                // ✅ Output JSON only once after loop
                echo json_encode([
                    'status' => 'success',
                    'data' => $chartData
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Year is required']);
            }
        }

        if (!empty($_POST['action']) && $_POST['action'] == 'get_latest_request') {
            header('Content-Type: application/json');

            $section = $_POST['section'] ?? null;

            $result = $request->getRecentRequest($section);

            $data = [];

            if ($result) {
                foreach($result as $row){
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
                    'data' => $data
                ]);
            } else {
                echo json_encode(['status'=> 'error', 'message'=> 'No data Found']);
            }
        }
    }


    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    }
?>