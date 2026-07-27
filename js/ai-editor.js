


































let resumeState = {
    id: resumeData.id || null,                           
    resume_title: resumeData.resume_title || 'AI Generated Resume',
    template_name: resumeData.template_name || null,     
    personal_details: resumeData.personal_details || {}, 
    summary_text: resumeData.summary_text || '',         
    experience: [],                                      
    education: [],                                       
    skills: '',                                          
    certifications: [],                                  
    languages: '',                                       
    status: 'draft',                                     
    
    researchInterests: '',
    publications: [],
    grants: [],
    teaching: [],
    memberships: ''
};




let conversationHistory = [];  
                                
let previewIframe = null;      
let currentZoom = 100;         
let isProcessing = false;      


document.addEventListener('DOMContentLoaded', () => {
    previewIframe = document.getElementById('resumePreview');

    
    if (!resumeState.template_name) {
        showTemplateSelectionModal();
    } else {
        initializeEditor();
    }

    setupEventListeners();
});


function showTemplateSelectionModal() {
    const modal = document.getElementById('templateSelectionModal');
    if (modal) {
        modal.classList.add('show');

        
        const selectButtons = modal.querySelectorAll('.select-template-btn');
        selectButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const templateName = this.dataset.template;
                selectTemplate(templateName);
            });
        });
    }
}

function selectTemplate(templateName) {
    resumeState.template_name = templateName;

    
    const modal = document.getElementById('templateSelectionModal');
    if (modal) {
        modal.classList.remove('show');
    }

    
    initializeEditor();
}

function initializeEditor() {
    
    const indicator = document.getElementById('templateIndicator');
    const templateDisplay = document.getElementById('templateNameDisplay');
    if (indicator && templateDisplay) {
        indicator.style.display = 'flex';
        const templateNames = {
            'modern': 'Modern',
            'professional': 'Professional',
            'academic-standard': 'Academic Standard'
        };
        templateDisplay.textContent = templateNames[resumeState.template_name] || resumeState.template_name;
    }

    
    loadTemplate();

    
    if (resumeData.experience) {
        try {
            resumeState.experience = typeof resumeData.experience === 'string' ?
                JSON.parse(resumeData.experience) : resumeData.experience;
        } catch(e) {
            console.error('Error loading experience:', e);
        }
    }

    if (resumeData.education) {
        try {
            resumeState.education = typeof resumeData.education === 'string' ?
                JSON.parse(resumeData.education) : resumeData.education;
        } catch(e) {
            console.error('Error loading education:', e);
        }
    }

    if (resumeData.skills) {
        resumeState.skills = resumeData.skills;
    }

    
    setTimeout(() => updatePreview(), 500);
}

function loadTemplate() {
    if (!previewIframe || !resumeState.template_name) return;

    previewIframe.src = `templates/${resumeState.template_name}.html?v=${Date.now()}`;

    previewIframe.onload = () => {
        updatePreview();
    };
}


function setupEventListeners() {
    
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');

    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (message && !isProcessing) {
                await handleUserMessage(message);
                chatInput.value = '';
                chatInput.style.height = 'auto';
            }
        });
    }

    
    if (chatInput) {
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    
    const quickActionBtns = document.querySelectorAll('.quick-action-btn');
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            handleQuickAction(action);
        });
    });

    
    const newChatBtn = document.getElementById('newChatBtn');
    if (newChatBtn) {
        newChatBtn.addEventListener('click', resetConversation);
    }

    
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    const refreshBtn = document.getElementById('refreshBtn');

    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', () => adjustZoom(10));
    }

    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', () => adjustZoom(-10));
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => updatePreview());
    }

    
    const saveBtn = document.getElementById('saveResumeBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveResume);
    }

    
    const downloadBtn = document.getElementById('downloadBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', downloadPDF);
    }
}


























async function handleUserMessage(message) {
    
    if (isProcessing) return;

    
    addMessageToChat('user', message);

    
    conversationHistory.push({
        role: 'user',
        content: message
    });

    
    isProcessing = true;
    const sendBtn = document.getElementById('sendBtn');
    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    try {
        
        
        
        
        
        
        
        
        const response = await fetch('api/ai-conversation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                conversationHistory: conversationHistory,
                resumeState: resumeState,
                templateName: resumeState.template_name
            })
        });

        const data = await response.json();

        if (data.success) {
            
            addMessageToChat('assistant', data.response);

            
            conversationHistory.push({
                role: 'assistant',
                content: data.response
            });

            
            
            
            
            
            
            
            
            
            
            if (data.updates && Object.keys(data.updates).length > 0) {
                console.log('Applying updates:', data.updates);
                applyUpdates(data.updates);     
                updatePreview();                 
                console.log('Updated resume state:', resumeState);
            }
        } else {
            addMessageToChat('assistant', 'Sorry, I encountered an error. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        addMessageToChat('assistant', 'Sorry, I encountered an error. Please try again.');
    } finally {
        
        isProcessing = false;
        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
        }
    }
}


function addMessageToChat(role, content) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${role === 'user' ? 'user-message' : 'assistant-message'}`;

    const avatarIcon = role === 'user' ? 'fa-user' : 'fa-robot';

    messageDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fa-solid ${avatarIcon}"></i>
        </div>
        <div class="message-content">
            <p>${content}</p>
        </div>
    `;

    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}










































function applyUpdates(updates) {
    console.log('Applying updates to resume state...');

    
    
    
    
    
    if (updates.personal_details) {
        Object.assign(resumeState.personal_details, updates.personal_details);
        console.log('Updated personal_details:', resumeState.personal_details);
    }

    
    
    
    if (updates.summary_text || updates.summary) {
        resumeState.summary_text = updates.summary_text || updates.summary;
        console.log('Updated summary:', resumeState.summary_text);
    }

    
    
    
    
    
    if (updates.experience) {
        if (Array.isArray(updates.experience)) {
            resumeState.experience = updates.experience;
            console.log('Updated experience:', resumeState.experience);
        }
    }

    
    
    
    if (updates.education) {
        if (Array.isArray(updates.education)) {
            resumeState.education = updates.education;
            console.log('Updated education:', resumeState.education);
        }
    }

    
    
    
    
    if (updates.skills) {
        resumeState.skills = updates.skills;
        console.log('Updated skills:', resumeState.skills);
    }

    
    
    
    if (updates.certifications) {
        resumeState.certifications = updates.certifications;
    }

    if (updates.languages) {
        resumeState.languages = updates.languages;
    }

    
    
    
    if (updates.researchInterests) {
        resumeState.researchInterests = updates.researchInterests;
    }

    if (updates.publications) {
        resumeState.publications = updates.publications;
    }

    if (updates.grants) {
        resumeState.grants = updates.grants;
    }

    if (updates.teaching) {
        resumeState.teaching = updates.teaching;
    }

    if (updates.memberships) {
        resumeState.memberships = updates.memberships;
    }
}


function handleQuickAction(action) {
    const chatInput = document.getElementById('chatInput');
    if (!chatInput) return;

    const prompts = {
        'add-experience': 'I\'d like to add my work experience. I worked as [Job Title] at [Company Name] from [Start Date] to [End Date]. My responsibilities included...',
        'add-education': 'I\'d like to add my education. I have a [Degree] in [Field of Study] from [University Name], graduated in [Year].',
        'add-skills': 'Here are my key skills: [List your technical skills, soft skills, certifications, etc.]'
    };

    chatInput.value = prompts[action] || '';
    chatInput.focus();
    chatInput.style.height = 'auto';
    chatInput.style.height = (chatInput.scrollHeight) + 'px';
}


function resetConversation() {
    if (confirm('Are you sure you want to start over? This will clear the current conversation but keep your resume data.')) {
        conversationHistory = [];
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.innerHTML = `
                <div class="message assistant-message">
                    <div class="message-avatar">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <p>Hi! I'm your AI resume assistant powered by Google Gemini. I'll help you build an ATS-optimized resume through conversation.</p>
                        <p>Let's start with some basics. <strong>What's your full name and the job title you're targeting?</strong></p>
                    </div>
                </div>
            `;
        }
    }
}


function updatePreview() {
    const iframeDoc = previewIframe.contentDocument || previewIframe.contentWindow.document;
    if (!iframeDoc) return;

    
    updatePersonalDetails(iframeDoc);
    updateSummary(iframeDoc);
    updateExperienceList(iframeDoc);
    updateEducationList(iframeDoc);

    
    if (resumeState.template_name === 'academic-standard') {
        updateAcademicFields(iframeDoc);
    } else {
        updateSkills(iframeDoc);
        updateCertifications(iframeDoc);
        updateLanguages(iframeDoc);
    }
}


function updatePersonalDetails(iframeDoc) {
    
    const fieldMapping = {
        'name': 'fullName',
        'fullName': 'fullName',
        'title': 'professionalTitle',
        'professionalTitle': 'professionalTitle',
        'email': 'email',
        'phone': 'phone',
        'location': 'location',
        'linkedin': 'linkedin'
    };

    Object.keys(fieldMapping).forEach(dataField => {
        const element = iframeDoc.querySelector(`[data-field="${dataField}"]`);
        if (element) {
            const stateField = fieldMapping[dataField];
            const value = resumeState.personal_details[stateField] || resumeState.personal_details[dataField] || '';

            if (value) {
                element.textContent = value;
                element.style.display = '';
            } else {
                element.textContent = '';
            }
        }
    });
}


function updateSummary(iframeDoc) {
    const summaryElement = iframeDoc.querySelector('[data-field="summary"]');
    const summarySection = iframeDoc.querySelector('[data-section="summary"]');

    if (summaryElement && summarySection) {
        if (resumeState.summary_text && resumeState.summary_text.trim()) {
            summarySection.style.display = 'block';
            summaryElement.textContent = resumeState.summary_text;
        } else {
            summarySection.style.display = 'none';
        }
    }
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
            entry.className = 'entry';
            entry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title-line">
                        <div class="entry-title">${escapeHtml(item.jobTitle || '')}</div>
                        <div class="entry-date">${escapeHtml(item.dates || '')}</div>
                    </div>
                    <div class="entry-company">${escapeHtml(item.company || '')}</div>
                </div>
                ${item.description ? `<div class="entry-description">${escapeHtml(item.description)}</div>` : ''}
            `;
            container.appendChild(entry);
        });
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
            entry.className = 'entry';
            entry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title-line">
                        <div class="entry-title">${escapeHtml(item.degree || '')}</div>
                        <div class="entry-date">${escapeHtml(item.dates || '')}</div>
                    </div>
                    <div class="entry-company">${escapeHtml(item.institution || '')}</div>
                </div>
            `;
            container.appendChild(entry);
        });
    }
}


function updateSkills(iframeDoc) {
    const skillsElement = iframeDoc.querySelector('[data-field="skills"]');
    const skillsSection = iframeDoc.querySelector('[data-section="skills"]');

    if (skillsElement && skillsSection) {
        if (resumeState.skills && resumeState.skills.trim()) {
            skillsSection.style.display = 'block';
            skillsElement.textContent = resumeState.skills;
        } else {
            skillsSection.style.display = 'none';
        }
    }
}


function updateCertifications(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="certifications-list"]');
    const section = iframeDoc.querySelector('[data-section="certifications"]');

    if (container && section && resumeState.certifications && resumeState.certifications.length > 0) {
        section.style.display = 'block';
        container.innerHTML = '';
        resumeState.certifications.forEach(cert => {
            const entry = iframeDoc.createElement('div');
            entry.className = 'entry';
            entry.innerHTML = `<div class="entry-title">${escapeHtml(cert.name || '')}</div>`;
            container.appendChild(entry);
        });
    } else if (section) {
        section.style.display = 'none';
    }
}


function updateLanguages(iframeDoc) {
    const element = iframeDoc.querySelector('[data-field="languages"]');
    const section = iframeDoc.querySelector('[data-section="languages"]');

    if (element && section) {
        if (resumeState.languages && resumeState.languages.trim()) {
            section.style.display = 'block';
            element.textContent = resumeState.languages;
        } else {
            section.style.display = 'none';
        }
    }
}


function updateAcademicFields(iframeDoc) {
    
    const riElement = iframeDoc.querySelector('[data-field="research-interests"]');
    const riSection = iframeDoc.querySelector('[data-section="research-interests"]');
    if (riElement && riSection) {
        if (resumeState.researchInterests && resumeState.researchInterests.trim()) {
            riSection.style.display = 'block';
            riElement.textContent = resumeState.researchInterests;
        } else {
            riSection.style.display = 'none';
        }
    }

    
    const skillsElement = iframeDoc.querySelector('[data-field="skills"]');
    const skillsSection = iframeDoc.querySelector('[data-section="skills"]');
    if (skillsElement && skillsSection) {
        if (resumeState.skills && resumeState.skills.trim()) {
            skillsSection.style.display = 'block';
            skillsElement.textContent = resumeState.skills;
        } else {
            skillsSection.style.display = 'none';
        }
    }

    
    const membElement = iframeDoc.querySelector('[data-field="memberships"]');
    const membSection = iframeDoc.querySelector('[data-section="memberships"]');
    if (membElement && membSection) {
        if (resumeState.memberships && resumeState.memberships.trim()) {
            membSection.style.display = 'block';
            membElement.textContent = resumeState.memberships;
        } else {
            membSection.style.display = 'none';
        }
    }
}


function adjustZoom(delta) {
    currentZoom += delta;
    currentZoom = Math.max(50, Math.min(200, currentZoom));

    const zoomDisplay = document.getElementById('zoomLevel');
    if (zoomDisplay) {
        zoomDisplay.textContent = currentZoom + '%';
    }

    if (previewIframe) {
        previewIframe.style.transform = `scale(${currentZoom / 100})`;
        previewIframe.style.transformOrigin = 'top center';
    }
}


async function saveResume() {
    const saveBtn = document.getElementById('saveResumeBtn');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';
    saveBtn.disabled = true;

    try {
        const response = await fetch('api/save-resume.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(resumeState)
        });

        const data = await response.json();

        if (data.success) {
            resumeState.id = data.resume_id;
            showNotificationModal('Resume saved successfully!', 'success');
        } else {
            showNotificationModal('Error saving resume: ' + (data.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Save error:', error);
        showNotificationModal('Error saving resume. Please try again.', 'error');
    } finally {
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    }
}


function downloadPDF() {
    try {
        const iframeWindow = previewIframe.contentWindow;
        iframeWindow.print();
    } catch (error) {
        console.error('Print error:', error);
        showNotificationModal('Error opening print dialog. Please try again.', 'error');
    }
}
