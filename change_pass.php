<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="./includes/css/login.css">
       <!--JQuery-->
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
     <!-- SweetAlert2 for Alerts -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Change Password Page</title>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <i class="bi bi-lock-fill"></i>
            <h3>Change Password</h3>
        </div>
        <form action="#" method="POST" id="changepasswordForm" data-id="<?php echo $_SESSION['user']['id'];?>">
            <div class="input-icon">
                <i class="bi bi-lock-fill"></i>
                <input type="password" class="form-control" placeholder="Enter new password" id="new_pass" required>
            </div>
            
            <div class="input-icon">
                <i class="bi bi-lock-fill"></i>
                <input type="password" class="form-control" placeholder="Confirm new password" id="confirm_pass" required>
            </div>
            <button type="submit" class="btn-login">Change Password</button>
        </form>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./includes/js/login.js"></script>
</body>
</html>

