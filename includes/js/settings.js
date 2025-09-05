$(document).ready(function(){
    $('#pesoexchangeRateForm').submit(function(e){
        e.preventDefault();
        const currencyID = 'PHP';
        const form = $(this).serialize() + `&action=update_rate&currency_id=${currencyID}`;
        console.log(form);

        Swal.fire({
            title: "Are you sure?",
            text: "You want to update rate?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, update it!",            
        }).then((result)=>{
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait while we update the exchange rate.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
                
                $.ajax({
                    url: "./backend/Route/requestRouteAction.php",
                    type: "POST",
                    data: form,
                    dataType: "json",
                    success: function(response){
                        if (response.status == 'success') {
                            Swal.close();
                            Swal.fire({
                                icon: "success",
                                title: "Update Success",
                                text: response.message
                            });
                            $("#pesoexchangeRateForms")[0].reset();
                        }else{
                            Swal.close();
                            Swal.fire({
                                icon: "error",
                                title: "Update Failed",
                                text: response.message
                            });                            
                        }
                    },
                    error: function(xhr, status, error){
                        Swal.close();
                        console.error("AJAX error:", status, error);
                        Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "An error occurred while creating the comparison.",
                        });
                    }                    
                })
            }
        })
    });

    $('#usdexchangerateform').submit(function(e){
        e.preventDefault();
        const currencyID = 'USD';
        const form = $(this).serialize() + `&action=update_rate&currency_id=${currencyID}`;
        console.log(form);

        Swal.fire({
            title: "Are you sure?",
            text: "You want to update rate?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, update it!",            
        }).then((result)=>{
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait while we update the exchange rate.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
                
                $.ajax({
                    url: "./backend/Route/requestRouteAction.php",
                    type: "POST",
                    data: form,
                    dataType: "json",
                    success: function(response){
                        if (response.status == 'success') {
                            Swal.close();
                            Swal.fire({
                                icon: "success",
                                title: "Update Success",
                                text: response.message
                            });
                            $('#usdexchangerateform')[0].reset();
                        }else{
                            Swal.close();
                            Swal.fire({
                                icon: "error",
                                title: "Update Failed",
                                text: response.message
                            });                            
                        }
                    },
                    error: function(xhr, status, error){
                        Swal.close();
                        console.error("AJAX error:", status, error);
                        Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "An error occurred while creating the comparison.",
                        });
                    }                    
                })
            }
        })
    });
    
});