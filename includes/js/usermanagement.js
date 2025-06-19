$(document).ready(function(){

    populateUserTable();
    //Populate select button
    function populateDepartmentOption() {
        $.ajax({
            url: './admin_action.php',
            method: 'POST',
            data: {
                action: 'get_userlist'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    // console.log(response.access);
                    var $select = $('#department');
                    $select.empty();
                    $select.append('<option value="">Select Department</option>');
                    $.each(response.data, function(index, item) {
                        $select.append(
                            $('<option>', {
                                value: item.department,
                                text: item.department
                            })
                        );
                    });

                    var $roleselect = $('#access');
                    $roleselect.empty();
                    $roleselect.append('<option value="">Select Access Control</option>');
                    $.each(response.access,function(index, itemrole){
                        $roleselect.append(
                            $('<option>',{
                                value: itemrole.role,
                                text: itemrole.access_level
                            })
                        );
                    });

                    var $select = $('#editdepartment');
                    $select.empty();
                    $select.append('<option value="">Select Department</option>');
                    $.each(response.data, function(index, item) {
                        $select.append(
                            $('<option>', {
                                value: item.department,
                                text: item.department
                            })
                        );
                    });

                    var $roleselect = $('#editaccess');
                    $roleselect.empty();
                    $roleselect.append('<option value="">Select Access Control</option>');
                    $.each(response.access,function(index, itemrole){
                        $roleselect.append(
                            $('<option>',{
                                value: itemrole.role,
                                text: itemrole.access_level
                            })
                        );
                    });
                }
            },
            error: function() {
                alert('Failed to load departments.');
            }
        });
    }

    //Populate usertable
    function populateUserTable(page = 1){
        const searchInput = $('#searchInput').val().toLowerCase();
        const $tbody = $('#userTableBody');
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
            url: './admin_action.php',
            type: 'POST',
            data: {
                action: 'get_user',
                searchinput: searchInput,
                page: page
            },
            dataType: 'json',
            success: function(response){
                $tbody.empty();
                if (response.status == 'success') {
                    response.data.forEach(item => {

                        const editbtn = `<button class="btn btn-sm btn-primary me-2" id="editbtn" data-id='${item.id}' data-bs-toggle="modal" data-bs-target="#editUserModal">
                                        <i class="bi bi-pencil-square"></i> Edit
                                        </button>`;
                        const deletebtn = `<button class="btn btn-sm btn-danger me-2" id="deletebtn" data-id='${item.id}'>
                                        <i class="bi bi-trash"></i> Delete
                                        </button>`;
                        const resetbtn = `<button class="btn btn-sm btn-secondary me-2" id="resetbtn" data-id='${item.id}'>
                                        <i class="bi bi-arrow-repeat"></i> Reset Password
                                        </button>`;
                        const $row = $(`
                                <tr>
                                    <td class="id">${item.id}</td>
                                    <td class="username">${item.username}</td>
                                    <td class="name">${item.name}</td>
                                    <td class="department">${item.department}</td>
                                    <td class="access">${item.access_level}</td>
                                    <td>${item.createdat}</td>
                                    <td>
                                        ${editbtn}
                                        ${deletebtn}
                                        ${resetbtn}
                                    </td>
                                </tr>
                            `);
                             $tbody.append($row);
                    });
                    const totalPages = Math.ceil(response.total / response.perPage);
                    paginateTable(page, totalPages);
                }else{
                    console.error('Error fetching items:', response.message);
                    const $row = $(`
                        <tr>
                            <td colspan="10" class="text-center">No items found.</td>
                        </tr>
                    `);
                    $tbody.append($row);
                }
            },
            error: function (xhr, status, error){
                $tbody.empty(); // Clear loading spinner
                console.error('AJAX error:', status, error);
                const $tbody = $("#userTableBody");
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

    //Paginate table
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
                populateUserTable(i);
            });

            $pagination.append($pageItem);
        }
    }
    // Call this function when the modal is shown
    $('#createusermodal').on('shown.bs.modal', function () {
        populateDepartmentOption();
    });

    //Search filtering
    $('#searchButton').on('click', function(){
        populateUserTable();
    });

    //Search filtering based on input
    $('#searchInput').on('input', function(){
        populateUserTable();
    });

    //creating new user account
    $('#createuserForm').submit(function(e){
        e.preventDefault();

        const formData = new FormData($('#createuserForm')[0]);
        formData.append('action','create_user');
        
        //debugging
        // console.log(formData);

        $.ajax({
            url: './admin_action.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function(){
                Swal.fire({
                    title: 'Please wait...',
                    text: 'Creating User',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response){
                    // console.log(response);
                    Swal.close();
                    if (response.status == 'success') {
                         Swal.fire({
                            icon: 'success',
                            title: 'Account Created',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    }else {
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

    //Edit user account
    $('#userTableBody').on('click', '#editbtn', function(){
        populateDepartmentOption();
        const id = $(this).data('id');
        const $row = $(this).closest('tr');
        const $username = $row.find('.username').text().trim();
        const $department = $row.find('.department').text().trim();
        const $role = $row.find('.access').text().trim();
        const $name = $row.find('.name').text().trim();

        console.log(id, $username, $department, $role, $name);

        $('#username_id').val($username);
        $('#editdepartment').val($department);
        $('#name').val($name)
        $('#editUserModal').data('id', id);
    });

    //submit changes
    $('#edituserForm').submit(function(e){
        e.preventDefault();
        const id = $('#editUserModal').data('id');
        const formData = new FormData($('#edituserForm')[0]);
        formData.append('action', 'edit_user');
        formData.append('id', id);
        console.log(formData);

        $.ajax({
            url: './admin_action.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function(){
                Swal.fire({
                    title: 'Please wait...',
                    text: 'Updating User data',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response){
                    // console.log(response);
                    Swal.close();
                    if (response.status == 'success') {
                         Swal.fire({
                            icon: 'success',
                            title: 'Update user',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    }else {
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

    //Delete user account
    $('#userTableBody').on('click', '#deletebtn', function(){
        const id = $(this).data('id');

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
                 $.ajax({
                    url: './admin_action.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: 'delete_user'
                    },
                    dataType: 'json',
                    success: function(response){
                        if (response.status == 'success') {
                            Swal.fire({
                                    icon: 'success',
                                    title: 'Delete User',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload();
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
            }
        });  
    });

    //Reset Password
    $('#userTableBody').on('click', '#resetbtn', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, reset password!'
        }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: './admin_action.php',
                        type: 'POST',
                        data: {
                            id: id,
                            action: 'reset_pwd'
                        },
                        dataType: 'json',
                        success: function(response){
                            if (response.status == 'success') {
                                Swal.fire({
                                        icon: 'success',
                                        title: 'Password Reset',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        window.location.reload();
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
            }
        });
    });
});