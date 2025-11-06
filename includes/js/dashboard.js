$(document).ready(function () {
  const role = $(".chart-container").data("role");
  const section = $(".chart-container").data("section");
  const username = $(".chart-container").data("username");
  const $tbody = $("#rfqTableBody");
  const now = new Date();
  const month = now.toLocaleString("default", { month: "long" });
  let rfqChartInstance = null;

  function InitializeBarChart() {
    const year = $("#yearSelect").val();
    const $data = {
      role: role,
      section: section,
      username: username,
      year: year,
    };

    // console.log("data: ", $data);
    if (rfqChartInstance != null) {
      rfqChartInstance.destroy();
    }

    $.ajax({
      url: "./backend/Route/requestRouteAction.php",
      type: "POST",
      data: {
        action: "get_chart_data",
        data: $data,
      },
      dataType: "json",
      success: function (response) {
        if (response.status !== "success") {
          console.log("No data from server:", response);
          return;
        }

        // Month labels: Jan, Feb, Mar...
        const chartLabels = generateMonthLabels();

        // Assign data from server
        const chartCompleted = response.Completed;
        const chartPending = response.Pending;
        const chartHold = response.Rejected;
        const ctx = document.getElementById("rfqChart").getContext("2d");

        // 🎨 Gradient backgrounds
        const gradientCompleted = ctx.createLinearGradient(0, 0, 0, 400);
        gradientCompleted.addColorStop(0, "rgba(59,130,246,0.9)");
        gradientCompleted.addColorStop(1, "rgba(96,165,250,0.4)");

        const gradientPending = ctx.createLinearGradient(0, 0, 0, 400);
        gradientPending.addColorStop(0, "rgba(251,191,36,0.9)");
        gradientPending.addColorStop(1, "rgba(253,224,71,0.4)");

        const gradientHold = ctx.createLinearGradient(0, 0, 0, 400);
        gradientHold.addColorStop(0, "rgba(239,68,68,0.9)");
        gradientHold.addColorStop(1, "rgba(252,165,165,0.4)");

        rfqChartInstance = new Chart(ctx, {
          type: "bar",
          data: {
            labels: chartLabels,
            datasets: [
              {
                label: "Completed",
                data: chartCompleted,
                backgroundColor: gradientCompleted,
                borderColor: "rgba(37, 99, 235, 1)",
                borderWidth: 1.5,
                borderRadius: 6,
                barPercentage: 0.7,
              },
              {
                label: "On-going",
                data: chartPending,
                backgroundColor: gradientPending,
                borderColor: "rgba(217, 119, 6, 1)",
                borderWidth: 1.5,
                borderRadius: 6,
                barPercentage: 0.7,
              },
              {
                label: "Hold",
                data: chartHold,
                backgroundColor: gradientHold,
                borderColor: "rgba(220, 38, 38, 1)",
                borderWidth: 1.5,
                borderRadius: 6,
                barPercentage: 0.7,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,

            // 🌟 Animation Section
            animation: {
              duration: 1500, // 1.5s
              easing: "easeOutBounce", // smooth bounce effect
              delay: (context) => {
                let delay = 0;
                if (
                  context.type === "data" &&
                  context.mode === "default" &&
                  context.dataIndex !== undefined
                ) {
                  delay = context.dataIndex * 100; // staggered delay
                }
                return delay;
              },
            },

            plugins: {
              legend: {
                position: "top",
                labels: {
                  color: "#1F2937",
                  font: {
                    size: 13,
                    weight: "600",
                    family: "'Poppins', 'Inter', 'Segoe UI', sans-serif",
                  },
                  usePointStyle: true,
                  padding: 16,
                },
              },
              title: {
                display: true,
                text: "Status Trend for the Year",
                color: "#111827",
                font: {
                  size: 18,
                  weight: "700",
                  family: "'Poppins', 'Inter', 'Segoe UI', sans-serif",
                },
                padding: { top: 10, bottom: 20 },
              },
              tooltip: {
                backgroundColor: "rgba(255,255,255,0.95)",
                titleColor: "#111827",
                bodyColor: "#1F2937",
                borderColor: "rgba(0,0,0,0.1)",
                borderWidth: 1,
                boxPadding: 6,
                usePointStyle: true,
                cornerRadius: 10,
                titleFont: { size: 14, weight: "600" },
                bodyFont: { size: 12 },
                callbacks: {
                  label: (context) =>
                    `${context.dataset.label}: ${context.formattedValue}`,
                },
              },
            },

            scales: {
              x: {
                stacked: true,
                ticks: {
                  color: "#4B5563",
                  font: { size: 12 },
                },
                grid: {
                  color: "rgba(0, 0, 0, 0.05)",
                },
              },
              y: {
                stacked: true,
                ticks: {
                  color: "#4B5563",
                  font: { size: 12 },
                },
                grid: {
                  color: "rgba(0, 0, 0, 0.05)",
                },
              },
            },

            interaction: {
              mode: "index",
              intersect: false,
            },

            // 🎯 Hover Effect (slight scale-up)
            hover: {
              mode: "nearest",
              intersect: true,
              onHover: (event, chartElement) => {
                event.native.target.style.cursor = chartElement.length
                  ? "pointer"
                  : "default";
              },
            },
          },
        });
      },
      error: function (err) {
        console.error("Error fetching chart data:", err);
      },
    });

    function generateMonthLabels() {
      const months = [];
      for (let i = 0; i < 12; i++) {
        const date = new Date(2000, i, 1); // year is irrelevant
        months.push(date.toLocaleString("default", { month: "short" }));
      }
      return months;
    }
  }

  function InitializeCardStatus() {
    const year = $("#yearSelect").val();
    const $data = {
      role: role,
      section: section,
      username: username,
      year: year,
    };
    $.ajax({
      url: "./backend/Route/requestRouteAction.php",
      type: "POST",
      data: {
        action: "get_piechart_data",
        data: $data,
      },
      dataType: "json",
      success: function (response) {
        if (response.status !== "success") {
          console.log("No data from server:", response);
          return;
        }
        const chartData =
          response.data.data.length > 0
            ? response.data.data[0]
            : {
                Completed: 0,
                "On-going": 0,
                Hold: 0,
              };

        const Completed = parseInt(chartData.Completed) || 0;
        const Pending = parseInt(chartData["On-going"]) || 0;
        const Hold = parseInt(chartData.Hold) || 0;

        const TotalRFQs = Completed + Pending + Hold;
        // console.log(TotalRFQs);
        $("#totalrfq").text(TotalRFQs);
        $("#pending").text(Pending);
        $("#completed").text(Completed);
        $("#hold").text(Hold);
        $(".overview").text(
          "Overview of your RFQ activities for the month of " + month
        );
      },
      error: function (err) {
        console.error("Error fetching chart data:", err);
      },
    });
  }

  function RenderLatestRequest() {
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
        action: "get_latest_request",
        section: section,
      },
      dataType: "json",
      success: function (response) {
        $tbody.empty();
        // console.log(response);
        if (response.status == "success") {
          response.data.forEach((item) => {
            const statusClasses = {
              Completed: "badge-approved",
              "On-going": "badge-pending",
              Rejected: "badge-rejected",
              Hold: "badge-hold",
            };

            const statusBadge = `
                  <span class="status-badge ${
                    statusClasses[item.requestor_status] || ""
                  }">
                    ${item.requestor_status}
                  </span>`;

            const $row = $(`
                            <tr>
                                <td>${item.control_number}</td>
                                <td>${item.item_name}</td>
                                <td>${item.item_description}</td>
                                <td>${statusBadge}</td>
                                <td>${item.created_at}</td>
                            </tr>
                        `);

            $tbody.append($row);
          });
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
      error: function (err) {
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

  $(document).on("change", "#yearSelect", function (e) {
    e.preventDefault();
    const year = $(this).data("year");
    $(".year-option").removeClass("active");
    $(this).addClass("active");
    InitializeBarChart();
  });
  // Submit new request
  $("#create_request").submit(function (e) {
    e.preventDefault();
    const formData = new FormData($("#create_request")[0]);
    formData.append("action", "create_request");
    formData.append("remarks", "For Section head approval");
    // console.log(formData);

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
            window.location.reload();
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

  $("#itemsTableBody").on("input", "#quantity", function () {
    this.value = this.value.replace(/[^0-9.]/g, "");
  });

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
                        <option value="Box">Box</option>s
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

  RenderLatestRequest();
  InitializeCardStatus();
  InitializeBarChart();
});
