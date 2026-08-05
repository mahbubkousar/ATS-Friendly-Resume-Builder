





























let resumeState = {
    id: resumeData.id,                                   
    resume_title: resumeData.resume_title,               
    template_name: 'modern',                             
    personal_details: resumeData.personal_details || {}, 
    summary_text: resumeData.summary_text || '',         
    experience: [],                                      
    education: [],                                       
    skills: [],                                          
    certifications: [],                                  
    languages: ''                                        
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

    
    document.getElementById('downloadBtn').addEventListener('click', EditorPreviewControls.print);

    
    EditorPreviewControls.setupZoom();
});










function loadTemplatePreview() {
    
    isTemplateLoading = true;

    
    const timestamp = new Date().getTime();
    previewIframe.src = appUrl(`templates/modern.html?v=${timestamp}`);

    
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

    
    
    
    const languagesElement = document.getElementById('languages');
    if (languagesElement) {
        languagesElement.addEventListener('input', debounce(() => {
            resumeState.languages = languagesElement.value;
            updatePreview();
        }, 300));
    }

    
    
    
    
    
    document.getElementById('addExperienceBtn').addEventListener('click', addExperience);
    document.getElementById('addEducationBtn').addEventListener('click', addEducation);
    document.getElementById('addSkillCategoryBtn').addEventListener('click', addSkillCategory);

    const addCertificationBtn = document.getElementById('addCertificationBtn');
    if (addCertificationBtn) {
        addCertificationBtn.addEventListener('click', addCertification);
    }
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
    updateSkillsList(iframeDoc);          
    updateCertificationsList(iframeDoc);  
    updateLanguages(iframeDoc);           
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
                    <div class="entry-title">${escapeHtml(item.position || '')}</div>
                    <div class="entry-date">${escapeHtml(item.startDate || '')} - ${escapeHtml(item.endDate || '')}</div>
                </div>
                <div class="entry-company">${escapeHtml(item.company || '')}, ${escapeHtml(item.location || '')}</div>
            </div>
        `;

        
        
        let description = '';
        if (item.description) {
            const points = item.description.split('\n').filter(p => p.trim());
            if (points.length > 0) {
                description = '<div class="entry-description"><ul>';
                points.forEach(point => {
                    description += `<li>${escapeHtml(point.trim())}</li>`;
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
                    <div class="entry-title">${escapeHtml(item.degree || '')}</div>
                    <div class="entry-date">${escapeHtml(item.startDate || '')} - ${escapeHtml(item.endDate || '')}</div>
                </div>
                <div class="entry-company">${escapeHtml(item.institution || '')}, ${escapeHtml(item.location || '')}</div>
            </div>
        `;

        container.appendChild(entry);
    });
}

function updateSkillsList(iframeDoc) {
    const skillsContainer = iframeDoc.querySelector('[data-field="skills"]');
    if (!skillsContainer) return;

    skillsContainer.innerHTML = '';

    if (resumeState.skills && resumeState.skills.length > 0) {
        resumeState.skills.forEach(skillCategory => {
            if (skillCategory.category && skillCategory.items && skillCategory.items.length > 0) {
                const skillCard = iframeDoc.createElement('div');
                skillCard.className = 'skill-category';
                skillCard.innerHTML = `
                    <div class="skill-category-title">${escapeHtml(skillCategory.category)}</div>
                    <div class="skill-items">${escapeHtml(skillCategory.items.join(', '))}</div>
                `;
                skillsContainer.appendChild(skillCard);
            }
        });
    }
}

function addSkillCategory() {
    const container = document.getElementById('skillsContainer');
    const index = resumeState.skills.length;

    const skillCategoryItem = {
        category: '',
        items: []
    };

    resumeState.skills.push(skillCategoryItem);

    const itemDiv = document.createElement('div');
    itemDiv.className = 'form-item';
    itemDiv.innerHTML = `
        <div class="form-item-header">
            <span>Skill Category ${index + 1}</span>
            <button type="button" class="remove-item-btn" onclick="removeSkillCategory(${index})">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <input type="text" class="form-input" placeholder="Category Name (e.g., Programming Languages)" data-skill-field="category" data-skill-index="${index}">
        <textarea class="form-textarea" placeholder="Skills (comma-separated, e.g., JavaScript, Python, Java)" rows="2" data-skill-field="items" data-skill-index="${index}"></textarea>
    `;

    container.appendChild(itemDiv);

    
    itemDiv.querySelectorAll('[data-skill-field]').forEach(field => {
        field.addEventListener('input', debounce((e) => {
            const idx = parseInt(e.target.dataset.skillIndex);
            const fieldName = e.target.dataset.skillField;

            if (fieldName === 'items') {
                
                resumeState.skills[idx][fieldName] = e.target.value.split(',').map(s => s.trim()).filter(s => s);
            } else {
                resumeState.skills[idx][fieldName] = e.target.value;
            }
            updatePreview();
        }, 300));
    });
}

function removeSkillCategory(index) {
    resumeState.skills.splice(index, 1);
    rebuildSkillsUI();
    updatePreview();
}

function rebuildSkillsUI() {
    const container = document.getElementById('skillsContainer');
    container.innerHTML = '';
    resumeState.skills.forEach((_, index) => {
        addSkillCategory();
    });
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
    resumeState.experience.forEach((_, index) => {
        addExperience();
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
    resumeState.education.forEach((_, index) => {
        addEducation();
    });
}

function addCertification() {
    const container = document.getElementById('certificationsContainer');
    const index = resumeState.certifications.length;

    const certificationItem = {
        name: '',
        organization: '',
        date: ''
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
        <input type="text" class="form-input" placeholder="Issuing Organization" data-cert-field="organization" data-cert-index="${index}">
        <input type="text" class="form-input" placeholder="Date (e.g., 2023)" data-cert-field="date" data-cert-index="${index}">
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
    resumeState.certifications.forEach((_, index) => {
        addCertification();
    });
}

function updateCertificationsList(iframeDoc) {
    const section = iframeDoc.querySelector('[data-section="certifications"]');
    const container = iframeDoc.querySelector('[data-field="certifications-list"]');

    if (!section || !container) return;

    if (resumeState.certifications && resumeState.certifications.length > 0) {
        section.style.display = 'block';
        container.innerHTML = '';

        resumeState.certifications.forEach(cert => {
            if (cert.name) {
                const entry = iframeDoc.createElement('div');
                entry.className = 'entry';
                entry.innerHTML = `
                    <div class="entry-header">
                        <div class="entry-title">${escapeHtml(cert.name || '')}</div>
                        <div class="entry-date">${escapeHtml(cert.date || '')}</div>
                    </div>
                    ${cert.organization ? `<div class="entry-company">${escapeHtml(cert.organization)}</div>` : ''}
                `;
                container.appendChild(entry);
            }
        });
    } else {
        section.style.display = 'none';
    }
}

function updateLanguages(iframeDoc) {
    const section = iframeDoc.querySelector('[data-section="languages"]');
    const languagesElement = iframeDoc.querySelector('[data-field="languages"]');

    if (!section || !languagesElement) return;

    if (resumeState.languages && resumeState.languages.trim()) {
        section.style.display = 'block';
        languagesElement.textContent = resumeState.languages;
    } else {
        section.style.display = 'none';
    }
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

            
            if (Array.isArray(skillsData) && skillsData.length > 0) {
                if (typeof skillsData[0] === 'string') {
                    
                    resumeState.skills = [{
                        category: 'Skills',
                        items: skillsData
                    }];
                } else {
                    
                    resumeState.skills = skillsData;
                }
                resumeState.skills.forEach(() => addSkillCategory());
            }
        } catch (e) {
            console.error('Error loading skills:', e);
        }
    }

    
    if (resumeData.certifications) {
        try {
            const certsData = typeof resumeData.certifications === 'string' ? JSON.parse(resumeData.certifications) : resumeData.certifications;
            resumeState.certifications = certsData;
            certsData.forEach(() => addCertification());
        } catch (e) {
            console.error('Error loading certifications:', e);
        }
    }

    
    if (resumeData.languages) {
        resumeState.languages = typeof resumeData.languages === 'string' ? resumeData.languages : JSON.stringify(resumeData.languages);
        document.getElementById('languages').value = resumeState.languages;
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
        formData.append('template_name', 'modern');
        formData.append('personal_details', JSON.stringify(resumeState.personal_details));
        formData.append('summary_text', resumeState.summary_text);
        formData.append('experience', JSON.stringify(resumeState.experience));
        formData.append('education', JSON.stringify(resumeState.education));
        formData.append('skills', JSON.stringify(resumeState.skills));
        formData.append('certifications', JSON.stringify(resumeState.certifications));
        formData.append('languages', resumeState.languages);
        formData.append('status', 'draft');

        const response = await fetch(appUrl('api/save-resume.php'), {
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
