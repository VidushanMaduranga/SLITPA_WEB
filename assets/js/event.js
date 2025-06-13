document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
});

function loadEvents() {
    fetch('events/event_api.php?action=list')
        .then(response => response.json())
        .then(data => {
            // Merge events and CPD sessions for the calendar
            const allUpcoming = [
                ...data.upcoming.map(ev => ({...ev, type: 'event'})),
                ...(data.cpd_upcoming ? data.cpd_upcoming.map(cpd => ({...cpd, type: 'cpd'})) : [])
            ];
            renderUpcomingEventsCalendar(allUpcoming);
            renderEventList(data.past, 'past-events-list', true);
        });
}

function renderEventList(events, containerId, isPast = false) {
    let html = '';
    if (!events || events.length === 0) {
        html = '<div class="col-12"><div class="alert alert-info">No events found.</div></div>';
    } else {
        if (isPast) {
            // Render each past event as a card/box
            events.forEach(event => {
                // Images row
                let imagesHtml = '';
                if (event.media && event.media.length > 0) {
                    imagesHtml = '<div class="row mt-3">';
                    event.media.forEach(img => {
                        if (img.media_type === 'image') {
                            imagesHtml += `<div class='col-md-4 mb-3'><img src='${img.file_path}' class='img-fluid rounded shadow-sm' alt='Event Image'></div>`;
                        }
                    });
                    imagesHtml += '</div>';
                }
                html += `
                <div class="card rounded-4 shadow p-4 mb-5 bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">${event.date}</div>
                        <div class="h5 fw-bold text-center flex-grow-1 m-0">${event.title}</div>
                        <div class="fw-bold text-end">${event.location}</div>
                    </div>
                    <div class="mb-2">${event.description || ''}</div>
                    ${imagesHtml}
                    <div class="text-end mt-3">
                        <button class="btn btn-primary view-details-btn" data-event-id="${event.id}">View Details</button>
                    </div>
                </div>
                `;
            });
        } else {
            html = '<div id="upcoming-events-calendar"></div>';
        }
    }
    document.getElementById(containerId).innerHTML = html;
    // Add click listeners for past events
    if (isPast) {
        document.querySelectorAll(`#${containerId} .view-details-btn`).forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const eventId = btn.getAttribute('data-event-id');
                showEventModal(eventId);
            });
        });
    }
}

// Placeholder for calendar rendering
function renderUpcomingEventsCalendar(events) {
    var calendarEl = document.getElementById('upcoming-events-calendar');
    if (!calendarEl) return;
    // Map events to FullCalendar format
    var calendarEvents = events.map(event => ({
        id: event.id,
        title: event.title,
        start: event.event_date || event.session_date,
        end: event.end_date && event.end_date !== event.event_date ? event.end_date : undefined,
        color: event.type === 'cpd' ? '#28a745' : '#1976d2', // green for CPD, blue for event
        extendedProps: {
            location: event.location,
            description: event.description,
            type: event.type
        }
    }));
    // Destroy previous calendar if exists
    if (calendarEl._fullCalendar) {
        calendarEl._fullCalendar.destroy();
    }
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 600,
        events: calendarEvents,
        eventClick: function(info) {
            showEventModal(info.event.id);
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }
    });
    calendar.render();
    calendarEl._fullCalendar = calendar;
}

function showEventModal(eventId) {
    fetch(`events/event_api.php?action=details&id=${eventId}`)
        .then(response => response.json())
        .then(event => {
            let imagesHtml = '';
            if (event.media && event.media.length > 0) {
                imagesHtml = `
                <div id="eventMediaCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        ${event.media.map((item, idx) => {
                            let path = item.file_path;
                            if (!/^uploads\//.test(path)) path = 'uploads/' + path;
                            if (event.type === 'cpd' && !/^uploads\/cpd_sessions\//.test(path)) path = 'uploads/cpd_sessions/' + item.file_path;
                            return `
                            <div class="carousel-item${idx === 0 ? ' active' : ''}">
                                    ${item.media_type === 'image' ? `<img src="${path}" class="d-block w-100" alt="Media">` : `<video controls class="d-block w-100"><source src="${path}" type="video/mp4"></video>`}
                            </div>
                            `;
                        }).join('')}
                    </div>
                    ${event.media.length > 1 ? `
                        <button class="carousel-control-prev" type="button" data-bs-target="#eventMediaCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#eventMediaCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    ` : ''}
                </div>
                `;
            }
            let modalHtml = '';
            if (event.type === 'cpd') {
                modalHtml = `
                    <div class="modal-header">
                        <h5 class="modal-title">${event.title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${imagesHtml}
                        <div class="mb-3"><strong>Date:</strong> ${event.date}</div>
                        <div class="mb-3"><strong>Location:</strong> ${event.location}</div>
                        <div class="mb-3"><strong>Organizer:</strong> ${event.organizer || ''}</div>
                        <div class="mb-3"><strong>Added:</strong> ${event.created_at || ''}</div>
                        <div class="mb-3">${event.description || ''}</div>
                    </div>
                `;
            } else {
                modalHtml = `
                <div class="modal-header">
                    <h5 class="modal-title">${event.title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ${imagesHtml}
                    <div class="mb-3"><strong>Date:</strong> ${event.date}</div>
                    <div class="mb-3"><strong>Location:</strong> ${event.location}</div>
                        <div class="mb-3">${event.description || ''}</div>
                </div>
            `;
            }
            document.getElementById('event-modal-content').innerHTML = modalHtml;
            var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
            eventModal.show();
        });
} 