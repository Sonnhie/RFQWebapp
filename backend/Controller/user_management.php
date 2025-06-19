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

        //Function to authenticate user login
        public function authenticate($username, $password){
            $query = "SELECT u.id, u.username, u.password, u.name, u.department, r.access_level FROM " . $this->user_table . " u INNER JOIN " . $this->role_table . " r ON u.role = r.role WHERE username = :username AND password = :password";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $password == $user['password']) {

                // Ensure session is started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                // Set session variables
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'access_level' => $user['access_level'],
                    'department' => $user['department'],
                    'name' => $user['name']
                ];
                return true;
            }else {
                return false;
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
    }

?>