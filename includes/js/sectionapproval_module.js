$(document).ready(function () {
  const page = 1;
  // const today = new Date().toISOString().split('T')[0]; // Format: YYYY-MM-DD
  // $('#fromDateFilter').attr('max', today).val(today);
  //$('#toDateFilter').attr('max', today).val(today);
  const $name = $("#requestTableBody").data("name");
  const $role = $("#requestTableBody").data("role");
  console.log($name, $role);
  // Initialize the table
  populateTable();
  //paginateTable();

  // Populate the table
  function populateTable(page = 1) {
    const section = $("#requestTableBody").data("section");

    const status = $("#statusFilter").val();
    const FromdateRange = $("#fromDateFilter").val();
    const TodateRange = $("#toDateFilter").val();
    const searchQuery = $("#searchInput").val().toLowerCase();

    console.log(status, FromdateRange, TodateRange, searchQuery);

    const filters = {
      from: FromdateRange,
      to: TodateRange,
      status: status,
      search: searchQuery,
      role: $role,
    };

    const $tbody = $("#requestTableBody");
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
      url: "././backend/Route/requestRouteAction.php",
      type: "POST",
      data: {
        action: "get_sectionapproval",
        section: section,
        filters: filters,
        page: page,
      },
      dataType: "json",
      success: function (response) {
        $tbody.empty(); // Clear loading spinner

        console.log("Response from server:", response);
        console.log(response.total, response.perPage);
        if (response.status === "success") {
          response.data.forEach((item) => {
            const statusClasses = {
              Approved: "badge-approved",
              "On-going": "badge-pending",
              Rejected: "badge-rejected",
              Hold: "badge-hold",
            };

            const statusBadge = `<span class="status-badge ${
              statusClasses[item.requestor_status] || ""
            }">${item.requestor_status}</span>`;

            const itemsButton = `<button class="btn btn-sm btn-primary me-3" data-bs-toggle="modal" data-bs-target="#itemsRfqModal"  data-id=${item.control_number} id="itemview_btn">
                                            <i class="bi bi-card-checklist"></i> View Items
                                            </button>`;
            const approvedButton = `<button class="btn btn-sm btn-success me-3" data-id=${item.control_number} data-sectionrequest=${item.requestor_section} id="approve_btn">
                                            <i class="bi bi-check2"></i> Approve
                                            </button>`;
            const holdButton = `<button class="btn btn-sm btn-danger me-3" data-id=${item.control_number} data-sectionrequest=${item.requestor_section} id="decline_btn">
                                            <i class="bi bi-slash-circle"></i> Hold
                                            </button>`;

            const $row = $(`
                            <tr>
                                <td>${item.control_number}</td>
                                <td>${statusBadge}</td>
                                <td>${item.item_remarks}</td>
                                <td>${item.requestor_section}</td>
                                <td>${item.created_at}</td>
                                <td>
                                    ${itemsButton}
                                    ${approvedButton}
                                    ${holdButton}
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
        const $tbody = $("#requestTableBody");
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

  // Pagination function
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
        populateTable(i);
      });

      $pagination.append($pageItem);
    }
  }

  //Populate Items
  function populateItems(controlNumber) {
    const $itemsTableBody = $("#itemsTableBody");
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
      url: "././backend/Route/requestRouteAction.php",
      type: "POST",
      data: { action: "get_single_items", control_number: controlNumber },
      dataType: "json",
      success: function (response) {
        $itemsTableBody.empty(); // Clear loading spinner

        console.log("Response from server:", response);
        if (response.status === "success") {
          response.data.forEach((item) => {
            const viewButton = `<button class="btn btn-sm btn-secondary me-3" data-bs-toggle="modal" data-bs-target="#attachmentRfqModal" data-control_number = ${item.control_number} data-id=${item.id} id="view_btn">
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
          console.error("Error fetching items:", response.message);
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
        console.error("AJAX error:", status, error);
        const $tbody = $("#itemsTableBody");
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

  // View Items button
  $("#requestTableBody").on("click", "#itemview_btn", function () {
    const controlNumber = $(this).data("id");
    populateItems(controlNumber);
    console.log("this is clicked");
  });

  $("#itemsTableBody").on("click", "#view_btn", function () {
    const itemId = $(this).data("id");
    const controlNumber = $(this).data("control_number");
    console.log("this is clicked");
    $.ajax({
      url: "././backend/Route/requestRouteAction.php",
      type: "POST",
      data: {
        action: "get_item_details",
        id: itemId,
        control_number: controlNumber,
      },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          const mimeType = response.data.file_type;
          const filePath = response.data.file_path;
          const fileName = response.data.file_name;

          const imageTypes = [
            "image/jpeg",
            "image/png",
            "image/gif",
            "image/webp",
            "image/svg+xml",
          ];

          if (imageTypes.includes(mimeType)) {
            // Show image
            $("#attachment_viewer").attr("src", filePath).show();
            $("#download_link").hide();
          } else {
            // Show download link
            $("#attachment_viewer").hide();
            $("#download_link")
              .attr("href", filePath)
              .attr("download", fileName)
              .text(`Download ${fileName}`)
              .show();
          }
        } else {
          alert(response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", status, error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "An error occurred while fetching attachment.",
        });
      },
    });
  });

  // Reopen Item View Modal when Attachment Modal is closed
  $("#attachmentRfqModal").on("hidden.bs.modal", function () {
    $("#itemsRfqModal").modal("show");
  });

  // Apply Filters button
  $("#statusFilter").on("change", function () {
    populateTable();
  });

  $("#fromDateFilter, #toDateFilter").on("change", function () {
    populateTable();
  });

  $("#searchInput").on("input", function () {
    populateTable();
  });

  // Clear Filters button
  $(".btn-danger").on("click", function () {
    $("select").val("");
    $("#searchInput").val("");
    $(".rfq-table tbody tr").show();
    $("#from_date").val("");
    $("#to_date").val("");

    populateTable();
  });

  $("#requestTableBody").on("click", "#approve_btn", function () {
    const controlNumber = $(this).data("id");
    const remarks = "For Procurement Verification";
    const status = "On-going";
    const section = $("#requestTableBody").data("section");
    const mainaddress = $(this).data("sectionrequest");
    console.log("sender: ", section, "receiver: ", mainaddress);
    Swal.fire({
      title: "Are you sure?",
      text: "You want to approve this request?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, approve it!",
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: "Processing...",
          text: "Please wait while we create the comparison.",
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });

        $.ajax({
          url: "././backend/Route/requestRouteAction.php",
          type: "POST",
          data: {
            action: "update_request",
            control_number: controlNumber,
            remarks: remarks,
            status: status,
            section: section,
            main: mainaddress,
          },
          dataType: "json",
          success: function (response) {
            if (response.status === "success") {
              Swal.fire("Approved!", response.message, "success");
              populateTable();
            } else {
              Swal.fire("Error!", response.message, "error");
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX error:", status, error);
            Swal.fire(
              "Error!",
              "An error occurred while approving the request.",
              "error"
            );
          },
        });
      }
    });
  });

  $("#requestTableBody").on("click", "#decline_btn", function () {
    const controlNumber = $(this).data("id");
    const mainaddress = $(this).data("sectionrequest");
    const section = $("#requestTableBody").data("section");
    // const remarks = 'Hold by Section Head';
    const status = "Hold";
    Swal.fire({
      title: "Are you sure?",
      text: "You want to hold this request?",
      icon: "warning",
      input: "text",
      inputLabel: "Remarks",
      inputPlaceholder: "Enter remarks here...",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, hold it!",
      preConfirm: (remarks) => {
        if (!remarks) {
          Swal.ValidationMessage("Remarks Required");
          return false;
        }
        return remarks;
      },
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: "Processing...",
          text: "Please wait while we create the comparison.",
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });
        const remarksinput = result.value;
        $.ajax({
          url: "././backend/Route/requestRouteAction.php",
          type: "POST",
          data: {
            action: "update_request",
            control_number: controlNumber,
            remarks: remarksinput,
            status: status,
            section: section,
            main: mainaddress,
          },
          dataType: "json",
          success: function (response) {
            if (response.status === "success") {
              Swal.fire("Hold!", response.message, "success");
              populateTable();
            } else {
              Swal.fire("Error!", response.message, "error");
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX error:", status, error);
            Swal.fire(
              "Error!",
              "An error occurred while declining the request.",
              "error"
            );
          },
        });
      }
    });
  });
});
