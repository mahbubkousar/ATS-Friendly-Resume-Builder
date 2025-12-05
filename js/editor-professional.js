


let resumeState = {
    id: resumeData.id,
    resume_title: resumeData.resume_title,
    template_name: 'professional',
    personal_details: resumeData.personal_details || {},
    summary_text: resumeData.summary_text || '',
    experience: [],
    education: [],
    skills: [],
    certifications: [],
    affiliations: ''
};

let previewIframe = null;
let isTemplateLoading = false;


document.addEventListener('DOMContentLoaded', function() {
    previewIframe = document.getElementById('resumePreview');

    
    loadTemplatePreview();

    
    setupEventListeners();

    
    if (resumeData.id) {
        loadResumeData();
    }

    
    document.getElementById('saveResumeBtn').addEventListener('click', saveResume);

    
    document.getElementById('downloadBtn').addEventListener('click', downloadPDF);

    
    setupZoomControls();
});

function loadTemplatePreview() {
    isTemplateLoading = true;
    const timestamp = new Date().getTime();
    previewIframe.src = `/ATS/templates/professional.html?v=${timestamp}`;

    previewIframe.onload = () => {
        setTimeout(() => {
            isTemplateLoading = false;
            updatePreview();
        }, 100);
    };
}

function setupEventListeners() {
    
    const personalFields = ['fullName', 'professionalTitle', 'email', 'phone', 'location', 'linkedin'];
    personalFields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.addEventListener('input', debounce(() => {
                resumeState.personal_details[fieldId] = element.value;
                updatePreview();
            }, 300));
        }
    });

    
    const summaryElement = document.getElementById('summary');
    if (summaryElement) {
        summaryElement.addEventListener('input', debounce(() => {
            resumeState.summary_text = summaryElement.value;
            updatePreview();
        }, 300));
    }

    
    const skillsElement = document.getElementById('skills');
    if (skillsElement) {
        skillsElement.addEventListener('input', debounce(() => {
            resumeState.skills = parseSkillCategories(skillsElement.value);
            updatePreview();
        }, 300));
    }

    
    const affiliationsElement = document.getElementById('affiliations');
    if (affiliationsElement) {
        affiliationsElement.addEventListener('input', debounce(() => {
            resumeState.affiliations = affiliationsElement.value;
            updatePreview();
        }, 300));
    }

    
    document.getElementById('addExperienceBtn').addEventListener('click', addExperience);
    document.getElementById('addEducationBtn').addEventListener('click', addEducation);
    document.getElementById('addCertificationBtn').addEventListener('click', addCertification);
}

function parseSkillCategories(skillsText) {
    
    
    const lines = skillsText.split('\n').map(l => l.trim()).filter(l => l);
    const categories = [];

    lines.forEach(line => {
        if (line.includes(':')) {
            const [category, items] = line.split(':').map(s => s.trim());
            if (category && items) {
                categories.push({
                    category: category,
                    items: items.split(',').map(s => s.trim()).filter(s => s)
                });
            }
        } else {
            
            const items = line.split(',').map(s => s.trim()).filter(s => s);
            if (items.length > 0) {
                categories.push({
                    category: 'General Skills',
                    items: items
                });
            }
        }
    });

    return categories;
}

function updatePreview() {
    if (isTemplateLoading) return;

    const iframeDoc = previewIframe.contentDocument || previewIframe.contentWindow.document;
    if (!iframeDoc) return;

    
    const fieldMapping = {
        fullName: 'name',
        professionalTitle: 'title'
    };

    Object.keys(resumeState.personal_details).forEach(key => {
        const value = resumeState.personal_details[key];
        const dataField = fieldMapping[key] || key;
        const element = iframeDoc.querySelector(`[data-field="${dataField}"]`);
        if (element && value) {
            element.textContent = value;
        }
    });

    
    const summaryElement = iframeDoc.querySelector('[data-field="summary"]');
    if (summaryElement && resumeState.summary_text) {
        summaryElement.textContent = resumeState.summary_text;
    }

    
    updateExperienceList(iframeDoc);

    
    updateEducationList(iframeDoc);

    
    updateSkillsWithCategories(iframeDoc);

    
    updateCertificationsList(iframeDoc);

    
    updateAffiliations(iframeDoc);
}

function updateExperienceList(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="experience-list"]');
    if (!container) return;

    container.innerHTML = '';
    resumeState.experience.forEach(item => {
        const entry = iframeDoc.createElement('div');
        entry.className = 'entry';

        const header = `
            <div class="entry-header">
                <div class="entry-title-line">
                    <div class="entry-title">${item.position || ''}</div>
                    <div class="entry-date">${item.startDate || ''} - ${item.endDate || ''}</div>
                </div>
                <div class="entry-company">${item.company || ''}${item.location ? ', ' + item.location : ''}</div>
            </div>
        `;

        let description = '';
        if (item.description) {
            const points = item.description.split('\n').filter(p => p.trim());
            if (points.length > 0) {
                description = '<div class="entry-description"><ul>';
                points.forEach(point => {
                    description += `<li>${point.trim()}</li>`;
                });
                description += '</ul></div>';
            }
        }

        entry.innerHTML = header + description;
        container.appendChild(entry);
    });
}

function updateEducationList(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="education-list"]');
    if (!container) return;

    container.innerHTML = '';
    resumeState.education.forEach(item => {
        const entry = iframeDoc.createElement('div');
        entry.className = 'entry';

        entry.innerHTML = `
            <div class="entry-header">
                <div class="entry-title-line">
                    <div class="entry-title">${item.degree || ''}</div>
                    <div class="entry-date">${item.startDate || ''} - ${item.endDate || ''}</div>
                </div>
                <div class="entry-company">${item.institution || ''}${item.location ? ', ' + item.location : ''}</div>
            </div>
        `;

        container.appendChild(entry);
    });
}

function updateSkillsWithCategories(iframeDoc) {
    const skillsContainer = iframeDoc.querySelector('[data-field="skills"]');
    if (!skillsContainer) return;

    skillsContainer.innerHTML = '';

    if (resumeState.skills && resumeState.skills.length > 0) {
        resumeState.skills.forEach(category => {
            const skillRow = iframeDoc.createElement('div');
            skillRow.className = 'skill-row';

            const label = iframeDoc.createElement('span');
            label.className = 'skill-label';
            label.textContent = category.category + ':';

            const items = iframeDoc.createElement('span');
            items.className = 'skill-items';
            items.textContent = category.items.join(', ');

            skillRow.appendChild(label);
            skillRow.appendChild(items);
            skillsContainer.appendChild(skillRow);
        });
    }
}

function updateCertificationsList(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="certifications-list"]');
    const section = iframeDoc.querySelector('[data-section="certifications"]');
    if (!container || !section) return;

    
    if (resumeState.certifications && resumeState.certifications.length > 0) {
        section.style.display = 'block';
        container.innerHTML = '';

        resumeState.certifications.forEach(item => {
            const entry = iframeDoc.createElement('div');
            entry.className = 'entry';

            entry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title-line">
                        <div class="entry-title">${item.name || ''}</div>
                        <div class="entry-date">${item.year || ''}</div>
                    </div>
                    ${item.issuer ? `<div class="entry-company">${item.issuer}</div>` : ''}
                </div>
            `;

            container.appendChild(entry);
        });
    } else {
        section.style.display = 'none';
    }
}

function updateAffiliations(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="affiliations"]');
    const section = iframeDoc.querySelector('[data-section="affiliations"]');
    if (!container || !section) return;

    
    if (resumeState.affiliations && resumeState.affiliations.trim()) {
        section.style.display = 'block';

        
        const lines = resumeState.affiliations.split('\n').map(l => l.trim()).filter(l => l);

        if (lines.length > 0) {
            container.innerHTML = '<ul>';
            lines.forEach(line => {
                
                const cleanLine = line.replace(/^[•\-\*]\s*/, '');
                container.innerHTML += `<li>${cleanLine}</li>`;
            });
            container.innerHTML += '</ul>';
        }
    } else {
        section.style.display = 'none';
    }
}

function addExperience() {
    const container = document.getElementById('experienceContainer');
    const index = resumeState.experience.length;

    const experienceItem = {
        position: '',
        company: '',
        location: '',
        startDate: '',
        endDate: '',
        description: ''
    };

    resumeState.experience.push(experienceItem);

    const itemDiv = document.createElement('div');
    itemDiv.className = 'form-item';
    itemDiv.innerHTML = `
        <div class="form-item-header">
            <span>Experience ${index + 1}</span>
            <button type="button" class="remove-item-btn" onclick="removeExperience(${index})">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <input type="text" class="form-input" placeholder="Position" data-exp-field="position" data-exp-index="${index}">
        <input type="text" class="form-input" placeholder="Company" data-exp-field="company" data-exp-index="${index}">
        <input type="text" class="form-input" placeholder="Location" data-exp-field="location" data-exp-index="${index}">
        <div class="form-row">
            <input type="text" class="form-input" placeholder="Start Date" data-exp-field="startDate" data-exp-index="${index}">
            <input type="text" class="form-input" placeholder="End Date" data-exp-field="endDate" data-exp-index="${index}">
        </div>
        <textarea class="form-textarea" placeholder="Description (one bullet point per line)" rows="4" data-exp-field="description" data-exp-index="${index}"></textarea>
    `;

    container.appendChild(itemDiv);

    
    itemDiv.querySelectorAll('[data-exp-field]').forEach(field => {
        field.addEventListener('input', debounce((e) => {
            const idx = parseInt(e.target.dataset.expIndex);
            const fieldName = e.target.dataset.expField;
            resumeState.experience[idx][fieldName] = e.target.value;
            updatePreview();
        }, 300));
    });
}

function removeExperience(index) {
    resumeState.experience.splice(index, 1);
    rebuildExperienceUI();
    updatePreview();
}

function rebuildExperienceUI() {
    const container = document.getElementById('experienceContainer');
    container.innerHTML = '';
    const experiences = [...resumeState.experience];
    resumeState.experience = [];
    experiences.forEach((exp, idx) => {
        addExperience();
        
        Object.keys(exp).forEach(key => {
            const field = container.querySelector(`[data-exp-index="${idx}"][data-exp-field="${key}"]`);
            if (field) field.value = exp[key];
        });
        resumeState.experience[idx] = exp;
    });
}

function addEducation() {
    const container = document.getElementById('educationContainer');
    const index = resumeState.education.length;

    const educationItem = {
        degree: '',
        institution: '',
        location: '',
        startDate: '',
        endDate: ''
    };

    resumeState.education.push(educationItem);

    const itemDiv = document.createElement('div');
    itemDiv.className = 'form-item';
    itemDiv.innerHTML = `
        <div class="form-item-header">
            <span>Education ${index + 1}</span>
            <button type="button" class="remove-item-btn" onclick="removeEducation(${index})">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <input type="text" class="form-input" placeholder="Degree" data-edu-field="degree" data-edu-index="${index}">
        <input type="text" class="form-input" placeholder="Institution" data-edu-field="institution" data-edu-index="${index}">
        <input type="text" class="form-input" placeholder="Location" data-edu-field="location" data-edu-index="${index}">
        <div class="form-row">
            <input type="text" class="form-input" placeholder="Start Date" data-edu-field="startDate" data-edu-index="${index}">
            <input type="text" class="form-input" placeholder="End Date" data-edu-field="endDate" data-edu-index="${index}">
        </div>
    `;

    container.appendChild(itemDiv);

    
    itemDiv.querySelectorAll('[data-edu-field]').forEach(field => {
        field.addEventListener('input', debounce((e) => {
            const idx = parseInt(e.target.dataset.eduIndex);
            const fieldName = e.target.dataset.eduField;
            resumeState.education[idx][fieldName] = e.target.value;
            updatePreview();
        }, 300));
    });
}

function removeEducation(index) {
    resumeState.education.splice(index, 1);
    rebuildEducationUI();
    updatePreview();
}

function rebuildEducationUI() {
    const container = document.getElementById('educationContainer');
    container.innerHTML = '';
    const educations = [...resumeState.education];
    resumeState.education = [];
    educations.forEach((edu, idx) => {
        addEducation();
        
        Object.keys(edu).forEach(key => {
            const field = container.querySelector(`[data-edu-index="${idx}"][data-edu-field="${key}"]`);
            if (field) field.value = edu[key];
        });
        resumeState.education[idx] = edu;
    });
}

function addCertification() {
    const container = document.getElementById('certificationsContainer');
    const index = resumeState.certifications.length;

    const certificationItem = {
        name: '',
        issuer: '',
        year: ''
    };

    resumeState.certifications.push(certificationItem);

    const itemDiv = document.createElement('div');
    itemDiv.className = 'form-item';
    itemDiv.innerHTML = `
        <div class="form-item-header">
            <span>Certification ${index + 1}</span>
            <button type="button" class="remove-item-btn" onclick="removeCertification(${index})">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <input type="text" class="form-input" placeholder="Certification Name" data-cert-field="name" data-cert-index="${index}">
        <input type="text" class="form-input" placeholder="Issuing Organization" data-cert-field="issuer" data-cert-index="${index}">
        <input type="text" class="form-input" placeholder="Year" data-cert-field="year" data-cert-index="${index}">
    `;

    container.appendChild(itemDiv);

    
    itemDiv.querySelectorAll('[data-cert-field]').forEach(field => {
        field.addEventListener('input', debounce((e) => {
            const idx = parseInt(e.target.dataset.certIndex);
            const fieldName = e.target.dataset.certField;
            resumeState.certifications[idx][fieldName] = e.target.value;
            updatePreview();
        }, 300));
    });
}

function removeCertification(index) {
    resumeState.certifications.splice(index, 1);
    rebuildCertificationsUI();
    updatePreview();
}

function rebuildCertificationsUI() {
    const container = document.getElementById('certificationsContainer');
    container.innerHTML = '';
    const certifications = [...resumeState.certifications];
    resumeState.certifications = [];
    certifications.forEach((cert, idx) => {
        addCertification();
        
        Object.keys(cert).forEach(key => {
            const field = container.querySelector(`[data-cert-index="${idx}"][data-cert-field="${key}"]`);
            if (field) field.value = cert[key];
        });
        resumeState.certifications[idx] = cert;
    });
}

function loadResumeData() {
    
    if (resumeData.experience) {
        try {
            const expData = typeof resumeData.experience === 'string' ? JSON.parse(resumeData.experience) : resumeData.experience;
            resumeState.experience = expData;
            expData.forEach(() => addExperience());
        } catch (e) {
            console.error('Error loading experience:', e);
        }
    }

    
    if (resumeData.education) {
        try {
            const eduData = typeof resumeData.education === 'string' ? JSON.parse(resumeData.education) : resumeData.education;
            resumeState.education = eduData;
            eduData.forEach(() => addEducation());
        } catch (e) {
            console.error('Error loading education:', e);
        }
    }

    
    if (resumeData.skills) {
        try {
            const skillsData = typeof resumeData.skills === 'string' ? JSON.parse(resumeData.skills) : resumeData.skills;
            resumeState.skills = skillsData;

            
            const skillsText = skillsData.map(cat => `${cat.category}: ${cat.items.join(', ')}`).join('\n');
            document.getElementById('skills').value = skillsText;
        } catch (e) {
            console.error('Error loading skills:', e);
        }
    }

    
    if (resumeData.certifications) {
        try {
            const certData = typeof resumeData.certifications === 'string' ? JSON.parse(resumeData.certifications) : resumeData.certifications;
            resumeState.certifications = certData;
            certData.forEach(() => addCertification());
        } catch (e) {
            console.error('Error loading certifications:', e);
        }
    }

    
    if (resumeData.affiliations) {
        try {
            const affData = typeof resumeData.affiliations === 'string' ? resumeData.affiliations : resumeData.affiliations;
            resumeState.affiliations = affData;
            document.getElementById('affiliations').value = affData;
        } catch (e) {
            console.error('Error loading affiliations:', e);
        }
    }
}

async function saveResume() {
    const saveBtn = document.getElementById('saveResumeBtn');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';
    saveBtn.disabled = true;

    try {
        const resumeTitle = document.getElementById('resumeTitle').value || 'Untitled Resume';

        const formData = new FormData();
        formData.append('resume_id', resumeState.id || '');
        formData.append('resume_title', resumeTitle);
        formData.append('template_name', 'professional');
        formData.append('personal_details', JSON.stringify(resumeState.personal_details));
        formData.append('summary_text', resumeState.summary_text);
        formData.append('experience', JSON.stringify(resumeState.experience));
        formData.append('education', JSON.stringify(resumeState.education));
        formData.append('skills', JSON.stringify(resumeState.skills));
        formData.append('certifications', JSON.stringify(resumeState.certifications));
        formData.append('affiliations', resumeState.affiliations);
        formData.append('status', 'draft');

        const response = await fetch('/ATS/api/save-resume.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            resumeState.id = result.resume_id;
            console.log('Resume saved successfully!', 'success');

            
            if (!resumeData.id) {
                const newUrl = `${window.location.pathname}?id=${result.resume_id}`;
                window.history.pushState({}, '', newUrl);
                resumeData.id = result.resume_id;
            }
        } else {
            console.log('Error saving resume: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Save error:', error);
        console.log('Error saving resume. Please try again.', 'error');
    } finally {
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    }
}

async function downloadPDF() {
    const downloadBtn = document.getElementById('downloadBtn');
    const originalText = downloadBtn.textContent;

    try {
        const iframeDoc = previewIframe.contentDocument || previewIframe.contentWindow.document;
        const iframeWindow = previewIframe.contentWindow;

        
        
        
        
        
        iframeWindow.print();

        
        
        
        

    } catch (error) {
        console.error('Print error:', error);
        showNotificationModal('Error opening print dialog. Please try again.', 'error');
    }
}

function setupZoomControls() {
    
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const wrapper = document.getElementById('previewWrapper');
    const zoomDisplay = document.getElementById('zoomLevel');

    if (!zoomInBtn || !zoomOutBtn || !wrapper || !zoomDisplay) return;

    let zoomLevel = 100;

    zoomInBtn.addEventListener('click', () => {
        if (zoomLevel < 150) {
            zoomLevel += 10;
            wrapper.style.transform = `scale(${zoomLevel / 100})`;
            zoomDisplay.textContent = zoomLevel + '%';
        }
    });

    zoomOutBtn.addEventListener('click', () => {
        if (zoomLevel > 50) {
            zoomLevel -= 10;
            wrapper.style.transform = `scale(${zoomLevel / 100})`;
            zoomDisplay.textContent = zoomLevel + '%';
        }
    });

    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            loadTemplatePreview();
        });
    }
}




function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}


async function performAIAnalysis() {
    try {
        const jobDescText = document.getElementById('jobDescText')?.value.trim();
        const jobDescFile = document.getElementById('jobDescFile')?.files[0];

        if (!jobDescText && !jobDescFile) {
            showNotificationModal('Please provide a job description to analyze against', 'error');
            return;
        }

        const analyzeBtn = document.querySelector('.analyze-btn');
        const originalText = analyzeBtn.innerHTML;
        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Analyzing...';

        
        showProgressModal();

        updateScoreDisplay('analyzing', '--', 'Analyzing your resume...');

        const resumeText = generateResumeText();
        const formData = new FormData();
        formData.append('resume_text', resumeText);

        if (jobDescText) {
            formData.append('job_description', jobDescText);
        } else if (jobDescFile) {
            formData.append('job_description_file', jobDescFile);
        }

        
        setStepActive(1);
        updateProgress(10, 'Extracting text from your resume...');
        await new Promise(resolve => setTimeout(resolve, 800));

        
        setStepActive(2);
        updateProgress(25, 'Analyzing formatting and structure...');
        await new Promise(resolve => setTimeout(resolve, 600));

        
        setStepActive(3);
        updateProgress(45, 'Checking keywords and terminology...');

        const response = await fetch('/ATS/api/analyze-ats-score.php', {
            method: 'POST',
            body: formData
        });

        
        setStepActive(4);
        updateProgress(70, 'Evaluating content structure...');

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Analysis failed');
        }

        
        setStepActive(5);
        updateProgress(90, 'Generating personalized insights...');
        await new Promise(resolve => setTimeout(resolve, 600));

        
        updateProgress(100, 'Analysis complete!');
        await new Promise(resolve => setTimeout(resolve, 400));

        
        hideProgressModal();

        const analysis = result.analysis || result;
        const score = analysis.overall_score || analysis.score || 0;
        updateScoreDisplay('success', score, getScoreLabel(score));
        displaySuggestions(
            analysis.improvements || [],
            analysis.keywords_found || [],
            analysis.keywords_missing || []
        );
        showNotificationModal('Analysis complete!', 'success');
    } catch (error) {
        console.error('Analysis error:', error);
        hideProgressModal();
        updateScoreDisplay('error', '--', 'Analysis failed');
        showNotificationModal('Error: ' + (error.message || 'Analysis failed'), 'error');
    } finally {
        const analyzeBtn = document.querySelector('.analyze-btn');
        analyzeBtn.disabled = false;
        analyzeBtn.innerHTML = 'Analyze Match';
    }
}

function generateResumeText() {
    let text = '';
    const fullName = document.getElementById('fullName')?.value || '';
    const professionalTitle = document.getElementById('professionalTitle')?.value || '';
    const email = document.getElementById('email')?.value || '';
    const phone = document.getElementById('phone')?.value || '';
    const location = document.getElementById('location')?.value || '';

    text += fullName + '\n';
    if (professionalTitle) text += professionalTitle + '\n';
    if (email || phone || location) {
        text += [email, phone, location].filter(Boolean).join(' | ') + '\n';
    }
    text += '\n';

    const summary = document.getElementById('summary')?.value || '';
    if (summary) {
        text += 'PROFESSIONAL SUMMARY\n' + summary + '\n\n';
    }

    if (resumeState.experience && resumeState.experience.length > 0) {
        text += 'EXPERIENCE\n';
        resumeState.experience.forEach(exp => {
            text += exp.title + '\n' + exp.company + '\n' + exp.dates + '\n' + exp.description + '\n\n';
        });
    }

    if (resumeState.education && resumeState.education.length > 0) {
        text += 'EDUCATION\n';
        resumeState.education.forEach(edu => {
            text += edu.degree + '\n' + edu.school + '\n' + edu.graduationDate + '\n';
            if (edu.gpa) text += 'GPA: ' + edu.gpa + '\n';
            text += '\n';
        });
    }

    if (resumeState.skills && resumeState.skills.length > 0) {
        text += 'SKILLS\n' + resumeState.skills.join(', ') + '\n\n';
    }

    return text;
}

function updateScoreDisplay(state, score, label) {
    const scoreText = document.querySelector('.score-text');
    const scoreStatus = document.querySelector('.score-status');
    const scoreFill = document.querySelector('.score-fill');

    if (scoreText) scoreText.textContent = score;
    if (scoreStatus) {
        scoreStatus.textContent = label;
        scoreStatus.className = 'score-status';
        if (state === 'success') {
            if (score >= 80) scoreStatus.classList.add('good');
            else if (score >= 60) scoreStatus.classList.add('average');
            else scoreStatus.classList.add('poor');
        }
    }

    if (scoreFill && typeof score === 'number') {
        const circumference = 283;
        const offset = circumference - (score / 100) * circumference;
        scoreFill.style.strokeDashoffset = offset;
    }
}

function getScoreLabel(score) {
    if (score >= 80) return 'Excellent Match';
    if (score >= 60) return 'Good Match';
    if (score >= 40) return 'Fair Match';
    return 'Needs Improvement';
}

function displaySuggestions(improvements, keywordsFound, keywordsMissing) {
    const container = document.querySelector('.suggestions-section');
    if (!container) return;

    let html = '<h3 class="form-section-title">Suggestions</h3>';

    if (keywordsFound && keywordsFound.length > 0) {
        html += '<div class="suggestion-box success-box"><h4><i class="fa-solid fa-check-circle"></i> Keywords Found</h4><div class="keywords-list">';
        keywordsFound.forEach(keyword => {
            html += `<span class="keyword-badge found">${keyword}</span>`;
        });
        html += '</div></div>';
    }

    if (keywordsMissing && keywordsMissing.length > 0) {
        html += '<div class="suggestion-box warning-box"><h4><i class="fa-solid fa-exclamation-triangle"></i> Missing Keywords</h4><div class="keywords-list">';
        keywordsMissing.forEach(keyword => {
            html += `<span class="keyword-badge missing">${keyword}</span>`;
        });
        html += '</div></div>';
    }

    if (improvements && improvements.length > 0) {
        html += '<div class="suggestion-box info-box"><h4><i class="fa-solid fa-lightbulb"></i> Improvements</h4><ul class="suggestions-list">';
        improvements.forEach(item => {
            html += `<li><strong>${item.category}:</strong> ${item.issue} - ${item.suggestion}</li>`;
        });
        html += '</ul></div>';
    }

    container.innerHTML = html;
}

function setupJobDescriptionToggle() {
    const textToggle = document.getElementById('textToggle');
    const fileToggle = document.getElementById('fileToggle');
    const textInput = document.getElementById('textInput');
    const fileInput = document.getElementById('fileInput');

    if (textToggle) {
        textToggle.addEventListener('click', () => {
            textToggle.classList.add('active');
            fileToggle.classList.remove('active');
            textInput.classList.remove('hidden');
            fileInput.classList.add('hidden');
        });
    }

    if (fileToggle) {
        fileToggle.addEventListener('click', () => {
            fileToggle.classList.add('active');
            textToggle.classList.remove('active');
            fileInput.classList.remove('hidden');
            textInput.classList.add('hidden');
        });
    }

    const jobDescFileInput = document.getElementById('jobDescFile');
    if (jobDescFileInput) {
        jobDescFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('fileName').textContent = file.name;
                document.querySelector('.file-upload-label').style.display = 'none';
                document.getElementById('fileInfo').classList.remove('hidden');
            }
        });
    }

    const removeFileBtn = document.getElementById('removeFile');
    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', () => {
            jobDescFileInput.value = '';
            document.querySelector('.file-upload-label').style.display = 'flex';
            document.getElementById('fileInfo').classList.add('hidden');
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const analyzeBtn = document.querySelector('.analyze-btn');
    if (analyzeBtn) {
        analyzeBtn.addEventListener('click', performAIAnalysis);
    }
    setupJobDescriptionToggle();
});
