$(document).ready(function() {
    // Initialize RFQ Status Chart
    let rfqChart = null; // Removed duplicate declaration
    
    // const currentYear = new Date().getFullYear();
    const currentYear = $('.year-option').data('year');
    const section = $('.chart-container').data('section');

    // $('#selectedYear').text(currentYear); // Set default visible label
    // console.log("Current year:", currentYear);
    
    // Load data for the default year
    loadChartData(currentYear, section);
    updateSummaryOverview(currentYear,section);

    $(document).on('click', '.year-option', function (e) {
        e.preventDefault(); // Prevent default anchor behavior
        const selectedYear = $(this).data('year');
        $('#selectedYear').text(selectedYear); // Update the visible label
        
        // console.log("Selected section:", section);

        // console.log("Selected year:", selectedYear);
    
        
        loadChartData(selectedYear, section);
        updateSummaryOverview(selectedYear, section);
    });
    
    // Load chart data based on the selected year
    function loadChartData(year, section) {
        // console.log("Loading chart data for year:", year, "and section:", section);
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: { action: 'get_chart_data', section: section, year: year },
            dataType: 'json',
            success: function (response) {
                // console.log('Chart data response:', response);
                if (response.status === 'success') {
                    if (rfqChart) {
                        rfqChart.destroy(); // Destroy the previous chart instance
                        rfqChart = null; // Reset the chart variable
                    }

                    // Create a new chart instance
                    const ctx = $('#rfqChart')[0].getContext('2d');

                    // Set the chart data
                    rfqChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            datasets: [
                                {
                                    label: 'Approved',
                                    data: response.approved,
                                    backgroundColor: '#007bff',
                                    borderRadius: 4
                                },
                                {
                                    label: 'Pending',
                                    data: response.pending,
                                    backgroundColor: '#ffc107',
                                    borderRadius: 4
                                },
                                {
                                    label: 'Rejected',
                                    data: response.rejected,
                                    backgroundColor: '#dc3545',
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#e9ecef'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    backgroundColor: '#2d3748',
                                    titleFont: {
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 14
                                    },
                                    padding: 12,
                                    cornerRadius: 8
                                }
                            }
                        }
                    });
                    
                } else {
                    console.error('Error fetching chart data:', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        })
    }
    
    // Initialize RFQ table 
    populateTable();

    // Button click handlers
    $('#newRfqBtn').click(function() {
        $('#newRfqModal').modal('show');
    });
    
    $('#manageVendorsBtn').click(function() {
        alert('Redirecting to vendor management page');
        // window.location.href = '/vendors';
    });
    
    $('#generateReportBtn').click(function() {
        alert('Generating RFQ report...');
        // Generate report logic here
    });
    
    // Sample activity feed update
    function updateActivityFeed() {
        const activities = [
            {
                icon: 'bi-check-circle-fill text-success',
                text: 'RFQ-2023-058 submitted for approval',
                time: 'Just now'
            },
            {
                icon: 'bi-person-plus-fill text-info',
                text: 'New vendor added: Office Supplies Co.',
                time: '30 minutes ago'
            },
            {
                icon: 'bi-file-earmark-text text-primary',
                text: 'RFQ-2023-057 updated',
                time: '1 hour ago'
            }
        ];
        
        // Prepend new activity
        activities.forEach(activity => {
            $('#activityTimeline').prepend(
                $('<div class="list-group-item border-0">').append(
                    $('<div class="d-flex overflow-scroll">').append(
                        $('<i class="me-2">').addClass(activity.icon),
                        $('<small>').text(activity.text)
                    ),
                    $('<small class="text-muted">').text(activity.time)
                )
            );
        });
    }
    
    // Simulate periodic activity updates
    setInterval(updateActivityFeed, 2400000);
    
    // Card hover effects
    $('.dashboard-card').hover(
        function() {
            $(this).css('transform', 'translateY(-3px)')
                   .css('box-shadow', '0 5px 15px rgba(0, 0, 0, 0.1)');
        },
        function() {
            $(this).css('transform', '')
                   .css('box-shadow', '');
        }
    );

     // Populate the table
     function populateTable(page = 1) {
        const section = $('#rfqTableBody').data('section');

        const today = new Date();
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(today.getDate() - 6);
        
        const toDate = today.toISOString().split('T')[0];
        const fromDate = sevenDaysAgo.toISOString().split('T')[0];

        const filters = {
            from: fromDate,
            to: toDate
        };

        const $tbody = $('#rfqTableBody');
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

                // console.log('Response from server:', response);
                // console.log(response.total, response.perPage);
                if (response.status === 'success') {
                    response.data.forEach(item => {
                        const statusClasses = {
                            'Approved': 'badge-approved',
                            'Pending': 'badge-pending',
                            'Declined': 'badge-declined',
                            'Cancelled': 'badge-cancelled'
                        };

                        const statusBadge = `<span class="status-badge ${statusClasses[item.requestor_status] || ''}">${item.requestor_status}</span>`;
                        
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

    //Summary overview
    function updateSummaryOverview(year, section) {
        // console.log("Updating summary overview for year:", year, "and section:", section);
        let total = 0;


        $('#totalrfq').text('0');
        $('#approved').text('0');
        $('#pending').text('0');
        $('#rejected').text('0');

        $.ajax({
            url: './action.php',
            type: 'POST',
            data: { action: 'get_summary_overview', section: section, year: year },
            dataType: 'json',
            success: function (response) {
                // console.log('Summary overview response:', response);
                response.forEach(item => {
                    const count = parseInt(item.total_count);
                    total += count;
                    if (item.item_status === 'Approved') {
                        $('#approved').text(count);
                    } else if (item.item_status === 'Pending') {
                        $('#pending').text(count);
                    } else if (item.item_status === 'Rejected') {
                        $('#rejected').text(count);
                    }
                });
                $('#totalrfq').text(total);
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }
});