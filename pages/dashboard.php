<?php
    session_start();

        if ($_SESSION['user']['access_level'] == 'Admin') {
            include_once './admin/admin_dashboard.php';
            echo '<script src="./includes/js/admin_dashboard.js"></script>';
        } else if ($_SESSION['user']['access_level'] == 'Requestor') {
            include_once './content/requestor_content.php';
            echo '<script src="./includes/js/dashboard.js"></script>';
        } else if ($_SESSION['user']['access_level'] == 'Verifier') {
            include_once './content/verifier_content.php';
            echo '<script src="./includes/js/dashboard.js"></script>';
        } else if ($_SESSION['user']['access_level'] == 'Section-Approver') {
            include_once './content/requestor_content.php';
            echo '<script src="./includes/js/dashboard.js"></script>';
        } else if ($_SESSION['user']['access_level'] == 'Requestor-Approver') {
          include_once './content/requestor_approver_content.php';
          echo '<script src="./includes/js/dashboard.js"></script>';
        } else if ($_SESSION['user']['access_level'] == 'Verifier-Approver') {
          include_once './content/verifier_content.php';
          echo '<script src="./includes/js/dashboard.js"></script>';
      }
?>

        

        <?php
// Include the footer
include_once '../components/footer.php';
?>