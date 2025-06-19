 // Load content dynamically
 function loadPage(page) {
    $('#mainContent').load('pages/' + page + '.php');
    history.pushState(null, '', '?page=' + page);
  }

  // On initial load
  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page') || 'dashboard'; // Default to dashboard if no page is specified

    $('#content-area').load('pages/' + page + '.php', function() {
      // Highlight the active nav link
      $('.nav-link').removeClass('active');
      $('.nav-link[data-page="' + page + '"]').addClass('active');
    });

    loadPage(page);

    // Handle nav clicks
    $('.nav-link').click(function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadPage(page);
    });

    $(document).on('click', '#viewall', function(e) {
        e.preventDefault();
        const page = $(this).data('page');

        Swal.fire({
              title: 'Redirecting...',
              icon: 'info',
              text: 'Please wait while we load the data.',
              showConfirmButton: false,
              allowOutsideClick: false,
              didOpen: () => {
              Swal.showLoading();
              }
            });
        setTimeout(() => {
          Swal.close();
          loadPage(page);
        }, 1000);
        
        // console.log('this is working');
    });

    //Logout button click event
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault(); // Prevent default form submission
        const action = 'logout';
        Swal.fire({
            title: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Logout',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: './admin_action.php',
                type: 'POST',
                data: {action: action},
                dataType: 'json',
                success: function(response){
                  if (response.status == 'success') {
                      Swal.fire({
                        icon: 'success',
                        title: 'Logout Successful',
                        showConfirmButton: false,
                        timer: 1500
                      }).then(() => {
                        window.location.href = response.route; // Redirect after alert
                      });                    
                  }
                },
                error: function(xhr, status, error){
                    console.log('Error: ' ,status, error, xhr);
                }
              })
            }
          });
    });

    // Back/forward browser navigation
    window.onpopstate = function() {
      const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';
      loadPage(page);
    };
    
  });