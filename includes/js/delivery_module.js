$(document).ready(function(){

    LoadDeliveryItemTable();
    $("#rfqSearchInput").on('keyup', function(){
        const $query = $(this).val();

        console.log($query);

        if ($query.length >= 2) {
            $.ajax({
                url: './action.php',
                type: 'POST',
                data: {query: $query, action: 'getsuggestion'},
                dataType: 'json',
                success: function(response){
                    let suggestions = '';
                    if (response.status == 'success') {
                        if (response.data.length > 0) {
                            response.data.forEach(item => {
                                suggestions += `<button type="button" class="list-group-item list-group-item-action rfq-item">${item}</button>`;
                            });
                        }else{
                            suggestions = `<div class="list-group-item disabled">No matches found</div>`;
                        }
                        $('#rfqSuggestionList').html(suggestions).show();
                    }
                }
            });
        }else{
            $('#rfqSuggestionList').empty().hide();
        }
    });

    // When user clicks a suggestion
    $(document).on('click', '.rfq-item', function () {
        $('#rfqSearchInput').val($(this).text());
        $('#rfqSuggestionList').empty().hide();
    });

    //Click to load items
    $('#loadItemsBtn').on('click', function(){
        const $controlNumber = $('#rfqSearchInput').val();

        console.log($controlNumber);

        LoadItems($controlNumber);
    });

    //function to load items
    function LoadItems($controlNumber){

        if (!$controlNumber) {
            alert('Invalid or empty Control Number');
            $('#rfqSearchInput').focus();
            return;
        }

        const $tbody = $('#deliveryitems');
        $tbody.empty();
        const loadingRow = $(`
            <tr>
                <td colspan="9" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `);
        $tbody.append(loadingRow);

        $.ajax({
            url: './action.php',
            type: 'POST',
            data: {action: 'LoadItems', control_number: $controlNumber},
            dataType: 'json',
            success: function(response){
                $tbody.empty();
                if (response.status == 'success') {
                    console.log(response);
                    response.data.forEach(item =>{
                        const $row = `
                        <tr>
                            <td><input type="text" class="form-control form-control-sm" name="item_name[]" value ="${item.item_name}" readonly></td>
                            <td><input type="text" class="form-control form-control-sm" name="item_description[]" value ="${item.item_description}" readonly></td>
                            <td><input type="text" class="form-control form-control-sm" name="item_quantity[]" value ="${item.item_quantity}" readonly></td>
                            <td><input type="text" class="form-control form-control-sm" name="supplier_name[]" placeholder="Supplier" required></td>
                            <td><input type="text" class="form-control form-control-sm" name="amount[]" placeholder="Total amount" required></td>
                            <td><input type="date" class="form-control form-control-sm" name="delivery_date[]" required></td>
                        </tr>
                        `;

                        $tbody.append($row);
                    });
                }else{
                     console.error('Error fetching items:', response.message);
                     const $row = $(`
                        <tr>
                            <td colspan="8" class="text-center">No items found.</td>
                        </tr>
                    `);
                    $tbody.append($row);
                }
            },
            error: function( xhr, status, error){
                $tbody.empty();
                console.error('AJAX error:', status, error);
                const $row = $(`
                    <tr>
                        <td colspan="9" class="text-center">Error fetching items.</td>
                    </tr>
                `);
                $tbody.append($row);
            }
        });
    }

    function LoadDeliveryItemTable(page = 1){
            const $searchQuery = $('#searchInput').val();
            const $tbody = $('#DeliveryItemTable');
            $tbody.empty();
            const loadingRow = $(`
                <tr>
                    <td colspan="11" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </td>
                </tr>
            `);
            $tbody.append(loadingRow);
            
            $.ajax({
                url: './action.php',
                type: 'POST',
                data: {action: 'LoadTable', search_query: $searchQuery, page: page},
                dataType: 'json',
                success: function(response){
                        $tbody.empty();     
                        if (response.status == 'success') {
                            response.data.forEach(item => {
                                // const receivedBtn = `<button class="btn btn-success btn-sm me-3">
                                //                     <i class="bi bi-truck"></i> Received
                                //                     </button>`;
                                const addremarksBtn = `<button class="btn btn-secondary btn-sm" id="addremarks" data-id=${item.id}>
                                                       <i class="bi bi-journal-check"></i> Add remarks
                                                    </button>`;

                                let receivedBtn = '', columnvalue = ' ' , deletebtn = '', editbtn = ``;

                                if (item.received_date == null) {
                                    receivedBtn = `<button class="btn btn-success btn-sm me-2" id="receivedbtn" data-id=${item.id} data-control =${item.control_number}>
                                                    <i class="bi bi-truck"></i> Received
                                                    </button>`;
                                    deletebtn = `<button class="btn btn-danger btn-sm me-2" id="deletebtn" data-id=${item.id} data-control =${item.control_number}>
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>`
                                    editbtn = `<button class="btn btn-primary btn-sm me-2" id="editbtn" data-bs-toggle="modal" data-bs-target="#editDeliveryModal" data-id=${item.id} data-control =${item.control_number}>
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>`;
                                }else{
                                    columnvalue = `${item.received_date}`;
                                }

                                const btnGroup = `
                                    ${receivedBtn}
                                    ${editbtn}
                                    ${deletebtn}
                                    ${addremarksBtn}
                                `;

                                const $row = `
                                    <tr>
                                        <td>${item.id}</td>
                                        <td>${item.control_number}</td>
                                        <td>${item.item_name}</td>
                                        <td>${item.item_quantity}</td>
                                        <td>${item.supplier_name}</td>
                                        <td>₱ ${parseFloat(item.item_amount).toFixed(2)}</td>
                                        <td>${item.item_status}</td>
                                        <td>${item.item_remarks}</td>
                                        <td>${item.delivery_date}</td>
                                        <td>${columnvalue}</td>
                                        <td>${btnGroup}</td>
                                    </tr>
                                `;

                                $tbody.append($row);
                            });
                           
                        }else{
                            console.error('Error fetching items:', response.message);
                            const $row = $(`
                                <tr>
                                    <td colspan="11" class="text-center">No items found.</td>
                                </tr>
                            `);
                            $tbody.append($row);
                        }
                        const totalPages = Math.ceil(response.total / response.perPage);
                        paginateTable(page, totalPages);
                },
                error: function(xhr, status, error){
                        $tbody.empty();
                        console.error('AJAX error:', status, error);
                        const $row = $(`
                            <tr>
                                <td colspan="11" class="text-center">Error fetching items.</td>
                            </tr>
                        `);
                        $tbody.append($row);
                    }
                });
    }

    function paginateTable(currentPage, totalPages) {

        const $pagination = $('#pagination');
        $pagination.empty();

        for (let i = 1; i <= totalPages; i++) {
            const $pageItem = $(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#">${i}</a>
                </li>
            `);

            $pageItem.on('click', function (e) {
                e.preventDefault();
                LoadDeliveryItemTable(i);
            });

            $pagination.append($pageItem);
        }
    }

    $('#deliveryForm').submit(function(e){
        e.preventDefault();
        console.log('clicked');
        const control_number = $('#rfqSearchInput').val();
        const formData = new FormData($("#deliveryForm")[0]);
        formData.append('action', 'save_delivery');
        formData.append('control_number', control_number);
        console.log(formData, control_number);

        $.ajax({
            url: './action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            beforeSend: function(){
                Swal.fire({
                    title: 'Please wait...',
                    text: 'Processing your request',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response){
                Swal.close();
                if (response.status == 'success') {
                        Swal.fire({
                        icon: 'success',
                        title: 'Delivery Created',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        Swal.close();
                        LoadDeliveryItemTable();
                    });
                }else{
                    Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    });
                }
            },
            error: function(xhr, status, error){
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while submitting the request.',
                });
            }
        });
    });

    $('#addDeliveryModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset(); // Reset all inputs in the form
        $('#rfqSearchInput').val('');
        $('#rfqSuggestionList').empty();
        $('#deliveryitems').empty();
    });

    $('#DeliveryItemTable').on('click', '#receivedbtn', function(){
        const id = $(this).data('id');
        const control_number = $(this).data('control');
        console.log(control_number);
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you already received this item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, received it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we updating the item.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: {action: 'received', id: id, control_number: control_number},
                    dataType: 'json',
                    success: function(response){
                        if (response.status == 'success') {
                            Swal.fire(
                                'Received!',
                                response.message,
                                'success'
                            );
                            LoadDeliveryItemTable();
                        }else{
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error){
                        console.error('AJAX error:', status, error, xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while approving the item.',
                        });
                    }
                });
            }
        });
    });

    $('#DeliveryItemTable').on('click', '#deletebtn', function(){
        const id = $(this).data('id');
        // console.log(id);
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you want to delete this item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we delete the item.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: {action: 'delete', id: id},
                    dataType: 'json',
                    success: function(response){
                        if (response.status == 'success') {
                            Swal.fire(
                                'Received!',
                                response.message,
                                'success'
                            );
                            LoadDeliveryItemTable();
                        }else{
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error){
                        console.error('AJAX error:', status, error, xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while approving the item.',
                        });
                    }
                });
            }
        });
    });

    $('#DeliveryItemTable').on('click', '#editbtn', function(){
        const id = $(this).data('id');
        const $row = $(this).closest('tr');
        $('#editdeliveryForm').data('id',id);
        const item_name =  $row.find('td:eq(2)').text();
        const item_quantity =  $row.find('td:eq(3)').text();
        $('#item_name').val(item_name);
        $('#item_quantity').val(item_quantity);
    });

    $('#editdeliveryForm').submit(function(e){
        e.preventDefault();
        const id = $(this).data('id');
        console.log(id);
        const formData = new FormData($('#editdeliveryForm')[0]);
        formData.append('action', 'editdelivery');
        formData.append('id', id);
        console.log(formData);
        Swal.fire({
            title: 'Are you sure?',
            text: "Save changes?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, saved it!'
        }).then((result) =>{
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we create the comparison.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(response){
                        Swal.close();
                        if (response.status == 'success') {
                             Swal.fire(
                                'Successfully updated!',
                                response.message,
                                'success'
                            );
                            $('#editDeliveryModal').modal('hide');
                            $('#editDeliveryModal').find('form')[0].reset();
                            LoadDeliveryItemTable();
                        }else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error){
                        Swal.close();
                        console.error('AJAX error:', status, error, xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while creating the comparison.',
                        });
                    }
                });
            }
        })
    });

    $('#DeliveryItemTable').on('click', '#addremarks', function(){
        const id = $(this).data('id');

        Swal.fire({
            title: 'Add Remarks',
            text: "You want to add remarks on this item? Please provide remarks.",
            input: 'textarea',
            inputPlaceholder: 'Enter remarks here...',
            inputAttributes: {
                'aria-label': 'Type your remarks here'
            },
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, hold it!',
            preConfirm: (remarks) => {
                if (!remarks) {
                    Swal.showValidationMessage('Remarks are required');
                }
                return remarks;
            }
        }).then((result) =>{
            if (result.isConfirmed) {
                 Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we update the details.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: {action: 'addremarks', remarks: result.value, id: id},
                    dataType: 'json',
                    success: function(response){
                        Swal.close();
                        if (response.status == 'success') {
                            Swal.fire(
                                'Updated',
                                response.message,
                                'success'
                            );
                            LoadDeliveryItemTable();                            
                        }else{
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );                            
                        }
                    },
                    error: function(xhr, status, error){
                        Swal.close();
                        console.error('AJAX error:', status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while holding the item.',
                        });
                    }
                });
            }
        });
    });
});