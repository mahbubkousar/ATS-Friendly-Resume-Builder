


let resumeState = {
    id: resumeData.id,
    resume_title: resumeData.resume_title,
    template_name: 'academic-standard',
    personal_details: resumeData.personal_details || {},
    summary_text: resumeData.summary_text || '',
    experience: [],
    education: [],
    skills: '',
    researchInterests: '',
    publications: [],
    grants: [],
    teaching: [],
    memberships: ''
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
    previewIframe.src = appUrl(`templates/academic-standard.html?v=${timestamp}`);

    previewIframe.onload = () => {
        setTimeout(() => {
            isTemplateLoading = false;
            updatePreview();
        }, 100);
    };
}

function setupEventListeners() {
    
    const personalFields = ['fullName', 'professionalTitle', 'department', 'email', 'phone', 'location', 'orcid', 'googleScholar'];
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

    
    const researchInterestsElement = document.getElementById('researchInterests');
    if (researchInterestsElement) {
        researchInterestsElement.addEventListener('input', debounce(() => {
            resumeState.researchInterests = researchInterestsElement.value;
            updatePreview();
        }, 300));
    }

    
    const skillsElement = document.getElementById('skills');
    if (skillsElement) {
        skillsElement.addEventListener('input', debounce(() => {
            resumeState.skills = skillsElement.value;
            updatePreview();
        }, 300));
    }

    
    const membershipsElement = document.getElementById('memberships');
    if (membershipsElement) {
        membershipsElement.addEventListener('input', debounce(() => {
            resumeState.memberships = membershipsElement.value;
            updatePreview();
        }, 300));
    }

    
    document.getElementById('addExperienceBtn').addEventListener('click', addExperience);
    document.getElementById('addEducationBtn').addEventListener('click', addEducation);
    document.getElementById('addPublicationBtn').addEventListener('click', addPublication);
    document.getElementById('addGrantBtn').addEventListener('click', addGrant);
    document.getElementById('addTeachingBtn').addEventListener('click', addTeaching);
}

function updatePreview() {
    if (isTemplateLoading) return;

    const iframeDoc = previewIframe.contentDocument || previewIframe.contentWindow.document;
    if (!iframeDoc) return;

    
    const fieldMapping = {
        fullName: 'name'
    };

    Object.keys(resumeState.personal_details).forEach(key => {
        const value = resumeState.personal_details[key];

        
        if (key === 'orcid' || key === 'googleScholar') return;

        const dataField = fieldMapping[key] || key;
        const element = iframeDoc.querySelector(`[data-field="${dataField}"]`);
        if (element && value) {
            element.textContent = value;
        }
    });

    
    const orcidValue = resumeState.personal_details.orcid || '';
    const scholarValue = resumeState.personal_details.googleScholar || '';

    const orcidDisplay = iframeDoc.querySelector('[data-field="orcid-display"]');
    const orcidSpan = iframeDoc.querySelector('[data-field="orcid"]');
    const scholarDisplay = iframeDoc.querySelector('[data-field="googleScholar-display"]');
    const scholarText = iframeDoc.querySelector('[data-field="googleScholar-text"]');
    const separator = iframeDoc.querySelector('[data-field="scholar-separator"]');
    const profilesDiv = iframeDoc.querySelector('.academic-profiles');

    if (orcidValue || scholarValue) {
        if (profilesDiv) profilesDiv.style.display = 'block';

        if (orcidValue) {
            if (orcidDisplay) orcidDisplay.style.display = 'inline';
            if (orcidSpan) orcidSpan.textContent = orcidValue;
        } else {
            if (orcidDisplay) orcidDisplay.style.display = 'none';
        }

        if (scholarValue) {
            if (scholarDisplay) scholarDisplay.style.display = 'inline';
            if (scholarText) scholarText.textContent = scholarValue;
        } else {
            if (scholarDisplay) scholarDisplay.style.display = 'none';
        }

        
        if (separator) {
            separator.style.display = (orcidValue && scholarValue) ? 'inline' : 'none';
        }
    } else {
        if (profilesDiv) profilesDiv.style.display = 'none';
    }

    
    updateSummary(iframeDoc);

    
    updateEducationList(iframeDoc);

    
    updateExperienceList(iframeDoc);

    
    updateResearchInterests(iframeDoc);

    
    updatePublications(iframeDoc);

    
    updateGrants(iframeDoc);

    
    updateTeaching(iframeDoc);

    
    updateSkills(iframeDoc);

    
    updateMemberships(iframeDoc);
}

function updateExperienceList(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="experience-list"]');
    const section = iframeDoc.querySelector('[data-section="experience"]');
    if (!container || !section) return;

    
    section.style.display = 'block';
    container.innerHTML = '';

    if (resumeState.experience && resumeState.experience.length > 0) {
        resumeState.experience.forEach(item => {
            const entry = iframeDoc.createElement('div');
            entry.className = 'experience-entry';

            const header = `
                <div class="position-title">${escapeHtml(item.position || '')}</div>
                <div class="employer-date">
                    <span class="employer">${escapeHtml(item.company || '')}${item.location ? ', ' + escapeHtml(item.location) : ''}</span>
                    <span style="float: right;">${escapeHtml(item.startDate || '')} - ${escapeHtml(item.endDate || '')}</span>
                </div>
            `;

            let description = '';
            if (item.description) {
                const points = item.description.split('\n').filter(p => p.trim());
                if (points.length > 0) {
                    description = '<div class="description"><ul>';
                    points.forEach(point => {
                        const cleanPoint = point.trim().replace(/^[•\-\*]\s*/, '');
                        description += `<li>${escapeHtml(cleanPoint)}</li>`;
                    });
                    description += '</ul></div>';
                }
            }

            entry.innerHTML = header + description;
            container.appendChild(entry);
        });
    }
}

function updateSummary(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="summary"]');
    const section = iframeDoc.querySelector('[data-section="summary"]');
    if (!container || !section) return;

    if (resumeState.summary_text && resumeState.summary_text.trim()) {
        section.style.display = 'block';
        container.textContent = resumeState.summary_text;
    } else {
        section.style.display = 'none';
    }
}

function updateEducationList(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="education-list"]');
    const section = iframeDoc.querySelector('[data-section="education"]');
    if (!container || !section) return;

    
    section.style.display = 'block';
    container.innerHTML = '';

    if (resumeState.education && resumeState.education.length > 0) {
        resumeState.education.forEach(item => {
            const entry = iframeDoc.createElement('div');
            entry.className = 'education-entry';

            entry.innerHTML = `
                <div class="degree-line">${escapeHtml(item.degree || '')}</div>
                <div class="institution">${escapeHtml(item.institution || '')}</div>
                <div class="date-gpa">${escapeHtml(item.startDate || '')} - ${escapeHtml(item.endDate || '')}</div>
            `;

            container.appendChild(entry);
        });
    }
}

function updateResearchInterests(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="research-interests"]');
    const section = iframeDoc.querySelector('[data-section="research-interests"]');
    if (!container || !section) return;

    if (resumeState.researchInterests && resumeState.researchInterests.trim()) {
        section.style.display = 'block';
        container.textContent = resumeState.researchInterests;
    } else {
        section.style.display = 'none';
    }
}

function updatePublications(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="publications-list"]');
    const section = iframeDoc.querySelector('[data-section="publications"]');
    if (!container || !section) return;

    if (resumeState.publications && resumeState.publications.length > 0) {
        section.style.display = 'block';
        container.innerHTML = '';

        resumeState.publications.forEach(item => {
            const pub = iframeDoc.createElement('div');
            pub.className = 'publication';
            pub.innerHTML = `
                ${escapeHtml(item.authors || '')} <span class="publication-year">(${escapeHtml(item.year || '')}).</span>
                <span class="publication-title">${escapeHtml(item.title || '')}</span>
                <em>${escapeHtml(item.journal || '')}</em>
            `;
            container.appendChild(pub);
        });
    } else {
        section.style.display = 'none';
    }
}

function updateGrants(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="grants-list"]');
    const section = iframeDoc.querySelector('[data-section="grants"]');
    if (!container || !section) return;

    if (resumeState.grants && resumeState.grants.length > 0) {
        section.style.display = 'block';
        container.innerHTML = '';

        resumeState.grants.forEach(item => {
            const grant = iframeDoc.createElement('div');
            grant.className = 'grant-entry';
            grant.innerHTML = `
                <div class="grant-title">${escapeHtml(item.title || '')}</div>
                <div class="grant-details">
                    ${escapeHtml(item.role || '')} (${escapeHtml(item.year || '')})<br>
                    ${item.amount ? 'Amount: ' + escapeHtml(item.amount) : ''}
                </div>
            `;
            container.appendChild(grant);
        });
    } else {
        section.style.display = 'none';
    }
}

function updateTeaching(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="teaching-list"]');
    const section = iframeDoc.querySelector('[data-section="teaching"]');
    if (!container || !section) return;

    if (resumeState.teaching && resumeState.teaching.length > 0) {
        section.style.display = 'block';
        container.innerHTML = '';

        resumeState.teaching.forEach(item => {
            const course = iframeDoc.createElement('div');
            course.className = 'course';
            course.innerHTML = `
                <span class="course-title">${escapeHtml(item.course || '')}</span> - ${escapeHtml(item.term || '')}
            `;
            container.appendChild(course);
        });
    } else {
        section.style.display = 'none';
    }
}

function updateSkills(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="skills"]');
    const section = iframeDoc.querySelector('[data-section="skills"]');
    if (!container || !section) return;

    if (resumeState.skills && resumeState.skills.trim()) {
        section.style.display = 'block';
        container.textContent = resumeState.skills;
    } else {
        section.style.display = 'none';
    }
}

function updateMemberships(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="memberships"]');
    const section = iframeDoc.querySelector('[data-section="memberships"]');
    if (!container || !section) return;

    if (resumeState.memberships && resumeState.memberships.trim()) {
        section.style.display = 'block';
        const lines = resumeState.memberships.split('\n').map(l => l.trim()).filter(l => l);

        if (lines.length > 0) {
            container.innerHTML = '';
            lines.forEach(line => {
                const div = iframeDoc.createElement('div');
                div.className = 'membership';
                const cleanLine = line.replace(/^[•\-\*]\s*/, '');
                div.textContent = cleanLine;
                container.appendChild(div);
            });
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
    const expCopy = [...resumeState.experience];
    resumeState.experience = [];
    expCopy.forEach((item) => {
        const idx = resumeState.experience.length;
        resumeState.experience.push(item);
        addExperience();
        
        Object.keys(item).forEach(key => {
            const field = container.querySelector(`[data-exp-field="${key}"][data-exp-index="${idx}"]`);
            if (field) field.value = item[key];
        });
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
    const eduCopy = [...resumeState.education];
    resumeState.education = [];
    eduCopy.forEach((item) => {
        const idx = resumeState.education.length;
        resumeState.education.push(item);
        addEducation();
        
        Object.keys(item).forEach(key => {
            const field = container.querySelector(`[data-edu-field="${key}"][data-edu-index="${idx}"]`);
            if (field) field.value = item[key];
        });
    });
}

function addPublication() {
    const container = document.getElementById('publicationsContainer');
    const index = resumeState.publications.length;

    const publicationItem = {
        authors: '',
        year: '',
        title: '',
        journal: ''
    };

    resumeState.publications.push(publicationItem);

    const itemDiv = document.createElement('div');
    itemDiv.className = 'form-item';
    itemDiv.innerHTML = `
        <div class="form-item-header">
            <span>Publication ${index + 1}</span>
            <button type="button" class="remove-item-btn" onclick="removePublication(${index})">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <input type="text" class="form-input" placeholder="Authors (e.g., Smith, J., & Jones, M.)" data-pub-field="authors" data-pub-index="${index}">
        <input type="text" class="form-input" placeholder="Year" data-pub-field="year" data-pub-index="${index}">
        <input type="text" class="form-input" placeholder="Title" data-pub-field="title" data-pub-index="${index}">
        <input type="text" class="form-input" placeholder="Journal/Conference" data-pub-field="journal" data-pub-index="${index}">
    `;

    container.appendChild(itemDiv);

    
    itemDiv.querySelectorAll('[data-pub-field]').forEach(field => {
        field.addEventListener('input', debounce((e) => {
            const idx = parseInt(e.target.dataset.pubIndex);
            const fieldName = e.target.dataset.pubField;
            resumeState.publications[idx][fieldName] = e.target.value;
            updatePreview();
        }, 300));
    });
}

function removePublication(index) {
    resumeState.publications.splice(index, 1);
    rebuildPublicationsUI();
    updatePreview();
}

function rebuildPublicationsUI() {
    const container = document.getElementById('publicationsContainer');
    container.innerHTML = '';
    const pubCopy = [...resumeState.publications];
    resumeState.publications = [];
    pubCopy.forEach((item) => {
        const idx = resumeState.publications.length;
        resumeState.publications.push(item);
        addPublication();
        
        Object.keys(item).forEach(key => {
            const field = container.querySelector(`[data-pub-field="${key}"][data-pub-index="${idx}"]`);
            if (field) field.value = item[key];
        });
    });
}

function addGrant() {
    const container = document.getElementById('grantsContainer');
    const index = resumeState.grants.length;

    const grantItem = {
        title: '',
        role: '',
        year: '',
        amount: ''
    };

    resumeState.grants.push(grantItem);

    const itemDiv = document.createElement('div');
    itemDiv.className = 'form-item';
    itemDiv.innerHTML = `
        <div class="form-item-header">
            <span>Grant ${index + 1}</span>
            <button type="button" class="remove-item-btn" onclick="removeGrant(${index})">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <input type="text" class="form-input" placeholder="Grant Title" data-grant-field="title" data-grant-index="${index}">
        <input type="text" class="form-input" placeholder="Your Role (e.g., Principal Investigator)" data-grant-field="role" data-grant-index="${index}">
        <input type="text" class="form-input" placeholder="Year(s) (e.g., 2023-2025)" data-grant-field="year" data-grant-index="${index}">
        <input type="text" class="form-input" placeholder="Amount (optional)" data-grant-field="amount" data-grant-index="${index}">
    `;

    container.appendChild(itemDiv);

    
    itemDiv.querySelectorAll('[data-grant-field]').forEach(field => {
        field.addEventListener('input', debounce((e) => {
            const idx = parseInt(e.target.dataset.grantIndex);
            const fieldName = e.target.dataset.grantField;
            resumeState.grants[idx][fieldName] = e.target.value;
            updatePreview();
        }, 300));
    });
}

function removeGrant(index) {
    resumeState.grants.splice(index, 1);
    rebuildGrantsUI();
    updatePreview();
}

function rebuildGrantsUI() {
    const container = document.getElementById('grantsContainer');
    container.innerHTML = '';
    const grantCopy = [...resumeState.grants];
    resumeState.grants = [];
    grantCopy.forEach((item) => {
        const idx = resumeState.grants.length;
        resumeState.grants.push(item);
        addGrant();
        
        Object.keys(item).forEach(key => {
            const field = container.querySelector(`[data-grant-field="${key}"][data-grant-index="${idx}"]`);
            if (field) field.value = item[key];
        });
    });
}

function addTeaching() {
    const container = document.getElementById('teachingContainer');
    const index = resumeState.teaching.length;

    const teachingItem = {
        course: '',
        term: ''
    };

    resumeState.teaching.push(teachingItem);

    const itemDiv = document.createElement('div');
    itemDiv.className = 'form-item';
    itemDiv.innerHTML = `
        <div class="form-item-header">
            <span>Teaching ${index + 1}</span>
            <button type="button" class="remove-item-btn" onclick="removeTeaching(${index})">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <input type="text" class="form-input" placeholder="Course Name & Code" data-teach-field="course" data-teach-index="${index}">
        <input type="text" class="form-input" placeholder="Term (e.g., Fall 2023, Spring 2024)" data-teach-field="term" data-teach-index="${index}">
    `;

    container.appendChild(itemDiv);

    
    itemDiv.querySelectorAll('[data-teach-field]').forEach(field => {
        field.addEventListener('input', debounce((e) => {
            const idx = parseInt(e.target.dataset.teachIndex);
            const fieldName = e.target.dataset.teachField;
            resumeState.teaching[idx][fieldName] = e.target.value;
            updatePreview();
        }, 300));
    });
}

function removeTeaching(index) {
    resumeState.teaching.splice(index, 1);
    rebuildTeachingUI();
    updatePreview();
}

function rebuildTeachingUI() {
    const container = document.getElementById('teachingContainer');
    container.innerHTML = '';
    const teachCopy = [...resumeState.teaching];
    resumeState.teaching = [];
    teachCopy.forEach((item) => {
        const idx = resumeState.teaching.length;
        resumeState.teaching.push(item);
        addTeaching();
        
        Object.keys(item).forEach(key => {
            const field = container.querySelector(`[data-teach-field="${key}"][data-teach-index="${idx}"]`);
            if (field) field.value = item[key];
        });
    });
}

function loadResumeData() {
    
    if (resumeData.experience) {
        try {
            const expData = typeof resumeData.experience === 'string' ? JSON.parse(resumeData.experience) : resumeData.experience;
            resumeState.experience = [];
            expData.forEach((item) => {
                const idx = resumeState.experience.length;
                resumeState.experience.push(item);
                addExperience();
                
                Object.keys(item).forEach(key => {
                    const field = document.querySelector(`[data-exp-field="${key}"][data-exp-index="${idx}"]`);
                    if (field) field.value = item[key];
                });
            });
        } catch (e) {
            console.error('Error loading experience:', e);
        }
    }

    
    if (resumeData.education) {
        try {
            const eduData = typeof resumeData.education === 'string' ? JSON.parse(resumeData.education) : resumeData.education;
            resumeState.education = [];
            eduData.forEach((item) => {
                const idx = resumeState.education.length;
                resumeState.education.push(item);
                addEducation();
                
                Object.keys(item).forEach(key => {
                    const field = document.querySelector(`[data-edu-field="${key}"][data-edu-index="${idx}"]`);
                    if (field) field.value = item[key];
                });
            });
        } catch (e) {
            console.error('Error loading education:', e);
        }
    }

    
    if (resumeData.skills) {
        try {
            const skillsData = typeof resumeData.skills === 'string' ? resumeData.skills : JSON.stringify(resumeData.skills);
            resumeState.skills = skillsData;
            document.getElementById('skills').value = skillsData;
        } catch (e) {
            console.error('Error loading skills:', e);
        }
    }

    
    if (resumeData.researchInterests) {
        resumeState.researchInterests = resumeData.researchInterests;
        document.getElementById('researchInterests').value = resumeData.researchInterests;
    }

    
    if (resumeData.publications) {
        try {
            const pubData = typeof resumeData.publications === 'string' ? JSON.parse(resumeData.publications) : resumeData.publications;
            resumeState.publications = [];
            pubData.forEach((item) => {
                const idx = resumeState.publications.length;
                resumeState.publications.push(item);
                addPublication();
                
                Object.keys(item).forEach(key => {
                    const field = document.querySelector(`[data-pub-field="${key}"][data-pub-index="${idx}"]`);
                    if (field) field.value = item[key];
                });
            });
        } catch (e) {
            console.error('Error loading publications:', e);
        }
    }

    
    if (resumeData.grants) {
        try {
            const grantData = typeof resumeData.grants === 'string' ? JSON.parse(resumeData.grants) : resumeData.grants;
            resumeState.grants = [];
            grantData.forEach((item) => {
                const idx = resumeState.grants.length;
                resumeState.grants.push(item);
                addGrant();
                
                Object.keys(item).forEach(key => {
                    const field = document.querySelector(`[data-grant-field="${key}"][data-grant-index="${idx}"]`);
                    if (field) field.value = item[key];
                });
            });
        } catch (e) {
            console.error('Error loading grants:', e);
        }
    }

    
    if (resumeData.teaching) {
        try {
            const teachData = typeof resumeData.teaching === 'string' ? JSON.parse(resumeData.teaching) : resumeData.teaching;
            resumeState.teaching = [];
            teachData.forEach((item) => {
                const idx = resumeState.teaching.length;
                resumeState.teaching.push(item);
                addTeaching();
                
                Object.keys(item).forEach(key => {
                    const field = document.querySelector(`[data-teach-field="${key}"][data-teach-index="${idx}"]`);
                    if (field) field.value = item[key];
                });
            });
        } catch (e) {
            console.error('Error loading teaching:', e);
        }
    }

    
    if (resumeData.memberships) {
        resumeState.memberships = resumeData.memberships;
        document.getElementById('memberships').value = resumeData.memberships;
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
        formData.append('template_name', 'academic-standard');
        formData.append('personal_details', JSON.stringify(resumeState.personal_details));
        formData.append('summary_text', resumeState.summary_text);
        formData.append('experience', JSON.stringify(resumeState.experience));
        formData.append('education', JSON.stringify(resumeState.education));
        formData.append('skills', resumeState.skills);
        formData.append('researchInterests', resumeState.researchInterests);
        formData.append('publications', JSON.stringify(resumeState.publications));
        formData.append('grants', JSON.stringify(resumeState.grants));
        formData.append('teaching', JSON.stringify(resumeState.teaching));
        formData.append('memberships', resumeState.memberships);
        formData.append('status', 'draft');

        const response = await fetch(appUrl('api/save-resume.php'), {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            resumeState.id = result.resume_id;
            showNotificationModal('Resume saved successfully!', 'success');

            
            if (!resumeData.id) {
                const newUrl = `${window.location.pathname}?id=${result.resume_id}`;
                window.history.pushState({}, '', newUrl);
                resumeData.id = result.resume_id;
            }
        } else {
            showNotificationModal('Error saving resume: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Save error:', error);
        showNotificationModal('Error saving resume. Please try again.', 'error');
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
    
}

// Debounce function moved to shared/utils.js
