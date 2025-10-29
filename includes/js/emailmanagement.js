$(document).ready(function () {
  const page = 1;

  RenderEmailTable();

  function RenderEmailTable(page = 1) {
    const searchInput = $("#searchInput").val().toLowerCase();
    const $tbody = $("#emailTableBody");
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
      url: "./admin_action.php",
      type: "POST",
      data: {
        action: "get_email",
        searchinput: searchInput,
        page: page,
      },
      dataType: "json",
      success: function (response) {
        $tbody.empty();
        if (response.status == "success") {
          response.data.forEach((item) => {
            const editbtn = `<button class="btn btn-sm btn-primary me-2" id="editbtn" data-id='${item.id}' data-bs-toggle="modal" data-bs-target="#editEmailModal">
                                        <i class="bi bi-pencil-square"></i> Edit
                                        </button>`;
            const deletebtn = `<button class="btn btn-sm btn-danger me-2" id="deletebtn" data-id='${item.id}'>
                                        <i class="bi bi-trash"></i> Delete
                                        </button>`;
            const $row = $(`
                                <tr>
                                    <td>${item.id}</td>
                                    <td class="name">${item.name}</td>
                                    <td class="email">${item.emailadd}</td>
                                    <td class="department">${item.department}</td>
                                    <td>
                                        ${editbtn}
                                        ${deletebtn}
                                    </td>
                                </tr>
                            `);
            $tbody.append($row);
          });
          const totalPages = Math.ceil(response.total / response.perPage);
          paginateTable(page, totalPages);
        } else {
          console.error("Error fetching items:", response.message);
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
        console.error("AJAX error:", status, error);
        const $tbody = $("#emailTableBody");
        $tbody.empty(); // Clear existing rows
        const $row = $(`
                    <tr>
                        <td colspan="9" class="text-center">Error fetching items.</td>
                    </tr>
                `);
        $tbody.append($row);
      },
    });
  }

  function paginateTable(currentPage, totalPages) {
    const $pagination = $("#pagination");
    $pagination.empty();

    for (let i = 1; i <= totalPages; i++) {
      const $pageItem = $(`
                <li class="page-item ${i === currentPage ? "active" : ""}">
                    <a class="page-link" href="#">${i}</a>
                </li>
            `);

      $pageItem.on("click", function (e) {
        e.preventDefault();
        RenderEmailTable(i);
      });

      $pagination.append($pageItem);
    }
  }

  //Search filtering
  $("#searchButton").on("click", function () {
    RenderEmailTable();
  });

  //Search filtering based on input
  $("#searchInput").on("input", function () {
    RenderEmailTable();
  });

  $("#emailaddress").on("input", function () {
    // ✅ check email format separately (not inside loop)
    const email = $("#emailaddress").val()?.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email && !emailPattern.test(email)) {
      $("#emailaddress").addClass("is-invalid");
      showAlert("⚠️ Please enter a valid email address!", "danger");
    }

    if (email && emailPattern.test(email)) {
      $("#emailaddress").removeClass("is-invalid").addClass("is-valid");
    }
  });

  $("#emailadd").on("input", function () {
    // ✅ check email format separately (not inside loop)
    const email = $("#emailadd").val()?.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    // console.log(email);
    if (email && !emailPattern.test(email)) {
      $("#emailadd").addClass("is-invalid");
      showAlert("⚠️ Please enter a valid email address!", "danger");
    }

    if (email && emailPattern.test(email)) {
      $("#emailadd").removeClass("is-invalid").addClass("is-valid");
    }
  });

  $("#createemailForm").submit(function (e) {
    e.preventDefault();

    let isValid = true;
    const $alert = $("#formAlert");

    // reset validation classes first
    $("#name, #department, #emailaddress").removeClass("is-valid is-invalid");

    // loop through required fields
    $("#name, #department, #emailaddress").each(function () {
      const value = $(this).val()?.trim();

      if (!value) {
        $(this).addClass("is-invalid").fadeOut(100).fadeIn(100);
        isValid = false;
      } else {
        $(this).addClass("is-valid");
      }
    });

    // ✅ check email format separately (not inside loop)
    const email = $("#emailaddress").val()?.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email && !emailPattern.test(email)) {
      $("#emailaddress")
        .removeClass("is-valid")
        .addClass("is-invalid")
        .fadeOut(100)
        .fadeIn(100);
      showAlert("⚠️ Please enter a valid email address!", "danger");
      return;
    }

    // 🚨 show alert if any field invalid
    if (!isValid) {
      showAlert("⚠️ Please fill in all required fields!", "danger");
      return;
    }

    // ✅ success message
    showAlert("✅ Form submitted successfully!", "success");

    // Hide any existing alert smoothly before showing a new one
    function showAlert(message, type) {
      const $alert = $("#formAlert");
      $alert
        .stop(true, true)
        .removeClass("d-none alert-success alert-danger")
        .addClass(`alert-${type}`)
        .html(message)
        .fadeIn(300)
        .delay(3000)
        .fadeOut(500);
    }

    const formData = new FormData($("#createemailForm")[0]);
    formData.append("action", "create_email");

    $.ajax({
      url: "./admin_action.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      beforeSend: function () {
        Swal.fire({
          title: "Please wait...",
          text: "Creating Email",
          allowOutsideClick: false,
          showConfirmButton: false,
          willOpen: () => {
            Swal.showLoading();
          },
        });
      },
      success: function (response) {
        console.log(response);
        Swal.close();
        if (response.status == "success") {
          Swal.fire({
            icon: "success",
            title: "Email Created",
            text: response.message,
            showConfirmButton: false,
            timer: 1500,
          }).then(() => {
            // ✅ Reset form fields and remove validation styles
            $("#createemailForm")[0].reset();
            $(
              "#createemailForm .is-valid, #createemailForm .is-invalid"
            ).removeClass("is-valid is-invalid");

            // ✅ Close modal after a short delay (optional animation effect)
            setTimeout(() => {
              const modal = bootstrap.Modal.getInstance(
                document.getElementById("createemailmodal")
              );
              modal.hide();
            }, 800);
            RenderEmailTable();
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.message,
          });
        }
      },
      error: function (xhr, status, error) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "An error occurred while submitting the request.",
        });
      },
    });
  });

  $("#editemailForm").submit(function (e) {
    e.preventDefault();
    let isValid = true;
    console.log("this is click");
    const $alert = $("#formAlert");

    // reset validation classes first
    $("#editname, #editdepartment, #emailadd").removeClass(
      "is-valid is-invalid"
    );

    // loop through required fields
    $("#editname, #editdepartment, #emailadd").each(function () {
      const value = $(this).val()?.trim();

      if (!value) {
        $(this).addClass("is-invalid").fadeOut(100).fadeIn(100);
        isValid = false;
      } else {
        $(this).addClass("is-valid");
      }
    });

    // ✅ check email format separately (not inside loop)
    const email = $("#emailadd").val()?.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email && !emailPattern.test(email)) {
      $("#emailadd")
        .removeClass("is-valid")
        .addClass("is-invalid")
        .fadeOut(100)
        .fadeIn(100);
      showAlert("⚠️ Please enter a valid email address!", "danger");
      return;
    }

    // 🚨 show alert if any field invalid
    if (!isValid) {
      showAlert("⚠️ Please fill in all required fields!", "danger");
      return;
    }

    // ✅ success message
    showAlert("✅ Form submitted successfully!", "success");

    const formData = new FormData($("#editemailForm")[0]);
    formData.append("action", "edit_email");

    $.ajax({
      url: "./admin_action.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      beforeSend: function () {
        Swal.fire({
          title: "Please wait...",
          text: "Updating Email",
          allowOutsideClick: false,
          showConfirmButton: false,
          willOpen: () => {
            Swal.showLoading();
          },
        });
      },
      success: function (response) {
        console.log(response);
        Swal.close();
        if (response.status == "success") {
          Swal.fire({
            icon: "success",
            title: "Email Created",
            text: response.message,
            showConfirmButton: false,
            timer: 1500,
          }).then(() => {
            // ✅ Reset form fields and remove validation styles
            $("#editemailForm")[0].reset();
            $(
              "#editemailForm .is-valid, #editemailForm .is-invalid"
            ).removeClass("is-valid is-invalid");

            // ✅ Close modal after a short delay (optional animation effect)
            setTimeout(() => {
              const modal = bootstrap.Modal.getInstance(
                document.getElementById("editEmailModal")
              );
              modal.hide();
            }, 800);
            RenderEmailTable();
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.message,
          });
        }
      },
      error: function (xhr, status, error) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "An error occurred while submitting the request.",
        });
      },
    });
  });

  // Hide any existing alert smoothly before showing a new one
  function showAlert(message, type) {
    const $alert = $("#formAlert");
    $alert
      .stop(true, true)
      .removeClass("d-none alert-success alert-danger")
      .addClass(`alert-${type}`)
      .html(message)
      .fadeIn(300)
      .delay(3000)
      .fadeOut(500);
  }

  function populateSelectOption() {
    $.ajax({
      url: "./admin_action.php",
      method: "POST",
      data: {
        action: "get_option",
      },
      dataType: "json",
      success: function (response) {
        if (response.status == "success") {
          console.log(response.access);

          var $select = $("#department");
          $select.empty();
          $select.append('<option value="">Select Department</option>');
          $.each(response.data, function (index, item) {
            $select.append(
              $("<option>", {
                value: item.department,
                text: item.department,
              })
            );
          });

          var $roleselect = $("#name");
          $roleselect.empty();
          $roleselect.append('<option value="">Select User Name</option>');
          $.each(response.access, function (index, itemname) {
            $roleselect.append(
              $("<option>", {
                value: itemname.name,
                text: itemname.name,
              })
            );
          });

          var $select = $("#editdepartment");
          $select.empty();
          $select.append('<option value="">Select Department</option>');
          $.each(response.data, function (index, item) {
            $select.append(
              $("<option>", {
                value: item.department,
                text: item.department,
              })
            );
          });

          var $roleselect = $("#editname");
          $roleselect.empty();
          $roleselect.append('<option value="">Select User Name</option>');
          $.each(response.access, function (index, itemrole) {
            $roleselect.append(
              $("<option>", {
                value: itemrole.name,
                text: itemrole.name,
              })
            );
          });
        }
      },
      error: function () {
        alert("Failed to load departments.");
      },
    });
  }

  //Delete user account
  $("#emailTableBody").on("click", "#deletebtn", function () {
    const id = $(this).data("id");

    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, delete it!",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "./admin_action.php",
          type: "POST",
          data: {
            id: id,
            action: "delete_email",
          },
          dataType: "json",
          success: function (response) {
            if (response.status == "success") {
              Swal.fire({
                icon: "success",
                title: "Delete Email",
                text: response.message,
                showConfirmButton: false,
                timer: 1500,
              }).then(() => {
                RenderEmailTable();
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: response.message,
              });
            }
          },
          error: function (xhr, status, error) {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred while submitting the request.",
            });
          },
        });
      }
    });
  });

  populateSelectOption();

  $("#emailTableBody").on("click", "#editbtn", function () {
    const id = $(this).data("id");
    const $row = $(this).closest("tr");
    const $name = $row.find(".name").text().trim();
    const $email = $row.find(".email").text().trim();
    const $department = $row.find(".department").text().trim();
    console.log($name, $email, $department);
    $("#editname").val($name);
    $("#editdepartment").val($department);
    $("#emailadd").val($email);
    $("#id").val(id);
  });
});
