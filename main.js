// DOM Elements
const projectDetailsModal = document.getElementById('projectDetailsModal');
const closeDetailsModal = document.getElementById('closeDetailsModal');
const closeDetails = document.getElementById('closeDetails');
const searchInput = document.getElementById('searchProjects');

// Get projects from localStorage
function getProjects() {
    return JSON.parse(localStorage.getItem('projects')) || [];
}

// Format date for display
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

// Render ongoing project card
function renderOngoingProject(project) {
    const card = document.createElement('div');
    card.className = 'project-card';
    
    card.innerHTML = `
        <div class="project-img">
            <img src="${project.image || `https://via.placeholder.com/400x250?text=${encodeURIComponent(project.name)}`}" alt="${project.name}">
        </div>
        <div class="project-content">
            <h3>${project.name}</h3>
            <p>${project.description}</p>
            <a href="javascript:void(0)" class="more-btn" onclick="viewProjectDetails(${project.id})">View Details <i class="fas fa-chevron-right"></i></a>
        </div>
    `;
    
    return card;
}

// Render completed project card
function renderCompletedProject(project) {
    const card = document.createElement('div');
    card.className = 'completed-project';
    
    card.innerHTML = `
        <img src="${project.image || `https://via.placeholder.com/400x250?text=${encodeURIComponent(project.name)}`}" alt="${project.name}">
        <div class="project-overlay">
            <h3>${project.name}</h3>
            <p>Completed: ${formatDate(project.endDate)}</p>
        </div>
    `;
    
    card.addEventListener('click', () => viewProjectDetails(project.id));
    
    return card;
}

// Load projects by status
function loadProjectsByStatus(status) {
    const projects = getProjects();
    const filteredProjects = projects.filter(p => p.status === status);
    
    const container = document.getElementById(`${status}-projects`);
    container.innerHTML = '';
    
    if (filteredProjects.length === 0) {
        container.innerHTML = `
            <div class="no-projects">
                <p>No ${status} projects found</p>
            </div>
        `;
        return;
    }
    
    if (status === 'completed') {
        filteredProjects.forEach(project => {
            container.appendChild(renderCompletedProject(project));
        });
    } else {
        filteredProjects.forEach(project => {
            container.appendChild(renderOngoingProject(project));
        });
    }
}

// Load all projects
function loadAllProjects() {
    loadProjectsByStatus('ongoing');
    loadProjectsByStatus('completed');
    loadProjectsByStatus('planned');
}

// View project details
function viewProjectDetails(projectId) {
    const projects = getProjects();
    const project = projects.find(p => p.id === projectId);
    
    if (!project) {
        alert('Project not found');
        return;
    }
    
    document.getElementById('detailsModalTitle').textContent = project.name;
    
    document.getElementById('projectDetailsContent').innerHTML = `
        <div class="project-details-view">
            <div class="details-image">
                <img src="${project.image || `https://via.placeholder.com/400x250?text=${encodeURIComponent(project.name)}`}" alt="${project.name}">
            </div>
            
            <div class="details-info">
                <div class="info-group">
                    <h3>Category</h3>
                    <p>${project.category}</p>
                </div>
                
                <div class="info-group">
                    <h3>Status</h3>
                    <p>${project.status.charAt(0).toUpperCase() + project.status.slice(1)}</p>
                </div>
                
                <div class="info-group">
                    <h3>Progress</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${project.progress}%"></div>
                    </div>
                    <p>${project.progress}% Complete</p>
                </div>
                
                <div class="info-group">
                    <h3>Timeline</h3>
                    <p>Start Date: ${formatDate(project.startDate)}</p>
                    <p>Estimated Completion: ${formatDate(project.endDate)}</p>
                </div>
                
                <div class="info-group">
                    <h3>Budget</h3>
                    <p>${formatCurrency(project.budget)}</p>
                </div>
                
                <div class="info-group">
                    <h3>Description</h3>
                    <p>${project.description}</p>
                </div>
            </div>
        </div>
    `;
    
    projectDetailsModal.style.display = 'block';
}

// Filter projects by search term
function filterProjects(searchTerm) {
    const projects = getProjects();
    const term = searchTerm.toLowerCase();
    
    // Clear all project containers
    document.getElementById('ongoing-projects').innerHTML = '';
    document.getElementById('completed-projects').innerHTML = '';
    document.getElementById('planned-projects').innerHTML = '';
    
    if (!term) {
        loadAllProjects();
        return;
    }
    
    const filteredProjects = projects.filter(project => 
        project.name.toLowerCase().includes(term) || 
        project.category.toLowerCase().includes(term) ||
        project.description.toLowerCase().includes(term)
    );
    
    // Group filtered projects by status
    const ongoing = filteredProjects.filter(p => p.status === 'ongoing');
    const completed = filteredProjects.filter(p => p.status === 'completed');
    const planned = filteredProjects.filter(p => p.status === 'planned');
    
    // Render filtered projects
    if (ongoing.length > 0) {
        ongoing.forEach(project => {
            document.getElementById('ongoing-projects').appendChild(renderOngoingProject(project));
        });
    } else {
        document.getElementById('ongoing-projects').innerHTML = '<div class="no-projects"><p>No matching ongoing projects</p></div>';
    }
    
    if (completed.length > 0) {
        completed.forEach(project => {
            document.getElementById('completed-projects').appendChild(renderCompletedProject(project));
        });
    } else {
        document.getElementById('completed-projects').innerHTML = '<div class="no-projects"><p>No matching completed projects</p></div>';
    }
    
    if (planned.length > 0) {
        planned.forEach(project => {
            document.getElementById('planned-projects').appendChild(renderOngoingProject(project));
        });
    } else {
        document.getElementById('planned-projects').innerHTML = '<div class="no-projects"><p>No matching planned projects</p></div>';
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Load all projects
    loadAllProjects();
    
    // Close modal buttons
    closeDetailsModal.addEventListener('click', function() {
        projectDetailsModal.style.display = 'none';
    });
    
    closeDetails.addEventListener('click', function() {
        projectDetailsModal.style.display = 'none';
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        filterProjects(this.value);
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === projectDetailsModal) {
            projectDetailsModal.style.display = 'none';
        }
    });
});

// Make viewProjectDetails available globally
window.viewProjectDetails = viewProjectDetails;