$(document).ready(function(){

    const role = $('.chart-container').data('role');
    const section = $('.chart-container').data('section');
    const $tbody = $('#rfqTableBody');
    const now = new Date();
    const month = now.toLocaleString('default', { month: 'long' });
    let rfqChartInstance = null;
    
    function InitializeBarChart() {
        const year = $('#yearSelect').val();
        const $data = {
            role: role,
            section: section,
            year: year
        };

        if (rfqChartInstance != null) {
                    rfqChartInstance.destroy();
        }

        $.ajax({
            url: './backend/Route/requestRouteAction.php',
            type: 'POST',
            data: {
                action: 'get_chart_data',
                data: $data
            },
            dataType: 'json',
            success: function(response) {
                


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
                const ctx = document.getElementById('rfqChart').getContext('2d');

                rfqChartInstance =  new Chart(ctx, {
                    type: 'bar', // Area chart in Chart.js is basically a line chart with fill
                    data: {
                        labels: chartLabels, // e.g. ["January", "February", "March", ...]
                        datasets: [
                            {
                                label: 'Completed',
                                data: chartCompleted,
                                backgroundColor: 'rgba(61, 228, 131, 0.44)',
                                borderColor: 'rgba(61, 228, 131, 1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Pending',
                                data: chartPending,
                                backgroundColor: 'rgba(24, 156, 218, 0.5)',
                                borderColor: 'rgba(69, 174, 223, 1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Hold',
                                data: chartHold,
                                backgroundColor: 'rgba(236, 29, 29, 0.4)',
                                borderColor: 'rgba(243, 84, 84, 1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            title: {
                                display: true,
                                text: 'Status Trend for the Year'
                            }
                        },
                        scales: {
                            x:{
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    }
                });


            },
            error: function(err) {
                console.error("Error fetching chart data:", err);
            }
        });

        function generateMonthLabels() {
            const months = [];
            for (let i = 0; i < 12; i++) {
                const date = new Date(2000, i, 1); // year is irrelevant
                months.push(date.toLocaleString('default', { month: 'short' }));
            }
            return months;
        }
    }

    function InitializeCardStatus(){
        const year = $('#yearSelect').val();
        const $data = {
            role: role,
            section: section,
            year: year
        };
         $.ajax({
            url: './backend/Route/requestRouteAction.php',
            type: 'POST',
            data: {
                action: 'get_piechart_data',
                data: $data
            },
            dataType: 'json',
            success: function(response) {
                if (response.status !== "success") {
                    console.log("No data from server:", response);
                    return;
                }
            const chartData = response.data.data.length > 0 ? response.data.data[0] : { 
                Completed: 0, 
                Pending: 0, 
                Hold: 0 
            };

            const Completed = parseInt(chartData.Completed) || 0;
            const Pending = parseInt(chartData.Pending) || 0;
            const Hold = parseInt(chartData.Hold) || 0;

            const TotalRFQs = Completed + Pending + Hold;
                console.log(TotalRFQs);
                $('#totalrfq').text(TotalRFQs);
                $('#pending').text(Pending);
                $('#completed').text(Completed);
                $('#hold').text(Hold);
                $('.overview').text('Overview of your RFQ activities for the month of ' + month);

            },
            error: function(err) {
                console.error("Error fetching chart data:", err);
            }
        });       
    }

    function RenderLatestRequest(){
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
            url: '././backend/Route/requestRouteAction.php',
            type: 'POST',
            data: {
                action: 'get_latest_request',
                section: section
            },
            dataType: 'json',
            success: function(response){
                $tbody.empty();
                console.log(response);
                if (response.status == 'success') {
                    response.data.forEach(item => {
                        const $row = $(`
                            <tr>
                                <td>${item.control_number}</td>
                                <td>${item.item_name}</td>
                                <td>${item.item_description}</td>
                                <td>${item.requestor_status}</td>
                                <td>${item.created_at}</td>
                            </tr>
                        `);
                        
                        $tbody.append($row);
                    });
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
            error: function(err){
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

    $(document).on('change', '#yearSelect',function(e){
        e.preventDefault();
        const year = $(this).data('year');
        $('.year-option').removeClass('active');
        $(this).addClass('active');
        InitializeBarChart();    
    });
    // Submit new request
    $('#create_request').submit(function (e) {
        e.preventDefault();
        const formData = new FormData($('#create_request')[0]);
        formData.append('action', 'create_request');
        formData.append('remarks', 'For Section head approval');
        console.log(formData);

        $.ajax({
            url: '././backend/Route/requestRouteAction.php',
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

    // Add new item row
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

    RenderLatestRequest();
    InitializeCardStatus();
    InitializeBarChart();
});