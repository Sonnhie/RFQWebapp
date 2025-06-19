$(document).ready(function () {
    //Handle Login Form Submission
    $('#loginForm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        const username = $('#username').val();
        const password = $('#password').val();
        const action = 'login'; // Action to be performed
       

        // Perform AJAX request to login
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: {
                action: action,
                username: username,
                password: password
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
                        // Redirect to dashboard after successful login
                        window.location.href = 'index.php';
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

});