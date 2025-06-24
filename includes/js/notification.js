$(document).ready(function() {
    
    loadNotifications();

    function loadNotifications() {
        const action = "get_notification";
        const section = $('#notification-list').data('section');
        const role = $('#notification-list').data('role');
        console.log("Loading notifications for section:", section, "and role:", role);
        $.ajax({
            url: './action.php',
            type: 'POST',
            data: {
                action: action,
                section: section,
                role: role
            },
            dataType: 'json',
            success: function(response){
                if (response.status === 'success') {
                    console.log("Notifications loaded successfully:", response.data.notifications);
                    // Clear existing notifications
                    $('#notification-list').empty();

                    // Append new notifications
                    response.data.notifications.forEach(notification => {
                        const notificationItem = `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>${notification.message}</span>
                                    <small class="text-muted">${new Date(notification.created_at).toLocaleString()}</small>
                                </div>
                            </li>
                        `;
                        $('#notification-list').append(notificationItem);
                    });
                } else {
                    console.error("Failed to load notifications:", response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading notifications:", error);
            }
        })
    }

}); 
// CSS for notification