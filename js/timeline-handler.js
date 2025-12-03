

document.querySelectorAll('.btn-view-timeline').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const applicationId = e.target.closest('button').dataset.applicationId;

        try {
            const response = await fetch(`/ATS/api/get-timeline.php?application_id=${applicationId}`);
            const result = await response.json();

            if (result.success) {
                const app = result.application;
                const timeline = result.timeline;

                let timelineHTML = '';

                if (timeline.length === 0) {
                    timelineHTML = `
                        <div class="timeline-empty">
                            <i class="fas fa-stream"></i>
                            <h3>No timeline events yet</h3>
                            <p>Timeline events will appear here as you update your application</p>
                        </div>
                    `;
                } else {
                    timelineHTML = '<div class="timeline">';

                    timeline.forEach((event, index) => {
                        const eventDate = new Date(event.event_date);
                        const formattedDate = eventDate.toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        let markerClass = '';
                        let iconClass = 'fa-circle';

                        switch (event.event_type) {
                            case 'application_submitted':
                                iconClass = 'fa-paper-plane';
                                break;
                            case 'status_changed':
                                markerClass = 'completed';
                                iconClass = 'fa-check-circle';
                                break;
                            case 'interview_scheduled':
                            case 'interview_completed':
                                markerClass = 'interview';
                                iconClass = 'fa-calendar-check';
                                break;
                            case 'offer_received':
                                markerClass = 'offer';
                                iconClass = 'fa-gift';
                                break;
                            case 'follow_up':
                                iconClass = 'fa-phone';
                                break;
                            case 'note_added':
                                iconClass = 'fa-sticky-note';
                                break;
                        }

                        timelineHTML += `
                            <div class="timeline-item">
                                <div class="timeline-marker ${markerClass}"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <h4 class="timeline-title">
                                            <i class="fas ${iconClass}"></i>
                                            ${event.event_title}
                                        </h4>
                                        <span class="timeline-date">${formattedDate}</span>
                                    </div>
                                    ${event.event_description ? `<p class="timeline-description">${event.event_description}</p>` : ''}
                                </div>
                            </div>
                        `;
                    });

                    timelineHTML += '</div>';
                }

                document.getElementById('timelineContent').innerHTML = timelineHTML;
                document.getElementById('timelineModal').style.display = 'flex';
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while loading the timeline');
        }
    });
});
