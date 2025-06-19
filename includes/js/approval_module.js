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

                        const isQuotationOrRejected = (item.item_remarks == 'For Quotation' || item.requestor_status == 'Rejected');
                        const isHold = (item.requestor_status === 'Hold' || item.item_remarks != 'For Quotation');

                        const statusBadge = `<span class="status-badge ${statusClasses[item.requestor_status] || ''}">${item.requestor_status}</span>`;

                        // Always show this
                        const itemsButton = `<button class="btn btn-sm btn-primary rounded-4 me-3" data-bs-toggle="modal" data-bs-target="#itemsRfqModal" data-id=${item.control_number} id="itemview_btn">
                            <i class="bi bi-card-checklist"></i> View Items
                        </button>`;

                        let approvedButton = '', declinedButton = '', emailButton = '', createcompasisonButton = '', viewcomparisonButton = '';

                        if (!isQuotationOrRejected) {
                            approvedButton = `<button class="btn btn-sm btn-success rounded-4 me-3" data-id=${item.control_number} data-section=${item.item_section} id="approve_btn">
                                <i class="bi bi-check2-circle"></i> Verify
                            </button>`;

                            declinedButton = `<button class="btn btn-sm btn-danger rounded-4 me-3" data-id=${item.control_number} data-section=${item.item_section} id="hold_btn">
                                <i class="bi bi-slash-circle"></i> Hold
                            </button>`;
                        }

                        if (!isHold) {
                            emailButton = `<button class="btn btn-sm btn-secondary rounded-4 me-3" data-bs-toggle="modal" data-bs-target="#emailsupplier" data-section=${item.item_section} data-id=${item.control_number} id="email_btn">
                                <i class="bi bi-envelope"></i> Email Supplier
                            </button>`;

                            createcompasisonButton = `<button class="btn btn-sm rounded-4 btn-secondary me-3" data-bs-toggle="modal" data-bs-target="#comparisonModal" data-section=${item.item_section} data-id=${item.control_number} id="create_comparison_btn">
                                <i class="bi bi-file-earmark-text"></i> Create Comparison
                            </button>`;

                            viewcomparisonButton = `<button class="btn btn-sm rounded-4 btn-outline-secondary me-3" data-bs-toggle="modal" data-bs-target="#comparisonTableModal" data-section=${item.item_section} data-id=${item.control_number} id="view_comparison_btn">
                                <i class="bi bi-eye"></i> View Comparison
                            </button>`;
                        }
                        const buttonGroup = `
                            ${itemsButton}
                            ${approvedButton}
                            ${declinedButton}
                            ${emailButton}
                            ${createcompasisonButton}
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

                        if (item.item_remarks != 'For Section head approval' && item.item_remarks != 'Declined by Section Head' && item.item_remarks != 'Completed') {
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

    $('#requestTableBody').on('click', '#email_btn', function() {
        const controlNumber = $(this).data('id');
        const section = $('#requestTableBody').data('section');
        $('#emailForm').data('id', controlNumber); // Store control number in the form
        $('#emailForm').data('section', section); // Store section in the form
        $('#emailsupplier').modal('show'); // Show the email modal
    });

    // Email Supplier button
    $('#emailForm').on('submit', function(e) {
        e.preventDefault();
        const controlNumber = $(this).data('id'); // Get control number from the form
        const section = $(this).data('section'); // Get section from the form
        console.log('Control Number:', controlNumber);
        
        const formData = {
        action: 'send_email_to_supplier',
        recipients: $("input[name='recipients[]']").map(function () {
            return $(this).val().trim();
        }).get().filter(email => email !== ''),
        ccs: $("input[name='ccs[]']").map(function () {
            return $(this).val().trim();
        }).get().filter(email => email !== ''),
        bccs: $("input[name='bccs[]']").map(function () {
            return $(this).val().trim();
        }).get().filter(email => email !== ''),
        control_number: controlNumber,
        section: section
         // If you set this dynamically
        };

        console.log('Form Data:', formData);
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to send email?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, send it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we email the supplier.',
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
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.close();
                            Swal.fire(
                                'Email Sent!',
                                response.message,
                                'success'
                            );
                            $('#emailsupplier').modal('hide'); // Hide the modal
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
                            text: 'An error occurred while sending the email.',
                        });
                    }
                });
            }
            }
        );
       
    });

    //
    $('#requestTableBody').on('click', '#create_comparison_btn', function() {
        const controlNumber = $(this).data('id');
        const section = $(this).data('section');
        $('#comparisonModal').data('id', controlNumber); // Store control number in the modal
        $('#comparisonModal').data('section', section); // Store section in the modal

        // Replace the h5 content with control number and section using the id of the tag
        $('#controlnumber').text(`Control Number: ${controlNumber}`);
        $('#section').text(`Section: ${section}`);

        $('#comparisonModal').modal('show'); // Show the comparison modal
    });

    // Populate the comparison table inside the comparison modal
    function populateComparisonTable(controlNumber) {
        const $comparisonTableBody = $('#itemDiv');
        $comparisonTableBody.empty(); // Clear existing rows

        const loadingRow = $(`
            <div class="text-center my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $comparisonTableBody.append(loadingRow);

        // Get the items for this control number, but do not fetch supplier data
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: { action: 'get_items_for_comparison', control_number: controlNumber },
            dataType: 'json',
            success: function(response) {
                $comparisonTableBody.empty(); // Clear loading spinner

                if (response.status === 'success' && Array.isArray(response.data)) {
                    if (response.data.length === 0) {
                        $comparisonTableBody.append(`
                            <div class="text-center">No items found for comparison.</div>
                        `);
                        return;
                    }

                    response.data.forEach((item, idx) => {
                        // Render item card with empty supplier rows for user input
                        $comparisonTableBody.append(`
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong>Item Name:</strong> ${item.item_name} <br>
                                    <strong>Description:</strong> ${item.item_description} <br>
                                    <strong>Quantity:</strong> ${item.item_quantity}
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" id="itemtable" data-itemname="${item.item_name}" data-itemid="${item.quantity}">
                                        <input type="hidden" name="item_name[${idx}]" value="${item.item_name}">   
                                        <input type="hidden" name="item_quantity[${idx}]" value="${item.item_quantity}"> 
                                        <thead>
                                                <tr>
                                                    <th>Supplier</th>
                                                    <th>Name</th>
                                                    <th>Price Per Unit</th>
                                                    <th>Discount</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="comparisonTableBody_${idx}">
                                                <tr>
                                                    <td>Supplier 1</td>
                                                    
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" name="supplier_name[${idx}][]" placeholder="Supplier Name" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm price-input" name="item_price[${idx}][]" placeholder="Price" required min="0" step="any">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm discount-input" name="item_discount[${idx}][]" placeholder="Discount" min="0" step="any">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm total-input" name="item_total[${idx}][]" placeholder="Total" readonly tabindex="-1">
                                                    </td>
                                                    <td>
                                                        <!-- Remove button hidden for first row -->
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7" class="text-end">
                                                        <button type="button" class="btn btn-primary btn-sm add-supplier" data-item-idx="${idx}"><i class="bi bi-plus-circle-fill"></i> Add Supplier</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `);

                        // Attach event handler for price/discount calculation after DOM insertion
                        setTimeout(() => {
                            $(`#comparisonTableBody_${idx}`).off('input', '.price-input, .discount-input').on('input', '.price-input, .discount-input', function () {
                                const $row = $(this).closest('tr');
                                const price = parseFloat($row.find('.price-input').val()) || 0;
                                const discount = parseFloat($row.find('.discount-input').val()) || 0;
                                const Quantity = parseFloat(item.item_quantity) || 0; // Use the item's quantity
                                const total = (price * Quantity) - discount;
                                $row.find('.total-input').val(total >= 0 ? total : 0);
                            });
                        }, 0);
                    });
                } else {
                    $comparisonTableBody.append(`
                        <div class="text-center">No items found for comparison.</div>
                    `);
                }
            },
            error: function() {
                $comparisonTableBody.empty();
                $comparisonTableBody.append(`
                    <div class="text-center text-danger">Error fetching items for comparison.</div>
                `);
            }
        });
    }

    // Show comparison modal and populate table when button is clicked
    $('#requestTableBody').on('click', '#create_comparison_btn', function() {
        const controlNumber = $(this).data('id');
        const section = $(this).data('section');
        $('#comparisonModal').data('id', controlNumber);
        $('#comparisonModal').data('section', section);
        $('#controlnumber').text(`Control Number: ${controlNumber}`);
        $('#section').text(`Section: ${section}`);
        populateComparisonTable(controlNumber);
        $('#comparisonModal').modal('show');
    });

    $('#requestTableBody').on('click', '#view_comparison_btn', function() {
        const controlNumber = $(this).data('id');
        $('#deletebtn').data('id', controlNumber);
    });

    $('#deletebtn').on('click', function() {
        const controlNumber = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this comparison?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we delete the comparison.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: { action: 'delete_comparison', control_number: controlNumber },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );
                            $('#comparisonTableModal').modal('hide'); // Hide the modal
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
                            text: 'An error occurred while deleting the comparison.',
                        });
                    }
                });
            }
        });
    });

    // Handle dynamic add/remove supplier rows in the modal
    $(document).on('click', '.add-supplier', function () {
        const itemIdx = $(this).data('item-idx');
        const $tbody = $(`#comparisonTableBody_${itemIdx}`);
        // Insert before the last row (which is the add button row)
        const $addRow = $tbody.find('tr').last();
        const supplierCount = $tbody.find('tr').length - 1; // Exclude add button row
        const newRow = `
            <tr>
                <td>Supplier ${supplierCount + 1}</td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="supplier_name[${itemIdx}][]" placeholder="Supplier Name" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm price-input" name="item_price[${itemIdx}][]" placeholder="Price" required min="0" step="any">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm discount-input" name="item_discount[${itemIdx}][]" placeholder="Discount" min="0" step="any">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm total-input" name="item_total[${itemIdx}][]" placeholder="Total" readonly tabindex="-1">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-supplier" data-item-idx="${itemIdx}"><i class="bi bi-trash-fill"></i> Remove</button>
                </td>
            </tr>
        `;
        $addRow.before(newRow);
        // Attach input event handler for the new row
        $tbody.find('tr').eq(-2).find('.price-input, .discount-input').on('input', function () {
            const $row = $(this).closest('tr');
            const Quantity = parseFloat($row.find('.item_quantity').val()) || 0;
            const price = parseFloat($row.find('.price-input').val()) || 0;
            const discount = parseFloat($row.find('.discount-input').val()) || 0;
            const total = (price * Quantity) - discount;
            // console.log('Price:', price, 'Quantity:', Quantity, 'Discount:', discount, 'Total:', total);
            $row.find('.total-input').val(total >= 0 ? total : 0);
        });
    });

    $(document).on('click', '.remove-supplier', function () {
        $(this).closest('tr').remove();
    });

    function createInput(name) {
        return `
            <div class="input-group mb-2">
                <input type="email" name="${name}" class="form-control" placeholder="Enter email">
                <button class="btn btn-outline-secondary remove-btn" type="button">Remove</button>
            </div>
        `;
    }

    $('#add-recipient').on('click', function () {
        $('#recipients-group').append(createInput('recipients[]'));
    });

    $('#add-cc').on('click', function () {
        $('#ccs-group').append(createInput('ccs[]'));
    });

    $('#add-bcc').on('click', function () {
        $('#bccs-group').append(createInput('bccs[]'));
    });

    // Handle dynamic removal
    $(document).on('click', '.remove-btn', function () {
        $(this).closest('.input-group').remove();
    })

    $('#comparisonForm').on('submit', function(e) {
        e.preventDefault();
        const controlNumber = $('#comparisonModal').data('id'); // Get control number from the modal
        const section = $('#comparisonModal').data('section'); // Get section from the modal
        // const quantity = $('#itemtable').data('quantity'); // Get quantity from the modal
        // const itemName = $('#itemtable').data('itemname'); // Get item name from the modal
        const formData = $(this).serialize() + `&action=create_comparison&control_number=${controlNumber}&section=${section}`;
        
        console.log('Form Data:', formData); // Debugging line to check form data
        console.log($(this).serializeArray()); // Debugging line to check serialized form data
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to create comparison?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create it!'
        }).then((result) => {
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
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire(
                                'Comparison Created!',
                                response.message,
                                'success'
                            );
                            $('#comparisonModal').modal('hide'); // Hide the modal
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
                            text: 'An error occurred while creating the comparison.',
                        });
                    }
                });
            }
        });
    });

    // Populate Comparison Modal Table on modal show
    $('#requestTableBody').on('click', '#view_comparison_btn', function() {
        const controlNumber = $(this).data('id');
        populateComparisonModalTable(controlNumber);
        console.log('Comparison button clicked for control number:', controlNumber);
    });
});
