$(document).ready(function(){
    $('#controlNumberForm').submit(function(e){
        e.preventDefault();
        const control_number = $('#controlNumberInput').val();
        console.log(control_number);
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: {action: 'gettimeline', control_number: control_number},
            dataType: 'json',
            success: function(response){
                    const timelineContainer = $('.timeline');
                    timelineContainer.empty(); // clear existing items

                    if (response.status === 'success' && Array.isArray(response.logs) && response.logs.length > 0) {
                        $('#noTimelineMessage').hide();
                        $('#timelineSection').show();

                        response.logs.forEach(item => {
                        let statusIcon = '';
                        let statusColor = 'text-secondary'; // default

                        switch (item.status.toLowerCase()) {
                            case 'pending':
                                statusIcon = 'bi bi-hourglass-split';
                                statusColor = 'text-warning';
                                break;
                            case 'hold':
                                statusIcon = 'bi bi-pause-circle-fill';
                                statusColor = 'text-secondary';
                                break;
                            case 'completed':
                                statusIcon = 'bi bi-check-circle-fill';
                                statusColor = 'text-success';
                                break;
                            default:
                                statusIcon = 'bi bi-info-circle';
                                statusColor = 'text-muted';
                                break;
                        }


                        const timelineItem = `
                            <li class="timeline-item text-center flex-shrink-0">
                                <div class="timeline-icon ${statusColor}">
                                    <i class="${statusIcon}"></i>
                                </div>
                                <div class="fw-bold ${statusColor}">${item.status}</div>
                                <small class="text-muted">${item.date} ${item.time}</small>
                                <div class="mt-1">${item.remarks || '<em>No remarks</em>'}</div>
                            </li>`;

                        
                        timelineContainer.append(timelineItem);
                    });

                    } else {
                        $('#timelineSection').hide();
                        $('#noTimelineMessage').show();
                    }
            },
            error: function(xhr, status, error){
                    Swal.close();
                    console.error('AJAX error:', status, error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while creating the timeline.',
                    });
            }
        })
    });

    $("#controlNumberInput").on('keyup', function(){
        const $query = $(this).val();

        console.log($query);

        if ($query.length >= 2) {
            $.ajax({
                url: './action.php',
                type: 'POST',
                data: {query: $query, action: 'getsuggestion'},
                dataType: 'json',
                success: function(response){
                    let suggestions = '';
                    if (response.status == 'success') {
                        if (response.data.length > 0) {
                            response.data.forEach(item => {
                                suggestions += `<button type="button" class="list-group-item list-group-item-action rfq-item">${item}</button>`;
                            });
                        }else{
                            suggestions = `<div class="list-group-item disabled">No matches found</div>`;
                        }
                        $('#rfqSuggestionList').html(suggestions).show();
                    }
                }
            });
        }else{
            $('#rfqSuggestionList').empty().hide();
        }
    });

    $(document).on('click', '.rfq-item', function () {
        $('#controlNumberInput').val($(this).text());
        $('#rfqSuggestionList').empty().hide();
    });
});