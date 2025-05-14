// functionality for the projects list page

document.addEventListener('DOMContentLoaded', function() {
    // Load projects from localStorage
    const projects = JSON.parse(localStorage.getItem('projects')) || [];
    
    // Populate the projects table
    populateProjectsTable(projects);
    
    // Set up filter functionality
    setupFilters();
});

// Function to populate the projects table
function populateProjectsTable(projects) {
    const tableBody = document.getElementById('projectsTableBody');
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    // Add each project to the table
    projects.forEach(project => {
        const row = document.createElement('tr');
        
        // Format the budget with commas
        const formattedBudget = Number(project.budget).toLocaleString();
        
        // Determine status class for styling
        let statusClass = '';
        switch(project.status) {
            case 'completed':
                statusClass = 'status-completed';
                break;
            case 'ongoing':
                statusClass = 'status-ongoing';
                break;
            case 'planned':
                statusClass = 'status-planned';
                break;
        }
        
        // Format the status text (capitalize first letter)
        const statusText = project.status.charAt(0).toUpperCase() + project.status.slice(1);
        
        row.innerHTML = `
            <td>${project.name}</td>
            <td>${project.category}</td>
            <td>${formatDate(project.startDate)}</td>
            <td>${formatDate(project.endDate)}</td>
            <td>KSh ${formattedBudget}</td>
            <td>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${project.progress}%"></div>
                </div>
                <span>${project.progress}%</span>
            </td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon view-btn" onclick="viewProjectDetails(${project.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon edit-btn" onclick="redirectToEdit(${project.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete-btn" onclick="deleteProject(${project.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
}

// Function to redirect to manage-project.html for editing
function redirectToEdit(projectId) {
    // Store the project ID to edit in sessionStorage
    sessionStorage.setItem('editProjectId', projectId);
    // Redirect to manage-project.html
    window.location.href = 'manage-project.html';
}

// Function to delete a project
function deleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        // Get projects from localStorage
        let projects = JSON.parse(localStorage.getItem('projects')) || [];
        
        // Filter out the project to delete
        projects = projects.filter(project => project.id !== projectId);
        
        // Save updated projects array back to localStorage
        localStorage.setItem('projects', JSON.stringify(projects));
        
        // Refresh the table
        populateProjectsTable(projects);
        
        // Update project counts
        updateProjectCounts();
        
        // Show success message
        alert('Project deleted successfully!');
    }
}

// Function to set up filter functionality
function setupFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('projectSearch');
    
    // Function to apply all filters
    function applyFilters() {
        const categoryValue = categoryFilter.value;
        const statusValue = statusFilter.value;
        const searchValue = searchInput.value.toLowerCase();
        
        // Get all projects
        let projects = JSON.parse(localStorage.getItem('projects')) || [];
        
        // Apply category filter
        if (categoryValue !== 'all') {
            projects = projects.filter(project => project.category === categoryValue);
        }
        
        // Apply status filter
        if (statusValue !== 'all') {
            projects = projects.filter(project => project.status === statusValue);
        }
        
        // Apply search filter
        if (searchValue) {
            projects = projects.filter(project => 
                project.name.toLowerCase().includes(searchValue) ||
                project.category.toLowerCase().includes(searchValue) ||
                project.description.toLowerCase().includes(searchValue)
            );
        }
        
        // Update the table with filtered projects
        populateProjectsTable(projects);
    }
    
    // Add event listeners to filters
    categoryFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
}

// Check if we need to edit a project (coming from another page)
document.addEventListener('DOMContentLoaded', function() {
    const editProjectId = sessionStorage.getItem('editProjectId');
    
    if (editProjectId) {
        // Clear the session storage
        sessionStorage.removeItem('editProjectId');
        
        // Find the project
        const projects = JSON.parse(localStorage.getItem('projects')) || [];
        const project = projects.find(p => p.id === parseInt(editProjectId));
        
        if (project) {
            // Open the details modal first
            viewProjectDetails(project.id);
            
            // Then simulate clicking the edit button after a short delay
            setTimeout(() => {
                document.getElementById('editFromDetails').click();
            }, 500);
        }
    }
});

// Add CSS styles for the projects list page
document.head.insertAdjacentHTML('beforeend', `
<style>
    .projects-table-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
        overflow: hidden;
    }
    
    .table-filters {
        display: flex;
        padding: 15px;
        background-color: #f9f9f9;
        border-bottom: 1px solid #eee;
    }
    
    .filter-group {
        margin-right: 20px;
        display: flex;
        align-items: center;
    }
    
    .filter-group label {
        margin-right: 8px;
        font-weight: 500;
    }
    
    .filter-group select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: white;
    }
    
    .projects-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .projects-table th {
        background-color: #f5f5f5;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #ddd;
    }
    
    .projects-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    
    .projects-table tr:hover {
        background-color: #f9f9f9;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 500;
    }
    
    .status-completed {
        background-color: #e6f7e6;
        color: #2e7d32;
    }
    
    .status-ongoing {
        background-color: #e3f2fd;
        color: #1565c0;
    }
    
    .status-planned {
        background-color: #fff8e1;
        color: #ff8f00;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .view-btn {
        background-color: #e3f2fd;
        color: #1565c0;
    }
    
    .view-btn:hover {
        background-color: #bbdefb;
    }
    
    .edit-btn {
        background-color: #e8f5e9;
        color: #2e7d32;
    }
    
    .edit-btn:hover {
        background-color: #c8e6c9;
    }
    
    .delete-btn {
        background-color: #ffebee;
        color: #c62828;
    }
    
    .delete-btn:hover {
        background-color: #ffcdd2;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .projects-table th:nth-child(3),
        .projects-table td:nth-child(3),
        .projects-table th:nth-child(4),
        .projects-table td:nth-child(4) {
            display: none;
        }
    }
    
    @media (max-width: 768px) {
        .table-filters {
            flex-direction: column;
            gap: 10px;
        }
        
        .filter-group {
            width: 100%;
        }
        
        .filter-group select {
            width: 100%;
        }
        
        .projects-table th:nth-child(5),
        .projects-table td:nth-child(5) {
            display: none;
        }
    }
</style>
`);
