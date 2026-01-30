// User list functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add any interactive features for user list here
    console.log('User list loaded');
    
    // Example: Add row click functionality
    const tableRows = document.querySelectorAll('.user-table tbody tr');
    tableRows.forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function() {
            // Could add modal or detail view here
            console.log('User row clicked');
        });
    });
});
