$(document).ready(function() {
    const $sidebar = $('#sidebar');
    const $sidebarToggle = $('#sidebarToggle');
    const $mainContent = $('#mainContent');
    const $notificationBadge = $('#notificationBadge');

    // Toggle sidebar on button click
    $sidebarToggle.on('click', function() {
        $sidebar.toggleClass('sidebar-collapsed');
        $mainContent.toggleClass('sidebar-collapsed');
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(event) {
        if (window.innerWidth <= 992) {
            const isClickInsideSidebar = $(event.target).closest($sidebar).length > 0;
            const isClickOnToggle = $(event.target).closest($sidebarToggle).length > 0;

            if (!isClickInsideSidebar && !isClickOnToggle && !$sidebar.hasClass('sidebar-collapsed')) {
                $sidebar.addClass('sidebar-collapsed');
                $mainContent.addClass('sidebar-collapsed');
            }
        }
    });

    // Handle window resize
    $(window).on('resize', function() {
        if (window.innerWidth > 992) {
            $sidebar.removeClass('sidebar-collapsed');
            $mainContent.removeClass('sidebar-collapsed');
        }
    });

    const userSection = $sidebar.data('section');
    const userRole = $sidebar.data('role');
    const socket = new WebSocket("ws://192.168.101.49:8080");
    console.log("WebSocket connection established for section:", userSection, "and role:", userRole); // DEBUG

    socket.onopen = () => {
        socket.send(JSON.stringify({
            event: "auth",
            section: userSection,
            role: userRole
        }));
    };

    socket.onmessage = function(event) {
        console.log("Raw event data:", event.data); // DEBUG

        const message = JSON.parse(event.data);
        console.log("Parsed message:", message); // DEBUG

        if (message.event === 'new_request') {
            $notificationBadge.text(parseInt($notificationBadge.text() || '0', 10) + 1);
        }
    };

    socket.onerror = function(error) {
        console.error('WebSocket error:', error);
    };

    socket.onclose = function() {
        console.warn('WebSocket closed. Attempting to reconnect...');
        setTimeout(() => location.reload(), 3000);
    };

    $notificationBadge.on('click', function() {
        $(this).text('');
        console.log("Notification badge clicked, resetting count");
    });
});
