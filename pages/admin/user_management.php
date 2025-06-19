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
    <h2 class="mb-0">Account List</h2>
    <button class="btn btn-primary" data-bs-toggle = "modal" data-bs-target="#createusermodal">
        <i class="bi bi-plus-circle me-2"></i> Add New User
    </button>
</div>

<!--User Table-->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="col-md-3 col-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Enter user id, name, or department">
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Access Level</th>
                        <th>Created at</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="userTableBody"></tbody>
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
<div class="modal fade" id="createusermodal" tabindex="-1" aria-labelledby="createusermodalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="createusermodalLabel">Create New Account</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
        <form action="#" id="createuserForm">
            <div class="modal-body">
                 <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="username" aria-label="" aria-describedby="visible-addon">
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="text" class="form-control" name="password" placeholder="Password" aria-describedby="visible-addon">
                        </div>
                    </div>
                 </div>
                 <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-buildings"></i></span>
                            <select class="form-select" name="department" id="department">
                               
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <select class="form-select" name="accesslevel" id="access">
                                <option selected>Access Level</option>
                            </select>
                        </div>
                    </div>
                 </div>
                  <div class="row">
                    <div class="col-12">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                        <input type="text" class="form-control" name="name" placeholder="Employee Name" aria-describedby="visible-addon">
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

<!--Edit user Modal-->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="editUserModalLabel">Edit User Account</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
        <form action="#" id="edituserForm">
            <div class="modal-body">
                 <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" name="username" id="username_id" placeholder="username" aria-label="" aria-describedby="visible-addon">
                        </div>
                    </div>
                    <!-- <div class="col-md-6 mb-4">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="text" class="form-control" name="password" placeholder="Password" aria-describedby="visible-addon">
                        </div>
                    </div> -->
                 </div>
                 <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-buildings"></i></span>
                            <select class="form-select" name="department" id="editdepartment">
                               
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <select class="form-select" name="accesslevel" id="editaccess">
                                <option selected>Access Level</option>
                            </select>
                        </div>
                    </div>
                 </div>
                  <div class="row">
                    <div class="col-12">
                        <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Employee Name" aria-describedby="visible-addon">
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

<script src="./includes/js/usermanagement.js"></script>

