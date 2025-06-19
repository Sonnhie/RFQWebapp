$(document).ready(function () {
    const page = 1;
   // const today = new Date().toISOString().split('T')[0]; // Format: YYYY-MM-DD
   // $('#fromDateFilter').attr('max', today).val(today);
    //$('#toDateFilter').attr('max', today).val(today);

    // Initialize the table
    populateTable();
    //paginateTable();
    // Add new item row
    $('#addItemButton').on('click', function () {
        const $tbody = $('#itemsTableBody');
        const $newRow = $(`
            <tr>
                <td><input type="text" class="form-control form-control-sm" name="item_name[]" placeholder="Item name"></td>
                <td><input type="text" class="form-control form-control-sm" name="item_description[]" placeholder="Description"></td>
                <td><input type="text" class="form-control" name="item_purpose[]" id="purpose" placeholder="Purchase purpose" required></td>
                <td><input type="number" class="form-control form-control-sm" name="item_quantity[]" placeholder="Qty"></td>
                <td>
                    <select class="form-select form-select-sm" name="item_unit[]">
                        <option value="Piece">Piece</option>
                        <option value="Box">Box</option>
                        <option value="Set">Set</option>
                        <option value="Gallon">Gallon</option>
                        <option value="Sack">Sack</option>
                    </select>
                </td>
                 <td><input class="form-control" type="file" id="attachment" name="item-attachment[]" required></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        $tbody.append($newRow);

        // Add delete event for this row
        $newRow.find('.btn-danger').on('click', function () {
            $newRow.remove();
        });
    });

    // Submit new request
    $('#create_request').submit(function (e) {
        e.preventDefault();

        const formData = new FormData($('#create_request')[0]);
        formData.append('action', 'create_request');
        formData.append('remarks', 'For Section head approval');

        //debugFormData(formData);

        // Ajax request to submit the form data
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
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
            success: function (response) {
                Swal.close();
                console.log('Response from server:', response);
                if (response.status === 'success') {
                    Swal.fire({
                    icon: 'success',
                    title: 'Request Created',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                    }).then(() => {
                    window.location.reload();
                    });
                } else {
                    Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    });
                }
            },
            error: function (xhr, status, error) {
            //console.error('AJAX error:', status, error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while submitting the request.',
            });
            }
        });
    });

    function debugFormData(formData) {
        console.log('Debugging initialized');
        console.log('Form submitted');
        console.log('Serialized form data:');
        for (const [key, value] of formData.entries()) {
            console.table(`${key}: ${value}`);
        }

        const attachment = $('input[name="item-attachment[]"]')[0];
        if (attachment.files.length === 0) {
            console.log('Attachment is null or empty');
        } else {
            console.log('Attachments:', attachment.files);
            for (let i = 0; i < attachment.files.length; i++) {
                console.table(attachment.files[i].name);
            }
        }
    }

    // Populate the table
    function populateTable(page = 1) {
        const section = $('#requestTableBody').data('section');
        console.log('Section:', section);
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
            data: { action: 'get_items', section: section, filters: filters, page: page },
            dataType: 'json',
            success: function (response) {
                $tbody.empty(); // Clear loading spinner

                console.log('Response from server:', response);
                console.log(response.total, response.perPage);
                if (response.status === 'success') {
                    response.data.forEach(item => {
                        const statusClasses = {
                            'Approved': 'badge-approved',
                            'Pending': 'badge-pending',
                            'Rejected': 'badge-rejected',
                             'Hold': 'badge-hold'
                        };

                        const isDisabled = (item.requestor_status === 'Approved' || item.requestor_status === 'Rejected');
                        const statusBadge = `<span class="status-badge ${statusClasses[item.requestor_status] || ''}">${item.requestor_status}</span>`;
                        const editButton = `<button class="btn btn-sm btn-secondary me-3" data-bs-toggle="modal" data-bs-target="#editRfqModal" data-id=${item.id} id="edit_btn" ${isDisabled ? 'disabled' : ''}>
                                                <i class="bi bi-pencil"></i>
                                            </button>`;
                        const deleteButton = `<button class="btn btn-sm btn-danger" data-id=${item.id} id="delete_btn" ${isDisabled && item.requestor_status !== 'Cancelled' ? 'disabled' : ''}>
                                                <i class="bi bi-trash"></i>
                                            </button>`;
                        const viewButton = `<button class="btn btn-sm btn-primary me-3" data-bs-toggle="modal" data-bs-target="#attachmentRfqModal" data-id=${item.id} id="view_btn">
                                                <i class="bi bi-eye"></i>
                                            </button>`;

                        const $row = $(`
                            <tr>
                                <td>${item.control_number}</td>
                                <td>${item.item_name}</td>
                                <td>${item.item_description}</td>
                                <td>${item.item_purpose}</td>
                                <td>${item.item_quantity}</td>
                                <td>${item.item_unit}</td>
                                <td>${statusBadge}</td>
                                <td>${item.item_remarks}</td>
                                <td>${item.created_at}</td>
                                <td>
                                    ${viewButton}
                                    ${editButton}
                                    ${deleteButton}
                                </td>
                            </tr>
                        `);

                        $tbody.append($row);
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

    function deleteItem(itemId) {

        // Ajax request to delete the item
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: { action: 'delete_item', id: itemId },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: response.message,
                    }).then(() => {
                        populateTable();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while deleting the item.',
                });
            }
        });
    }

    function editItem(formData) {
        // Ajax request to edit the item
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: response.message,
                    }).then(() => {
                        populateTable();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the item.',
                });
            }
        });

    }

    // Delete item button click event
    $('#requestTableBody').on('click', '#delete_btn', function () {
        const itemId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteItem(itemId);
            }
        });
    });

    $('#requestTableBody').on('click', '#edit_btn', function(){
        const itemId = $(this).data('id');
        const $row = $(this).closest('tr');
        const itemName = $row.find('td:eq(1)').text();
        const itemDescription = $row.find('td:eq(2)').text();
        const itemPurpose = $row.find('td:eq(3)').text();
        const itemQuantity = $row.find('td:eq(4)').text();
        const itemUnit = $row.find('td:eq(5)').text();
        console.log(itemId);
        $('#item_name').val(itemName);
        $('#item_description').val(itemDescription);
        $('#item_purpose').val(itemPurpose);
        $('#item_quantity').val(itemQuantity);
        $('#item_unit').val(itemUnit);

       $('#editRfqModal').data('itemId', itemId);
    });

    $('#edit_request').submit(function (e) {
        e.preventDefault();
        const itemId = $('#editRfqModal').data('itemId');
        const itemName = $('#item_name').val();
        //console.log(itemId);
        const formData = new FormData($('#edit_request')[0]);
        formData.append('action', 'edit_request');
        formData.append('item_id', itemId);
        
        debugFormData(formData);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save changes!'
        }).then((result) => {
            if (result.isConfirmed) {
                editItem(formData);
                $('#editRfqModal').modal('hide');
            }
        });
    });

    $('#requestTableBody').on('click', '#view_btn', function(){
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
    $('.btn-outline-danger').on('click', function() {
        $('select').val('');
        $('#searchInput').val('');
        $('.rfq-table tbody tr').show();
        $('#from_date').val('');
        $('#to_date').val('');

        populateTable();
    });
        
    // Export button
    $('.btn-outline-secondary').on('click', function() {
        alert('Export functionality would generate a CSV/PDF');
    });
});
