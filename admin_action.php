<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use Database\dbconnection;
use App\Controller\QueryBuilder;
use App\Controller\user_management;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;

$database = new dbconnection();
$db = $database->getConnection();

$usermanagement = new user_management($db);

if (!empty($_POST['action']) && $_POST['action'] == 'get_userlist') {
    header('Content-Type: application/json');

    $result = $usermanagement->GetDepartment();
    $roleresult = $usermanagement->GetRole();

    if (!empty($result)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'User successfully loaded.',
            'data' => $result,
            'access' => $roleresult
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to retrieve data.'
        ]);
    }
    exit;
}

if (!empty($_POST['action']) && $_POST['action'] == 'get_user') {
    header('Content-Type: application/json');

    $searchInput = $_POST['searchinput'] ?? null;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $perpage = 10;

    $result = $usermanagement->GetUsersList($searchInput, $page, $perpage);
    $resultcount = $usermanagement->GetuserListCount($searchInput); // ✅ actual total count

    $data = [];

    if (!empty($result)) {
        foreach ($result as $row) {
            $data[] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'name' => $row['name'],
                'department' => $row['department'],
                'access_level' => $row['access_level'],
                'createdat' => $row['created_at']
            ];
        }
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'total' => $resultcount, // ✅ dynamic count
            'currentPage' => $page,
            'perPage' => $perpage
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to retrieve data.'
        ]);
    }
}

if (!empty($_POST['action']) && $_POST['action'] == 'create_user') {
    header('Content-Type: application/json');

    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $department = isset($_POST['department']) ? $_POST['department'] : null;
    $accesslevel = isset($_POST['accesslevel']) ? $_POST['accesslevel'] : null;
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $position = isset($_POST['position']) ? $_POST['position'] : null;
    $hashedpassword = password_hash($password, PASSWORD_DEFAULT);

    $data = [];
    $paths = $usermanagement->getUploadPaths($department);


    if (!$paths) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Invalid department: ' . $department
        ]);
    }

    // $uploadDir = __DIR__ . $paths['upload_path'];
    $uploadDir = "D:/Uploads" . $paths['upload_path'];
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
    $filename = uniqid("sig_") . "." . $ext;
    $filepath = $uploadDir . "/" . $filename;

    if (move_uploaded_file($_FILES['signature']['tmp_name'], $filepath)) {
        $dbPath = ltrim($paths['upload_path'], "./") . "/" . $filename;

        $data = [
            'username' => $username,
            'password' => $hashedpassword,
            'department' => $department,
            'role' => $accesslevel,
            'name' => $name,
            'position' => $position,
            'path' => $dbPath
        ];

        // $uploadedresult = $usermanagement->InsertSignature($data);
        $result = $usermanagement->InsertNewUserData($data);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'User created successfully.',
                'logs' => $paths
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create user acccount'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Uploading of signature failed.'
        ]);
    }
}

if (!empty($_POST['action']) && $_POST['action'] == 'edit_user') {
    header('Content-Type: application/json');

    $username   = $_POST['username'] ?? null;
    $department = $_POST['department'] ?? null;
    $role       = $_POST['accesslevel'] ?? null;
    $name       = $_POST['name'] ?? null;
    $position   = $_POST['position'] ?? null;
    $id         = $_POST['id'] ?? null;

    $paths = $usermanagement->getUploadPaths($department);

    if (!$paths) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid department: ' . $department
        ]);
        exit;
    }

    $uploadDir = "D:/Uploads" . $paths['upload_path'];
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['signature']['name'])) {
        $ext      = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("sig_") . "." . $ext;
        $filepath = $uploadDir . "/" . $filename;

        if (move_uploaded_file($_FILES['signature']['tmp_name'], $filepath)) {
            // Save relative path for DB
            $dbPath = ltrim($paths['upload_path'], "./") . "/" . $filename;

            $data = [
                'username'   => $username,
                'department' => $department,
                'role'       => $role,
                'name'       => $name,
                'id'         => $id,
                'position'   => $position,
                'path'       => $dbPath
            ];

            $oldEsign = $usermanagement->getEsign($data);
            
            if (!empty($oldEsign['signature_path'])) {
                $absolutePath = "D:/Uploads" . $oldEsign['signature_path'];
                $filename = basename($oldEsign['signature_path']);
                if (file_exists($absolutePath, $filename)) {
                    unlink($absolutePath);
                }
            }


            $result = $usermanagement->UpdateUser($data);
            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $result['message'],
                    'path' => $data['path']
                ]);
            }else{
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid Query: ' . $result['message']
                ]);
            }
        }

    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'No signature file uploaded.'
        ]);
    }
}


if (!empty($_POST['action']) && $_POST['action'] == 'delete_user') {
    header('Content-Type: application/json');

    $id = isset($_POST['id']) ? $_POST['id'] : null;

    $result = $usermanagement->DeleteUser($id);

    echo json_encode([
        'status'  => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ]);
}

if (!empty($_POST['action']) && $_POST['action'] == 'reset_pwd') {
    header('Content-Type: application/json');

    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $defaultPass = 'Password01';

    $hashedpassword = password_hash($defaultPass, PASSWORD_DEFAULT);

    $result = $usermanagement->resetPassword($id, $hashedpassword);

    echo json_encode([
        'status'  => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ]);
}

if (!empty($_POST['action']) && $_POST['action'] == 'change_pass') {
    header('Content-Type: application/json');

    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $newpass = isset($_POST['id']) ? $_POST['newpass'] : null;

    $hashedpassword = password_hash($newpass, PASSWORD_DEFAULT);

    $result = $usermanagement->changePassword($id, $hashedpassword);

    echo json_encode([
        'status'  => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ]);
}

if (!empty($_POST['action']) && $_POST['action'] == 'logout') {
    header('Content-Type: application/json');
    $userid = $_SESSION['user']['id'] ?? null;
    $userstatus = 'Offline';
    $result = $usermanagement->logout($userid, $userstatus);

    echo json_encode([
        'status' => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ]);
}

if (!empty($_POST['action']) && $_POST['action'] == 'get_email') {
    header('Content-Type: application/json');

    $searchInput = $_POST['searchinput'] ?? null;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $perpage = 15;

    $result = $usermanagement->GetEmailList($searchInput, $page, $perpage);
    $resultcount = $usermanagement->GetEmailListCount($searchInput); // ✅ actual total count

    $data = [];

    if (!empty($result)) {
        foreach ($result as $row) {
            $data[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'emailadd' => $row['emailadd'],
                'department' => $row['department']
            ];
        }
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'total' => $resultcount, // ✅ dynamic count
            'currentPage' => $page,
            'perPage' => $perpage
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to retrieve data.'
        ]);
    }
}

if (!empty($_POST['action'] && $_POST['action'] == 'create_email')) {

    $name = $_POST['name'] ?? null;
    $emailadd = $_POST['emailaddress'] ?? null;
    $department = $_POST['department'] ?? null;

    try {
        $data = [
            'name' => $name,
            'emailadd' => $emailadd,
            'department' => $department
        ];

        $result = $usermanagement->InsertNewEmail($data);

        echo json_encode([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Internal server error: ' . $e
        ]);
    }
}

if (!empty($_POST['action'] && $_POST['action'] == 'edit_email')) {

    $id = $_POST['id'] ?? null;
    $name = $_POST['editname'] ?? null;
    $emailadd = $_POST['emailadd'] ?? null;
    $department = $_POST['editdepartment'] ?? null;

    try {
        $data = [
            'id' => $id,
            'name' => $name,
            'emailadd' => $emailadd,
            'department' => $department
        ];

        $result = $usermanagement->UpdateEmail($data);

        echo json_encode([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Internal server error: ' . $e
        ]);
    }
}

if (!empty($_POST['action']) && $_POST['action'] == 'get_option') {
    header('Content-Type: application/json');

    $departmentresult = $usermanagement->GetDepartment();
    $nameresult = $usermanagement->getNames();

    if (!empty($departmentresult) && !empty($nameresult)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Option successfully loaded.',
            'data' => $departmentresult,
            'access' => $nameresult
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to retrieve data.'
        ]);
    }
    exit;
}

if (!empty($_POST['action']) && $_POST['action'] == 'delete_email') {
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? null;

    $result = $usermanagement->DeleteEmail($id);

    echo json_encode([
        'status' => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ]);
}
