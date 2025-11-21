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

<!-- ============================= -->
<!--  GLOBAL CDN + LOCAL FALLBACK  -->
<!-- ============================= -->

<!-- Bootstrap 5 CSS (CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css">
<script>
if (!document.querySelector('link[href*="bootstrap.min.css"]')) {
    document.write('<link rel="stylesheet" href="./assets/bootstrap/css/bootstrap.min.css">');
}
</script>

<!-- Bootstrap Icons (CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
<script>
if (!document.querySelector('link[href*="bootstrap-icons"]')) {
    document.write('<link rel="stylesheet" href="./assets/bootstrap/icons/bootstrap-icons.css">');
}
</script>

<!-- jQuery (CDN) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
if (typeof jQuery === 'undefined') {
    document.write('<script src="./assets/bootstrap/jquery/jquery.min.js"><\/script>');
}
</script>

<!-- SweetAlert2 (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
if (typeof Swal === "undefined") {
    document.write('<script src="./assets/bootstrap/sweetalert/sweetalert2.min.js"><\/script>');
}
</script>

<!-- DataTables CSS (CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.5/jquery.dataTables.min.css">
<script>
if (!document.querySelector('link[href*="jquery.dataTables"]')) {
    document.write('<link rel="stylesheet" href="./assets/datatables/datatables.min.css">');
}
</script>

<!-- DataTables JS (CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.5/jquery.dataTables.min.js"></script>
<script>
if (typeof $.fn.DataTable === 'undefined') {
    document.write('<script src="./assets/datatables/datatables.min.js"><\/script>');
}
</script>

<!-- Chart.js (CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
if (typeof Chart === "undefined") {
    document.write('<script src="./assets/chartjs/chart.umd.min.js"><\/script>');
}
</script>

<!-- Moment.js (CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
if (typeof moment === "undefined") {
    document.write('<script src="./assets/moment/moment.min.js"><\/script>');
}
</script>

<!-- Daterangepicker CSS (CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.css">
<script>
if (!document.querySelector('link[href*="daterangepicker"]')) {
    document.write('<link rel="stylesheet" href="./assets/daterangepicker/daterangepicker.css">');
}
</script>

<!-- Daterangepicker JS (CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>
<script>
if (typeof $.fn.daterangepicker === "undefined") {
    document.write('<script src="./assets/daterangepicker/daterangepicker.min.js"><\/script>');
}
</script>

<!-- Custom CSS & JS -->
<link rel="stylesheet" href="./includes/css/common.css">
<script src="./includes/js/common.js"></script>


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
    
  