$(document).ready(function () {
    //Handle Login Form Submission
    $('#loginForm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        const username = $('#username').val();
        const password = $('#password').val();
        const action = 'login'; // Action to be performed
        const machine_token = localStorage.getItem('machine_token');
        
        // Perform AJAX request to login
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: {
                action: action,
                username: username,
                password: password,
                machine_token: machine_token
            },
            dataType: 'json',
            success: function(response){
                console.log(response);
                if (response.status == 'success') {
                    Swal.fire({
                        icon: "success",
                        title: "Login Successful",
                        showConfirmButton: false,
                        timer: 1500
                      }).then(() => {
                         if(password == 'Password01'){
                            window.location.href = 'change_pass.php';
                         }else{
                            window.location.href = 'index.php';
                         }
                      });  
                }
                else {
                    Swal.fire({
                        icon: "error",
                        title: "Login Failed",
                        text: response.message,
                        confirmButtonText: "OK"
                    });
                }
            },
            error: function(xhr, status, error) {
                // Handle error response
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                Swal.fire({
                    icon: "error",
                    title: "Login Failed",
                    text: errorMessage,
                    confirmButtonText: "OK"
                });
            }
        })
    });


    $('#changepasswordForm').submit(function(e){
        e.preventDefault();
        const newpass = $('#new_pass').val();
        const confirmedpass = $('#confirm_pass').val();
        const action = 'change_pass';
        const id = $(this).data('id');

        // console.log(newpass, confirmedpass, id);

        if (newpass !== confirmedpass) {
            Swal.fire({
                icon: "error",
                title: "Invalid password",
                text: 'Password do not match',
                confirmButtonText: "OK"
            });
            return;
        }

        if (newpass.length < 8) {
            Swal.fire({
                icon: "info",
                title: "Invalid password",
                text: 'Password must be at least 8 characters long.',
                confirmButtonText: "OK"
            });
            return;
        }

         $.ajax({
            url: './admin_action.php',
            type: 'POST',
            data: {
                action: action,
                newpass: newpass,
                id: id,
                confirmedpass: confirmedpass
            },
            dataType: 'json',
            success: function(response){
                console.log(response);
                if (response.status == 'success') {
                    Swal.fire({
                        icon: "success",
                        title: "Change Password",
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                      }).then(() => {
                        window.location.href = 'login.php';
                      });  
                }
                else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text: response.message,
                        confirmButtonText: "OK"
                    });
                }
            },
            error: function(xhr, status, error) {
                // Handle error response
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                Swal.fire({
                    icon: "error",
                    title: "Password Change failed",
                    text: errorMessage,
                    confirmButtonText: "OK"
                });
            }
         });

    });
});