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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Accounting;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
$database = new dbconnection();
$db = $database->getConnection();
$request = new request($db);
$logs = new logs($db);
$autoemail = new emailnotification_management($db);
$dashboard_management = new dashboard_management($db);
$user_management = new user_management($db);

$dotenv->load();

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
        $gencontrol_number = $request->createRFQNumber();

        $control_number = $gencontrol_number;
        $item_name = $_POST['item_name'] ?? null;
        $item_description = $_POST['item_description'] ?? null;
        $item_quantity = $_POST['item_quantity'] ?? null;
        $item_unit = $_POST['item_unit'] ?? null;
        $item_purpose = $_POST['item_purpose'] ?? null;
        $requestor_section = $_POST['requestor_section'] ?? null;
        $requestor_name = $_POST['requestor_name'] ?? null;
        $item_remarks = $_POST['remarks'] ?? null;
        $requestor_status = 'On-going';
        $item_attachment = $_FILES['item-attachment'] ?? null;

        // Validate
        if (empty($item_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Item name is required']);
            exit;
        }

        if (empty($_FILES['item-attachment']['tmp_name']) || count($_FILES['item-attachment']['tmp_name']) === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'File upload is required'
            ]);
            exit;
        }
        // $uploadDir = __DIR__ . "/../../" . "/Uploads/Items"; // absolute path
        $uploadDir = 'D:/Uploads/Attachments';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $maxFileSize = 40 * 1024 * 1024; // 40MB
        $filePaths = [];

        foreach ($_FILES['item-attachment']['tmp_name'] as $key => $tmpName) {
            $fileSize = $_FILES['item-attachment']['size'][$key] ?? 0;

            if ($fileSize > $maxFileSize) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "File {$key} size exceeds 40MB"
                ]);
                exit;
            }

            if ($_FILES['item-attachment']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['item-attachment']['name'][$key], PATHINFO_EXTENSION);
                $uniqueName = uniqid("file_") . "." . $ext;
                $filePath = $uploadDir . "/" . $uniqueName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    // store relative path for DB
                    $filePaths[$key] = $filePath;
                }
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
                'control_number'   => $control_number,
                'item_name'        => $name ?? null,
                'item_description' => $item_description[$key] ?? null,
                'item_quantity'    => $item_quantity[$key] ?? null,
                'item_unit'        => $item_unit[$key] ?? null,
                'item_purpose'     => $item_purpose[$key] ?? null,
                'requestor_section' => $requestor_section ?? null,
                'requestor_status' => $requestor_status,
                'item_remarks'     => $item_remarks,
                'requestor_name'   => $requestor_name,
                'path_file'        => $filePaths[$key] ?? null  // ✅ correct reference
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

        $emailData = [
            'address_section' => $requestor_section,
            'bcc_Section' => 'Procurement'
        ];

        $status = $statusdata['requestor_status'] ?? 'On-going';

        // Status badge styles
        $statusStyles = [
            "Completed"  => "background:#28a745; color:#fff;",   // Green
            "On-going"   => "background:#f0ad4e; color:#fff;",   // Orange
            "Cancelled" => "background:#d9534f; color:#fff;",   // Red
            "Hold"      => "background:#ffc107; color:#212529;"
        ];

        $statusBadgeStyle = $statusStyles[$status] ?? "background:#6c757d; color:#fff;"; // Default gray

        // Company logo (replace with your actual hosted logo path)
        $companyLogo = __DIR__ . '/../../' . 'img/logo.png';
        $subject = "Request for Quotation - {$control_number}";
        // $subject = "tEST EMAI";
        $message = "
        <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#f4f6f9; padding:20px; font-family: Arial, sans-serif;'>
            <tr>
                <td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' border='0' style='background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>
                        
                        <!-- Header with Logo -->
                        <tr>
                            <td style='background:#003366; color:#ffffff; padding:20px 30px;'>
                                <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                    <tr>
                                        <td align='left'>
                                            <img src='https://logovectorseek.com/wp-content/uploads/2019/11/nidec-corporation-logo-vector.png' alt='Company Logo' style='height:40px; width:40px;'>
                                        </td>
                                        <td align='right' style='color:#ffffff; font-size:18px; font-weight:bold;'>
                                            New Request for Quotation Created
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style='padding:30px; color:#333333; font-size:15px; line-height:1.6;'>
                                <p>Dear <strong>{$emailData['address_section']} Team</strong>,</p>

                                <p>This is to inform you that a <strong>new Request for Quotation (RFQ)</strong> has been created by 
                                <strong>{$requestor_name}</strong>. Below are the details of the new request:</p>

                                <div style='background:#f1f5f9; border-left:4px solid #003366; padding:12px 18px; margin:18px 0; font-size:15px; font-weight:bold; color:#1a1a1a;'>
                                    Control Number: {$control_number}
                                </div>

                                <p>Current Status:</p>
                                <p style='margin:15px 0;'>
                                    <span style='display:inline-block; {$statusBadgeStyle} padding:8px 16px; border-radius:4px; font-weight:bold; font-size:14px;'>
                                        {$requestor_status}
                                    </span>
                                </p>

                                <p><strong>Remarks:</strong></p>
                                <div style='margin:18px 0; padding:15px; background:#fafafa; border:1px solid #e0e0e0; border-radius:4px; color:#555;'>
                                    {$item_remarks}
                                </div>

                                <p>Please review the request details in the RFQ System and take the appropriate next steps.</p>

                                <p style='margin-top:25px;'>Best regards,<br>
                                <strong>Nidec Instruments Philippines Corporation</strong></p>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style='background:#f8f9fa; text-align:center; padding:15px; font-size:12px; color:#777; border-top:1px solid #e0e0e0;'>
                                This is an automated notification. Please do not reply directly.<br>
                                &copy; " . date('Y') . " Nidec Instruments Philippines Corporation
                            </td>
                        </tr>

                        <!-- Confidentiality Notice -->
                        <tr>
                            <td style='background:#ffffff; padding:20px; font-size:11px; color:#777; line-height:1.5; text-align:justify; border-top:1px solid #eee;'>
                                <strong>Confidentiality and Data Privacy Notice:</strong><br>
                                This message, including any attachments, is intended solely for the addressee and may contain 
                                confidential or personal information. Unauthorized use, disclosure, or distribution is prohibited. 
                                If you received this message in error, please notify the sender immediately and permanently delete it. 
                                NIDEC INSTRUMENTS (PHILIPPINES) CORPORATION processes personal data in accordance with the Data Privacy 
                                Act of 2012 (RA 10173) and its Privacy Policy.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>";


        // Send back summary response
        if ($success_count > 0 && $error_count === 0) {
            // Create a new request log entry for the successful submission
            // $notification = [
            //     'control_number' => $control_number,
            //     'message' => "New request created by " . $requestor_section . " with Control Number: " . $control_number,
            //     'section' => $requestor_section,
            // ];

            $emailnotif = $autoemail->SendEmailupdateStatus($emailData, $message, $subject);
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
            'access' => $_POST['access'] ?? null,
            'username' => $_POST['username'] ?? null,
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
                    'updated_at' => $row['updated_at'],
                    'updateLogs' => $updatedDate = $request->Getlogs($row['control_number'])
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
        $control_number = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $filePath = $request->getAttachment($id, $control_number = null); // this returns full file path (ex: D:/Uploads/Attachments/file.png)

        if (empty($filePath) || !file_exists($filePath)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'File not found.'
            ]);
            exit;
        }

        // ✅ Get the actual MIME type of the file
        $mimeType = mime_content_type($filePath);

        // ✅ Extract only the file name (no directory)
        $fileName = basename($filePath);


        // ✅ Build a valid URL for frontend (served through preview.php)
        $previewUrl = "http://192.168.101.49/RFMSystem/preview.php?file=" . urlencode($fileName);

        // $headers = @get_headers($previewUrl);

        // if ($headers) {
        //     if (strpos($headers[0], '200') !== false) {
        //         echo "✅ File exists and is accessible.";
        //     } elseif (strpos($headers[0], '403') !== false) {
        //         echo "⚠️ Unauthorized access.";
        //     } elseif (strpos($headers[0], '404') !== false) {
        //         echo "❌ File not found.";
        //     } else {
        //         echo "⚠️ Other error: " . $headers[0];
        //     }
        // } else {
        //     $previewUrl = "http://localhost/RFMSystem/preview.php?file=" . urlencode($fileName);

        //     $headersLocal = @get_headers($previewUrl);

        //     if ($headersLocal && strpos($headersLocal[0], '200') !== false) {
        //         echo "✅ Localhost fallback accessible.";
        //     } else {
        //         echo "❌ Cannot access file from both remote and localhost.";
        //     }
        // }


        // ✅ Prepare response data
        $data = [
            'file_type' => $mimeType,
            'file_path' => $previewUrl,
            'file_name' => $fileName
        ];

        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
        exit;
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'edit_request') {
        header('Content-Type: application/json');

        $id              = $_POST['item_id'] ?? null;
        $control_number  = $_POST['control_number'] ?? null;
        $item_name       = $_POST['item_name'] ?? null;
        $item_description = $_POST['item_description'] ?? null;
        $item_purpose    = $_POST['item_purpose'] ?? null;
        $item_quantity   = $_POST['item_quantity'] ?? null;
        $item_unit       = $_POST['item_unit'] ?? null;
        $section         = $_POST['section'] ?? null;

        $filePath = $request->getAttachment($id, $control_number); // fixed here ✅
        $uploadDir = 'D:/Uploads/Attachments';
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        if (isset($_FILES['item_attachment']) && $_FILES['item_attachment']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['item_attachment']['size'] > $maxFileSize) {
                echo json_encode(['status' => 'error', 'message' => 'File size exceeds 2MB']);
                exit;
            }

            $tmpName = $_FILES['item_attachment']['tmp_name'];
            $ext = pathinfo($_FILES['item_attachment']['name'], PATHINFO_EXTENSION);
            $uniqueName = uniqid("file_") . "." . $ext;
            $newFilePath = $uploadDir . "/" . $uniqueName;

            if (move_uploaded_file($tmpName, $newFilePath)) {
                // delete old file after successful upload
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $filePaths = $newFilePath;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
                exit;
            }
        } else {
            // keep old file if no new one uploaded
            $filePaths = $filePath;
        }


        $emailData = [
            'address_section' => 'Procurement',
            'bcc_Section' => $section
        ];

        $data = [
            'id'               => $id,
            'item_name'        => $item_name,
            'item_description' => $item_description,
            'item_quantity'    => $item_quantity,
            'item_unit'        => $item_unit,
            'item_purpose'     => $item_purpose,
            'path_file'        => $filePaths
        ];

        $statusdata = [
            'control_number'   => $control_number,
            'requestor_status' => 'On-going',
            'item_remarks'     => 'For Procurement Verification',
        ];

        $attachment = $request->updateAttachment($data);
        $result = $request->editItems($data);

        $status = $statusdata['requestor_status'] ?? 'On-going';

        // Status badge styles
        $statusStyles = [
            "Completed"  => "background:#28a745; color:#fff;",   // Green
            "On-going"   => "background:#f0ad4e; color:#fff;",   // Orange
            "Cancelled" => "background:#d9534f; color:#fff;",   // Red
            "Hold"      => "background:#ffc107; color:#212529;"
        ];

        $statusBadgeStyle = $statusStyles[$status] ?? "background:#6c757d; color:#fff;"; // Default gray

        // Company logo (replace with your actual hosted logo path)
        $companyLogo = __DIR__ . '/../../' . 'img/logo.png';
        $subject = "Request for Quotation - {$control_number}";
        // $subject = "tEST EMAI";
        $message = "<table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#f4f6f9; padding:20px; font-family: Arial, sans-serif;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' border='0' style='background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>
                            
                            <!-- Header with Logo -->
                            <tr>
                                <td style='background:#003366; color:#ffffff; padding:20px 30px;'>
                                    <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                        <tr>
                                            <td align='left'>
                                                <img src='https://logovectorseek.com/wp-content/uploads/2019/11/nidec-corporation-logo-vector.png' alt='Company Logo' style='height:40px; width:40px;'>
                                            </td>
                                            <td align='right' style='color:#ffffff; font-size:18px; font-weight:bold;'>
                                                Request for Quotation Update
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Body -->
                            <tr>
                                <td style='padding:30px; color:#333333; font-size:15px; line-height:1.6;'>
                                    <p>Dear <strong>{$emailData['address_section']} Team</strong>,</p>

                                    <p>This is to inform you that the request for the <strong>{$emailData['bcc_section']}</strong> department with control number:</p>
                                    <div style='background:#f1f5f9; border-left:4px solid #003366; padding:12px 18px; margin:18px 0; font-size:15px; font-weight:bold; color:#1a1a1a;'>
                                        {$control_number}
                                    </div>

                                    <p>Current Status:</p>
                                    <p style='margin:15px 0;'>
                                        <span style='display:inline-block; {$statusBadgeStyle} padding:8px 16px; border-radius:4px; font-weight:bold; font-size:14px;'>
                                            {$statusdata['requestor_status']}
                                        </span>
                                    </p>

                                    <p><strong>Remarks:</strong></p>
                                    <div style='margin:18px 0; padding:15px; background:#fafafa; border:1px solid #e0e0e0; border-radius:4px; color:#555;'>
                                        {$statusdata['item_remarks']}
                                    </div>

                                    <p>Please review the details and take the necessary actions if required.</p>
                                    <p style='margin-top:25px;'>Best regards,<br>
                                    <strong>Nidec Instruments Philippines Corporation</strong></p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background:#f8f9fa; text-align:center; padding:15px; font-size:12px; color:#777; border-top:1px solid #e0e0e0;'>
                                    This is an automated notification. Please do not reply directly.<br>
                                    &copy; " . date('Y') . " Nidec Instruments Philippines Corporation
                                </td>
                            </tr>

                        <!-- Confidentiality Notice -->
                        <tr>
                            <td style='background:#ffffff; padding:20px; font-size:11px; color:#777; line-height:1.5; text-align:justify; border-top:1px solid #eee;'>
                                <strong>Confidentiality and Data Privacy Notice:</strong><br>
                                This message, including any attachments, is intended solely for the addressee and may contain 
                                confidential or personal information. Unauthorized use, disclosure, or distribution is prohibited. 
                                If you received this message in error, please notify the sender immediately and permanently delete it. 
                                NIDEC INSTRUMENTS (PHILIPPINES) CORPORATION processes personal data in accordance with the Data Privacy 
                                Act of 2012 (RA 10173) and its Privacy Policy.
                            </td>
                        </tr>
                        </table>
                    </td>
                </tr>
            </table>";

        if ($result) {
            $update = $request->UpdateRequestStatus($statusdata);
            if ($update) {
                $emailnotif = $autoemail->SendEmailupdateStatus($emailData, $message, $subject);
                echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update request status']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'delete_item') {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $control_number = isset($_POST['control_number']) ? $_POST['control_number'] : null;
        $item_name = isset($_POST['itemName']) ? $_POST['itemName'] : null;
        $item_description = isset($_POST['itemDescription']) ? $_POST['itemDescription'] : null;
        $result = $request->deleteRequest($id);
        $deleteAttachment = $request->deleteAttachment($id);


        //send email update
        $emailData = [
            'address_section' => 'Procurement',
            'bcc_Section' => $section
        ];
        $status = $statusdata['requestor_status'] ?? 'On-going';

        // Status badge styles
        $statusStyles = [
            "Completed"  => "background:#28a745; color:#fff;",   // Green
            "On-going"   => "background:#f0ad4e; color:#fff;",   // Orange
            "Cancelled" => "background:#d9534f; color:#fff;",   // Red
            "Hold"      => "background:#ffc107; color:#212529;"
        ];

        $statusBadgeStyle = $statusStyles[$status] ?? "background:#6c757d; color:#fff;"; // Default gray

        // Company logo (replace with your actual hosted logo path)
        $companyLogo = __DIR__ . '/../../' . 'img/logo.png';
        $subject = "Request for Quotation - {$control_number}";
        // $subject = "tEST EMAI";
        $message = "<table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#f4f6f9; padding:20px; font-family: Arial, sans-serif;'>
            <tr>
                <td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' border='0' style='background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>
                        
                        <!-- Header with Logo -->
                        <tr>
                            <td style='background:#003366; color:#ffffff; padding:20px 30px;'>
                                <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                    <tr>
                                        <td align='left'>
                                            <img src='https://logovectorseek.com/wp-content/uploads/2019/11/nidec-corporation-logo-vector.png' alt='Company Logo' style='height:40px; width:40px;'>
                                        </td>
                                        <td align='right' style='color:#ffffff; font-size:18px; font-weight:bold;'>
                                            Request for Quotation Update
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style='padding:30px; color:#333333; font-size:15px; line-height:1.6;'>
                                <p>Dear <strong>{$emailData['address_section']} Team</strong>,</p>

                                <p>This is to inform you that an <strong>{$item_name} - {$item_description}  has been deleted</strong> from the Request for Quotation (RFQ) initiated by the <strong>{$emailData['bcc_section']}</strong> department with control number:</p>
                                <div style='background:#f1f5f9; border-left:4px solid #003366; padding:12px 18px; margin:18px 0; font-size:15px; font-weight:bold; color:#1a1a1a;'>
                                    {$control_number}
                                </div>

                                <p><strong>Current Status:</strong></p>
                                <p style='margin:15px 0;'>
                                    <span style='display:inline-block; {$statusBadgeStyle} padding:8px 16px; border-radius:4px; font-weight:bold; font-size:14px;'>
                                        Cancelled
                                    </span>
                                </p>

                                <p><strong>Remarks:</strong></p>
                                <div style='margin:18px 0; padding:15px; background:#fafafa; border:1px solid #e0e0e0; border-radius:4px; color:#555;'>
                                    {$statusdata['item_remarks']}
                                </div>

                                <p>Please be advised that this update reflects the removal of one or more items from the RFQ. Kindly review the RFQ details and coordinate with the requestor if any clarification is needed.</p>

                                <p style='margin-top:25px;'>Best regards,<br>
                                <strong>Nidec Instruments Philippines Corporation</strong></p>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style='background:#f8f9fa; text-align:center; padding:15px; font-size:12px; color:#777; border-top:1px solid #e0e0e0;'>
                                This is an automated notification. Please do not reply directly.<br>
                                &copy; " . date('Y') . " Nidec Instruments Philippines Corporation
                            </td>
                        </tr>

                        <!-- Confidentiality Notice -->
                        <tr>
                            <td style='background:#ffffff; padding:20px; font-size:11px; color:#777; line-height:1.5; text-align:justify; border-top:1px solid #eee;'>
                                <strong>Confidentiality and Data Privacy Notice:</strong><br>
                                This message, including any attachments, is intended solely for the addressee and may contain 
                                confidential or personal information. Unauthorized use, disclosure, or distribution is prohibited. 
                                If you received this message in error, please notify the sender immediately and permanently delete it. 
                                NIDEC INSTRUMENTS (PHILIPPINES) CORPORATION processes personal data in accordance with the Data Privacy 
                                Act of 2012 (RA 10173) and its Privacy Policy.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>";

        if ($result) {
            $emailnotif = $autoemail->SendEmailupdateStatus($emailData, $message, $subject);
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
        } else {
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

    if (!empty($_POST['action']) && $_POST['action'] == 'get_historybycontrolnumber') {
        header('Content-Type: application/json');

        $section = $_POST['section'] ?? null;
        $filter = $_POST['filters'] ?? null;
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $perPage = 10;

        //Data Object
        $filters = [
            'section' => $section,
            'access' => $_POST['access'] ?? null,
            'username' => $_POST['username'] ?? null,
            'from' => $filter['from'] ?? null,
            'to' => $filter['to'] ?? null,
            'status' => $filter['status'] ?? null,
            'search' => $filter['search'] ?? null,
            'remarks' => $filter['remarks'] ?? null
        ];

        if ($filters['remarks'] != 'For Section Head Approval') {
            $result = $request->fetchHistoryByControlNumber($filters, $page, $perPage);
            $totalResult = $request->countHistoryByControlNumber($filters, $page, $perPage);
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
                    'created_at' => $row['created_at'],
                    'updateLogs' => $updatedDate = $request->Getlogs($row['control_number'])
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
        // $main = $_POST['main'] ?? null;
        $main = $_POST['main'] ?? null;
        $emailData = [];

        $data = [
            'control_number'   => $_POST['control_number'] ?? null,
            'item_remarks'     => $_POST['remarks'] ?? null,
            'requestor_status' => $_POST['status'] ?? null,
            'section'          => $section
        ];

        if ($main != "Procurement") {
            $emailData = [
                'address_section' => $main,
                'bcc_Section' => "Procurement"
            ];
        } else {
            $emailData = [
                'address_section' => "Procurement",
                'bcc_Section' => "Procurement"
            ];
        }

        $status = $data['requestor_status'] ?? 'On-going';

        // Status badge styles
        $statusStyles = [
            "Completed"  => "background:#28a745; color:#fff;",   // Green
            "On-going"   => "background:#f0ad4e; color:#fff;",   // Orange
            "Cancelled" => "background:#d9534f; color:#fff;",   // Red
            "Hold"      => "background:#ffc107; color:#212529;"
        ];

        $statusBadgeStyle = $statusStyles[$status] ?? "background:#6c757d; color:#fff;"; // Default gray

        // Company logo (replace with your actual hosted logo path)
        $companyLogo = __DIR__ . '/../../' . 'img/logo.png';
        $subject = "Request for Quotation - {$data['control_number']}";
        // $subject = "tEST EMAI";
        $message = "<table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#f4f6f9; padding:20px; font-family: Arial, sans-serif;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' border='0' style='background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>
                            
                            <!-- Header with Logo -->
                            <tr>
                                <td style='background:#003366; color:#ffffff; padding:20px 30px;'>
                                    <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                        <tr>
                                            <td align='left'>
                                                <img src='https://logovectorseek.com/wp-content/uploads/2019/11/nidec-corporation-logo-vector.png' alt='Company Logo' style='height:40px; width:40px;'>
                                            </td>
                                            <td align='right' style='color:#ffffff; font-size:18px; font-weight:bold;'>
                                                Request for Quotation Update
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Body -->
                            <tr>
                                <td style='padding:30px; color:#333333; font-size:15px; line-height:1.6;'>
                                    <p>Dear <strong>{$emailData['address_section']} Team</strong>,</p>

                                    <p>This is to inform you that the request for the <strong>{$emailData['address_section']}</strong> department with control number:</p>
                                    <div style='background:#f1f5f9; border-left:4px solid #003366; padding:12px 18px; margin:18px 0; font-size:15px; font-weight:bold; color:#1a1a1a;'>
                                        {$data['control_number']}
                                    </div>

                                    <p>Current Status:</p>
                                    <p style='margin:15px 0;'>
                                        <span style='display:inline-block; {$statusBadgeStyle} padding:8px 16px; border-radius:4px; font-weight:bold; font-size:14px;'>
                                            {$data['requestor_status']}
                                        </span>
                                    </p>

                                    <p><strong>Remarks:</strong></p>
                                    <div style='margin:18px 0; padding:15px; background:#fafafa; border:1px solid #e0e0e0; border-radius:4px; color:#555;'>
                                        {$data['item_remarks']}
                                    </div>

                                    <p>Please review the details and take the necessary actions if required.</p>
                                    <p style='margin-top:25px;'>Best regards,<br>
                                    <strong>Nidec Instruments Philippines Corporation</strong></p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background:#f8f9fa; text-align:center; padding:15px; font-size:12px; color:#777; border-top:1px solid #e0e0e0;'>
                                    This is an automated notification. Please do not reply directly.<br>
                                    &copy; " . date('Y') . " Nidec Instruments Philippines Corporation
                                </td>
                            </tr>

                        <!-- Confidentiality Notice -->
                        <tr>
                            <td style='background:#ffffff; padding:20px; font-size:11px; color:#777; line-height:1.5; text-align:justify; border-top:1px solid #eee;'>
                                <strong>Confidentiality and Data Privacy Notice:</strong><br>
                                This message, including any attachments, is intended solely for the addressee and may contain 
                                confidential or personal information. Unauthorized use, disclosure, or distribution is prohibited. 
                                If you received this message in error, please notify the sender immediately and permanently delete it. 
                                NIDEC INSTRUMENTS (PHILIPPINES) CORPORATION processes personal data in accordance with the Data Privacy 
                                Act of 2012 (RA 10173) and its Privacy Policy.
                            </td>
                        </tr>
                        </table>
                    </td>
                </tr>
            </table>";


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

        foreach ($getData as $row) {
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

    if (!empty($_POST['action']) && $_POST['action'] == 'send_email_to_supplier') {
        header('Content-Type: application/json');
        $recipients = $_POST['recipients'] ?? [];
        $ccs = $_POST['ccs'] ?? [];
        $bccs = $_POST['bccs'] ?? [];
        $controlNumber = $_POST['control_number'] ?? 'N/A';
        $section = $_POST['section'] ?? 'Procurement';

        $itemDetails = $request->fetchRequestById($controlNumber);
        $companyLogo = __DIR__ . '/../../' . 'img/logo.png';
        $tableHtml = "
        <table width='100%' cellpadding='8' cellspacing='0' style='border-collapse: collapse; font-family: Arial, sans-serif; font-size:14px;'>
            <thead>
                <tr style='background-color:#003366; color:#ffffff; text-align:left;'>
                    <th style='padding:10px; border:1px solid #ddd;'>Item Name</th>
                    <th style='padding:10px; border:1px solid #ddd;'>Description</th>
                    <th style='padding:10px; border:1px solid #ddd;'>Quantity</th>
                    <th style='padding:10px; border:1px solid #ddd;'>UOM</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($itemDetails as $row) {
            $tableHtml .= "
                <tr style='background-color:#f9f9f9;'>
                    <td style='padding:10px; border:1px solid #ddd;'>{$row['item_name']}</td>
                    <td style='padding:10px; border:1px solid #ddd;'>{$row['item_description']}</td>
                    <td style='padding:10px; border:1px solid #ddd; text-align:center;'>{$row['item_quantity']}</td>
                    <td style='padding:10px; border:1px solid #ddd; text-align:center;'>{$row['item_uom']}</td>
                </tr>";
        }
        $tableHtml .= "
            </tbody>
        </table>";

        $subject = "Request for Quotation: {$controlNumber}";
        $body = "
        <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#f4f6f9; padding:20px; font-family: Arial, sans-serif;'>
            <tr>
                <td align='center'>
                    <table width='650' cellpadding='0' cellspacing='0' border='0' style='background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>
                        
                        <!-- Header with Logo -->
                        <tr>
                            <td style='background:#003366; color:#ffffff; padding:20px 30px;'>
                                <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                    <tr>
                                        <td align='left' style='vertical-align:middle;'>
                                            <img src='https://logovectorseek.com/wp-content/uploads/2019/11/nidec-corporation-logo-vector.png' style='height:40px; width:40px;'>
                                        </td>
                                        <td align='center' style='font-size:20px; font-weight:bold; letter-spacing:0.5px; color:#ffffff;'>
                                            Request for Quotation
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style='padding:30px; color:#333333; font-size:15px; line-height:1.6;'>
                                <p>Dear Supplier,</p>
                                <p>I hope this email finds you well. We would like to request a quotation for the following items:</p>
                                
                                {$tableHtml}

                                <p style='margin-top:20px;'>Please provide us with the following details in your quotation:</p>
                                <ul style='margin:15px 0; padding-left:20px;'>
                                    <li>Unit price and total price</li>
                                    <li>Payment terms</li>
                                    <li>Lead time and delivery schedule</li>
                                    <li>Availability of stock</li>
                                    <li>Warranty (if applicable)</li>
                                </ul>

                                <p>Should you require any further information, please feel free to reach out.</p>

                                <p>Looking forward to your prompt response.<br>
                                Kindly see the attached file for reference.</p>

                                <p style='margin-top:25px;'>Best regards,<br>
                                <strong>Nidec Instruments Philippines Corporation</strong></p>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style='background:#f8f9fa; text-align:center; padding:15px; font-size:12px; color:#555; border-top:1px solid #e0e0e0;'>
                                <strong>Note:</strong> This is an auto-generated email. Please do not reply directly.<br>
                                Send this email thread and your response to <a href='mailto:regine.guellena@nidec.com' style='color:#003366; font-weight:bold;'>regine.guellena@nidec.com</a>.<br><br>
                                &copy; " . date('Y') . " Nidec Instruments Philippines Corporation
                            </td>
                        </tr>
                        <!-- Confidentiality Notice -->
                        <tr>
                            <td style='background:#ffffff; padding:20px; font-size:11px; color:#777; line-height:1.5; text-align:justify; border-top:1px solid #eee;'>
                                <strong>Confidentiality and Data Privacy Notice:</strong><br>
                                This message, including any attachments, is intended solely for the addressee and may contain 
                                confidential or personal information. Unauthorized use, disclosure, or distribution is prohibited. 
                                If you received this message in error, please notify the sender immediately and permanently delete it. 
                                NIDEC INSTRUMENTS (PHILIPPINES) CORPORATION processes personal data in accordance with the Data Privacy 
                                Act of 2012 (RA 10173) and its Privacy Policy.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>";

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
        $error_count   = 0;

        $control_number     = $_POST['control_number'] ?? null;
        $item_names         = $_POST['item_name'] ?? [];
        $quantities         = $_POST['item_quantity'] ?? [];
        $descriptions       = $_POST['item_description'] ?? [];
        $uom                = $_POST['item_unit'] ?? [];
        $payment_terms      = $_POST['payment_terms'] ?? [];
        $delivery_terms     = $_POST['delivery_terms'] ?? [];
        $supplier_names     = $_POST['supplier_name'] ?? [];
        $supplier_prices    = $_POST['item_price'] ?? [];
        $currency           = $_POST['currency'] ?? null;
        $supplier_discounts = $_POST['item_discount'] ?? [];
        $total_prices       = $_POST['item_total'] ?? [];
        $section            = $_POST['section'] ?? null;
        $bcc                = $_POST['bccsection'] ?? null;

        if (empty($control_number)) {
            echo json_encode(['status' => 'error', 'message' => 'Control number is required']);
            exit;
        }

        $path = 'D:/Uploads/Quotation/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!empty($_FILES['quotation']['name'])) {
            $ext = pathinfo($_FILES['quotation']['name'], PATHINFO_EXTENSION);
            $filename = uniqid("quote_") . "." . $ext;
            $filepath = $path . $filename;

            if (move_uploaded_file($_FILES['quotation']['tmp_name'], $filepath)) {
                // ---------- Save Each Item × Supplier ---------

                $dbpath = $path . $filename;

                foreach ($item_names as $itemIdx => $item_name) {
                    $quantity      = $quantities[$itemIdx] ?? null;
                    $itemSuppliers = $supplier_names[$itemIdx] ?? [];
                    $itemPrices    = $supplier_prices[$itemIdx] ?? [];
                    $itemDiscounts = $supplier_discounts[$itemIdx] ?? [];
                    $itemTotals    = $total_prices[$itemIdx] ?? [];


                    foreach ($itemSuppliers as $supplierIdx => $supplier_name) {
                        $data = [
                            'control_number'    => $control_number,
                            'item_name'         => $item_name,
                            'supplier_name'     => $supplier_name ?? null,
                            'item_quantity'     => $quantity,
                            'item_description'  => $descriptions[$itemIdx] ?? null,
                            'item_unit'         => $uom[$itemIdx] ?? null,
                            'supplier_price'    => $itemPrices[$supplierIdx] ?? null,
                            'supplier_discount' => $itemDiscounts[$supplierIdx] ?? null,
                            'total_price'       => $itemTotals[$supplierIdx] ?? null,
                            'currency'          => $currency ?? null,
                            'payment_days'      => $payment_terms[$itemIdx][$supplierIdx] ?? null,
                            'delivery_days'     => $delivery_terms[$itemIdx][$supplierIdx] ?? null,
                            'path'              => $dbpath ?? null
                        ];

                        $response = $request->CreateComparison($data);

                        if ($response) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create comparison: Error to execute']);
        }

        // ---------- Logs & Email ----------
        $data = [
            'control_number'   => $control_number,
            'item_remarks'     => 'Comparison created',
            'requestor_status' => 'Completed'
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
            'bcc_Section'     => $bcc
        ];


        $resultLogs = $logs->CreateRequestLogs($data);

        if ($success_count > 0) {
            $result     = $request->UpdateRequestStatus($data);
            $emailnotif = $autoemail->SendEmailupdateStatus($emailData, $body, $subject);

            echo json_encode(['status' => 'success', 'message' => 'Comparison created successfully']);
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
        $access = $filters['access'] ?? null;
        $username = $filters['username'] ?? null;

        $filterData = [
            'dateFrom' => $filters['dateFrom'] ?? null,
            'dateTo' => $filters['dateTo'] ?? null,
            'role' => $filters['role'] ?? null,
            'searchValue' => $filters['searchValue'] ?? null,
            'access' => $access,
            'username' => $username
        ];

        //Count total request
        $totalRequest = $request->countComparison($filterData, $section);
        //Get data
        $getData = $request->getComparison($filterData, $section, $page, $perPage);

        $data = [];

        foreach ($getData as $row) {
            $data[] = [
                'control_number' => $row['control_number'] ?? null,
                'item_status' => $row['item_status'] ?? null,
                'item_remarks' => $row['item_remarks'] ?? null,
                'item_section' => $row['item_section'] ?? null,
                'created_at' => $row['created_at'] ?? null,
                'item_requestor' => $row['item_requestor'] ?? null
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
        // $filePath = $result['upload_path'];
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
                        'currency' => $row['currency'],
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
                'data' => array_values($groupedData), // Reset keys to make JSON array

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
            echo json_encode(['status' => 'error', 'message' => 'empty id']);
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

        if (!is_array($data)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data format']);
            exit;
        }

        $year = $data['year'] ?? null;
        $section = $data['section'] ?? 'Procurement';
        $username = $data['username'] ?? null;
        $role = $data['role'] ?? null;

        $data = [
            'current_year' => $year,
            'section' => $section,
            'username' => $username,
            'role' => $role
        ];


        if (!empty($year)) {

            $chartData = $dashboard_management->getChartData($data);
            $dataChart = $chartData['data'] ?? []; // ✅ Always default to empty array

            // Debug output: send raw data to frontend for checking
            if (empty($dataChart)) {
                // Optional: comment this out in production
                echo json_encode(['status' => 'error', 'message' => 'Raw chart data', 'data' => $dataChart]);
                exit;
            }

            // Initialize months with zeroes (1-based index)
            $approvedData = array_fill(1, 12, 0);
            $pendingData  = array_fill(1, 12, 0);
            $rejectedData = array_fill(1, 12, 0);

            // ✅ Safe foreach
            if (!empty($dataChart) && is_array($dataChart)) {
                foreach ($dataChart as $row) {
                    $month = (int)$row['month'];
                    $approvedData[$month] += (int)($row['Completed'] ?? 0);
                    $pendingData[$month]  += (int)($row['On-going'] ?? 0);
                    $rejectedData[$month] += (int)($row['Hold'] ?? 0);
                }
            }

            // Output JSON only once after loop
            echo json_encode([
                'status' => 'success',
                'Completed' => array_values($approvedData),
                'Pending'   => array_values($pendingData),
                'Rejected'  => array_values($rejectedData)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Year is required']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'get_piechart_data') {
        header('Content-Type: application/json');

        $data = $_POST['data'] ?? null;
        $year = $data['year'] ?? null;
        $username = $data['username'] ?? null;
        $role = $data['role'] ?? null;
        $section = isset($data['section']) ? $data['section'] : 'Procurement'; // Default to 'Procurement' if not set

        $data = [
            'current_year' => $year,
            'section' => $section,
            'username' => $username,
            'role' => $role
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
                'data' => $data
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No data Found']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'gettimeline') {
        header('Content-Type: application/json');

        $id = isset($_POST['control_number']) ? $_POST['control_number'] : null;

        $response = $request->GetTimeline($id);

        echo json_encode([
            'status' => $response['success'] ? 'success' : 'error',
            'message' => $response['message'],
            'logs' => $response['logs']
        ]);
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'getsuggestion') {
        header('Content-Type: application/json');
        $query = isset($_POST['query']) ? $_POST['query'] : null;

        $result = $request->FetchControlNumber($query);

        echo json_encode([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => $result['data']
        ]);
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'download_comparison_pdf') {
        header('Content-Type: application/json');

        $control_number = $_POST['control_number'] ?? null;
        $requestor = $_POST['requestor'] ?? null;
        $section = $_POST['section'] ?? null;
        $description = $_POST['description'] ?? null;

        if (empty($control_number)) {
            echo json_encode(['status' => 'error', 'message' => 'Control number is required']);
            exit;
        }

        $data = [];
        $data = [
            'requestor_name' => $requestor,
            'section' => $section
        ];

        $procurement_sign = $request->getDeptEsign('Procurement');
        $deptesign = $request->getDeptEsign($section);
        $procurement_esign_cells = ['A50', 'D50', 'I50'];
        $procurement_printedsign_cells = ['A51', 'D51', 'I51'];
        $dept_esign_cells = ['A65', 'D65', 'B65'];
        $dept_printedsign_cells = ['A66', 'D66', 'B66'];


        $items = $request->fetchComparisonByControlNumber($control_number);
        $esignpath = $request->getEsign($data);
        $route = "D:/Uploads/Sign/";
        if (!empty($esignpath['signature_path'])) {
            $esign = __DIR__ . '/../../' . $esignpath['signature_path'];
        }

        $itemNum = 1;

        // Load template
        $spreadsheet = IOFactory::load(__DIR__ . '/../../Templates/ComparisonSheetForm.xlsx');
        $sheet = $spreadsheet->getActiveSheet();

        // Set page setup
        $spreadsheet->getActiveSheet()
            ->getPageSetup()
            ->setHorizontalCentered(true);
        $spreadsheet->getActiveSheet()
            ->getPageSetup()
            ->setVerticalCentered(true);


        $sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getStyle('A1:O69')->getFont()->setSize(17);

        $sheet->getPageMargins()->setTop(0.25);
        $sheet->getPageMargins()->setBottom(0.25);
        $sheet->getPageMargins()->setLeft(0.7);
        $sheet->getPageMargins()->setRight(0.2);

        $sheet->getStyle('A7:O69')->getAlignment()->setIndent(1);
        $sheet->getStyle('A15:A17')->getAlignment()->setIndent(1);

        // Set values
        $sheet->setCellValue('C7', $control_number);
        $sheet->setCellValue('C9', $section);
        $sheet->setCellValue('C11', $requestor);


        // foreach ($procurement_sign as $sign) {

        //     $absolutepath = $_SERVER['DOCUMENT_ROOT'] . '/RFMSystem/' . $sign['signature_path'];
        //     $signature3 = new Drawing();
        //     $signature3->setName('Signature4');
        //     $signature3->setDescription('Electronic Signature');
        //     $name = $sign['name'] ?? null;
        //     $position = $sign['position'] ?? null;
        //     $signaturepath = $sign['signature_path'] ?? null;

        //     if (!empty($signaturepath)) {
        //         if ($position == 'Staff') {
        //             $signature3->setPath($absolutepath);
        //             $signature3->setHeight(200);
        //             $signature3->setCoordinates($procurement_esign_cells[0]);
        //             $signature3->setWorksheet($sheet);
        //             $sheet->setCellValue($procurement_printedsign_cells[0], $name);
        //         }
        //         if ($position == 'Supervisor') {
        //             $signature3->setPath($absolutepath);
        //             $signature3->setHeight(200);
        //             $signature3->setCoordinates($procurement_esign_cells[1]);
        //             $signature3->setWorksheet($sheet);
        //             $sheet->setCellValue($procurement_printedsign_cells[1], $name);
        //         }
        //         if ($position == 'Manager' || $position == 'GenManager') {
        //             $signature3->setPath($absolutepath);
        //             $signature3->setHeight(200);
        //             $signature3->setCoordinates($procurement_esign_cells[2]);
        //             $signature3->setWorksheet($sheet);
        //             $sheet->setCellValue($procurement_printedsign_cells[2], $name);
        //         }
        //     }
        // }

        // foreach ($deptesign as $sign) {

        //     $absolutepath = $_SERVER['DOCUMENT_ROOT'] . '/RFMSystem/' . $sign['signature_path'];
        //     $signature3 = new Drawing();
        //     $signature3->setName('Signature4');
        //     $signature3->setDescription('Electronic Signature');
        //     $name = $sign['name'] ?? null;
        //     $position = $sign['position'] ?? null;

        //     if (!empty($signaturepath)) {
        //         if ($position == 'Staff') {
        //             $signaturestaff = new Drawing();
        //             $signaturestaff->setName('Signaturestaff'); 
        //             $signaturestaff->setDescription('Electronic Signature');
        //             $signaturestaff->setPath($absolutepath);
        //             $signaturestaff->setHeight(200);
        //             $signaturestaff->setCoordinates($dept_esign_cells[0]);
        //             // $signaturestaff->setOffsetX(10); 
        //             $signaturestaff->setWorksheet($sheet);

        //             $sheet->setCellValue($dept_printedsign_cells[0], $name);
        //         }
        //         if ($position == 'Supervisor') {
        //             $signaturesupervisor = new Drawing();
        //             $signaturesupervisor->setName('Signaturesupervisor');
        //             $signaturesupervisor->setDescription('Electronic Signature');
        //             $signaturesupervisor->setPath($absolutepath);
        //             $signaturesupervisor->setHeight(200);
        //             $signaturesupervisor->setCoordinates($dept_esign_cells[2]);
        //             //$signaturesupervisor->setOffsetX(100);  
        //             $signaturesupervisor->setWorksheet($sheet);
        //             $sheet->setCellValue($dept_printedsign_cells[2], $name);
        //         }
        //         if ($position == 'Manager' || $position == 'GenManager') {
        //             $signatureManager = new Drawing();
        //             $signatureManager->setName('Signaturesupervisor');
        //             $signatureManager->setDescription('Electronic Signature');
        //             $signatureManager->setPath($absolutepath);
        //             $signatureManager->setHeight(200);
        //             $signatureManager->setCoordinates($dept_esign_cells[1]);
        //             $signatureManager->setWorksheet($sheet);
        //             $sheet->setCellValue($dept_printedsign_cells[1], $name);
        //         }
        //     }
        // }

        $rowIndex = 19; // Starting row for items
        $rowDiscount = 33;
        $rowheader = 15; // Header row
        $curr = '';
        if ($items) {
            $groupedData = [];
            $suppliers = [];
            $totalDiscounts = [];

            // Collect all supplier names (to make them as column headers)
            foreach ($items as $row) {
                if (!in_array($row['supplier_name'], $suppliers)) {
                    $suppliers[] = $row['supplier_name'];
                }
            }

            $cells = ['G35', 'I35', 'K35', 'M35', 'O35'];
            //Group Item and Suppliers Details
            foreach ($items as $row) {
                $item_name = $row['item_name'];
                $curr = $row['currency'];

                if ($row['currency'] === 'USD') {
                    $sheet->getStyle('F19:O33')
                        ->getNumberFormat()
                        ->setFormatCode('"$"#,##0.00');
                    foreach ($cells as $cell) {
                        $sheet->getStyle($cell)
                            ->getNumberFormat()
                            ->setFormatCode('"$"#,##0.00');
                    }
                } else {
                    $sheet->getStyle('F19:O033')
                        ->getNumberFormat()
                        ->setFormatCode('₱#,##0.00');
                    foreach ($cells as $cell) {
                        $sheet->getStyle($cell)
                            ->getNumberFormat()
                            ->setFormatCode('"₱"#,##0.00');
                    }
                }
                // Group items by item_name
                if (!isset($groupedData[$item_name])) {
                    $groupedData[$item_name] = [
                        'control_number' => $row['control_number'],
                        'item_name'      => $item_name,
                        'item_remarks'   => $row['remarks'],
                        'item_quantity'  => $row['item_quantity'],
                        'item_unit'      => $row['item_uom'],
                        'currency'       => $row['currency'],
                        'item_description' => $row['item_description'],
                        'suppliers'      => []
                    ];
                }

                // Assign supplier details
                $groupedData[$item_name]['suppliers'][$row['supplier_name']] = [
                    'id'       => $row['id'],
                    'price'    => $row['supplier_price'],
                    'discount' => $row['supplier_discount'],
                    'total'    => $row['total_price'],
                    'payment_days' => $row['payment_terms'],
                    'delivery_days' => $row['delivery_time']
                ];
            }

            // $exchangeRate = $request->GetExchangerate($curr);
            $exchangeRate = $request->GetExchangerate('USD');

            // Build supplier → column mapping (start from F)
            $supplierColumns = [];
            $colIndex = Coordinate::columnIndexFromString('F');

            foreach ($suppliers as $supplier) {
                $supplierColumns[$supplier] = [
                    'priceCol' => Coordinate::stringFromColumnIndex($colIndex),
                    'totalCol' => Coordinate::stringFromColumnIndex($colIndex + 1)
                ];
                $colIndex += 2;
            }

            // Write supplier headers
            foreach ($suppliers as $supplier) {
                $sheet->setCellValue($supplierColumns[$supplier]['priceCol'] . $rowheader, $supplier);
                $sheet->setCellValue($supplierColumns[$supplier]['totalCol'] . $rowheader, "TOTAL");
            }

            foreach ($groupedData as  $item) {
                foreach ($suppliers as $supplier) {
                    $details  = $item['suppliers'][$supplier] ?? null;
                    // var_dump($details['payment_days']);
                    // var_dump($details['delivery_days']);
                    $sheet->setCellValue($supplierColumns[$supplier]['priceCol'] . $rowheader + 1, $details['payment_days'] . ' ' . 'Days');
                    $sheet->setCellValue($supplierColumns[$supplier]['priceCol'] . $rowheader + 2, $details['delivery_days'] . ' ' . 'Days');
                }
            }

            // ✅ NEW: init discount totals
            $totalDiscounts = [];
            $rate = [];

            foreach ($suppliers as $supplier) {
                $totalDiscounts[$supplier] = 0;
            }

            foreach ($exchangeRate as $rows) {
                $rate = $rows['currency_value'];
            }

            // Write item rows
            foreach ($groupedData as $item) {
                $sheet->setCellValue('A' . $rowIndex, $itemNum++);
                $sheet->setCellValue('B' . $rowIndex, $item['item_name'] . ' - ' . $item['item_description']);
                $sheet->setCellValue('D' . $rowIndex, $item['item_quantity']);
                $sheet->setCellValue('E' . $rowIndex, $item['item_unit']);


                //Write supplier details on the supplier column
                foreach ($suppliers as $supplier) {
                    $details  = $item['suppliers'][$supplier] ?? null;
                    $priceCol = $supplierColumns[$supplier]['priceCol'];
                    $totalCol = $supplierColumns[$supplier]['totalCol'];
                    $discountCol = $supplierColumns[$supplier]['totalCol'];

                    $sheet->setCellValue($priceCol . $rowIndex, $details['price'] ?? '');
                    $sheet->setCellValue($totalCol . $rowIndex, $details['total'] ?? '');

                    //Add up discount per supplier
                    if (!empty($details['discount'])) {
                        $totalDiscounts[$supplier] += (float)$details['discount'];
                    }
                }

                $rowIndex++;
            }

            //Write the accumulated discount on the cell
            foreach ($suppliers as $supplier) {
                $discountCol = $supplierColumns[$supplier]['totalCol'];

                $sheet->setCellValue($discountCol . $rowDiscount, $totalDiscounts[$supplier]);

                foreach ($items as $row) {
                    if ($row['currency'] == "USD") {
                        $sheet->setCellValue($discountCol . $rowDiscount + 1, $rate);
                    } else {
                        $sheet->setCellValue($discountCol . $rowDiscount + 1, '');
                    }
                }
            }

            // $totalcells = ['F36', 'H36', 'J36', 'L36', 'N36'];
            // $subtotalcells = ['G35', 'I35', 'K35', 'M35', 'O35'];
            // $ERate = ['G34', 'I34', 'K34', 'M34', 'O34'];


            // Export as PDF
            $writer = new Mpdf($spreadsheet);
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Comparison_' . $control_number . '.pdf"');
            $writer->save('php://output');
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No comparison data found']);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'update_rate') {
        header('Content-Type: application/json');

        $currency_value = $_POST['currency'] ?? null;
        $currency_id = $_POST['currency_id'] ?? null;

        $rate = [];
        if (empty($currency) && empty($currency_id)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid or empty rate.'
            ]);
            return;
        }

        $rate = [
            'currency_id' => $currency_id,
            'currency_value' => $currency_value
        ];

        try {
            $result = $request->updateExchangeRate($rate);

            if ($result) {
                echo json_encode([
                    'status' => $result['success'] ? 'success' : 'error',
                    'message' => $result['message']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => 'error',
                'message' => 'Internale server error: ' . $e->getMessage()
            ]);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'delete_request') {
        header('Content-Type: application/json');

        $control_number = $_POST['control_number'] ?? null;
        $reqsection     = $_POST['reqsection'] ?? null;
        $section        = $_POST['section'] ?? null;
        $remarks        = $_POST['remarks'] ?? null;
        $requestor      = $_POST['requestor'] ?? null;
        $DataLogs = [
            'control_number'   => $control_number,
            'requestor_status' => 'Cancelled',
            'item_remarks'     => $remarks
        ];

        $emailData = [
            'address_section' => $reqsection,
            'bcc_Section'     => $section
        ];

        $status = "Cancelled";
        $statusBadgeStyle = $statusStyles[$status] ?? "background:#6c757d; color:#fff;"; // Default gray
        $companyLogo = __DIR__ . '/../../' . 'img/logo.png';
        try {

            $delete = $request->DeleteRequestByControlNumber($control_number);
            if (!empty($delete['success']) && $delete['success'] === true) {

                $logRemarks = $logs->CreateRequestLogs($DataLogs);
                if ($logRemarks) {
                    $subject = "Request for Quotation - {$control_number}";
                    $message = "<table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#f4f6f9; padding:20px; font-family: Arial, sans-serif;'>
                                <tr>
                                    <td align='center'>
                                        <table width='600' cellpadding='0' cellspacing='0' border='0' style='background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>
                                            
                                            <!-- Header with Logo -->
                                            <tr>
                                                <td style='background:#003366; color:#ffffff; padding:20px 30px;'>
                                                    <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                                        <tr>
                                                            <td align='left'>
                                                                <img src='https://logovectorseek.com/wp-content/uploads/2019/11/nidec-corporation-logo-vector.png' alt='Company Logo' style='height:40px;'>
                                                            </td>
                                                            <td align='right' style='color:#ffffff; font-size:18px; font-weight:bold;'>
                                                                Request for Quotation Update
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <!-- Body -->
                                            <tr>
                                                <td style='padding:30px; color:#333333; font-size:15px; line-height:1.6;'>
                                                    <p>Dear <strong>{$reqsection} Team</strong>,</p>

                                                    <p>This is to inform you that the request for the <strong>{$reqsection}</strong> department with control number:</p>
                                                    <div style='background:#f1f5f9; border-left:4px solid #003366; padding:12px 18px; margin:18px 0; font-size:15px; font-weight:bold; color:#1a1a1a;'>
                                                        {$control_number}
                                                    </div>

                                                    <p>The request has been updated with the following status:</p>
                                                    <p style='margin:15px 0;'>
                                                        <span style='display:inline-block; {$statusBadgeStyle} padding:8px 16px; border-radius:4px; font-weight:bold; font-size:14px;'>
                                                            {$status}
                                                        </span>
                                                    </p>

                                                    <p><strong>Remarks:</strong></p>
                                                    <div style='margin:18px 0; padding:15px; background:#fafafa; border:1px solid #e0e0e0; border-radius:4px; color:#555;'>
                                                        {$remarks}
                                                    </div>

                                                    <p>Please review the details and take the necessary actions if required.</p>
                                                    <p style='margin-top:25px;'>Best regards,<br>
                                                    <strong>Nidec Instruments Philippines Corporation</strong></p>
                                                </td>
                                            </tr>

                                            <!-- Footer -->
                                            <tr>
                                                <td style='background:#f8f9fa; text-align:center; padding:15px; font-size:12px; color:#777; border-top:1px solid #e0e0e0;'>
                                                    This is an automated notification. Please do not reply directly.<br>
                                                    &copy; " . date('Y') . " Nidec Instruments Philippines Corporation
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>";

                    $autoemail->SendEmailupdateStatus($emailData, $message, $subject);
                }
                echo json_encode([
                    'status'  => 'success',
                    'message' => $delete['message']
                ]);
            } else {
                echo json_encode([
                    'status'  => 'error',
                    'message' => $delete['message']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'System error: ' . $e->getMessage()
            ]);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'getQuotation') {
        header('Content-Type: application/json');

        $control_number = $_POST['control_number'] ? $_POST['control_number'] : null;
        $filePath = $request->getQuotationAttachment($control_number);

        // ✅ Get the actual MIME type of the file
        $mimeType = mime_content_type($filePath);

        // ✅ Extract only the file name (no directory)
        $fileName = basename($filePath);

        // ✅ Build a valid URL for frontend (served through preview.php)
        $previewUrl = "http://localhost/RFMSystem/download.php?file=" . urlencode($fileName);

        // ✅ Prepare response data
        $data = [
            'file_type' => $mimeType,
            'file_path' => $previewUrl,
            'file_name' => $fileName
        ];

        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);

        exit;
    }
}
