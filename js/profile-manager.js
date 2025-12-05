


function closeEducationModal() {
    document.getElementById('addEducationModal').style.display = 'none';
    document.getElementById('educationForm').reset();
}

function closeExperienceModal() {
    document.getElementById('addExperienceModal').style.display = 'none';
    document.getElementById('experienceForm').reset();
}


document.getElementById('addEducationBtn')?.addEventListener('click', () => {
    document.getElementById('addEducationModal').style.display = 'flex';
});


document.getElementById('addExperienceBtn')?.addEventListener('click', () => {
    document.getElementById('addExperienceModal').style.display = 'flex';
});


document.getElementById('isCurrentJob')?.addEventListener('change', (e) => {
    const endDateInput = document.getElementById('expEndDate');
    if (e.target.checked) {
        endDateInput.value = '';
        endDateInput.disabled = true;
    } else {
        endDateInput.disabled = false;
    }
});


document.getElementById('educationForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        institution: formData.get('institution'),
        degree: formData.get('degree'),
        field: formData.get('field'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date'),
        gpa: formData.get('gpa')
    };

    try {
        const response = await fetch('/ATS/api/add-education.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            console.log('Education added successfully!');
            closeEducationModal();
            
            location.reload();
        } else {
            console.log('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error adding education:', error);
        console.log('Failed to add education');
    }
});


document.getElementById('experienceForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        title: formData.get('title'),
        company: formData.get('company'),
        location: formData.get('location'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date'),
        is_current: formData.get('is_current') === 'on',
        description: formData.get('description')
    };

    try {
        const response = await fetch('/ATS/api/add-experience.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            console.log('Experience added successfully!');
            closeExperienceModal();
            
            location.reload();
        } else {
            console.log('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error adding experience:', error);
        console.log('Failed to add experience');
    }
});


async function deleteEducation(id) {
    if (!confirm('Are you sure you want to delete this education record?')) {
        return;
    }

    try {
        const response = await fetch(`/ATS/api/delete-education.php?id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            console.log('Education deleted successfully!');
            location.reload();
        } else {
            console.log('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting education:', error);
        console.log('Failed to delete education');
    }
}

async function deleteExperience(id) {
    if (!confirm('Are you sure you want to delete this work experience?')) {
        return;
    }

    try {
        const response = await fetch(`/ATS/api/delete-experience.php?id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            console.log('Experience deleted successfully!');
            location.reload();
        } else {
            console.log('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting experience:', error);
        console.log('Failed to delete experience');
    }
}


window.addEventListener('click', (e) => {
    const eduModal = document.getElementById('addEducationModal');
    const expModal = document.getElementById('addExperienceModal');

    if (e.target === eduModal) {
        closeEducationModal();
    }
    if (e.target === expModal) {
        closeExperienceModal();
    }
});
