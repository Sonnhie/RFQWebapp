$(document).ready(function () {
  const page = 1;

  // Initialize the table
  populateTable(page);
  //paginateTable();
  // Add new item row
  $("#addItemButton").on("click", function () {
    const $tbody = $("#itemsTableBody");
    const $newRow = $(`
            <tr>
                <td><input type="text" class="form-control form-control-sm" name="item_name[]" placeholder="Item name"></td>
                <td><textarea class="form-control" name="item_description[]" placeholder="Description" rows="4" required></textarea></td>
                <td><textarea class="form-control" name="item_purpose[]" id="purpose" placeholder="Purchase purpose" rows="4" required></textarea></td>
                <td><input type="text" class="form-control form-control-sm" name="item_quantity[]" min="1" id="quantity" placeholder="Qty"></td>
                <td>
                    <select class="form-select form-select-sm" name="item_unit[]">
                        <option value="Piece">Piece</option>
                        <option value="Box">Box</option>
                        <option value="Meter">Meter</option>
                        <option value="Set">Set</option>
                        <option value="Gallon">Gallon</option>
                        <option value="Sack">Sack</option>
                    </select>
                </td>
                 <td><input class="form-control" type="file" id="attachment" accept=".jpg,.png,application/pdf" name="item-attachment[]" required></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);

    $tbody.append($newRow);

    // Add delete event for this row
    $newRow.find(".btn-danger").on("click", function () {
      $newRow.remove();
    });
  });

  $("#itemsTableBody").on("input", "#quantity", function () {
    this.value = this.value.replace(/[^0-9.]/g, "");
  });

  $("#edit_request").on("input", "#quantity", function () {
    this.value = this.value.replace(/[^0-9.]/g, "");
  });

  // Submit new request
  $("#create_request").submit(function (e) {
    e.preventDefault();
    const formData = new FormData($("#create_request")[0]);
    formData.append("action", "create_request");
    formData.append("remarks", "For Section head approval");
    console.log(formData);

    $.ajax({
      url: "././backend/Route/requestRouteAction.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      beforeSend: function () {
        Swal.fire({
          title: "Please wait...",
          text: "Processing your request",
          allowOutsideClick: false,
          showConfirmButton: false,
          willOpen: () => {
            Swal.showLoading();
          },
        });
      },
      success: function (response) {
        Swal.close();
        console.log("Response from server:", response);
        if (response.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Request Created",
            text: response.message,
            showConfirmButton: false,
            timer: 1500,
          }).then(() => {
            populateTable(1);
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
        //console.error('AJAX error:', status, error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "An error occurred while submitting the request.",
        });
      },
    });
  });

  // function debugFormData(formData) {
  //   console.log("Debugging initialized");
  //   console.log("Form submitted");
  //   console.log("Serialized form data:");
  //   for (const [key, value] of formData.entries()) {
  //     console.table(`${key}: ${value}`);
  //   }

  //   const attachment = $('input[name="item-attachment[]"]')[0];
  //   if (attachment.files.length === 0) {
  //     console.log("Attachment is null or empty");
  //   } else {
  //     console.log("Attachments:", attachment.files);
  //     for (let i = 0; i < attachment.files.length; i++) {
  //       console.table(attachment.files[i].name);
  //     }
  //   }
  // }

  // Populate the table
  function populateTable(page = 1) {
    const section = $("#requestTableBody").data("section");
    const status = $("#statusFilter").val();
    const FromdateRange = $("#fromDateFilter").val();
    const TodateRange = $("#toDateFilter").val();
    const searchQuery = $("#searchInput").val().toLowerCase();
    const access = $("#requestTableBody").data("access");
    const username = $("#requestTableBody").data("username");
    const filters = {
      from: FromdateRange,
      to: TodateRange,
      status: status,
      search: searchQuery,
    };

    console.log(section, access, username);
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
        action: "get_items",
        section: section,
        access: access,
        username: username,
        filters: filters,
        page: page,
      },
      dataType: "json",
      success: function (response) {
        // console.log(response);
        $tbody.empty(); // Clear loading spinner

        // console.log('Response from server:', response);
        // console.log(response.total, response.perPage);
        if (response.status === "success") {
          response.data.forEach((item) => {
            const statusClasses = {
              Completed: "badge-approved",
              "On-going": "badge-pending",
              Rejected: "badge-rejected",
              Hold: "badge-hold",
            };

            const remark = {
              created: "created",
              approve: "Approved by",
              section: "Section head approval",
              hold: "Hold",
              verified: "Verified by",
              acknowledge: "Acknowledge by",
              disapprove: "Disapproved by",
            };

            let isDisabled = false;
            let editButton = "";
            let viewButton = "";
            let deleteButton = "";
            let remarks = item.item_remarks || "";
            let status = item.requestor_status || "";
            console.log(remarks);

            if (
              !(
                remarks.includes(remark.created) ||
                remarks.includes(remark.section) ||
                status == "Hold"
              )
            ) {
              isDisabled = true;
            }

            // const isDisabled = item.requestor_status === "Approved";

            const statusBadge = `
                  <span class="status-badge ${
                    statusClasses[item.requestor_status] || ""
                  }">
                    ${item.requestor_status}
                  </span>`;

            editButton = `<button class="btn btn-sm btn-secondary me-3" 
                          data-bs-toggle="modal" 
                          data-bs-target="#editRfqModal" 
                          data-id=${item.id} 
                          data-control_number=${item.control_number} 
                          id="edit_btn" 
                          ${isDisabled ? "disabled" : ""}>
                          <i class="bi bi-pencil"></i>
                          </button>`;

            viewButton = `<button class="btn btn-sm btn-primary me-3" 
                          data-bs-toggle="modal" 
                          data-bs-target="#attachmentRfqModal" 
                          data-control_number = ${item.control_number} 
                          data-id=${item.id} 
                          id="view_btn">
                          <i class="bi bi-eye"></i>
                          </button>`;
            deleteButton = `<button class="btn btn-sm btn-danger" 
                            data-id=${item.id} 
                            data-control_number=${item.control_number}
                            id="delete_btn" 
                            ${
                              isDisabled &&
                              item.requestor_status !== "Cancelled"
                                ? "disabled"
                                : ""
                            }>
                            <i class="bi bi-trash"></i>
                            </button>`;

            const $CalculateDaysDelay = (actualDate) => {
              const oneDay = 1000 * 60 * 60 * 24;
              const requestedDate = new Date(actualDate);
              const currdate = new Date();
              // console.log(requestedDate, currdate);
              requestedDate.setHours(0, 0, 0, 0);
              currdate.setHours(0, 0, 0, 0);

              const diffInMs = currdate.getTime() - requestedDate.getTime();
              // console.log("Diff in M: ", diffInMs);
              const diffInDays = Math.floor(diffInMs / oneDay);
              // console.log("Diff in Days: ", diffInDays);
              const pastdue = diffInDays - 7;
              // console.log("Past due: ", pastdue);
              if (pastdue > 0 && item.requestor_status != "Completed") {
                return `<span class="badge bg-danger">Delay ${pastdue} days</span>`;
              } else {
                // return `<span class="badge bg-success">---</span>`;
                return ``;
              }
            };

            const $row = $(`
                            <tr>
                                <td>${item.control_number}</td>
                                <td>${item.item_name}</td>
                                <td>${item.item_description}</td>
                                <td>${item.item_purpose}</td>
                                <td>${item.item_quantity}</td>
                                <td>${item.item_unit}</td>
                                <td>${item.requestor_section}</td>
                                <td>${statusBadge}</td>
                                <td>${$CalculateDaysDelay(item.created_at)}</td>
                                <td>${item.requestor_name}</td>
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
          console.error("Error fetching items:", response.message);
          const $row = $(`
                        <tr>
                            <td colspan="12" class="text-center">No items found.</td>
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

  function deleteItem(data) {
    // Ajax request to delete the item
    $.ajax({
      url: "././backend/Route/requestRouteAction.php",
      type: "POST",
      data: {
        action: "delete_item",
        data: data,
      },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Deleted",
            text: response.message,
          }).then(() => {
            populateTable();
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
        console.error("AJAX error:", status, error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "An error occurred while deleting the item.",
        });
      },
    });
  }

  function editItem(formData) {
    // Ajax request to edit the item
    $.ajax({
      url: "././backend/Route/requestRouteAction.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Updated",
            text: response.message,
          }).then(() => {
            populateTable();
            $("#edit_request")[0].reset();
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
        console.error("AJAX error:", status, error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "An error occurred while updating the item.",
        });
      },
    });
  }

  // Delete item button click event
  $("#requestTableBody").on("click", "#delete_btn", function () {
    const itemId = $(this).data("id");
    const $row = $(this).closest("tr");
    const control_number = $("#delete_btn").data("control_number");
    const itemName = $row.find("td:eq(1)").text();
    const itemDescription = $row.find("td:eq(2)").text();
    const section = $(this).data("section");
    const mainsection = $("#requestTableBody").data("section");

    const data = {
      itemId: itemId,
      itemName: itemName,
      itemDescription: itemDescription,
      section: section,
      control_number: control_number,
    };

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
        deleteItem(data);
      }
    });
  });

  $("#requestTableBody").on("click", "#edit_btn", function () {
    const itemId = $(this).data("id");
    const control_number = $(this).data("control_number");
    const $row = $(this).closest("tr");
    const itemName = $row.find("td:eq(1)").text();
    const itemDescription = $row.find("td:eq(2)").text();
    const itemPurpose = $row.find("td:eq(3)").text();
    const itemQuantity = $row.find("td:eq(4)").text();
    const itemUnit = $row.find("td:eq(5)").text();
    console.log(itemId);
    $("#control_number").val(control_number);
    $("#item_name").val(itemName);
    $("#item_description").val(itemDescription);
    $("#item_purpose").val(itemPurpose);
    $("#item_quantity").val(itemQuantity);
    $("#item_unit").val(itemUnit);
    $("#editRfqModal").data("itemId", itemId);
  });

  // Edit item form submission
  $("#edit_request").submit(function (e) {
    e.preventDefault();
    const section = $(this).data("section");

    const itemId = $("#editRfqModal").data("itemId");
    console.log(section);
    const formData = new FormData($("#edit_request")[0]);
    formData.append("action", "edit_request");
    formData.append("item_id", itemId);
    formData.append("section", section);

    const attachmentInput = $('input[name="item_attachment"]');
    const attachment = attachmentInput.files; // get the first selected file
    if (attachment && attachment.size > 10 * 1024 * 1024) {
      // Check if the file size exceeds 10MB
      Swal.fire({
        icon: "error",
        title: "File Size Error",
        text: "The attachment exceeds the maximum allowed size of 40MB.",
      });
      return;
    }

    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, save changes!",
    }).then((result) => {
      if (result.isConfirmed) {
        editItem(formData);
        $("#editRfqModal").modal("hide");
      }
    });
  });

  $("#requestTableBody").on("click", "#view_btn", function () {
    const itemId = $(this).data("id");
    const controlNumber = $(this).data("control_number");

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

  // Apply Filters button
  $("#statusFilter").on("change", function () {
    populateTable();
  });

  $("#fromDateFilter, #toDateFilter").on("change", function () {
    populateTable();
  });

  function debounce(func, delay) {
    let timeout;
    return function () {
      clearTimeout(timeout);
      timeout = setTimeout(func, delay);
    };
  }

  const debouncedPopulate = debounce(populateTable, 1000);

  $("#searchInput").on("input", debouncedPopulate);

  // Clear Filters button
  $(".btn-outline-danger").on("click", function () {
    $("select").val("");
    $("#searchInput").val("");
    $(".rfq-table tbody tr").show();
    $("#from_date").val("");
    $("#to_date").val("");

    populateTable();
  });

  // Export button
  $(".btn-outline-secondary").on("click", function () {
    alert("Export functionality would generate a CSV/PDF");
  });
});
