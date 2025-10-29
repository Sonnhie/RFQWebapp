<?php
session_start();
file_put_contents('debug.log', "Reached file\n", FILE_APPEND);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Welcome,
            <?php
            if ($_SESSION['user']['access_level'] == 'Admin') {
                echo 'Administrator';
            } else {
                echo 'Client';
            }
            ?>
        </h5>
        <div class="d-flex align-items-center">
            <img src="./assets/img/profile.png" alt="User Profile" class="rounded-circle" width="40" height="40">
            <span class="ms-2 fw-semibold">
                <?php
                echo htmlspecialchars($_SESSION['user']['name']);
                ?>
            </span>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Email Account List</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createemailmodal">
        <i class="bi bi-plus-circle me-2"></i> Add Email Account
    </button>
</div>


<!--User Table-->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="col-md-5 col-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Enter user id, name, or department">
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" id="Table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Email Address</th>
                        <th>Department</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="emailTableBody"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-center">
                            <nav aria-label="Page navigation">
                                <ul class="pagination" id="pagination">

                                </ul>
                            </nav>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!--Add user Modal-->
<div class="modal fade" id="createemailmodal" tabindex="-1" aria-labelledby="createusermodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createemailmodalLabel">Create New Email Account</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Alert Message -->
            <div id="formAlert" class="alert d-none mx-3 mt-3" role="alert"></div>

            <!-- Form -->
            <form action="#" id="createemailForm" novalidate>
                <div class="modal-body">

                    <!-- Employee Name -->
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                            <select class="form-select" name="name" id="name" required>
                                <option value="">Select Employee Name</option>
                            </select>
                        </div>
                        <div class="invalid-feedback">Please select an employee name.</div>
                    </div>

                    <!-- Department -->
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-buildings"></i></span>
                            <select class="form-select" name="department" id="department" required>
                                <option value="">Select Department</option>
                            </select>
                        </div>
                        <div class="invalid-feedback">Please select a department.</div>
                    </div>

                    <!-- Email Address -->
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" name="emailaddress" id="emailaddress" placeholder="Email Address" required>
                        </div>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!--Edit user Modal-->
<div class="modal fade" id="editEmailModal" tabindex="-1" aria-labelledby="editEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editEmailModalLabel">Edit Email Account</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Alert Message -->
            <div id="formAlert" class="alert d-none mx-3 mt-3" role="alert"></div>
            <form action="#" id="editemailForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                <select class="form-select" name="name" id="editname">
                                    <!-- <option selected>Select Employee Name</option> -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-buildings"></i></span>
                                <select class="form-select" name="department" id="editdepartment">
                                    <!-- <option selected>Select Department</option> -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="text" class="form-control" name="emailaddress" id="emailadd" placeholder="Email Address" aria-describedby="visible-addon">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="./includes/js/emailmanagement.js"></script>