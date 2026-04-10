/**
 * Calendar Dashboard JavaScript
 * Handles calendar rendering, events display, and modals
 */

// Calendar events data (populated from PHP)
let calendarEvents = [];

// Current date
let currentDate = new Date();

// Function to get status badge HTML
function getStatusBadge(category, statusTitle) {
    let config = { class: 'secondary', icon: 'bi-circle', text: statusTitle || 'N/A' };
    
    switch(category?.toLowerCase()) {
        case 'start':
            config = { class: 'success', icon: 'bi-play-circle', text: 'Started' };
            break;
        case 'end':
            config = { class: 'secondary', icon: 'bi-stop-circle', text: 'Completed' };
            break;
        case 'due':
            config = { class: 'danger', icon: 'bi-exclamation-triangle', text: 'Due Date' };
            break;
        case 'upcoming':
            config = { class: 'info', icon: 'bi-calendar-plus', text: 'Upcoming' };
            break;
        default:
            config = { class: 'info', icon: 'bi-info-circle', text: statusTitle || 'Active' };
    }
    
    return `<span class="badge bg-white border border-${config.class} text-${config.class} p-2"><i class="bi ${config.icon} me-1"></i>${config.text}</span>`;
}

// Function to get type badge HTML
function getTypeBadge(type) {
    const typeConfig = {
        'audit': { class: 'success', icon: 'bi-check-circle', text: 'Audit' },
        'compliance': { class: 'warning', icon: 'bi-shield-check', text: 'Compliance' },
        'upcoming_audit': { class: 'info', icon: 'bi-calendar-plus', text: 'Upcoming Audit' }
    };
    
    const config = typeConfig[type?.toLowerCase()] || { class: 'secondary', icon: 'bi-circle', text: type || 'Unknown' };
    return `<span class="badge bg-white border border-${config.class} text-${config.class} p-2"><i class="bi ${config.icon} me-1"></i>${config.text}</span>`;
}

// Function to render calendar
function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Set month/year display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    const monthYearElement = document.getElementById('currentMonthYear');
    if (monthYearElement) {
        monthYearElement.textContent = `${monthNames[month]} ${year}`;
    }
    
    // Get first day of month and total days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // Generate days HTML
    let daysHTML = '<div class="row g-0">';
    let dayCount = 1;
    
    // Create rows for each week
    for (let week = 0; week < 6 && dayCount <= daysInMonth; week++) {
        daysHTML += '<div class="row g-0">';
        
        for (let weekday = 0; weekday < 7; weekday++) {
            if (week === 0 && weekday < firstDay) {
                // Empty cell before month starts
                daysHTML += '<div class="col border bg-white" style="min-height: 120px;"></div>';
            } else if (dayCount > daysInMonth) {
                // Empty cell after month ends
                daysHTML += '<div class="col border bg-white" style="min-height: 120px;"></div>';
            } else {
                // Calendar day cell
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dayCount).padStart(2, '0')}`;
                
                // Find events for this day
                const dayEvents = calendarEvents.filter(event => event.start === dateStr);
                
                // Check if today
                const today = new Date();
                const isToday = today.getDate() === dayCount && 
                               today.getMonth() === month && 
                               today.getFullYear() === year;
                
                // Build day cell
                let dayClass = 'col border p-2 bg-white calendar-day-cell';
                if (isToday) dayClass += ' border-primary border-2';
                
                let dayContent = `<div class="${dayClass}" style="min-height: 120px;" onclick="showEventModal('${dateStr}')">`;
                dayContent += `<div class="d-flex justify-content-between align-items-start mb-2">`;
                
                // Day number styling
                if (isToday) {
                    dayContent += `<div class="day-number-today">${dayCount}</div>`;
                } else {
                    dayContent += `<span class="day-number text-dark">${dayCount}</span>`;
                }
                
                if (dayEvents.length > 0) {
                    dayContent += `<span class="event-count-badge">${dayEvents.length}</span>`;
                }
                
                dayContent += `</div>`;
                
                if (dayEvents.length > 0) {
                    // Create scrollable container for events
                    dayContent += '<div class="events-scrollable mt-2">';
                    
                    // Show all events with scrolling
                    dayEvents.forEach(event => {
                        let badgeClass = '';
                        let iconClass = '';
                        switch(event.type) {
                            case 'audit': 
                                badgeClass = 'event-badge-audit';
                                iconClass = 'bi-check-circle';
                                break;
                            case 'compliance': 
                                badgeClass = 'event-badge-compliance';
                                iconClass = 'bi-shield-check';
                                break;
                            case 'upcoming_audit':
                                badgeClass = 'event-badge-upcoming';
                                iconClass = 'bi-calendar-plus';
                                break;
                            default: 
                                badgeClass = 'event-badge-default';
                                iconClass = 'bi-calendar';
                        }
                        
                        // Add special class for due dates
                        if (event.category === 'due') {
                            badgeClass = 'event-badge-due';
                            iconClass = 'bi-exclamation-triangle';
                        }
                        
                        dayContent += `<div class="event-badge ${badgeClass}">
                            <i class="bi ${iconClass}"></i>${event.title.substring(0, 25)}${event.title.length > 25 ? '...' : ''}
                        </div>`;
                    });
                    
                    dayContent += '</div>';
                }
                
                dayContent += '</div>';
                daysHTML += dayContent;
                dayCount++;
            }
        }
        
        daysHTML += '</div>';
    }
    
    daysHTML += '</div>';
    const calendarDays = document.getElementById('calendarDays');
    if (calendarDays) {
        calendarDays.innerHTML = daysHTML;
    }
}

// Function to show event modal
window.showEventModal = function(date) {
    const dayEvents = calendarEvents.filter(event => event.start === date);
    
    if (dayEvents.length > 0) {
        // Format date for display
        const displayDate = new Date(date + 'T12:00:00').toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const modalDate = document.getElementById('modalDate');
        if (modalDate) {
            modalDate.textContent = displayDate;
        }
        
        // Build modal content with scrollable events
        let modalBody = '<div class="row g-3" style="max-height: 500px; overflow-y: auto;">';
        
        dayEvents.forEach((event, index) => {
            modalBody += `
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header" style="background-color: ${event.color}; color: white;">
                            <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>${event.title}</h6>
                        </div>
                        <div class="card-body bg-white">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <small class="text-secondary d-block">Type</small>
                                        ${getTypeBadge(event.type)}
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-secondary d-block">Audit Unit</small>
                                        <span class="text-dark"><i class="bi bi-building me-2 text-secondary"></i>${event.audit_unit_name || 'N/A'}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <small class="text-secondary d-block">Status</small>
                                        ${getStatusBadge(event.category, event.status_title)}
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-secondary d-block">Date</small>
                                        <span class="text-dark"><i class="bi bi-calendar3 me-2 text-secondary"></i>${event.start}</span>
                                    </div>
                                </div>
                            </div>
                            ${event.assesment_period_from && event.assesment_period_to ? `
                                <div class="mt-2">
                                    <small class="text-secondary d-block">Assessment Period</small>
                                    <span class="text-dark"><i class="bi bi-calendar-range me-2 text-secondary"></i>${event.assesment_period_from} to ${event.assesment_period_to}</span>
                                </div>
                            ` : ''}
                            ${event.description ? `
                                <div class="mt-3 p-3 bg-light rounded">
                                    <small class="text-secondary d-block mb-1"><i class="bi bi-file-text me-2"></i>Description</small>
                                    <p class="mb-0 text-dark">${event.description}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        modalBody += '</div>';
        const modalBodyElement = document.getElementById('eventDetailsModalBody');
        if (modalBodyElement) {
            modalBodyElement.innerHTML = modalBody;
        }
        
        // Show modal using Bootstrap 5
        const modalElement = document.getElementById('eventDetailsModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }
};

// Function to show event details from list view
window.showEventDetails = function(date) {
    const dayEvents = calendarEvents.filter(event => event.start === date);
    
    if (dayEvents.length > 0) {
        const displayDate = new Date(date + 'T12:00:00').toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const modalDate = document.getElementById('modalDate');
        if (modalDate) {
            modalDate.textContent = displayDate;
        }
        
        let modalBody = '<div class="row g-3" style="max-height: 500px; overflow-y: auto;">';
        
        dayEvents.forEach((event, index) => {
            modalBody += `
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header" style="background-color: ${event.color}; color: white;">
                            <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>${event.title}</h6>
                        </div>
                        <div class="card-body bg-white">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <small class="text-secondary d-block">Type</small>
                                        <span class="badge bg-white border border-${event.type == 'audit' ? 'success' : (event.type == 'compliance' ? 'warning' : 'info')} text-${event.type == 'audit' ? 'success' : (event.type == 'compliance' ? 'warning' : 'info')} p-2">
                                            <i class="bi ${event.type == 'audit' ? 'bi-check-circle' : (event.type == 'compliance' ? 'bi-shield-check' : 'bi-calendar-plus')} me-1"></i>
                                            ${event.type.toUpperCase()}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-secondary d-block">Audit Unit</small>
                                        <span class="text-dark"><i class="bi bi-building me-2 text-secondary"></i>${event.audit_unit_name || 'N/A'}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <small class="text-secondary d-block">Date</small>
                                        <span class="text-dark"><i class="bi bi-calendar3 me-2 text-secondary"></i>${event.start}</span>
                                    </div>
                                </div>
                            </div>
                            ${event.description ? `
                                <div class="mt-3 p-3 bg-light rounded">
                                    <small class="text-secondary d-block mb-1"><i class="bi bi-file-text me-2"></i>Description</small>
                                    <p class="mb-0 text-dark">${event.description}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        modalBody += '</div>';
        const modalBodyElement = document.getElementById('eventDetailsModalBody');
        if (modalBodyElement) {
            modalBodyElement.innerHTML = modalBody;
        }
        
        const modalElement = document.getElementById('eventDetailsModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }
};

// Month change function
window.changeMonth = function(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    renderCalendar();
};

// Initialize calendar when document is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof calendarEventsData !== 'undefined') {
        calendarEvents = calendarEventsData;
        renderCalendar();
    }
});