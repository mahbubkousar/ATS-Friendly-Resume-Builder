/*
 * ATS CONVERTER JAVASCRIPT - TEMPORARILY DISABLED
 * This file is commented out as the ATS Converter feature is disabled.
 * To re-enable: Remove the comment blocks at the beginning and end of this file.
 */

/* COMMENTED OUT - All ATS Converter functionality





let resumeFile = null;              
let extractedData = null;           
let selectedTemplate = null;        
let convertedResumeState = null;    


const resumeUploadArea = document.getElementById('resumeUploadArea');
const resumeFileInput = document.getElementById('resumeFile');
const resumeFileInfo = document.getElementById('resumeFileInfo');
const jobDescriptionTextarea = document.getElementById('jobDescription');
const startConvertBtn = document.getElementById('startConvertBtn');

const uploadSection = document.getElementById('uploadSection');
const processingSection = document.getElementById('processingSection');
const previewSection = document.getElementById('previewSection');


document.addEventListener('DOMContentLoaded', () => {
    setupFileUpload();
    setupFormValidation();
    setupConversionFlow();
});


function setupFileUpload() {
    
    resumeUploadArea.addEventListener('click', () => {
        resumeFileInput.click();
    });

    
    resumeFileInput.addEventListener('change', (e) => {
        handleFileSelect(e.target.files[0]);
    });

    
    resumeUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        resumeUploadArea.style.borderColor = 'var(--primary)';
        resumeUploadArea.style.background = '#f0f0ff';
    });

    resumeUploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        resumeUploadArea.style.borderColor = '#d0d0d0';
        resumeUploadArea.style.background = '#f8f9fa';
    });

    resumeUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        resumeUploadArea.style.borderColor = '#d0d0d0';
        resumeUploadArea.style.background = '#f8f9fa';

        const file = e.dataTransfer.files[0];
        handleFileSelect(file);
    });
}

function handleFileSelect(file) {
    if (!file) return;

    
    const allowedTypes = ['application/pdf', 'application/msword',
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                         'text/plain'];

    if (!allowedTypes.includes(file.type)) {
        showNotificationModal('Please upload a PDF, DOCX, DOC, or TXT file.', 'error');
        return;
    }

    
    if (file.size > 10 * 1024 * 1024) {
        showNotificationModal('File size must be less than 10MB.', 'error');
        return;
    }

    resumeFile = file;

    
    resumeUploadArea.style.display = 'none';
    resumeFileInfo.style.display = 'flex';
    resumeFileInfo.querySelector('.file-name').textContent = file.name;

    validateForm();
}

function removeResumeFile() {
    resumeFile = null;
    resumeFileInput.value = '';
    resumeUploadArea.style.display = 'block';
    resumeFileInfo.style.display = 'none';
    validateForm();
}


function setupFormValidation() {
    jobDescriptionTextarea.addEventListener('input', validateForm);
}

function validateForm() {
    const hasFile = resumeFile !== null;
    const hasJobDesc = jobDescriptionTextarea.value.trim().length > 50;

    startConvertBtn.disabled = !(hasFile && hasJobDesc);
}


function setupConversionFlow() {
    startConvertBtn.addEventListener('click', startConversion);
}

















async function startConversion() {
    
    uploadSection.style.display = 'none';
    processingSection.style.display = 'block';

    try {
        
        document.getElementById('activityLog').innerHTML = '';

        
        
        
        
        
        
        
        
        
        
        await executeStep(1, async () => {
            addActivityLog('📄 Reading your resume file...');
            await new Promise(resolve => setTimeout(resolve, 300));

            addActivityLog('🤖 Using AI to extract information from your resume...');
            
            extractedData = await extractResumeData(resumeFile);

            
            const name = extractedData.personal_details?.fullName || 'Unknown';
            const expCount = extractedData.experience?.length || 0;
            const eduCount = extractedData.education?.length || 0;

            addActivityLog(`✅ Successfully extracted data for: ${name}`, 'success');
            addActivityLog(`📊 Found ${expCount} work experiences and ${eduCount} education entries`, 'info');

            updateStepStatus(1, `Extracted: ${name}`);
        });

        
        
        
        
        
        
        
        
        let jobAnalysis;
        await executeStep(2, async () => {
            addActivityLog('📋 Analyzing job description...');
            await new Promise(resolve => setTimeout(resolve, 300));

            addActivityLog('🔍 Identifying key skills and requirements...');
            
            jobAnalysis = await analyzeJobDescription(jobDescriptionTextarea.value, extractedData);

            
            const jobType = jobAnalysis.jobType || 'Position';
            const skillsCount = jobAnalysis.keySkills?.length || 0;

            addActivityLog(`✅ Job analyzed: ${jobType}`, 'success');
            addActivityLog(`🎯 Identified ${skillsCount} key skills required for this role`, 'info');

            
            if (jobAnalysis.keySkills && jobAnalysis.keySkills.length > 0) {
                const topSkills = jobAnalysis.keySkills.slice(0, 3).join(', ');
                addActivityLog(`💡 Top skills: ${topSkills}`, 'info');
            }

            updateStepStatus(2, `${jobType}`);
        });

        
        
        
        
        
        
        
        
        
        await executeStep(3, async () => {
            addActivityLog('🎨 Evaluating best template for your role...');
            await new Promise(resolve => setTimeout(resolve, 300));

            
            selectedTemplate = jobAnalysis.recommendedTemplate || 'modern';

            const templateNames = {
                'modern': 'Modern Professional',
                'professional': 'Professional',
                'academic-standard': 'Academic Standard',
                'executive': 'Executive',
                'technical': 'Technical',
                'creative': 'Creative'
            };

            
            addActivityLog(`✅ Selected: ${templateNames[selectedTemplate] || selectedTemplate}`, 'success');
            addActivityLog(`💡 Reason: ${jobAnalysis.reasoning || 'Best fit for your profile'}`, 'info');

            updateStepStatus(3, `Selected: ${templateNames[selectedTemplate] || selectedTemplate}`);
        });

        
        
        
        
        
        
        
        
        await executeStep(4, async () => {
            addActivityLog('⚡ Optimizing content for ATS systems...');
            await new Promise(resolve => setTimeout(resolve, 300));

            addActivityLog('🔧 Incorporating keywords and tailoring content...');
            
            convertedResumeState = await optimizeResume(extractedData, jobAnalysis, selectedTemplate);

            addActivityLog('✅ Resume optimization complete!', 'success');
            addActivityLog('📝 Your ATS-optimized resume is ready to preview', 'info');

            updateStepStatus(4, 'Optimization complete');
        });

        
        
        
        
        
        const missingFields = checkMissingFields(convertedResumeState);

        if (missingFields.length > 0) {
            
            await showMissingInfoModal(missingFields);
        }

        
        showPreview();

    } catch (error) {
        console.error('Conversion error:', error);
        showNotificationModal('Conversion failed: ' + error.message, 'error');

        
        setTimeout(() => {
            processingSection.style.display = 'none';
            uploadSection.style.display = 'block';
        }, 2000);
    }
}
















async function executeStep(stepNumber, asyncFunction) {
    const stepElement = document.getElementById(`step${stepNumber}`);

    
    stepElement.classList.add('active');
    stepElement.querySelector('.step-status').textContent = 'Processing...';

    try {
        
        await asyncFunction();

        
        stepElement.classList.remove('active');
        stepElement.classList.add('completed');
        stepElement.querySelector('.step-status').textContent = 'Completed';

        
        await new Promise(resolve => setTimeout(resolve, 500));

    } catch (error) {
        
        stepElement.classList.remove('active');
        stepElement.querySelector('.step-status').textContent = 'Failed';
        
        throw error;
    }
}


function updateStepStatus(stepNumber, message) {
    const stepElement = document.getElementById(`step${stepNumber}`);
    const statusElement = stepElement.querySelector('.step-status');
    statusElement.textContent = message;
}


function addActivityLog(message, type = 'active') {
    const activityLog = document.getElementById('activityLog');

    
    const previousActive = activityLog.querySelector('.activity-item.active');
    if (previousActive && type !== 'info') {
        previousActive.classList.remove('active');
        previousActive.classList.add('completed');
        const icon = previousActive.querySelector('i');
        icon.className = 'fa-solid fa-check-circle';
    }

    const activityItem = document.createElement('div');
    activityItem.className = `activity-item ${type}`;

    let icon = 'fa-circle-notch fa-spin';
    if (type === 'success') {
        icon = 'fa-check-circle';
    } else if (type === 'error') {
        icon = 'fa-exclamation-circle';
    } else if (type === 'info') {
        icon = 'fa-info-circle';
    }

    activityItem.innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <span>${message}</span>
    `;

    activityLog.appendChild(activityItem);

    
    activityLog.scrollTop = activityLog.scrollHeight;

    return activityItem;
}


async function extractResumeData(file) {
    const formData = new FormData();
    formData.append('resume', file);

    const response = await fetch('api/extract-resume.php', {
        method: 'POST',
        body: formData
    });

    console.log('Response status:', response.status);
    console.log('Response headers:', response.headers);

    const text = await response.text();
    console.log('Raw response:', text);

    if (!response.ok) {
        console.error('API Response (not OK):', text);
        throw new Error(`API request failed with status: ${response.status}. Response: ${text.substring(0, 200)}`);
    }

    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error('Failed to parse JSON:', text);
        throw new Error('Invalid JSON response from server');
    }

    if (!data.success) {
        console.error('API Error:', data);
        throw new Error(data.error || 'Failed to extract resume data');
    }

    return data.data;
}

async function analyzeJobDescription(jobDesc, resumeData) {
    const response = await fetch('api/analyze-job.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            jobDescription: jobDesc,
            resumeData: resumeData
        })
    });

    const data = await response.json();

    if (!data.success) {
        throw new Error(data.error || 'Failed to analyze job description');
    }

    return data.analysis;
}

async function optimizeResume(resumeData, jobAnalysis, template) {
    const response = await fetch('api/optimize-resume.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            resumeData: resumeData,
            jobAnalysis: jobAnalysis,
            template: template
        })
    });

    const data = await response.json();

    if (!data.success) {
        throw new Error(data.error || 'Failed to optimize resume');
    }

    return data.resumeState;
}


function checkMissingFields(resumeState) {
    const missing = [];

    if (!resumeState.personal_details.email) {
        missing.push({ field: 'email', label: 'Email Address', type: 'email' });
    }

    if (!resumeState.personal_details.phone) {
        missing.push({ field: 'phone', label: 'Phone Number', type: 'tel' });
    }

    if (!resumeState.personal_details.location) {
        missing.push({ field: 'location', label: 'Location', type: 'text' });
    }

    return missing;
}

function showMissingInfoModal(missingFields) {
    return new Promise((resolve) => {
        const modal = document.getElementById('missingInfoModal');
        const fieldsContainer = document.getElementById('missingInfoFields');

        
        fieldsContainer.innerHTML = '';
        missingFields.forEach(field => {
            const fieldDiv = document.createElement('div');
            fieldDiv.className = 'missing-field';
            fieldDiv.innerHTML = `
                <label for="missing_${field.field}">${field.label}</label>
                <input
                    type="${field.type}"
                    id="missing_${field.field}"
                    data-field="${field.field}"
                    placeholder="Enter ${field.label.toLowerCase()}"
                >
            `;
            fieldsContainer.appendChild(fieldDiv);
        });

        modal.style.display = 'flex';

        
        window.missingInfoResolve = resolve;
    });
}

function skipMissingInfo() {
    const modal = document.getElementById('missingInfoModal');
    modal.style.display = 'none';

    if (window.missingInfoResolve) {
        window.missingInfoResolve();
    }
}

function submitMissingInfo() {
    const modal = document.getElementById('missingInfoModal');
    const inputs = modal.querySelectorAll('input[data-field]');

    inputs.forEach(input => {
        const field = input.dataset.field;
        const value = input.value.trim();

        if (value) {
            convertedResumeState.personal_details[field] = value;
        }
    });

    modal.style.display = 'none';

    if (window.missingInfoResolve) {
        window.missingInfoResolve();
    }
}


function showPreview() {
    processingSection.style.display = 'none';
    previewSection.style.display = 'block';

    
    const templateNames = {
        'modern': 'Modern Professional',
        'professional': 'Professional',
        'academic-standard': 'Academic Standard',
        'classic': 'Classic',
        'creative': 'Creative',
        'executive': 'Executive',
        'technical': 'Technical',
        'teaching-faculty': 'Teaching Faculty',
        'research-scientist': 'Research Scientist'
    };

    document.getElementById('selectedTemplateName').textContent =
        templateNames[selectedTemplate] || 'Modern Professional';

    
    const previewIframe = document.getElementById('convertedPreview');
    previewIframe.src = `templates/${selectedTemplate}.html?v=${Date.now()}`;

    previewIframe.onload = () => {
        updatePreviewContent(previewIframe, convertedResumeState);
    };

    
    document.getElementById('saveConvertedBtn').addEventListener('click', saveConvertedResume);
}

function updatePreviewContent(iframe, resumeState) {
    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

    console.log('Updating preview with resume state:', resumeState);
    console.log('Selected template:', selectedTemplate);

    
    updatePersonalDetails(iframeDoc, resumeState.personal_details);
    updateSummary(iframeDoc, resumeState.summary_text);
    updateExperience(iframeDoc, resumeState.experience);
    updateEducation(iframeDoc, resumeState.education);

    if (selectedTemplate === 'academic-standard') {
        updateAcademicFields(iframeDoc, resumeState);
    } else {
        updateSkills(iframeDoc, resumeState.skills);
        updateCertifications(iframeDoc, resumeState.certifications);
        updateLanguages(iframeDoc, resumeState.languages);
    }

    console.log('Preview updated successfully');
}

function updatePersonalDetails(iframeDoc, personalDetails) {
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
            const value = personalDetails[stateField] || personalDetails[dataField] || '';
            if (value) {
                element.textContent = value;
            }
        }
    });
}

function updateSummary(iframeDoc, summaryText) {
    const summaryElement = iframeDoc.querySelector('[data-field="summary"]');
    const summarySection = iframeDoc.querySelector('[data-section="summary"]');

    if (summaryElement && summarySection && summaryText) {
        summarySection.style.display = 'block';
        summaryElement.textContent = summaryText;
    }
}

function updateExperience(iframeDoc, experience) {
    const container = iframeDoc.querySelector('[data-field="experience-list"]');
    if (!container || !experience) return;

    container.innerHTML = '';
    experience.forEach(item => {
        const entry = iframeDoc.createElement('div');
        entry.className = 'entry';
        entry.innerHTML = `
            <div class="entry-header">
                <div class="entry-title-line">
                    <div class="entry-title">${item.jobTitle || ''}</div>
                    <div class="entry-date">${item.dates || ''}</div>
                </div>
                <div class="entry-company">${item.company || ''}</div>
            </div>
            ${item.description ? `<div class="entry-description">${item.description}</div>` : ''}
        `;
        container.appendChild(entry);
    });
}

function updateEducation(iframeDoc, education) {
    const container = iframeDoc.querySelector('[data-field="education-list"]');
    if (!container || !education) return;

    container.innerHTML = '';
    education.forEach(item => {
        const entry = iframeDoc.createElement('div');
        entry.className = 'entry';
        entry.innerHTML = `
            <div class="entry-header">
                <div class="entry-title-line">
                    <div class="entry-title">${item.degree || ''}</div>
                    <div class="entry-date">${item.year || ''}</div>
                </div>
                <div class="entry-company">${item.institution || ''}</div>
            </div>
            ${item.details ? `<div class="entry-description">${item.details}</div>` : ''}
        `;
        container.appendChild(entry);
    });
}

function updateSkills(iframeDoc, skills) {
    const skillsElement = iframeDoc.querySelector('[data-field="skills"]');
    if (skillsElement && skills) {
        skillsElement.textContent = skills;
    }
}

function updateCertifications(iframeDoc, certifications) {
    const container = iframeDoc.querySelector('[data-field="certifications-list"]');
    if (!container || !certifications) return;

    container.innerHTML = '';
    certifications.forEach(cert => {
        const entry = iframeDoc.createElement('div');
        entry.className = 'entry';
        entry.innerHTML = `
            <div class="entry-header">
                <div class="entry-title-line">
                    <div class="entry-title">${cert.name || ''}</div>
                    <div class="entry-date">${cert.year || ''}</div>
                </div>
                <div class="entry-company">${cert.issuer || ''}</div>
            </div>
        `;
        container.appendChild(entry);
    });
}

function updateLanguages(iframeDoc, languages) {
    const languagesElement = iframeDoc.querySelector('[data-field="languages"]');
    if (languagesElement && languages) {
        languagesElement.textContent = languages;
    }
}

function updateAcademicFields(iframeDoc, resumeState) {
    
    const researchElement = iframeDoc.querySelector('[data-field="research-interests"]');
    if (researchElement && resumeState.researchInterests) {
        researchElement.textContent = resumeState.researchInterests;
    }

    
    const pubContainer = iframeDoc.querySelector('[data-field="publications-list"]');
    if (pubContainer && resumeState.publications) {
        pubContainer.innerHTML = '';
        resumeState.publications.forEach(pub => {
            const entry = iframeDoc.createElement('div');
            entry.className = 'entry';
            entry.innerHTML = `<p>${pub}</p>`;
            pubContainer.appendChild(entry);
        });
    }
}


async function saveConvertedResume() {
    const resumeName = document.getElementById('resumeName').value.trim();

    if (!resumeName) {
        showNotificationModal('Please enter a name for your resume.', 'error');
        return;
    }

    const saveBtn = document.getElementById('saveConvertedBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    try {
        
        const saveData = {
            resume_title: resumeName,
            template_name: selectedTemplate,
            personal_details: convertedResumeState.personal_details || {},
            summary_text: convertedResumeState.summary_text || '',
            status: 'published'  
        };

        
        if (selectedTemplate === 'academic-standard') {
            saveData.researchInterests = convertedResumeState.researchInterests || '';
            saveData.publications = convertedResumeState.publications || [];
            saveData.grants = convertedResumeState.grants || [];
            saveData.teaching = convertedResumeState.teaching || [];
            saveData.memberships = convertedResumeState.memberships || '';
            saveData.experience = convertedResumeState.experience || [];
            saveData.education = convertedResumeState.education || [];
        } else {
            saveData.experience = convertedResumeState.experience || [];
            saveData.education = convertedResumeState.education || [];
            saveData.skills = convertedResumeState.skills || '';
            saveData.certifications = convertedResumeState.certifications || [];
            saveData.languages = convertedResumeState.languages || '';
        }

        console.log('Saving resume data:', saveData);

        const response = await fetch('api/save-resume.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(saveData)
        });

        console.log('Save response status:', response.status);
        const responseText = await response.text();
        console.log('Save response text:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse response:', responseText);
            throw new Error('Invalid server response');
        }

        console.log('Save response data:', data);

        if (data.success) {
            showNotificationModal('Resume saved successfully!', 'success');

            
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2000);
        } else {
            throw new Error(data.message || data.error || 'Failed to save resume');
        }

    } catch (error) {
        console.error('Save error:', error);
        showNotificationModal('Failed to save resume: ' + error.message, 'error');

        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save to Dashboard';
    }
}


function startOver() {
    if (confirm('Are you sure you want to start over? Your current conversion will be lost.')) {
        
        resumeFile = null;
        extractedData = null;
        selectedTemplate = null;
        convertedResumeState = null;

        
        removeResumeFile();
        jobDescriptionTextarea.value = '';

        
        document.querySelectorAll('.progress-step').forEach(step => {
            step.classList.remove('active', 'completed');
            step.querySelector('.step-status').textContent = 'Waiting...';
        });

        
        previewSection.style.display = 'none';
        processingSection.style.display = 'none';
        uploadSection.style.display = 'block';
    }
}
END OF COMMENTED OUT CONTENT */
