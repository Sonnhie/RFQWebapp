$(document).ready(function () {
  let section = $("#comparisonTable").data("section");
  let page = 1;
  let limit = 10;
  const $tbody = $("#comparisonTable");
  const $tbodyItems = $("#comparisonModalTableBody");
  const $tbodySupplier = $("#itemDiv");
  const $editForm = $("#comparisonEditForm");
  const $role = $tbodySupplier.data("role");
  const $name = $tbodySupplier.data("name");
  console.log($role);
  console.log(section, $name);

  function InitializeDataTable() {
    const renderTable = (data = []) => {
      // console.log(data);
      $tbody.empty();
      const $loading = $(`<tr>
                                    <td colspan="9" class="text-center">
                                            <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
                                            <span class="text-primary" role="status">Loading...</span>
                                    </td>
                            </tr>`);
      $tbody.append($loading);

      if (data.length === 0) {
        setTimeout(function () {
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
        }, 1500);
      }

      $tbody.empty();
      data.forEach((item) => {
        const statusClasses = {
          Completed: "badge-approved",
          "On-going": "badge-pending",
          Rejected: "badge-rejected",
          Hold: "badge-hold",
        };
        const statusBadge = `<span class="status-badge ${
          statusClasses[item.item_status] || ""
        }">${item.item_status}</span>`;
        // const isDisabledapprove = (item.item_remarks === 'Comparison approved by Requestor' && item.item_remarks == 'Comparison approved by: Melanie Gancayco' || $role == 'Verifier-Approver' || $role == 'Manager' && item.item_remarks == 'Comparison approved by: Melanie Gancayco')
        // const isDisabled = (item.item_remarks === 'Comparison approved by: Melanie Gancayco' && item.item_remarks === 'Comparison approved by: Willy Arante')

        let isDisabled = false;
        let approveButton = "";
        let dissapproveButton = "";
        let downloadButton = "";
        let quotationButton = "";

        const remark = {
          created: "created",
          approve: "Approved by",
          verified: "Verified by",
          acknowledge: "Acknowledge by",
          disapprove: "Disapproved by",
        };

        let remarks = item.item_remarks || "";
        console.log(remarks);
        if (
          (remarks.includes(remark.approve) ||
            remarks.includes(remark.verified)) &&
          ($role == "Requestor" || $role == "Section-Approver")
        ) {
          isDisabled = true;
        } else if (
          (remarks.includes(remark.acknowledge) ||
            remarks.includes(remark.approve)) &&
          $role == "Manager"
        ) {
          isDisabled = true;
        } else if (
          (remarks.includes(remark.approve) ||
            remarks.includes(remark.verified)) &&
          $role === "Verifier-Approver"
        ) {
          isDisabled = true;
        } else if (
          (remarks.includes(remark.created) ||
            remarks.includes(remark.approve) ||
            remarks.includes(remark.verified) ||
            remarks.includes(remark.acknowledge)) &&
          $role === "Verifier"
        ) {
          isDisabled = true;
        }
        // Build button once
        approveButton = `
                        <button class="btn btn-sm btn-success rounded-2 me-3" 
                                data-section="${item.item_section}" 
                                data-id="${item.control_number}" 
                                id="approve_btn" ${
                                  isDisabled ? "disabled" : ""
                                }>
                            <i class="bi bi-hand-thumbs-up"></i> Approve
                        </button>`;

        dissapproveButton = `
                        <button class="btn btn-sm btn-danger rounded-2 me-3"
                                data-section="${item.item_section}"
                                data-id="${item.control_number}"
                                
                                id="dissapprove_btn" ${
                                  isDisabled ? "disabled" : ""
                                }>
                            <i class="bi bi-hand-thumbs-down"></i> Dissapprove
                        </button>`;

        downloadButton = `<button class="btn btn-sm btn-info rounded-2 me-3" 
                                            data-section="${item.item_section}"
                                            data-id="${item.control_number}"
                                            data-requestor="${item.item_requestor}"
                                            data-description="${item.item_description}"
                                            id="download_btn">
                                            <i class="bi bi-download"></i> Comparison Sheet
                                        </button>`;

        const viewButton = `<button class="btn btn-sm rounded-2 btn-secondary me-3" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#comparisonTableModal" 
                                            data-section="${item.item_section}"
                                            data-id="${item.control_number}" 
                                            id="view_comparison_btn" >
                                            <i class="bi bi-eye"></i> View
                                        </button>`;

        const QuotationButton = `
                                  <a class="btn btn-sm rounded-2 btn-success me-3"
                                    id="quotation_btn"
                                    data-section="${item.item_section}"
                                    data-id="${item.control_number}">
                                    <i class="bi bi-download"></i> Download Quotation
                                  </a>
                                `;

        const editButton = `<button class="btn btn-sm btn-primary rounded-2 me-3" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#comparisonModal" 
                                            data-section="${item.item_section}" 
                                            data-id="${item.control_number}" 
                                            id="edit_comparison_btn">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>`;

        // Start with empty array
        const groupBtn = [];

        // Always include view button
        groupBtn.push(viewButton);

        // Always include edit button (optional condition)
        // groupBtn.push(editButton);

        // Conditionally include approve button based on role
        if (
          $role === "Verifier-Approver" ||
          $role === "Section-Approver" ||
          $role === "Manager" ||
          $role === "Requestor"
        ) {
          groupBtn.push(approveButton);
        }

        if ($role === "Verifier") {
          groupBtn.push(editButton);
          groupBtn.push(downloadButton);
          groupBtn.push(QuotationButton);
        }

        if ($role === "Requestor") {
          groupBtn.push(dissapproveButton);
          groupBtn.push(downloadButton);
          groupBtn.push(QuotationButton);
        }

        if ($role === "Section-Approver") {
          groupBtn.push(downloadButton);
          groupBtn.push(QuotationButton);
        }

        // Now groupBtn contains the HTML for the allowed buttons
        // You can join them into a single HTML string when rendering:
        const buttonsHtml = groupBtn.join("");

        const $row = $(`
                        <tr>
                            <td>${item.control_number}</td>
                            <td>${statusBadge}</td>
                            <td>${item.item_remarks}</td>
                            <td>${item.item_section}</td>
                            <td>${item.created_at}</td>
                            <td>
                                ${buttonsHtml}
                            </td>
                        </tr>
                    `);

        $tbody.append($row);
      });
    };

    const renderPagination = (currentPage, totalPages) => {
      console.log("Page:" + currentPage, "Total Page: " + totalPages);
      const $pagination = $("#pagination");
      $pagination.empty();

      const prevDisabled = currentPage === 1 ? "disabled" : "";
      const prevBtn = $(`
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" aria-label="Previous" data-page="${
                      currentPage - 1
                    }">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            `);

      $pagination.append(prevBtn);

      for (let i = 1; i <= totalPages; i++) {
        const activeClass = i === currentPage ? "active" : "";
        const pageBtn = $(`
                    <li class="page-item ${activeClass}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
        $pagination.append(pageBtn);
      }

      const nextDisabled = currentPage === totalPages ? "disabled" : "";
      const nextBtn = $(`
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" aria-label="Next" data-page="${
                      currentPage + 1
                    }">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            `);
      $pagination.append(nextBtn);
    };

    const getData = (limit) => {
      const FromdateRange = $("#fromDateFilter").val();
      const TodateRange = $("#toDateFilter").val();
      const searchQuery = $("#searchInput").val().toLowerCase();
      const access = $("#comparisonTable").data("access");
      const username = $("#comparisonTable").data("username");
      const $filter = {
        dateFrom: FromdateRange,
        dateTo: TodateRange,
        searchValue: searchQuery,
        role: $role,
        access: access,
        username: username,
      };

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
          action: "getcomparisonData",
          filters: $filter,
          section: section,
          page: page,
          limit: limit,
        },
        dataType: "json",
        success: function (response) {
          $tbody.empty();
          try {
            const totalPage = Math.ceil(response.total / response.perPage);
            renderTable(response.data || []);
            renderPagination(page, totalPage);
          } catch (error) {
            console.log(error);
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

    getData(limit);
  }

  function InitializeComparisonTable(control_number) {
    const renderComparisonTable = (data = []) => {
      $tbodyItems.empty();
      const loading = $(`
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                            <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                            <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                        </td>
                    </tr>
            `);
      $tbodyItems.append(loading);

      if (
        data &&
        Array.isArray(data.suppliers) &&
        data.suppliers.length === 0
      ) {
        setTimeout(function () {
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
        }, 1500);
      }
      $tbodyItems.empty();
      data.forEach((item) => {
        const supplierRows = item.suppliers
          .map((supplier, index) => {
            return `
                    <tr>
                        ${
                          index === 0
                            ? `<td rowspan="${item.suppliers.length}">${item.item_name}</td>`
                            : ""
                        }
                        <td>${supplier.supplier_name}</td>
                        <td>${item.currency === "USD" ? "$" : "₱"} ${parseFloat(
              supplier.item_price
            ).toFixed(2)}</td>
                        <td>${item.currency === "USD" ? "$" : "₱"} ${parseFloat(
              supplier.item_discount
            ).toFixed(2)}</td>
                        <td>${item.currency === "USD" ? "$" : "₱"} ${parseFloat(
              supplier.item_total
            ).toFixed(2)}</td>
                        ${
                          index === 0
                            ? `<td rowspan="${item.suppliers.length}">${item.item_remarks}</td>`
                            : ""
                        }
                    </tr>`;
          })
          .join("");

        $tbodyItems.append(supplierRows);
      });
    };

    const getItems = (control_number) => {
      $.ajax({
        url: "./backend/Route/requestRouteAction.php",
        type: "POST",
        data: {
          action: "get_comparison_items",
          control_number: control_number,
        },
        dataType: "json",
        success: function (response) {
          console.log(response);
          renderComparisonTable(response.data || []);
        },
        error: function (err) {
          console.log(err);
        },
      });
    };

    getItems(control_number);
  }

  function InitializeSupplierTable(control_number) {
    const renderTable = (data = []) => {
      $tbodySupplier.empty();
      const loading = $(`
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                            <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                            <div class="spinner-grow text-primary" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                        </td>
                    </tr>
            `);
      $tbodySupplier.append(loading);

      if (
        data &&
        Array.isArray(data.suppliers) &&
        data.suppliers.length === 0
      ) {
        setTimeout(function () {
          $tbodySupplier.empty();
          const $noDataRow = $(`
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <span class="text-center text-danger">No Verified Request found</span>
                                        </td>
                                    </tr>
                                `);
          $tbody.append($noDataRow);
          return;
        }, 1500);
      }
      $tbodySupplier.empty();
      data.forEach((item) => {
        const supplierRows = item.suppliers
          .map((supplier, index) => {
            $tbodySupplier.append(`<tr id="rowIdx" data=>
                                ${
                                  index === 0
                                    ? `
                                    <td rowspan="${item.suppliers.length}">
                                        ${item.item_name}
                                    </td>`
                                    : ""
                                }
                                <td>
                                    <input type="hidden" name="supplier_id[]" value="${
                                      supplier.id
                                    }"/>
                                    <input type="text" id="item_supplier" name="supplier_name[]" data-index="${index}" value="${
              supplier.supplier_name
            }" class="form-control" />
                                </td>
                                <td>
                                    <input type="number" id="item_price" name="item_price[]" data-index="${index}" value="${parseFloat(
              supplier.item_price
            ).toFixed(2)}" class="form-control" step="0.01" />
                                </td>
                                <td>
                                    <input type="number" id="item_discount" name="item_discount[]" data-index="${index}" value="${parseFloat(
              supplier.item_discount
            ).toFixed(2)}" class="form-control" step="0.01" />
                                </td>
                                <td>
                                    <input type="number" id="item_total" name="item_total[]" data-index="${index}" value="${parseFloat(
              supplier.item_total
            ).toFixed(2)}" class="form-control" step="0.01" readonly />
                                </td>
                                <td>
                                  <input type="file" id="upload_path" name="upload_path[]" data-index="${index}">
                                </td>
                        </tr>`);

            setTimeout(() => {
              $($tbodySupplier)
                .off("input", "#item_price, #item_discount")
                .on("input", "#item_price, #item_discount", function () {
                  const $row = $(this).closest("tr");
                  const price = parseFloat($row.find("#item_price").val()) || 0;
                  const discount =
                    parseFloat($row.find("#item_discount").val()) || 0;
                  const Quantity = parseFloat(item.item_quantity) || 0; // Use the item's quantity
                  const total = price * Quantity - discount;
                  $row.find("#item_total").val(total >= 0 ? total : 0);
                });
            }, 0);
          })
          .join("");
        $tbodySupplier.append(supplierRows);
      });
    };

    const getData = (control_number) => {
      $.ajax({
        url: "./backend/Route/requestRouteAction.php",
        type: "POST",
        data: {
          action: "get_comparison_items",
          control_number: control_number,
        },
        dataType: "json",
        success: function (response) {
          console.log(response);
          renderTable(response.data || []);
        },
        error: function (err) {
          console.log(err);
        },
      });
    };

    getData(control_number);
  }

  function EditComparison() {
    const submitEditForm = () => {
      const action = "Edit_Comparison";
      const control_number = $("#edit_comparison_btn").data("id");
      const mainaddress = $("#edit_comparison_btn").data("section");
      console.log(mainaddress);
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
            data:
              $($editForm).serialize() +
              "&action=" +
              action +
              "&section=" +
              section +
              "&main=" +
              mainaddress +
              "&control_number=" +
              control_number,
            dataType: "json",
            success: function (response) {
              console.log(response);
              Swal.close();
              if (response.status == "success") {
                Swal.fire("Comparison Created!", response.message, "success");
                InitializeDataTable();
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
    };
    submitEditForm();
  }

  function ApprovalComparison(control_number) {
    const updateStatus = (control_number) => {
      const action = "update_request";
      const mainaddress = $("#edit_comparison_btn").data("section");
      const controlnumber = control_number;
      let remarks = "";
      let status = "";

      if ($role == "Verifier-Approver") {
        remarks = "Comparison Verified by: " + $name;
        status = "On-going";
      } else if ($role == "Manager") {
        remarks = "Comparison Acknowledge by: " + $name;
        status = "Completed";
      } else if ($role == "Requestor") {
        remarks = "Comparison Approved by: " + $name;
        status = "Completed";
      } else if ($role == "Section-Approver") {
        remarks = "Comparison Approved by: " + $name;
        status = "Completed";
      }

      Swal.fire({
        title: "Are you sure?",
        text: "You want to approve this comparison?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, approve it!",
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Processing...",
            text: "Please wait while we update the comparison.",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });

          $.ajax({
            url: "./backend/Route/requestRouteAction.php",
            type: "POST",
            data: {
              action: action,
              control_number: controlnumber,
              status: status,
              remarks: remarks,
              section: section,
              main: mainaddress,
            },
            dataType: "json",
            success: function (response) {
              if (response.status === "success") {
                Swal.fire("Comparison Approved!", response.message, "success");
                InitializeDataTable();
              } else {
                Swal.fire("Error!", response.message, "error");
              }
            },
          });
        }
      });
    };

    updateStatus(control_number);
  }

  function DisapprovalComparison(control_number) {
    const updateStatus = (control_number) => {
      const action = "update_request";
      const mainaddress = $("#edit_comparison_btn").data("section");
      const controlnumber = control_number;
      let remarks = "Disapproved by: " + $name;
      let status = "Completed";

      // if ($role == 'Verifier-Approver') {
      //     remarks = 'Comparison Verified by: ' + $name;
      //     status = 'Pending'
      // }
      // else if($role == 'Manager'){
      //     remarks = 'Comparison Acknowledge by: ' + $name;
      //     status = 'Completed'
      // }
      // else if($role == 'Requestor'){
      //     remarks = 'Comparison Approved by: ' + $name;
      //     status = 'Completed'
      // }
      // else if($role == 'Section-Approver'){
      //     remarks = 'Comparison Approved by: ' + $name;
      //     status = 'Completed'
      // }

      Swal.fire({
        title: "Are you sure?",
        text: "You want to disapprove this comparison?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, disapprove it!",
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Processing...",
            text: "Please wait while we update the comparison.",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });

          $.ajax({
            url: "./backend/Route/requestRouteAction.php",
            type: "POST",
            data: {
              action: action,
              control_number: controlnumber,
              status: status,
              remarks: remarks,
              section: section,
              main: mainaddress,
            },
            dataType: "json",
            success: function (response) {
              if (response.status === "success") {
                Swal.fire(
                  "Comparison Disapproved!",
                  response.message,
                  "success"
                );
                InitializeDataTable();
              } else {
                Swal.fire("Error!", response.message, "error");
              }
            },
          });
        }
      });
    };

    updateStatus(control_number);
  }

  function DownloadPDF(control_number) {
    const downloadPDF = (control_number) => {
      const action = "download_comparison_pdf";
      const mainaddress = $("#edit_comparison_btn").data("section");
      const controlnumber = control_number;
      const item_section = $("#download_btn").data("section");
      const item_description = $("#download_btn").data("description");
      const requestor = $("#download_btn").data("requestor");
      Swal.fire({
        title: "Processing...",
        text: "Please wait while we rendering your comparison.",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });
      $.ajax({
        url: "./backend/Route/requestRouteAction.php",
        type: "POST",
        data: {
          action: action,
          control_number: controlnumber,
          section: item_section,
          main: mainaddress,
          requestor: requestor,
          description: item_description,
        },
        xhrFields: {
          responseType: "blob",
        },
        success: function (response, status, xhr) {
          // console.log(response);
          var contentType = xhr.getResponseHeader("Content-Type");
          var filename = xhr.getResponseHeader("Content-Disposition");

          console.log(filename);
          if (contentType === "application/pdf") {
            var blob = new Blob([response], { type: "application/pdf" });
            var fileURL = URL.createObjectURL(blob);

            Swal.fire({
              title: "PDF Ready",
              text: "Do you want to view or download the PDF?",
              icon: "question",
              showCancelButton: true,
              confirmButtonText: "View",
              cancelButtonText: "Download",
            }).then((result) => {
              if (result.isConfirmed) {
                // View in browser
                window.open(fileURL, "_blank");
              } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Download
                var a = document.createElement("a");
                a.href = fileURL;
                a.download = "Comparison-" + control_number + ".pdf";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                setTimeout(() => URL.revokeObjectURL(fileURL), 1000);
              }
            });
          } else {
            Swal.fire(
              "Error!",
              "An error occurred while generating the PDF.",
              "error"
            );
          }
        },
        error: function (err) {
          console.log(err);
          Swal.fire(
            "Error!",
            "An error occurred while downloading the PDF.",
            "error"
          );
        },
      });
    };
    downloadPDF(control_number);
  }

  $("#showlimit").on("change", function () {
    limit = $(this).val();
    InitializeDataTable();
  });

  $tbody.on("click", "#view_comparison_btn", function () {
    const id = $(this).data("id");
    console.log(id);
    InitializeComparisonTable(id);
  });

  $tbody.on("click", "#quotation_btn", function (e) {
    e.preventDefault(); // prevent opening "#"
    const control_number = $(this).data("id");
    const button = $(this);

    $.ajax({
      url: "././backend/Route/requestRouteAction.php",
      type: "POST",
      data: {
        action: "getQuotation",
        control_number: control_number,
      },
      dataType: "json",
      beforeSend: function () {
        Swal.fire({
          title: "Please wait...",
          text: "Fetching quotation file...",
          allowOutsideClick: false,
          showConfirmButton: false,
          willOpen: () => Swal.showLoading(),
        });
      },
      success: function (response) {
        Swal.close();
        if (response.status === "success") {
          const fileName = response.data.file_name;

          // Route file through your secure download endpoint
          const downloadUrl = `download.php?file=${encodeURIComponent(
            fileName
          )}`;

          // Update button link dynamically
          button
            .attr("href", downloadUrl)
            .attr("download", fileName)
            .attr("target", "_blank");

          // Automatically start the download
          window.open(downloadUrl, "_blank");
        } else {
          Swal.fire({
            icon: "warning",
            title: "Not Found",
            text: response.message,
          });
        }
      },
      error: function (xhr, status, error) {
        Swal.close();
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "An error occurred while fetching attachment.",
        });
        console.error("AJAX error:", status, error);
      },
    });
  });

  $tbody.on("click", "#edit_comparison_btn", function () {
    const id = $(this).data("id");
    console.log(id);
    $tbodySupplier.data("id", id);
    InitializeSupplierTable(id);
  });

  $tbody.on("click", "#approve_btn", function () {
    const control_number = $(this).data("id");
    console.log(control_number);

    ApprovalComparison(control_number);
  });

  $tbody.on("click", "#dissapprove_btn", function () {
    const control_number = $(this).data("id");
    console.log(control_number);

    DisapprovalComparison(control_number);
  });

  $tbody.on("click", "#download_btn", function () {
    const control_number = $(this).data("id");
    console.log(control_number);
    DownloadPDF(control_number);
  });

  $editForm.submit(function (e) {
    e.preventDefault();
    const id = $(this).data("id");
    EditComparison();
  });

  // Event delegation for pagination
  $("#pagination").on("click", ".page-link", function (e) {
    e.preventDefault();
    const selectedPage = parseInt($(this).data("page"));
    limit = $("#showlimit").val();
    if (!isNaN(selectedPage) && selectedPage >= 1) {
      page = selectedPage;
      InitializeDataTable();
    }
  });

  $("#fromDateFilter, #toDateFilter").on("change", function () {
    InitializeDataTable();
  });

  function debounce(func, delay) {
    let timeout;
    return function () {
      clearTimeout(timeout);
      timeout = setTimeout(func, delay);
    };
  }

  const debouncedPopulate = debounce(InitializeDataTable, 1000);

  $("#searchInput").on("input", debouncedPopulate);

  InitializeDataTable();
});
