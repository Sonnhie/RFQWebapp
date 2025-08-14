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

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./includes/css/common.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!--JQuery-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom JS -->
    <script src="./includes/js/common.js"></script>


    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">

    <!-- <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

    <!-- Chart.js for Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- SweetAlert2 for Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Moment.js (required for daterangepicker) -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

    <!-- Daterangepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS for Sidebar--> 
    <script src="./includes/js/sidebar.js"></script>
    
  