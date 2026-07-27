


function closeApplicationModal() {
    document.getElementById('addApplicationModal').style.display = 'none';
    document.getElementById('applicationForm').reset();
    document.getElementById('interviewDetailsSection').style.display = 'none';
}

function closeViewApplicationModal() {
    document.getElementById('viewApplicationModal').style.display = 'none';
}

function closeTimelineModal() {
    document.getElementById('timelineModal').style.display = 'none';
}


document.getElementById('applicationStatus')?.addEventListener('change', (e) => {
    const interviewSection = document.getElementById('interviewDetailsSection');
    const interviewStatuses = ['Interview Scheduled', 'Interview Completed'];

    if (interviewStatuses.includes(e.target.value)) {
        interviewSection.style.display = 'block';
    } else {
        interviewSection.style.display = 'none';
    }
});


document.getElementById('addApplicationBtn')?.addEventListener('click', () => {
    document.getElementById('addApplicationModal').style.display = 'flex';
});


window.addEventListener('click', (e) => {
    const addModal = document.getElementById('addApplicationModal');
    const viewModal = document.getElementById('viewApplicationModal');
    const timelineModal = document.getElementById('timelineModal');

    if (e.target === addModal) {
        closeApplicationModal();
    }
    if (e.target === viewModal) {
        closeViewApplicationModal();
    }
    if (e.target === timelineModal) {
        closeTimelineModal();
    }
});


document.getElementById('applicationForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        company_name: formData.get('company_name'),
        job_title: formData.get('job_title'),
        job_location: formData.get('job_location'),
        job_type: formData.get('job_type'),
        application_date: formData.get('application_date'),
        salary_range: formData.get('salary_range'),
        status: formData.get('status'),
        priority: formData.get('priority'),
        application_url: formData.get('application_url'),
        contact_person: formData.get('contact_person'),
        contact_email: formData.get('contact_email'),
        notes: formData.get('notes'),
        interview_date: formData.get('interview_date'),
        interview_location: formData.get('interview_location'),
        interview_notes: formData.get('interview_notes')
    };

    const editId = e.target.dataset.editId;
    const isEdit = !!editId;

    if (isEdit) {
        data.application_id = editId;
    }

    try {
        const url = appUrl(isEdit ? 'api/update-application.php' : 'api/add-application.php');
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            closeApplicationModal();
            delete e.target.dataset.editId;
            document.querySelector('#addApplicationModal h2').textContent = 'Add Job Application';
            document.querySelector('#addApplicationModal button[type="submit"]').textContent = 'Add Application';
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
});


document.querySelectorAll('.application-status-select').forEach(select => {
    select.addEventListener('change', async (e) => {
        const applicationId = e.target.dataset.applicationId;
        const newStatus = e.target.value;

        if (!confirm('Are you sure you want to update the status?')) {
            e.target.value = e.target.dataset.originalValue || e.target.value;
            return;
        }

        try {
            const response = await fetch(appUrl('api/update-application-status.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    application_id: applicationId,
                    status: newStatus
                })
            });

            const result = await response.json();

            if (result.success) {
                
                e.target.dataset.originalValue = newStatus;

                
                const card = e.target.closest('.application-card');
                card.style.transition = 'all 0.3s ease';
                card.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    card.style.transform = 'scale(1)';
                }, 300);

                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('Error: ' + result.message);
                e.target.value = e.target.dataset.originalValue || e.target.value;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating the status');
            e.target.value = e.target.dataset.originalValue || e.target.value;
        }
    });

    
    select.dataset.originalValue = select.value;
});


document.querySelectorAll('.btn-delete-application').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const applicationId = e.target.closest('button').dataset.applicationId;

        if (!confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(appUrl('api/delete-application.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    application_id: applicationId
                })
            });

            const result = await response.json();

            if (result.success) {
                
                const card = document.querySelector(`[data-application-id="${applicationId}"]`);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';

                    setTimeout(() => {
                        card.remove();

                        
                        const grid = document.querySelector('.applications-grid');
                        if (grid && grid.children.length === 0) {
                            location.reload(); 
                        }
                    }, 300);
                }
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting the application');
        }
    });
});


document.querySelectorAll('.btn-view-application').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const applicationId = e.target.closest('button').dataset.applicationId;
        const card = document.querySelector(`.application-card[data-application-id="${applicationId}"]`);

        if (!card) return;

        
        const jobTitle = card.querySelector('.application-job-title')?.textContent || '';
        const companyName = card.querySelector('.application-company-name')?.textContent?.replace(/^\s*\S+\s*/, '') || '';
        const location = card.querySelector('.application-meta span:has(.fa-map-marker-alt)')?.textContent?.trim() || 'N/A';
        const jobType = card.querySelector('.application-meta span:has(.fa-briefcase)')?.textContent?.trim() || 'N/A';
        const appliedDate = card.querySelector('.application-meta span:has(.fa-calendar)')?.textContent?.replace('Applied ', '') || 'N/A';
        const salary = card.querySelector('.application-salary')?.textContent?.trim() || 'Not specified';
        const status = card.querySelector('.application-status-select')?.value || 'N/A';
        const priority = card.querySelector('.application-priority')?.textContent?.trim() || 'N/A';
        const notes = card.querySelector('.application-notes')?.textContent?.trim() || 'No notes added';

        
        const detailsHTML = `
            <div style="padding: 1.5rem;">
                <div style="margin-bottom: 2rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">${jobTitle}</h2>
                    <p style="font-size: 1.1rem; color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-building" style="color: var(--accent-color);"></i> ${companyName}
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 0.5rem;">Location</label>
                        <p style="font-size: 0.95rem; color: var(--text-dark);">${location}</p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 0.5rem;">Job Type</label>
                        <p style="font-size: 0.95rem; color: var(--text-dark);">${jobType}</p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 0.5rem;">Applied Date</label>
                        <p style="font-size: 0.95rem; color: var(--text-dark);">${appliedDate}</p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 0.5rem;">Salary Range</label>
                        <p style="font-size: 0.95rem; color: var(--text-dark);">${salary}</p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 0.5rem;">Status</label>
                        <p style="font-size: 0.95rem; color: var(--accent-color); font-weight: 600;">${status}</p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 0.5rem;">Priority</label>
                        <p style="font-size: 0.95rem; color: var(--text-dark);">${priority}</p>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 0.5rem;">Notes</label>
                    <div style="background: var(--bg-subtle); padding: 1rem; border-radius: 8px; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;">
                        ${notes}
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <button onclick="closeViewApplicationModal()" class="btn-primary" style="background: var(--accent-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        Close
                    </button>
                </div>
            </div>
        `;

        document.getElementById('applicationDetailsContent').innerHTML = detailsHTML;
        document.getElementById('viewApplicationModal').style.display = 'flex';
    });
});


document.querySelectorAll('.btn-edit-application').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const appId = e.target.closest('button').dataset.applicationId;
        
        try {
            const response = await fetch(appUrl(`api/get-application.php?id=${appId}`));
            const result = await response.json();
            
            if (result.success) {
                const app = result.application;
                const form = document.getElementById('applicationForm');
                
                form.querySelector('[name="company_name"]').value = app.company_name || '';
                form.querySelector('[name="job_title"]').value = app.job_title || '';
                form.querySelector('[name="job_location"]').value = app.job_location || '';
                form.querySelector('[name="job_type"]').value = app.job_type || 'Full-time';
                form.querySelector('[name="application_date"]').value = app.application_date || '';
                form.querySelector('[name="salary_range"]').value = app.salary_range || '';
                form.querySelector('#applicationStatus').value = app.status || 'Applied';
                form.querySelector('[name="priority"]').value = app.priority || 'Medium';
                form.querySelector('[name="resume_id"]').value = app.resume_id || '';
                form.querySelector('[name="application_url"]').value = app.application_url || '';
                form.querySelector('[name="contact_person"]').value = app.contact_person || '';
                form.querySelector('[name="contact_email"]').value = app.contact_email || '';
                form.querySelector('[name="notes"]').value = app.notes || '';
                form.querySelector('[name="interview_date"]').value = app.interview_date ? app.interview_date.replace(' ', 'T').substring(0, 16) : '';
                form.querySelector('[name="interview_location"]').value = app.interview_location || '';
                form.querySelector('[name="interview_notes"]').value = app.interview_notes || '';
                
                if (['Interview Scheduled', 'Interview Completed'].includes(app.status)) {
                    document.getElementById('interviewDetailsSection').style.display = 'block';
                }
                
                form.dataset.editId = appId;
                document.querySelector('#addApplicationModal h2').textContent = 'Edit Job Application';
                document.querySelector('#addApplicationModal button[type="submit"]').textContent = 'Update Application';
                document.getElementById('addApplicationModal').style.display = 'flex';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load application data');
        }
    });
});


document.getElementById('exportApplicationsBtn')?.addEventListener('click', () => {
    
    window.open(appUrl('api/export-applications-pdf.php'), '_blank');
});
