<?php
    $hashFromDb = '$2y$10$CYrZQM/PF34pDDKhODAYc.72OkHWtHbXZCX8PEyUH3K'; // copy from DB
    $inputPassword = 'itstaff';
    $hashed = password_hash($inputPassword,PASSWORD_DEFAULT);

    if (password_verify($inputPassword, $hashFromDb)) {
        echo $hashed;
        echo "Password matches!";
    } else {
        echo $hashed;
        echo "Password does NOT match!";
    }
?>