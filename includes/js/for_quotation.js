$(document).ready(function () {
    const page = 1;
   // const today = new Date().toISOString().split('T')[0]; // Format: YYYY-MM-DD
   // $('#fromDateFilter').attr('max', today).val(today);
    //$('#toDateFilter').attr('max', today).val(today);

    // Initialize the table
    populateTable();
    //paginateTable();
 

    // Populate the table
    function populateTable(page = 1) {
        const section = $('#requestTableBody').data('section');

        const status = $('#statusFilter').val();
        const FromdateRange = $('#fromDateFilter').val();
        const TodateRange = $('#toDateFilter').val();
        const searchQuery = $('#searchInput').val().toLowerCase();
        
        console.log(status, FromdateRange, TodateRange, searchQuery);

        const filters = {
            from: FromdateRange,
            to: TodateRange,
            status: status,
            search: searchQuery
        };

        const $tbody = $('#requestTableBody');
        $tbody.empty(); // Clear existing rows
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
            data: { action: 'get_itemsbycontrolnumber', section: section, filters: filters, page: page },
            dataType: 'json',
            success: function (response) {
                $tbody.empty(); // Clear loading spinner

                //console.log('Response from server:', response);
                //console.log(response.total, response.perPage);
                if (response.status === 'success') {
                    response.data.forEach(item => {

                        // Determine the status badge class based on the requestor_status
                        const statusClasses = {
                            'Approved': 'badge-approved',
                            'Pending': 'badge-pending',
                            'Rejected': 'badge-rejected',
                            'Hold': 'badge-hold'
                        };

                        const isHold = (item.requestor_status === 'Hold');

                        const statusBadge = `<span class="status-badge ${statusClasses[item.requestor_status] || ''}">${item.requestor_status}</span>`;

                        // Always show this
                        const itemsButton = `<button class="btn btn-sm btn-primary rounded-4 me-3" data-bs-toggle="modal" data-bs-target="#itemsRfqModal" data-id=${item.control_number} id="itemview_btn">
                            <i class="bi bi-card-checklist"></i> View Items
                        </button>`;

                        let  viewcomparisonButton = '';

                        if (!isHold) {

                            viewcomparisonButton = `<button class="btn btn-sm rounded-4 btn-outline-secondary me-3" data-bs-toggle="modal" data-bs-target="#comparisonTableModal" data-section=${item.item_section} data-id=${item.control_number} id="view_comparison_btn">
                                <i class="bi bi-eye"></i> View Comparison
                            </button>`;
                        }
                        const buttonGroup = `
                            ${itemsButton}
                            ${viewcomparisonButton}
                        `;

                        const $row = $(`
                            <tr>
                                <td>${item.control_number}</td>
                                <td>${statusBadge}</td>
                                <td>${item.item_remarks}</td>
                                <td>${item.created_at}</td>
                                <td>${item.item_section}</td>
                                <td>
                                    ${buttonGroup}
                                </td>
                            </tr>
                        `);

                        if (item.item_remarks == 'Comparison created' && item.item_remarks != 'Completed') {
                            $tbody.append($row);
                        }
                    });
                    const totalPages = Math.ceil(response.total / response.perPage);
                    paginateTable(page, totalPages);
                } else {
                    console.error('Error fetching items:', response.message);
                    const $row = $(`
                        <tr>
                            <td colspan="10" class="text-center">No items found.</td>
                        </tr>
                    `);
                    $tbody.append($row);
                }
            },
            error: function (xhr, status, error) {
                $tbody.empty(); // Clear loading spinner
                console.error('AJAX error:', status, error);
                const $tbody = $('#requestTableBody');
                $tbody.empty(); // Clear existing rows
                const $row = $(`
                    <tr>
                        <td colspan="9" class="text-center">Error fetching items.</td>
                    </tr>
                `);
                $tbody.append($row);
            }
        });
    }

    // Pagination function
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
                populateTable(i);
            });

            $pagination.append($pageItem);
        }
    }

    //Populate Items
    function populateItems(controlNumber) {
        const $itemsTableBody = $('#itemsTableBody');
        $itemsTableBody.empty(); // Clear existing rows
        const loadingRow = $(`
            <tr>
                <td colspan="9" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `);
        $itemsTableBody.append(loadingRow);

        $.ajax({
            url: './action.php',
            type: 'POST',
            data: { action: 'get_single_items', control_number: controlNumber },
            dataType: 'json',
            success: function (response) {
                $itemsTableBody.empty(); // Clear loading spinner

                console.log('Response from server:', response);
                if (response.status === 'success') {
                    response.data.forEach(item => {
                        const viewButton = `<button class="btn btn-sm btn-secondary me-3" data-bs-toggle="modal" data-bs-target="#attachmentRfqModal" data-id=${item.id} id="view_btn">
                                                <i class="bi bi-eye"></i>
                                            </button>`;
                        const $row = $(`
                            <tr>
                                <td>${item.item_name}</td>
                                <td>${item.item_purpose}</td>
                                <td>${item.item_description}</td>
                                <td>${item.item_quantity}</td>
                                <td>${item.item_unit}</td>
                                <td>
                                    ${viewButton}
                                </td>
                            </tr>
                        `);

                        $itemsTableBody.append($row);
                    });
                } else {
                    console.error('Error fetching items:', response.message);
                    const $row = $(`
                        <tr>
                            <td colspan="10" class="text-center">No items found.</td>
                        </tr>
                    `);
                    $itemsTableBody.append($row);
                }
            },
            error: function (xhr, status, error) {
                $itemsTableBody.empty(); // Clear loading spinner
                console.error('AJAX error:', status, error);
                const $tbody = $('#itemsTableBody');
                $tbody.empty(); // Clear existing rows
                const $row = $(`
                    <tr>
                        <td colspan="9" class="text-center">Error fetching items.</td>
                    </tr>
                `);
                $tbody.append($row);
            }
        });
    };

    function populateComparisonModalTable(controlNumber) {
        const $comparisonTableBody = $('#comparisonModalTableBody');
        $comparisonTableBody.empty();

        const loadingRow = $(`
            <tr>
                <td colspan="5" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `);

        $comparisonTableBody.append(loadingRow);

        $.ajax({
            url: './action.php',
            type: 'POST',
            data: { action: 'get_comparison_items', control_number: controlNumber },
            dataType: 'json',
            success: function (response) {
                $comparisonTableBody.empty();

                if (response.status === 'success') {
                    response.data.forEach(item => {
                        const supplierRows = item.suppliers.map((supplier, index) => {
                            return `
                                <tr>
                                    ${index === 0 ? `<td rowspan="${item.suppliers.length}">${item.item_name}</td>` : ''}
                                    <td>${supplier.supplier_name}</td>
                                    <td>₱ ${parseFloat(supplier.item_price).toFixed(2)}</td>
                                    <td>₱ ${parseFloat(supplier.item_discount).toFixed(2)}</td>
                                    <td>₱ ${parseFloat(supplier.item_total).toFixed(2)}</td>
                                     ${index === 0 ? `<td rowspan="${item.suppliers.length}">${item.item_remarks}</td>` : ''}
                                </tr>
                            `;
                        }).join('');

                        $comparisonTableBody.append(supplierRows);
                    });
                } else {
                    const $row = $(`
                        <tr>
                            <td colspan="5" class="text-center">No items found.</td>
                        </tr>
                    `);
                    $comparisonTableBody.append($row);
                }
            },
            error: function () {
                $comparisonTableBody.empty();
                const $row = $(`
                    <tr>
                        <td colspan="5" class="text-center">Error fetching items.</td>
                    </tr>
                `);
                $comparisonTableBody.append($row);
            }
        });
    }

    // View Items button
    $('#requestTableBody').on('click', '#itemview_btn', function(){
        const controlNumber = $(this).data('id');
        populateItems(controlNumber);
        console.log('this is clicked');
    });

    $('#itemsTableBody').on('click', '#view_btn', function(){
        const itemId = $(this).data('id');
        console.log('this is clicked');
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: { action: 'get_item_details', id: itemId },
            dataType: 'json',
            success: function(response){
                console.log('Response from server:', response);
                if (response.status === 'success') {
                    const base64 = response.data.file_content;
                    const mimeType = response.data.file_type;
        
                    $('#attachment_viewer').attr('src', `data:${mimeType};base64,${base64}`);
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX error:', status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while fetching attachment.',
                });
            }
        });
    });

    // Reopen Item View Modal when Attachment Modal is closed
    $('#attachmentRfqModal').on('hidden.bs.modal', function () {
        $('#itemsRfqModal').modal('show');
    });

    // Apply Filters button
    $('#statusFilter').on('change', function() {
       populateTable();
    });


    $('#fromDateFilter, #toDateFilter').on('change', function () {
        populateTable();
    });
    

    $('#searchInput').on('input', function() {
        populateTable();
    });
        
    // Clear Filters button
    $('.btn-danger').on('click', function() {
        $('select').val('');
        $('#searchInput').val('');
        $('.rfq-table tbody tr').show();
        $('#from_date').val('');
        $('#to_date').val('');

        populateTable();
    });
    
    // Approve button
    $('#requestTableBody').on('click', '#approve_btn', function() {
        const controlNumber = $(this).data('id');
        const status = 'Pending';
        const section = $(this).data('section');
        const remarks = 'For Quotation';
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to verify this item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we verify the item.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: { action: 'verify_item', control_number: controlNumber, status: status, remarks: remarks, section: section },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire(
                                'Approved!',
                                response.message,
                                'success'
                            );
                            populateTable();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
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

    $('#requestTableBody').on('click', '#hold_btn', function() {
        const controlNumber = $(this).data('id');
        const section = $(this).data('section');
        const status = 'Hold';
        Swal.fire({
            title: 'Hold Item',
            text: "You want to hold this item? Please provide remarks.",
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
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we hold the item.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: { action: 'hold_item', control_number: controlNumber, remarks: result.value, status: status, section: section },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire(
                                'Hold!',
                                response.message,
                                'success'
                            );
                            populateTable();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
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

    $('#requestTableBody').on('click', '#view_comparison_btn', function() {
        const controlNumber = $(this).data('id');
        populateComparisonModalTable(controlNumber);
        $('#approvedbtn').data('id', controlNumber);
        $('#approvedbtn').data('section', $(this).data('section'));
        $('#holdbtn').data('id', controlNumber);
        $('#holdbtn').data('section', $(this).data('section'));
    });

    $('#approvedbtn').on('click', function() {
        const controlNumber = $(this).data('id');
        const status = 'Approved';
        const section = $(this).data('section');
        console.log(controlNumber, status, section);
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to approve this Comparison Sheet?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we approve the comparison.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: { action: 'approve_comparison', control_number: controlNumber, status: status, section: section },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire(
                                'Approved!',
                                response.message,
                                'success'
                            );
                            populateTable();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
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

    $('#holdbtn').on('click', function() {
        const controlNumber = $(this).data('id');
        const section = $(this).data('section');
        const status = 'Hold';
        
        Swal.fire({
            title: 'Hold Comparison',
            text: "You want to hold this Comparison Sheet? Please provide remarks.",
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
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we hold the comparison.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: { action: 'hold_comparison', control_number: controlNumber, remarks: result.value, status: status, section: section },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire(
                                'Hold!',
                                response.message,
                                'success'
                            );
                            populateTable();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
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
