// filter table
function initTableFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('feedbackSearch');
    const feedbackTable = document.querySelector('.feedback-table');
    const rows = feedbackTable.querySelectorAll('tbody tr');

    function applyFilters() {
        const categoryValue = categoryFilter.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();

        rows.forEach(row => {
            const category = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const status = row.querySelector('td:nth-child(5) .status-badge').textContent.toLowerCase();
            const title = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const user = row.querySelector('td:nth-child(2) span').textContent.toLowerCase();

            const categoryMatch = categoryValue === 'all' || category.includes(categoryValue);
            const statusMatch = statusValue === 'all' || status.includes(statusValue);
            const searchMatch = searchValue === '' || 
                title.includes(searchValue) || 
                user.includes(searchValue) ||
                row.querySelector('td:first-child').textContent.includes(searchValue);

            row.style.display = (categoryMatch && statusMatch && searchMatch) ? '' : 'none';
        });
    }

    categoryFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
}

document.addEventListener('DOMContentLoaded', initTableFilters);