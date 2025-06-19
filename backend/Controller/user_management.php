<?php
    // Include the user model
    include_once './backend/Model/usermodel.php';
    // Include the database connection
    include_once './database/dbconnection.php';
    // Create a new instance of the database connection
    $database = new DBConnection();
    $db = $database->getConnection();

    // Fetch all users from the database
    class UserManagement{
        private $user_table = 'user_table';
        private $role_table = 'role_table';
        private $department_table = 'department_table';
        private $conn;

        public function __construct($db){
            $this->conn = $db;
        }

        public function authenticate(string $username, string $password, $machine_token)
        {
            $sql = "
                SELECT u.id, u.username, u.password, u.name, u.department, u.machine_token, u.user_status, r.access_level
                FROM   {$this->user_table} AS u
                JOIN   {$this->role_table} AS r ON u.role = r.role   -- adjust PK/FK if needed
                WHERE  u.username = :username
                LIMIT  1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            /* ---------- 1.  user not found ---------- */
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User does not exist in the database.'
                ];
            }

            /* ---------- 2.  wrong password ---------- */
            if (!password_verify($password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Password does not match the stored hash.'
                ];
            }

            /* ---------- 4.   ---------- */
            if ($user['machine_token'] && $user['machine_token'] !== $machine_token) {
                return ([
                    'success' => false,
                    'message' => 'This account is already logged in from another machine.'
                ]);                    
            }

            if (empty($user['machine_token'])) {
                $update = $this->conn->prepare("UPDATE {$this->user_table} SET machine_token = :token, user_status = :status WHERE id = :id");
                $update->execute([
                    ':token' => $machine_token,
                    ':id' => $user['id'],
                    ':status' => 'Active'
                ]);
            }

            /* ---------- 5.  login success ---------- */
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user'] = [
                'id'           => $user['id'],
                'username'     => $user['username'],
                'password'     => $user['password'],
                'access_level' => $user['access_level'],
                'department'   => $user['department'],
                'name'         => $user['name'],
                'user_status'  => $user['user_status']
            ];

            return [
                'success' => true,
                'message' => 'Login successful.'
            ];
        }

        public function logout($userid, $userstatus) {

            try{
                $stmt = $this->conn->prepare("Update {$this->user_table} set machine_token = null , user_status = :user_status wher id = :id");
                $stmt->execute([
                    ':user_status' =>  $userstatus,
                    ':id' => $userid
                ]);

                // Start the session to ensure it's active
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                // Destroy session
                session_start();
                session_unset();
                session_destroy();

                return [
                    'success' => true,
                    'message' => 'Logout successfully.'
                ];

            }catch(Exception $e){
                return [
                    'success' => false,
                    'message' => 'Internal server error: ' . $e->getMessage() 
                ];
            }
            
        }


        //Check if the user is logged in
        public function isLoggedIn(){
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            // Check if the user is logged in
            if (!isset($_SESSION['user'])) {
                session_destroy();
                header('Location: login.php');
                exit();
            }
        }


        //Get Department
        public function GetDepartment(){
            $query = "SELECT department FROM " . $this->department_table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                return $result;
            } else {
                return [];
            }
        }

        //Get Department
        public function GetRole(){
            $query = "SELECT access_level, role FROM " . $this->role_table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                return $result;
            } else {
                return [];
            }
        }

        //Get user list
        public function GetUsersList($searchquery, $page = null ,$perpage = null){
            $query = "SELECT u.id,
                 u.username,
                 u.name,
                 u.department,
                 u.created_at,
                 r.access_level
            FROM {$this->user_table} AS u
            LEFT JOIN {$this->role_table} AS r 
                    ON u.role = r.role"; 

            $params = [];      
            
            if(!empty($searchquery)){
                $query .= " where u.username LIKE :searchQuery OR u.name LIKE :searchQuery OR u.department LIKE :searchQuery";
                $params[':searchQuery'] = '%' . $searchquery . '%';
            }

            // Order by newest first
            $query .= " ORDER BY u.id DESC";

            // Pagination using LIMIT and OFFSET
            $offset = ($page - 1) * $perpage;
            $query .= " LIMIT :limit OFFSET :offset";

            // Prepare and bind
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            //Bind LIMIT and OFFSET (must be integers)
            $stmt->bindValue(':limit', (int)$perpage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //Get user list count
        public function GetuserListCount($searchquery = null){
            $query = "SELECT COUNT(*) AS total
                    FROM {$this->user_table} AS u
                    LEFT JOIN {$this->role_table} AS r ON u.role = r.role";

            $conditions = [];
            $params = [];

            if (!empty($searchquery)) {
                $conditions[] = "(u.username LIKE :searchQuery OR u.name LIKE :searchQuery OR u.department LIKE :searchQuery)";
                $params[':searchQuery'] = '%' . $searchquery . '%';
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        }

        //insert user data
        public function InsertNewUserData($data){
            $query = "INSERT INTO {$this->user_table} (username,password,name,department,role) 
                      VALUES (:username, :password, :name, :department, :role)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':password', $data['password']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':department', $data['department']);
            $stmt->bindParam(':role', $data['role']);
            $result = $stmt->execute();

            if ($result) {
                return true;
            }else{
                return false;
            }
        }

        //update user data
        public function UpdateUser($data){
            if (empty($data) || empty($data['id'])) {
                return [
                    'success' => false,
                    'message' => 'Null, empty parameters, or missing user ID.'
                ];
            }

            try {
                $query = "UPDATE {$this->user_table} 
                    SET username = :username, department = :department, role = :role, name = :name
                    WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':username', $data['username']);
                $stmt->bindParam(':department', $data['department']);
                $stmt->bindParam(':role', $data['role']);
                $stmt->bindParam(':name', $data['name']);
                $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);

                if ($stmt->execute()) {
                    return [
                    'success' => true,
                    'message' => 'User data updated successfully.'
                    ];
                } else {
                    return [
                    'success' => false,
                    'message' => 'Failed to update user data.'
                    ];
                }
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Exception: ' . $e->getMessage()
                ];
            }
        }

        //Delete user data
        public function DeleteUser($id){

            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Empty id.'
                ];
            }

            try{
                $query = "delete from {$this->user_table} where id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id,PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    return [
                        'success' => true,
                        'message' => 'Deleted successfully.'
                    ];
                }else{
                    return [
                        'success' => false,
                        'message' => 'Failed to delete.'
                    ];
                }
            }catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Exception: ' . $e->getMessage()
                ];
            }
        }

        public function resetPassword($id, $password){

             if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Empty id.'
                ];
            }

            try{
                $query = "UPDATE {$this->user_table} SET password = :password where id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':password', $password);
                
                if ($stmt->execute()) {
                    return [
                        'success' => true,
                        'message' => 'Password reset successfully.' 
                    ];
                }else{
                    return [
                        'success' => false,
                        'message' => 'Failed to reset password.'
                    ];
                }
            }catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Exception: ' . $e->getMessage()
                ];
            }
            
        }

        public function changePassword($id, $password){

             if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Empty id.'
                ];
            }

            try{
                $query = "UPDATE {$this->user_table} SET password = :password where id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':password', $password);
                
                if ($stmt->execute()) {
                    return [
                        'success' => true,
                        'message' => 'Password changed successfully.' 
                    ];
                }else{
                    return [
                        'success' => false,
                        'message' => 'Failed to changed password.'
                    ];
                }
            }catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Exception: ' . $e->getMessage()
                ];
            }
            
        }
    }

?>