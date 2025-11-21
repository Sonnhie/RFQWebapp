$(document).ready(function () {
  let section = $(".table").data("section");
  const page = 1;
  const $tbody = $("#verifiedTable");

  const renderTable = (data = []) => {
    $tbody.empty();
    const $loading = $(`<tr>
                                <td colspan="9" class="text-center">
                                        <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
                                        <span class="text-primary" role="status">Loading...</span>
                                </td>
                        </tr>`);
    $tbody.append($loading);

    setTimeout(function () {
      if (data.length === 0) {
        $tbody.empty();
        const $noDataRow = $(`
                        <tr>
                            <td colspan="9" class="text-center">
                                <span class="text-center text-danger">No Verified Request found</span>
                            </td>
                        </tr>
                    `);
        $tbody.append($noDataRow);
        return;
      }
      console.log(data);
      $tbody.empty();
      data.forEach((item) => {
        // $tbody.empty();
        const statusClasses = {
          Approved: "badge-approved",
          "On-going": "badge-pending",
          Rejected: "badge-rejected",
          Hold: "badge-hold",
        };
        const statusBadge = `<span class="status-badge ${
          statusClasses[item.item_status] || ""
        }">${item.item_status}</span>`;

        const emailButton = `<button class="btn btn-sm btn-primary rounded-2 me-3" data-bs-toggle="modal" data-bs-target="#emailsupplier" data-section="${item.item_section}" data-id="${item.control_number}" id="email_btn">
                                        <i class="bi bi-envelope"></i> Email Supplier
                                    </button>`;

        const comparisonButton = `<button class="btn btn-sm  btn-success rounded-2 me-3" data-bs-toggle="modal" data-bs-target="#comparisonModal" data-section="${item.item_section}" data-id="${item.control_number}" id="create_comparison_btn">
                                            <i class="bi bi-file-earmark-text"></i> Create Comparison
                                        </button>`;

        const actionButton = `
                        ${emailButton}
                        ${comparisonButton}
                `;
        const $row = $(`
                    <tr>
                        <td>${item.control_number}</td>
                        <td>${statusBadge}</td>
                        <td>${item.item_remarks}</td>
                        <td>${item.item_section}</td>
                        <td>${item.created_at}</td>
                        <td>
                            ${actionButton}
                        </td>
                    </tr>
                `);
        $tbody.append($row);
      });
    }, 1500);
  };

  const renderPagination = (currentPage, totalPages) => {
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
  };

  const getData = (page = 1) => {
    const $filter = {
      dateFrom: $("#fromDateFilter").val(),
      dateTo: $("#toDateFilter").val(),
      searchValue: $("#searchInput").val(),
    };

    const limit = $("#limit").val();

    $tbody.empty();
    const $loading = `
            <tr>
                <td colspan="9" class="text-center">
                    <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                    <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                    <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                </td>
            </tr>`;
    $tbody.append($loading);

    $.ajax({
      url: "./backend/Route/requestRouteAction.php",
      type: "POST",
      data: {
        action: "getData",
        filters: $filter,
        section: section,
        page: page,
        limit: limit,
      },
      dataType: "json",
      success: function (response) {
        $tbody.empty();
        if (response.status == "success") {
          renderTable(response.data || []);
          const totalPage = Math.ceil(response.total / response.perPage);
          console.log(totalPage);
          renderPagination(page, totalPage);
        }
      },
      error: function (err) {
        console.log(err);
        const error = `
                    <tr>
                        <td colspan="9" class="text-center text-danger">Error Loading data</td>
                    </tr>`;
        $tbody.append(error);
      },
    });
  };

  $tbody.on("click", "#email_btn", function () {
    const controlNumber = $(this).data("id");
    $("#emailForm").data("id", controlNumber); // Store control number in the form
    $("#emailForm").data("section", section); // Store section in the form
    $("#emailsupplier").modal("show"); // Show the email modal
  });

  $("#emailForm").on("submit", function (e) {
    e.preventDefault();
    const controlNumber = $(this).data("id"); // Get control number from the form
    const section = $(this).data("section"); // Get section from the form
    console.log("Control Number:", controlNumber);

    const formData = {
      action: "send_email_to_supplier",
      recipients: $("input[name='recipients[]']")
        .map(function () {
          return $(this).val().trim();
        })
        .get()
        .filter((email) => email !== ""),
      ccs: $("input[name='ccs[]']")
        .map(function () {
          return $(this).val().trim();
        })
        .get()
        .filter((email) => email !== ""),
      bccs: $("input[name='bccs[]']")
        .map(function () {
          return $(this).val().trim();
        })
        .get()
        .filter((email) => email !== ""),
      control_number: controlNumber,
      section: section,
      // If you set this dynamically
    };

    // console.log('Form Data:', formData);
    Swal.fire({
      title: "Are you sure?",
      text: "You want to send email?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, send it!",
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: "Processing...",
          text: "Please wait while we email the supplier.",
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });

        $.ajax({
          url: "./backend/Route/requestRouteAction.php",
          type: "POST",
          data: formData,
          dataType: "json",
          success: function (response) {
            if (response.status === "success") {
              Swal.close();
              Swal.fire("Email Sent!", response.message, "success");
              $("#emailsupplier").modal("hide"); // Hide the modal
            } else {
              Swal.fire("Error!", response.message, "error");
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX error:", status, error);
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred while sending the email.",
            });
          },
        });
      }
    });
  });

  $(document).on("click", ".remove-supplier", function () {
    $(this).closest("tr").remove();
  });

  function createInput(name) {
    return `
            <div class="input-group mb-2">
                <input type="email" name="${name}" class="form-control" placeholder="Enter email">
                <button class="btn btn-outline-secondary remove-btn" type="button">Remove</button>
            </div>
        `;
  }

  $("#add-recipient").on("click", function () {
    $("#recipients-group").append(createInput("recipients[]"));
  });

  $("#add-cc").on("click", function () {
    $("#ccs-group").append(createInput("ccs[]"));
  });

  $("#add-bcc").on("click", function () {
    $("#bccs-group").append(createInput("bccs[]"));
  });

  // Handle dynamic removal
  $(document).on("click", ".remove-btn", function () {
    $(this).closest(".input-group").remove();
  });

  // Handle dynamic add/remove supplier rows in the modal
  $(document).on("click", ".add-supplier", function () {
    const itemIdx = $(this).data("item-idx");
    const $tbody = $(`#comparisonTableBody_${itemIdx}`);
    let maxSuppliers = 3;
    // Insert before the last row (which is the add button row)
    const $addRow = $tbody.find("tr").last();
    let supplierCount = $tbody.find("tr").length - 1; // Exclude add button row

    if (supplierCount <= maxSuppliers) {
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
                                                        <input type="number" class="form-control form-control-sm" name="payment_terms[${itemIdx}][]" placeholder="Days" min="0" step="any">
                                                    </td>
                                                     <td>
                                                        <input type="number" class="form-control form-control-sm" name="delivery_terms[${itemIdx}][]" placeholder="Days" min="0" step="any">
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
      supplierCount++;
    } else {
      Swal.fire({
        icon: "warning",
        title: "Limit Reached",
        text: `You can only add up to ${maxSuppliers + 1} suppliers.`,
      });
      return;
    }

    // Attach input event handler for the new row
    $tbody
      .find("tr")
      .eq(-2)
      .find(".price-input, .discount-input")
      .on("input", function () {
        const $row = $(this).closest("tr");
        const Quantity = parseFloat($row.find(".item_quantity").val()) || 0;
        const price = parseFloat($row.find(".price-input").val()) || 0;
        const discount = parseFloat($row.find(".discount-input").val()) || 0;
        const total = price * Quantity - discount;
        // console.log('Price:', price, 'Quantity:', Quantity, 'Discount:', discount, 'Total:', total);
        $row.find(".total-input").val(total >= 0 ? total : 0);
      });
  });

  // Populate the comparison table inside the comparison modal
  function populateComparisonTable(controlNumber) {
    const $comparisonTableBody = $("#itemDiv");
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
      url: "./backend/Route/requestRouteAction.php",
      type: "POST",
      data: {
        action: "get_items_for_comparison",
        control_number: controlNumber,
      },
      dataType: "json",
      success: function (response) {
        $comparisonTableBody.empty(); // Clear loading spinner

        if (response.status === "success" && Array.isArray(response.data)) {
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
                                    <strong>Quantity:</strong> ${item.item_quantity} <br>
                                    <strong>Unit:</strong> ${item.item_unit}
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" id="itemtable" data-itemname="${item.item_name}" data-itemid="${item.quantity}">
                                        <input type="hidden" name="item_name[${idx}]" value="${item.item_name}">   
                                        <input type="hidden" name="item_quantity[${idx}]" value="${item.item_quantity}"> 
                                        <input type="hidden" name="item_description[${idx}]" value="${item.item_description}"> 
                                        <input type="hidden" name="item_unit[${idx}]" value="${item.item_unit}">

                                        <thead>
                                                <tr>
                                                    <th>Supplier</th>
                                                    <th>Name</th>
                                                    <th>Price Per Unit</th>
                                                    <th>Discount</th>
                                                    <th>Payment Terms</th>
                                                    <th>Delivery lead Time</th>
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
                                                        <input type="number" class="form-control form-control-sm" name="payment_terms[${idx}][]" placeholder="Days" min="0" step="any">
                                                    </td>
                                                     <td>
                                                        <input type="number" class="form-control form-control-sm" name="delivery_terms[${idx}][]" placeholder="Days" min="0" step="any">
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
              $(`#comparisonTableBody_${idx}`)
                .off("input", ".price-input, .discount-input")
                .on("input", ".price-input, .discount-input", function () {
                  const $row = $(this).closest("tr");
                  const price =
                    parseFloat($row.find(".price-input").val()) || 0;
                  const discount =
                    parseFloat($row.find(".discount-input").val()) || 0;
                  const Quantity = parseFloat(item.item_quantity) || 0; // Use the item's quantity
                  const total = price * Quantity - discount;
                  $row.find(".total-input").val(total >= 0 ? total : 0);
                });
            }, 0);
          });
        } else {
          $comparisonTableBody.append(`
                        <div class="text-center">No items found for comparison.</div>
                    `);
        }
      },
      error: function () {
        $comparisonTableBody.empty();
        $comparisonTableBody.append(`
                    <div class="text-center text-danger">Error fetching items for comparison.</div>
                `);
      },
    });
  }

  // Show comparison modal and populate table when button is clicked
  $tbody.on("click", "#create_comparison_btn", function () {
    const controlNumber = $(this).data("id");
    const mainsection = $(this).data("section");
    $("#comparisonModal").data("id", controlNumber);
    $("#comparisonModal").data("section", mainsection);
    $("#controlnumber").text(`Control Number: ${controlNumber}`);
    $("#section").text(`Section: ${section}`);
    populateComparisonTable(controlNumber);
    $("#comparisonModal").modal("show");
    // console.log(mainsection, section);
  });

  $("#comparisonForm").on("submit", function (e) {
    e.preventDefault();

    const controlNumber = $("#comparisonModal").data("id");
    const requestorsection = $("#comparisonModal").data("section");
    const fileInput = $("#formFile")[0].files[0];

    // Create FormData object
    let formData = new FormData(this); // Automatically gets all inputs (including file)

    // Append extra data not in form
    formData.append("action", "create_comparison");
    formData.append("control_number", controlNumber);
    formData.append("section", requestorsection);
    formData.append("bccsection", section);
    for (let [key, value] of formData.entries()) {
      console.log(`${key}:`, value);
    }

    Swal.fire({
      title: "Are you sure?",
      text: "You want to create comparison?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, create it!",
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
          url: "./backend/Route/requestRouteAction.php",
          type: "POST",
          data: formData,
          processData: false, // IMPORTANT for FormData
          contentType: false, // IMPORTANT for FormData
          dataType: "json",
          success: function (response) {
            Swal.close();
            if (response.status === "success") {
              Swal.fire("Comparison Created!", response.message, "success");
              getData();
              $("#comparisonModal").modal("hide");
            } else {
              Swal.fire("Error!", response.message, "error");
            }
          },
          error: function (xhr, status, error) {
            Swal.close();
            console.error("AJAX error:", status, error);
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred while creating the comparison.",
            });
          },
        });
      }
    });
  });

  // Event delegation for pagination
  $("#pagination").on("click", ".page-link", function (e) {
    e.preventDefault();
    const selectedPage = parseInt($(this).data("page"));
    if (!isNaN(selectedPage) && selectedPage >= 1) {
      page = selectedPage;
      getData();
    }
  });

  // Initial fetch
  getData();
});
