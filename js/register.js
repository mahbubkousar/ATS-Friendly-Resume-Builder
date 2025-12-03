
let currentStep = 1;
const totalSteps = 4;


const stepInfo = {
    1: {
        title: 'Create Your Account',
        subtitle: "Let's start with your basic information"
    },
    2: {
        title: 'Personal Information',
        subtitle: 'Tell us more about yourself'
    },
    3: {
        title: 'Education Background',
        subtitle: 'Add your educational qualifications (optional)'
    },
    4: {
        title: 'Work Experience',
        subtitle: 'Share your professional journey (optional)'
    }
};


function showNotification(message) {
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #1a202c;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.3);
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
        font-weight: 500;
        z-index: 10000;
        animation: slideInRight 0.4s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'fadeOut 0.4s ease forwards';
        setTimeout(() => notification.remove(), 400);
    }, 3000);
}


function init() {
    updateStep(1);
    setupEventListeners();
}


function setupEventListeners() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const skipBtn = document.getElementById('skipBtn');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('registrationForm');
    const addEducationBtn = document.getElementById('addEducation');
    const addExperienceBtn = document.getElementById('addExperience');

    if (prevBtn) {
        prevBtn.addEventListener('click', () => goToStep(currentStep - 1));
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (validateCurrentStep()) {
                goToStep(currentStep + 1);
            }
        });
    }

    if (skipBtn) {
        skipBtn.addEventListener('click', () => goToStep(currentStep + 1));
    }

    if (form) {
        form.addEventListener('submit', handleSubmit);
    }

    
    if (addEducationBtn) {
        addEducationBtn.addEventListener('click', addEducation);
    }

    
    if (addExperienceBtn) {
        addExperienceBtn.addEventListener('click', addExperience);
    }

    
    document.addEventListener('change', (e) => {
        if (e.target.name && e.target.name.includes('experience') && e.target.name.includes('current')) {
            const entry = e.target.closest('.experience-entry');
            if (entry) {
                const endDateInput = entry.querySelector('input[name*="endDate"]');
                if (endDateInput) {
                    if (e.target.checked) {
                        endDateInput.value = '';
                        endDateInput.disabled = true;
                        endDateInput.style.opacity = '0.5';
                    } else {
                        endDateInput.disabled = false;
                        endDateInput.style.opacity = '1';
                    }
                }
            }
        }
    });
}


function validateCurrentStep() {
    const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const requiredInputs = currentStepElement.querySelectorAll('[required]');
    let isValid = true;

    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#ef4444';
            setTimeout(() => {
                input.style.borderColor = '';
            }, 2000);
        } else {
            input.style.borderColor = '';
        }
    });

    
    if (currentStep === 1) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const terms = document.getElementById('terms').checked;

        if (password.length < 8) {
            showNotification('Password must be at least 8 characters long');
            return false;
        }

        if (password !== confirmPassword) {
            showNotification('Passwords do not match');
            return false;
        }

        if (!terms) {
            showNotification('Please agree to the Terms of Service');
            return false;
        }
    }

    if (!isValid) {
        showNotification('Please fill in all required fields');
    }

    return isValid;
}


function goToStep(stepNumber) {
    if (stepNumber < 1 || stepNumber > totalSteps) return;

    
    document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('active');
    document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.remove('active');
    document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.add('completed');

    
    currentStep = stepNumber;
    document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');
    document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.add('active');

    updateStep(currentStep);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}


function updateStep(step) {
    
    document.getElementById('stepTitle').textContent = stepInfo[step].title;
    document.getElementById('stepSubtitle').textContent = stepInfo[step].subtitle;

    
    prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
    skipBtn.style.display = (step === 3 || step === 4) ? 'inline-flex' : 'none';
    nextBtn.style.display = step === totalSteps ? 'none' : 'inline-flex';
    submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';

    
    document.querySelectorAll('.progress-step').forEach((stepEl, index) => {
        const stepNum = index + 1;
        if (stepNum < step) {
            stepEl.classList.add('completed');
            stepEl.classList.remove('active');
        } else if (stepNum === step) {
            stepEl.classList.add('active');
            stepEl.classList.remove('completed');
        } else {
            stepEl.classList.remove('active', 'completed');
        }
    });
}


let educationCount = 1;
function addEducation() {
    const educationList = document.getElementById('educationList');
    const newEntry = document.createElement('div');
    newEntry.className = 'education-entry';
    newEntry.setAttribute('data-index', educationCount);

    newEntry.innerHTML = `
        <div class="entry-header">
            <h4>Education #${educationCount + 1}</h4>
            <button type="button" class="btn-remove-entry" onclick="removeEntry(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="form-group">
            <label class="form-label">Institution Name</label>
            <input type="text" name="education[${educationCount}][institution]" class="auth-input" placeholder="University/College name">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Degree</label>
                <input type="text" name="education[${educationCount}][degree]" class="auth-input" placeholder="e.g., Bachelor of Science">
            </div>
            <div class="form-group">
                <label class="form-label">Field of Study</label>
                <input type="text" name="education[${educationCount}][field]" class="auth-input" placeholder="e.g., Computer Science">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Start Date</label>
                <input type="month" name="education[${educationCount}][startDate]" class="auth-input">
            </div>
            <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="month" name="education[${educationCount}][endDate]" class="auth-input">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">GPA (Optional)</label>
            <input type="text" name="education[${educationCount}][gpa]" class="auth-input" placeholder="e.g., 3.8/4.0">
        </div>
    `;

    educationList.appendChild(newEntry);
    educationCount++;

    
    const firstEntry = educationList.querySelector('.education-entry[data-index="0"] .btn-remove-entry');
    if (firstEntry) {
        firstEntry.style.display = 'flex';
    }

    newEntry.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}


let experienceCount = 1;
function addExperience() {
    const experienceList = document.getElementById('experienceList');
    const newEntry = document.createElement('div');
    newEntry.className = 'experience-entry';
    newEntry.setAttribute('data-index', experienceCount);

    newEntry.innerHTML = `
        <div class="entry-header">
            <h4>Experience #${experienceCount + 1}</h4>
            <button type="button" class="btn-remove-entry" onclick="removeEntry(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="form-group">
            <label class="form-label">Company Name</label>
            <input type="text" name="experience[${experienceCount}][company]" class="auth-input" placeholder="Company name">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Job Title</label>
                <input type="text" name="experience[${experienceCount}][title]" class="auth-input" placeholder="e.g., Software Engineer">
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="experience[${experienceCount}][location]" class="auth-input" placeholder="City, State">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Start Date</label>
                <input type="month" name="experience[${experienceCount}][startDate]" class="auth-input">
            </div>
            <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="month" name="experience[${experienceCount}][endDate]" class="auth-input">
            </div>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" class="checkbox-input" name="experience[${experienceCount}][current]">
                <span>I currently work here</span>
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="experience[${experienceCount}][description]" class="auth-input auth-textarea" rows="3" placeholder="Describe your responsibilities and achievements"></textarea>
        </div>
    `;

    experienceList.appendChild(newEntry);
    experienceCount++;

    
    const firstEntry = experienceList.querySelector('.experience-entry[data-index="0"] .btn-remove-entry');
    if (firstEntry) {
        firstEntry.style.display = 'flex';
    }

    newEntry.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}


function removeEntry(button) {
    const entry = button.closest('.education-entry, .experience-entry');
    const list = entry.parentElement;

    entry.style.animation = 'fadeOut 0.3s ease';
    setTimeout(() => {
        entry.remove();

        
        const entries = list.querySelectorAll('.education-entry, .experience-entry');
        if (entries.length === 1) {
            const firstEntry = entries[0].querySelector('.btn-remove-entry');
            if (firstEntry) {
                firstEntry.style.display = 'none';
            }
        }

        
        entries.forEach((e, index) => {
            const title = e.querySelector('h4');
            if (title) {
                title.textContent = `${list.id === 'educationList' ? 'Education' : 'Experience'} #${index + 1}`;
            }
        });
    }, 300);
}


function handleSubmit(e) {
    e.preventDefault();

    
    const submitButton = document.getElementById('submitBtn');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';

    
    const form = document.getElementById('registrationForm');

    
    const formData = new FormData(form);
    const data = {
        education: [],
        experience: []
    };

    
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('education[')) {
            
            const matches = key.match(/education\[(\d+)\]\[(\w+)\]/);
            if (matches) {
                const index = parseInt(matches[1]);
                const field = matches[2];
                if (!data.education[index]) {
                    data.education[index] = {};
                }
                data.education[index][field] = value;
            }
        } else if (key.startsWith('experience[')) {
            
            const matches = key.match(/experience\[(\d+)\]\[(\w+)\]/);
            if (matches) {
                const index = parseInt(matches[1]);
                const field = matches[2];
                if (!data.experience[index]) {
                    data.experience[index] = {};
                }
                if (key.includes('[current]')) {
                    data.experience[index][field] = value === 'on';
                } else {
                    data.experience[index][field] = value;
                }
            }
        } else if (key !== 'confirm-password') {
            data[key] = value;
        }
    }

    
    data.education = data.education.filter(e => e && (e.institution || e.degree));
    data.experience = data.experience.filter(e => e && (e.company || e.title));

    
    fetch('api/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(result.message);
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);
        } else {
            showNotification(result.message);
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;

            
            const errorDiv = document.getElementById('errorMessage');
            if (errorDiv) {
                errorDiv.textContent = result.message;
                errorDiv.style.display = 'block';
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                }, 5000);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.');
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}


const animationStyles = document.createElement('style');
animationStyles.textContent = `
    @keyframes fadeOut {
        to {
            opacity: 0;
            transform: scale(0.95);
        }
    }
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(animationStyles);


document.addEventListener('DOMContentLoaded', init);
