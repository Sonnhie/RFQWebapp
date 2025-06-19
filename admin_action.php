<?php
    session_start();

    include_once './database/dbconnection.php';
    include_once './backend/Controller/user_management.php';

    $usermanagement = new UserManagement($db);

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
            foreach($result as $row){
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
        }
        else{
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

        $hashedpassword = password_hash($password, PASSWORD_DEFAULT);

        $data = [];

        $data = [
            'username' => $username,
            'password' => $hashedpassword,
            'department' => $department,
            'role' => $accesslevel,
            'name' => $name
        ];
        
        $result = $usermanagement->InsertNewUserData($data);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'User created successfully.'
            ]);
        }else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create user acccount'
            ]);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'edit_user') {
        header('Content-Type: application/json');

        $username = isset($_POST['username']) ? $_POST['username'] : null;
        $department = isset($_POST['department']) ? $_POST['department'] : null;
        $role = isset($_POST['accesslevel']) ? $_POST['accesslevel'] : null;
        $name = isset($_POST['name']) ? $_POST['name'] : null;
        $id = isset($_POST['id']) ? $_POST['id'] : null;

        $data = [];

        $data = [
            'username' => $username,
            'department' => $department,
            'role' => $role,
            'name' => $name,
            'id' => $id
        ];

        $result = $usermanagement->UpdateUser($data);

        echo json_encode([
            'status'  => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ]);
    }

    if(!empty($_POST['action']) && $_POST['action'] == 'delete_user'){
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

    if(!empty($_POST['action']) && $_POST['action'] == 'change_pass'){
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

    
    if (!empty($_POST['action']) && $_POST['action'] == 'logout' ) {
        header('Content-Type: application/json');
        $userid = $_SESSION['user']['id'] ?? null;
        $userstatus = 'Offline';
        $result = $usermanagement->logout($userid, $userstatus);

        echo json_encode([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ]);
    }
?>