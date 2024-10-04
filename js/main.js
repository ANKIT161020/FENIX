document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    // Handle active state for navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });

    // Handle active state for filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            filterFiles(button.textContent.trim());
        });
    });

    // Function to filter files (dummy example)
    function filterFiles(filterType) {
        console.log(`Filtering files by: ${filterType}`);
        // Add file filtering logic here
    }

    // Example of a notification system
    const notification = document.querySelector('.notification');
    
    function showNotification(message) {
        notification.textContent = message;
        notification.classList.add('show');
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    // Example usage
    showNotification('Dashboard v1.0 notes deleted. Restore or dismiss.');
});
