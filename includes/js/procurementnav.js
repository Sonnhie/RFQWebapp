$(document).ready(function() {

    $('#logoutBtn').on('click', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        Swal.fire({
            title: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Logout',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              $.post('logout.php', function() {
                Swal.fire({
                  icon: 'success',
                  title: 'Logout Successful',
                  showConfirmButton: false,
                  timer: 1500
                }).then(() => {
                  window.location.href = 'login.php'; // Redirect after alert
                });
              });
            }
          });
    });

});