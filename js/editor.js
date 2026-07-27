

let currentResumeData = resumeData || {}; 
let experienceItems = [];
let educationItems = [];
let projectItems = [];
let boardItems = [];
let portfolioItems = [];
let publicationItems = [];
let grantItems = [];
let teachingItems = [];
let referenceItems = [];


const TEMPLATE_CONFIG = {
    'modern': {
        type: 'professional',
        fields: []
    },
    'professional': {
        type: 'professional',
        fields: []
    },
    'academic-standard': {
        type: 'academic',
        fields: ['researchInterests', 'publications', 'grants', 'teaching', 'references']
    }
};


document.addEventListener('DOMContentLoaded', () => {
    
    const urlParams = new URLSearchParams(window.location.search);
    const resumeId = urlParams.get('id');

    console.log('Resume ID:', resumeId);
    console.log('Template name:', currentResumeData.template_name);
    console.log('Should show modal?', !resumeId && !currentResumeData.template_name);

    if (!resumeId && !currentResumeData.template_name) {
        
        console.log('Showing template selection modal...');
        showTemplateSelectionModal();
    } else {
        
        console.log('Loading editor normally...');
        initializeEditor();
        loadTemplatePreview();
        setupEventListeners();
    }

    
    if (urlParams.get('download') === 'true') {
        
        setTimeout(() => {
            downloadPDF();
        }, 2000); 
    }
});


function showTemplateSelectionModal() {
    const modal = document.getElementById('templateSelectionModal');
    if (modal) {
        modal.classList.add('show');

        
        const selectBtns = modal.querySelectorAll('.select-template-btn');
        selectBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const templateName = btn.dataset.template;
                selectTemplate(templateName);
            });
        });
    }
}

function selectTemplate(templateName) {
    console.log('Template selected:', templateName);

    
    const currentUrl = new URL(window.location.href);
    const resumeId = currentUrl.searchParams.get('id');

    
    let editorUrl = `/ATS/editor-${templateName}.php`;
    if (resumeId) {
        editorUrl += `?id=${resumeId}`;
    }

    
    window.location.href = editorUrl;
}

function initializeEditor() {
    
    loadExperienceAndEducation();

    
    adjustFieldsForTemplate(currentResumeData.template_name || 'classic');

    
    if (currentResumeData.id) {
        console.log('Editing existing resume:', currentResumeData.id);
    } else {
        console.log('Creating new resume');
    }
}

function adjustFieldsForTemplate(templateName) {
    
    document.querySelectorAll('.template-specific').forEach(section => {
        section.style.display = 'none';
    });

    
    const config = TEMPLATE_CONFIG[templateName];
    if (!config) {
        console.warn('Unknown template:', templateName);
        return;
    }

    
    config.fields.forEach(field => {
        const sectionMap = {
            'projects': { id: 'projectsSection', init: () => { if (projectItems.length === 0) addProjectItem(); } },
            'achievements': { id: 'achievementsSection' },
            'board': { id: 'boardSection', init: () => { if (boardItems.length === 0) addBoardItem(); } },
            'portfolio': { id: 'portfolioSection', init: () => { if (portfolioItems.length === 0) addPortfolioItem(); } },
            'researchInterests': { id: 'researchInterestsSection' },
            'publications': { id: 'publicationsSection', init: () => { if (publicationItems.length === 0) addPublicationItem(); } },
            'grants': { id: 'grantsSection', init: () => { if (grantItems.length === 0) addGrantItem(); } },
            'teaching': { id: 'teachingSection', init: () => { if (teachingItems.length === 0) addTeachingItem(); } },
            'references': { id: 'referencesSection', init: () => { if (referenceItems.length === 0) addReferenceItem(); } }
        };

        const sectionInfo = sectionMap[field];
        if (sectionInfo) {
            const section = document.getElementById(sectionInfo.id);
            if (section) {
                section.style.display = 'block';
                
                if (sectionInfo.init) {
                    sectionInfo.init();
                }
            }
        }
    });

    console.log(`Adjusted fields for template: ${templateName} (${config.type})`);
}

function loadExperienceAndEducation() {
    
    fetch('/ATS/api/get-user-data.php?type=experience')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                experienceItems = data.data;
                renderExperienceItems();
            } else {
                addExperienceItem(); 
            }
        })
        .catch(err => {
            console.error('Error loading experience:', err);
            addExperienceItem(); 
        });

    
    fetch('/ATS/api/get-user-data.php?type=education')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                educationItems = data.data;
                renderEducationItems();
            } else {
                addEducationItem(); 
            }
        })
        .catch(err => {
            console.error('Error loading education:', err);
            addEducationItem(); 
        });
}

function renderExperienceItems() {
    const container = document.getElementById('experienceContainer');
    container.innerHTML = '';

    experienceItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'experience-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Job Title" value="${escapeHtml(item.job_title || '')}" data-index="${index}" data-field="job_title">
            <input type="text" class="form-input" placeholder="Company Name" value="${escapeHtml(item.company_name || '')}" data-index="${index}" data-field="company_name">
            <input type="text" class="form-input" placeholder="Location" value="${escapeHtml(item.location || '')}" data-index="${index}" data-field="location">
            <div style="display: flex; gap: 10px;">
                <input type="text" class="form-input small" placeholder="Start Date (e.g., Jan 2020)" value="${escapeHtml(item.start_date || '')}" data-index="${index}" data-field="start_date">
                <input type="text" class="form-input small" placeholder="End Date (e.g., Present)" value="${escapeHtml(item.end_date || '')}" data-index="${index}" data-field="end_date">
            </div>
            <textarea class="form-textarea" placeholder="Description (separate bullet points with new lines)" rows="3" data-index="${index}" data-field="description">${escapeHtml(item.description || '')}</textarea>
            ${experienceItems.length > 1 ? `<button class="remove-btn" onclick="removeExperience(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    
    container.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            experienceItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function renderEducationItems() {
    const container = document.getElementById('educationContainer');
    container.innerHTML = '';

    educationItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'education-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Degree" value="${escapeHtml(item.degree || '')}" data-index="${index}" data-field="degree">
            <input type="text" class="form-input" placeholder="Institution" value="${escapeHtml(item.institution || '')}" data-index="${index}" data-field="institution">
            <input type="text" class="form-input" placeholder="Location" value="${escapeHtml(item.location || '')}" data-index="${index}" data-field="location">
            <div style="display: flex; gap: 10px;">
                <input type="text" class="form-input small" placeholder="Start Date" value="${escapeHtml(item.start_date || '')}" data-index="${index}" data-field="start_date">
                <input type="text" class="form-input small" placeholder="End Date" value="${escapeHtml(item.end_date || '')}" data-index="${index}" data-field="end_date">
            </div>
            ${educationItems.length > 1 ? `<button class="remove-btn" onclick="removeEducation(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    
    container.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            educationItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function addExperienceItem() {
    experienceItems.push({
        job_title: '',
        company_name: '',
        location: '',
        start_date: '',
        end_date: '',
        description: ''
    });
    renderExperienceItems();
}

function addEducationItem() {
    educationItems.push({
        degree: '',
        institution: '',
        location: '',
        start_date: '',
        end_date: ''
    });
    renderEducationItems();
}

function removeExperience(index) {
    if (experienceItems.length > 1) {
        experienceItems.splice(index, 1);
        renderExperienceItems();
        debounceUpdatePreview();
    }
}

function removeEducation(index) {
    if (educationItems.length > 1) {
        educationItems.splice(index, 1);
        renderEducationItems();
        debounceUpdatePreview();
    }
}






function addProjectItem() {
    projectItems.push({
        name: '',
        technologies: '',
        description: ''
    });
    renderProjectItems();
}

function renderProjectItems() {
    const container = document.getElementById('projectsContainer');
    if (!container) return;

    container.innerHTML = '';
    projectItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'experience-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Project Name" value="${escapeHtml(item.name || '')}" data-index="${index}" data-field="name">
            <input type="text" class="form-input" placeholder="Technologies Used (e.g., React, Node.js)" value="${escapeHtml(item.technologies || '')}" data-index="${index}" data-field="technologies">
            <textarea class="form-textarea" placeholder="Project Description" rows="3" data-index="${index}" data-field="description">${escapeHtml(item.description || '')}</textarea>
            ${projectItems.length > 1 ? `<button class="remove-btn" onclick="removeProject(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    container.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            projectItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function removeProject(index) {
    if (projectItems.length > 1) {
        projectItems.splice(index, 1);
        renderProjectItems();
        debounceUpdatePreview();
    }
}


function addBoardItem() {
    boardItems.push({
        title: '',
        organization: '',
        years: ''
    });
    renderBoardItems();
}

function renderBoardItems() {
    const container = document.getElementById('boardContainer');
    if (!container) return;

    container.innerHTML = '';
    boardItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'education-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Title (e.g., Board of Directors)" value="${escapeHtml(item.title || '')}" data-index="${index}" data-field="title">
            <input type="text" class="form-input" placeholder="Organization Name" value="${escapeHtml(item.organization || '')}" data-index="${index}" data-field="organization">
            <input type="text" class="form-input" placeholder="Years (e.g., 2020-Present)" value="${escapeHtml(item.years || '')}" data-index="${index}" data-field="years">
            ${boardItems.length > 1 ? `<button class="remove-btn" onclick="removeBoard(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    container.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            boardItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function removeBoard(index) {
    if (boardItems.length > 1) {
        boardItems.splice(index, 1);
        renderBoardItems();
        debounceUpdatePreview();
    }
}


function addPortfolioItem() {
    portfolioItems.push({
        name: '',
        role: '',
        description: '',
        link: ''
    });
    renderPortfolioItems();
}

function renderPortfolioItems() {
    const container = document.getElementById('portfolioContainer');
    if (!container) return;

    container.innerHTML = '';
    portfolioItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'experience-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Project Name" value="${escapeHtml(item.name || '')}" data-index="${index}" data-field="name">
            <input type="text" class="form-input" placeholder="Role (e.g., Lead Designer)" value="${escapeHtml(item.role || '')}" data-index="${index}" data-field="role">
            <textarea class="form-textarea" placeholder="Description" rows="2" data-index="${index}" data-field="description">${escapeHtml(item.description || '')}</textarea>
            <input type="text" class="form-input" placeholder="Portfolio Link (optional)" value="${escapeHtml(item.link || '')}" data-index="${index}" data-field="link">
            ${portfolioItems.length > 1 ? `<button class="remove-btn" onclick="removePortfolio(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    container.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            portfolioItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function removePortfolio(index) {
    if (portfolioItems.length > 1) {
        portfolioItems.splice(index, 1);
        renderPortfolioItems();
        debounceUpdatePreview();
    }
}


function addPublicationItem() {
    publicationItems.push({
        authors: '',
        year: '',
        title: '',
        journal: '',
        details: ''
    });
    renderPublicationItems();
}

function renderPublicationItems() {
    const container = document.getElementById('publicationsContainer');
    if (!container) return;

    container.innerHTML = '';
    publicationItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'experience-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Authors (e.g., Smith, J., & Doe, A.)" value="${escapeHtml(item.authors || '')}" data-index="${index}" data-field="authors">
            <input type="text" class="form-input small" placeholder="Year" value="${escapeHtml(item.year || '')}" data-index="${index}" data-field="year">
            <input type="text" class="form-input" placeholder="Publication Title" value="${escapeHtml(item.title || '')}" data-index="${index}" data-field="title">
            <input type="text" class="form-input" placeholder="Journal/Conference Name" value="${escapeHtml(item.journal || '')}" data-index="${index}" data-field="journal">
            <input type="text" class="form-input" placeholder="Details (e.g., 45(3), 234-256)" value="${escapeHtml(item.details || '')}" data-index="${index}" data-field="details">
            ${publicationItems.length > 1 ? `<button class="remove-btn" onclick="removePublication(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    container.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            publicationItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function removePublication(index) {
    if (publicationItems.length > 1) {
        publicationItems.splice(index, 1);
        renderPublicationItems();
        debounceUpdatePreview();
    }
}


function addGrantItem() {
    grantItems.push({
        title: '',
        role: '',
        amount: '',
        years: ''
    });
    renderGrantItems();
}

function renderGrantItems() {
    const container = document.getElementById('grantsContainer');
    if (!container) return;

    container.innerHTML = '';
    grantItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'education-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Grant Title (e.g., NIH R01)" value="${escapeHtml(item.title || '')}" data-index="${index}" data-field="title">
            <input type="text" class="form-input" placeholder="Role (e.g., Principal Investigator)" value="${escapeHtml(item.role || '')}" data-index="${index}" data-field="role">
            <input type="text" class="form-input" placeholder="Amount (e.g., $500,000)" value="${escapeHtml(item.amount || '')}" data-index="${index}" data-field="amount">
            <input type="text" class="form-input" placeholder="Years (e.g., 2023-2028)" value="${escapeHtml(item.years || '')}" data-index="${index}" data-field="years">
            ${grantItems.length > 1 ? `<button class="remove-btn" onclick="removeGrant(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    container.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            grantItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function removeGrant(index) {
    if (grantItems.length > 1) {
        grantItems.splice(index, 1);
        renderGrantItems();
        debounceUpdatePreview();
    }
}


function addTeachingItem() {
    teachingItems.push({
        courses: '',
        institution: '',
        years: ''
    });
    renderTeachingItems();
}

function renderTeachingItems() {
    const container = document.getElementById('teachingContainer');
    if (!container) return;

    container.innerHTML = '';
    teachingItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'education-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Course(s) Taught" value="${escapeHtml(item.courses || '')}" data-index="${index}" data-field="courses">
            <input type="text" class="form-input" placeholder="Institution" value="${escapeHtml(item.institution || '')}" data-index="${index}" data-field="institution">
            <input type="text" class="form-input" placeholder="Years" value="${escapeHtml(item.years || '')}" data-index="${index}" data-field="years">
            ${teachingItems.length > 1 ? `<button class="remove-btn" onclick="removeTeaching(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    container.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            teachingItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function removeTeaching(index) {
    if (teachingItems.length > 1) {
        teachingItems.splice(index, 1);
        renderTeachingItems();
        debounceUpdatePreview();
    }
}


function addReferenceItem() {
    referenceItems.push({
        name: '',
        title: '',
        institution: '',
        email: '',
        phone: ''
    });
    renderReferenceItems();
}

function renderReferenceItems() {
    const container = document.getElementById('referencesContainer');
    if (!container) return;

    container.innerHTML = '';
    referenceItems.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'experience-item';
        div.innerHTML = `
            <input type="text" class="form-input" placeholder="Name (e.g., Dr. Jane Smith)" value="${escapeHtml(item.name || '')}" data-index="${index}" data-field="name">
            <input type="text" class="form-input" placeholder="Title (e.g., Professor of Psychology)" value="${escapeHtml(item.title || '')}" data-index="${index}" data-field="title">
            <input type="text" class="form-input" placeholder="Institution" value="${escapeHtml(item.institution || '')}" data-index="${index}" data-field="institution">
            <input type="email" class="form-input" placeholder="Email" value="${escapeHtml(item.email || '')}" data-index="${index}" data-field="email">
            <input type="tel" class="form-input" placeholder="Phone" value="${escapeHtml(item.phone || '')}" data-index="${index}" data-field="phone">
            ${referenceItems.length > 1 ? `<button class="remove-btn" onclick="removeReference(${index})">Remove</button>` : ''}
        `;
        container.appendChild(div);
    });

    container.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', (e) => {
            const index = parseInt(e.target.dataset.index);
            const field = e.target.dataset.field;
            referenceItems[index][field] = e.target.value;
            debounceUpdatePreview();
        });
    });
}

function removeReference(index) {
    if (referenceItems.length > 1) {
        referenceItems.splice(index, 1);
        renderReferenceItems();
        debounceUpdatePreview();
    }
}

function setupEventListeners() {
    
    document.getElementById('addExperienceBtn')?.addEventListener('click', addExperienceItem);

    
    document.getElementById('addEducationBtn')?.addEventListener('click', addEducationItem);

    
    document.getElementById('addProjectBtn')?.addEventListener('click', addProjectItem);
    document.getElementById('addBoardBtn')?.addEventListener('click', addBoardItem);
    document.getElementById('addPortfolioBtn')?.addEventListener('click', addPortfolioItem);
    document.getElementById('addPublicationBtn')?.addEventListener('click', addPublicationItem);
    document.getElementById('addGrantBtn')?.addEventListener('click', addGrantItem);
    document.getElementById('addTeachingBtn')?.addEventListener('click', addTeachingItem);
    document.getElementById('addReferenceBtn')?.addEventListener('click', addReferenceItem);

    
    

    
    const formInputs = document.querySelectorAll('.form-input, .form-textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', debounceUpdatePreview);
    });

    
    document.getElementById('achievements')?.addEventListener('input', debounceUpdatePreview);
    document.getElementById('researchInterests')?.addEventListener('input', debounceUpdatePreview);

    
    document.getElementById('saveResumeBtn')?.addEventListener('click', saveResume);

    
    document.getElementById('downloadBtn')?.addEventListener('click', downloadPDF);

    
    setupJobDescriptionToggle();

    
    document.querySelector('.analyze-btn')?.addEventListener('click', performAIAnalysis);
}

function loadTemplatePreview() {
    const iframe = document.getElementById('resumePreview');
    const templateName = currentResumeData.template_name || 'classic';

    
    isTemplateLoading = true;
    console.log('Loading template:', templateName, '(isTemplateLoading = true)');

    
    const timestamp = new Date().getTime();
    iframe.src = `/ATS/templates/${templateName}.html?v=${timestamp}`;

    
    iframe.onload = () => {
        console.log('Template loaded, updating preview');
        
        setTimeout(() => {
            isTemplateLoading = false;
            console.log('Template ready (isTemplateLoading = false)');
            updatePreview();
        }, 100);
    };
}

let updateTimer;
let isTemplateLoading = false;

function debounceUpdatePreview() {
    console.log('debounceUpdatePreview called, isTemplateLoading:', isTemplateLoading);
    if (isTemplateLoading) {
        console.log('Template is loading, skipping update');
        return;
    }
    clearTimeout(updateTimer);
    updateTimer = setTimeout(updatePreview, 300);
}

function updatePreview() {
    console.log('updatePreview called');
    const iframe = document.getElementById('resumePreview');
    if (!iframe || !iframe.contentWindow) {
        console.error('No iframe or contentWindow found');
        return;
    }

    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    if (!iframeDoc) {
        console.error('No iframeDoc found');
        return;
    }
    console.log('iframe and iframeDoc found, updating...');

    
    const nameField = iframeDoc.querySelector('[data-field="name"]');
    console.log('Template has data-field="name"?', nameField ? 'YES' : 'NO');
    if (!nameField) {
        console.error('Template does not have data-field attributes! Template may not be loaded correctly.');
        console.log('iframe src:', iframe.src);
        console.log('iframeDoc body:', iframeDoc.body);
    }

    
    const formData = {
        fullName: document.getElementById('fullName')?.value || '',
        professionalTitle: document.getElementById('professionalTitle')?.value || '',
        email: document.getElementById('email')?.value || '',
        phone: document.getElementById('phone')?.value || '',
        location: document.getElementById('location')?.value || '',
        linkedin: document.getElementById('linkedin')?.value || '',
        summary: document.getElementById('summary')?.value || ''
    };

    
    const fieldMapping = {
        fullName: 'name',
        professionalTitle: 'title',
        email: 'email',
        phone: 'phone',
        location: 'location',
        linkedin: 'linkedin',
        summary: 'summary'
    };

    
    console.log('Form data:', formData);
    Object.keys(formData).forEach(key => {
        const templateField = fieldMapping[key] || key;
        const element = iframeDoc.querySelector(`[data-field="${templateField}"]`);
        console.log(`Looking for [data-field="${templateField}"], found:`, element);
        if (element) {
            element.textContent = formData[key];
            console.log(`Updated ${key} to:`, formData[key]);
        }
    });

    
    const expContainer = iframeDoc.querySelector('[data-field="experience-list"]') || iframeDoc.querySelector('[data-field="experience"]');
    if (expContainer && experienceItems.length > 0) {
        expContainer.innerHTML = '';
        experienceItems.forEach(item => {
            if (!item.job_title && !item.company_name) return; 

            const entryDiv = document.createElement('div');
            entryDiv.className = 'entry';

            let html = '<div class="entry-header">';
            html += '<div class="entry-title-line">';
            html += `<div class="entry-title">${escapeHtml(item.job_title || '')}</div>`;
            html += `<div class="entry-date">${escapeHtml(item.start_date || '')} ${item.end_date ? '- ' + escapeHtml(item.end_date) : ''}</div>`;
            html += '</div>';
            html += `<div class="entry-company">${escapeHtml(item.company_name || '')}${item.location ? ', ' + escapeHtml(item.location) : ''}</div>`;
            html += '</div>';

            if (item.description) {
                html += '<div class="entry-description"><ul>';
                const bullets = item.description.split('\n').filter(line => line.trim());
                bullets.forEach(bullet => {
                    html += `<li>${escapeHtml(bullet)}</li>`;
                });
                html += '</ul></div>';
            }

            entryDiv.innerHTML = html;
            expContainer.appendChild(entryDiv);
        });
    }

    
    const eduContainer = iframeDoc.querySelector('[data-field="education-list"]') || iframeDoc.querySelector('[data-field="education"]');
    if (eduContainer && educationItems.length > 0) {
        eduContainer.innerHTML = '';
        educationItems.forEach(item => {
            if (!item.degree && !item.institution) return; 

            const entryDiv = document.createElement('div');
            entryDiv.className = 'entry';

            let html = '<div class="entry-header">';
            html += '<div class="entry-title-line">';
            html += `<div class="entry-title">${escapeHtml(item.degree || '')}</div>`;
            html += `<div class="entry-date">${escapeHtml(item.start_date || '')} ${item.end_date ? '- ' + escapeHtml(item.end_date) : ''}</div>`;
            html += '</div>';
            html += `<div class="entry-company">${escapeHtml(item.institution || '')}${item.location ? ', ' + escapeHtml(item.location) : ''}</div>`;
            html += '</div>';

            entryDiv.innerHTML = html;
            eduContainer.appendChild(entryDiv);
        });
    }

    
    const skills = document.getElementById('skills')?.value || '';
    const skillsContainer = iframeDoc.querySelector('[data-field="skills"]');
    if (skillsContainer && skills) {
        const templateName = currentResumeData.template_name || 'classic';

        if (templateName === 'classic') {
            
            skillsContainer.innerHTML = '';
            const skillArray = skills.split(',').map(s => s.trim()).filter(s => s);
            skillArray.forEach(skill => {
                const span = document.createElement('span');
                span.className = 'skill-item';
                span.textContent = skill;
                skillsContainer.appendChild(span);
            });
        } else if (templateName === 'modern') {
            
            skillsContainer.innerHTML = '';
            const skillArray = skills.split(',').map(s => s.trim()).filter(s => s);

            
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'skill-category';
            categoryDiv.innerHTML = `
                <div class="skill-category-title">Skills</div>
                <div class="skill-items">${escapeHtml(skillArray.join(', '))}</div>
            `;
            skillsContainer.appendChild(categoryDiv);
        } else if (templateName === 'professional') {
            
            skillsContainer.innerHTML = '';
            const skillArray = skills.split(',').map(s => s.trim()).filter(s => s);

            const rowDiv = document.createElement('div');
            rowDiv.className = 'skill-row';
            rowDiv.innerHTML = `
                <span class="skill-label">Skills:</span>
                <span class="skill-items">${escapeHtml(skillArray.join(', '))}</span>
            `;
            skillsContainer.appendChild(rowDiv);
        }
    }
}

function saveResume() {
    const resumeTitle = document.getElementById('resumeTitle')?.value.trim();

    if (!resumeTitle) {
        console.log('Please enter a resume title');
        return;
    }

    const personalDetails = {
        fullName: document.getElementById('fullName')?.value || '',
        professionalTitle: document.getElementById('professionalTitle')?.value || '',
        email: document.getElementById('email')?.value || '',
        phone: document.getElementById('phone')?.value || '',
        location: document.getElementById('location')?.value || '',
        linkedin: document.getElementById('linkedin')?.value || ''
    };

    const summary = document.getElementById('summary')?.value || '';
    const templateName = document.getElementById('templateSelect')?.value || 'classic';

    const data = {
        resume_id: currentResumeData.id || null,
        resume_title: resumeTitle,
        template_name: templateName,
        personal_details: personalDetails,
        summary_text: summary,
        status: 'draft'
    };

    console.log('Saving resume...');

    fetch('/ATS/api/save-resume.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log('Resume saved successfully!');
            currentResumeData.id = result.resume_id;

            
            if (!window.location.search.includes('id=')) {
                const newUrl = window.location.pathname + '?id=' + result.resume_id;
                window.history.pushState({}, '', newUrl);
            }
        } else {
            console.log('Error: ' + result.message);
        }
    })
    .catch(err => {
        console.error('Save error:', err);
        console.log('Failed to save resume');
    });
}

// Simplified PDF download using browser print function (same as other editors)
function downloadPDF() {
    try {
        const iframe = document.getElementById('resumePreview');
        if (!iframe || !iframe.contentWindow) {
            console.log('Preview not loaded yet');
            return;
        }

        console.log('Generating PDF...');

        // Use browser's print dialog - simpler and more reliable
        const iframeWindow = iframe.contentWindow;
        iframeWindow.focus();
        iframeWindow.print();

        console.log('PDF print dialog opened');
    } catch (error) {
        console.error('Error opening print dialog:', error);
        console.log('Error downloading PDF');
    }
}


async function performAIAnalysis() {
    try {
        
        const jobDescText = document.getElementById('jobDescText')?.value.trim();
        const jobDescFile = document.getElementById('jobDescFile')?.files[0];

        if (!jobDescText && !jobDescFile) {
            console.log('Please provide a job description to analyze against');
            return;
        }

        
        const analyzeBtn = document.querySelector('.analyze-btn');
        const originalText = analyzeBtn.innerHTML;
        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Analyzing...';

        
        updateScoreDisplay('analyzing', '--', 'Analyzing your resume...');

        
        const resumeText = generateResumeText();

        
        const formData = new FormData();
        formData.append('resume_text', resumeText);

        if (jobDescText) {
            formData.append('job_description', jobDescText);
        } else if (jobDescFile) {
            formData.append('job_description_file', jobDescFile);
        }

        
        console.log('Sending request to API...');
        console.log('Resume text length:', resumeText.length);
        console.log('Has job description:', !!jobDescText || !!jobDescFile);

        const response = await fetch('/ATS/api/analyze-ats-score.php', {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status);

        const responseText = await response.text();
        console.log('Raw response:', responseText);

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Invalid JSON response from server');
        }

        console.log('Parsed result:', result);

        if (result.success) {
            console.log('Success! Full API Response:', result);

            
            const analysis = result.analysis || result;

            console.log('Overall score:', analysis.overall_score);
            console.log('Improvements:', analysis.improvements);
            console.log('Keywords found:', analysis.keywords_found);
            console.log('Keywords missing:', analysis.keywords_missing);

            
            const score = analysis.overall_score || analysis.score || 0;
            updateScoreDisplay('success', score, getScoreLabel(score));

            
            displaySuggestions(
                analysis.improvements || [],
                analysis.keywords_found || [],
                analysis.keywords_missing || []
            );

            console.log('Analysis complete!');
        } else {
            console.error('API returned error:', result);
            updateScoreDisplay('error', '--', 'Analysis failed');
            console.log('Error: ' + (result.message || 'Analysis failed'));
        }

    } catch (error) {
        console.error('Analysis error:', error);
        updateScoreDisplay('error', '--', 'Analysis failed');
        console.log('Failed to analyze resume. Please try again.');
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

    text += `${fullName}\n${professionalTitle}\n`;
    text += `${email} | ${phone} | ${location}\n\n`;

    
    const summary = document.getElementById('summary')?.value || '';
    if (summary) {
        text += `PROFESSIONAL SUMMARY\n${summary}\n\n`;
    }

    
    if (experienceItems.length > 0) {
        text += 'EXPERIENCE\n';
        experienceItems.forEach(item => {
            if (item.job_title || item.company_name) {
                text += `${item.job_title} | ${item.company_name}\n`;
                text += `${item.start_date} - ${item.end_date}\n`;
                if (item.description) {
                    text += `${item.description}\n`;
                }
                text += '\n';
            }
        });
    }

    
    if (educationItems.length > 0) {
        text += 'EDUCATION\n';
        educationItems.forEach(item => {
            if (item.degree || item.institution) {
                text += `${item.degree} | ${item.institution}\n`;
                text += `${item.start_date} - ${item.end_date}\n\n`;
            }
        });
    }

    
    const skills = document.getElementById('skills')?.value || '';
    if (skills) {
        text += `SKILLS\n${skills}\n\n`;
    }

    return text;
}


function updateScoreDisplay(state, score, label) {
    const scoreText = document.querySelector('.score-text');
    const scoreStatus = document.querySelector('.score-status');
    const scoreFill = document.querySelector('.score-fill');

    if (scoreText) scoreText.textContent = score;
    if (scoreStatus) scoreStatus.textContent = label;

    if (scoreFill && score !== '--') {
        const numScore = parseInt(score);
        const circumference = 2 * Math.PI * 45;
        const offset = circumference - (numScore / 100) * circumference;
        scoreFill.style.strokeDashoffset = offset;

        
        if (numScore >= 80) {
            scoreFill.style.stroke = '#10b981'; 
        } else if (numScore >= 60) {
            scoreFill.style.stroke = '#f59e0b'; 
        } else {
            scoreFill.style.stroke = '#ef4444'; 
        }
    }
}


function getScoreLabel(score) {
    const numScore = parseInt(score);
    if (numScore >= 80) return 'Excellent Match';
    if (numScore >= 60) return 'Good Match';
    if (numScore >= 40) return 'Fair Match';
    return 'Needs Improvement';
}


function displaySuggestions(improvements, keywordsFound, keywordsMissing) {
    const suggestionsSection = document.querySelector('.suggestions-section');
    if (!suggestionsSection) {
        console.error('Suggestions section not found');
        return;
    }

    let html = '<h3 class="form-section-title">Analysis Results</h3>';

    
    if (keywordsFound && keywordsFound.length > 0) {
        html += '<div class="suggestion-box success-box">';
        html += '<h4><i class="fa-solid fa-check-circle"></i> Keywords Found</h4>';
        html += '<div class="keywords-list">';
        keywordsFound.forEach(keyword => {
            html += `<span class="keyword-badge found">${escapeHtml(keyword)}</span>`;
        });
        html += '</div></div>';
    }

    
    if (keywordsMissing && keywordsMissing.length > 0) {
        html += '<div class="suggestion-box warning-box">';
        html += '<h4><i class="fa-solid fa-exclamation-circle"></i> Missing Keywords</h4>';
        html += '<div class="keywords-list">';
        keywordsMissing.forEach(keyword => {
            html += `<span class="keyword-badge missing">${escapeHtml(keyword)}</span>`;
        });
        html += '</div></div>';
    }

    
    if (improvements && improvements.length > 0) {
        html += '<div class="suggestion-box info-box">';
        html += '<h4><i class="fa-solid fa-lightbulb"></i> Recommendations</h4>';
        html += '<ul class="suggestions-list">';
        improvements.forEach(improvement => {
            
            if (typeof improvement === 'string') {
                html += `<li>${escapeHtml(improvement)}</li>`;
            } else if (improvement.suggestion) {
                html += `<li><strong>${escapeHtml(improvement.category || 'General')}:</strong> ${escapeHtml(improvement.suggestion)}</li>`;
            }
        });
        html += '</ul></div>';
    }

    
    if ((!keywordsFound || keywordsFound.length === 0) &&
        (!keywordsMissing || keywordsMissing.length === 0) &&
        (!improvements || improvements.length === 0)) {
        html += '<p class="empty-state">No specific recommendations at this time. Your resume looks good!</p>';
    }

    suggestionsSection.innerHTML = html;
    console.log('Suggestions displayed');
}


function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function setupJobDescriptionToggle() {
    const textToggle = document.getElementById('textToggle');
    const fileToggle = document.getElementById('fileToggle');
    const textInput = document.getElementById('textInput');
    const fileInput = document.getElementById('fileInput');

    if (textToggle && fileToggle) {
        textToggle.addEventListener('click', () => {
            textToggle.classList.add('active');
            fileToggle.classList.remove('active');
            textInput.classList.remove('hidden');
            fileInput.classList.add('hidden');
        });

        fileToggle.addEventListener('click', () => {
            fileToggle.classList.add('active');
            textToggle.classList.remove('active');
            fileInput.classList.remove('hidden');
            textInput.classList.add('hidden');
        });
    }

    
    const jobDescFile = document.getElementById('jobDescFile');
    const fileUploadLabel = document.querySelector('.file-upload-label');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const removeFileBtn = document.getElementById('removeFile');

    if (jobDescFile) {
        jobDescFile.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.type !== 'application/pdf') {
                    console.log('Please upload a PDF file only');
                    jobDescFile.value = '';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    console.log('File size must be 5MB or smaller');
                    jobDescFile.value = '';
                    return;
                }

                fileName.textContent = file.name;
                fileUploadLabel.style.display = 'none';
                fileInfo.classList.remove('hidden');
                console.log('File uploaded successfully');
            }
        });
    }

    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', () => {
            jobDescFile.value = '';
            fileUploadLabel.style.display = 'flex';
            fileInfo.classList.add('hidden');
        });
    }
}

console.log('Resume Editor initialized');
