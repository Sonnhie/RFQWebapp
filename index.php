<?php
  session_start();
    require __DIR__ . '../vendor/autoload.php';
  use App\Controller\user_management;
  use Database\dbconnection;

  $database = new dbconnection();
  $db = $database->getConnection();
  $checkLogin = new user_management($db);
  $checkLogin->isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement RFM System</title>
    <link rel="icon" type="image/x-icon" href="./assets/img/upward.png">

   <!-- Bootstrap 5 CSS (Cloudflare CDN - reliable) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="./includes/css/common.css">

<!-- Bootstrap Icons (Cloudflare CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">

<!--Bootstrap local resources-->
<link rel="stylesheet" href="./assets/bootstrap/css/bootstrap.min.css">
<script src="./assets/bootstrap/sweetalert/sweetalert2.min.js"></script>
<script src="./assets/bootstrap/jquery/jquery.min.js"></script>

 <!-- SweetAlert2 for Alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jQuery (Google CDN) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


<!-- Custom JS -->
<script src="./includes/js/common.js"></script>


<!-- DataTables CSS & JS (Cloudflare CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.5/jquery.dataTables.min.css">
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.5/jquery.dataTables.min.js"></script> -->

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>

<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>


<!-- Daterangepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>

</head>
<body>

    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar Navigation -->
     <?php
        // Admin sidebar navigation 
        if ($_SESSION['user']['access_level'] == 'Admin') {
            include_once './components/sidebar_admin.php';
        } 
        // Procurement sidebar navigation
        else if ($_SESSION['user']['access_level'] == 'Procurement') {
            include_once './components/sidebar_procurement.php';
        }
        // Requestor sidebar navigation
        else if ($_SESSION['user']['access_level'] == 'Requestor') {
            include_once './components/sidebar_requestor.php';
        } 
        //Verifier sidebar navigation
        else if ($_SESSION['user']['access_level'] == 'Verifier') {
            include_once './components/sidebar_procurement.php';
        } 
        //Section-Approver sidebar navigation
        else if ($_SESSION['user']['access_level'] == 'Section-Approver') {
            include_once './components/sidebar_sectionapprover.php';
        } 
        //Department-Approver sidebar navigation
        else if ($_SESSION['user']['access_level'] == 'Requestor-Approver') {
            include_once './components/sidebar_section_approver.php';
        } 
        //Verifier-Approver sidebar navigation
        else if ($_SESSION['user']['access_level'] == 'Verifier-Approver') {
            include_once './components/sidebar_procurement.php';
        }
        //Manager
        else if ($_SESSION['user']['access_level'] == 'Manager') {
            include_once './components/sidebar_manager.php';
        }
     ?>
     
    <!-- Main Content Area -->
    <main class="main-content" id="mainContent"></main>
  
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS for Sidebar--> 
    <script src="./includes/js/sidebar.js"></script>
    
  