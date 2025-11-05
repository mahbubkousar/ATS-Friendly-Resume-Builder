// Resume Editor JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Resume data object
    let resumeData = {
        template: 'modern_professional',
        personal: {
            fullName: '',
            email: '',
            phone: '',
            location: '',
            summary: ''
        },
        experience: [],
        education: [],
        skills: []
    };

    // Load saved data from localStorage if available
    const savedData = ResumeSync.Storage.get('currentResume');
    if (savedData) {
        resumeData = savedData;
        populateForm(resumeData);
    }

    // Template selector
    const templateSelect = document.getElementById('templateSelect');
    if (templateSelect) {
        templateSelect.value = resumeData.template || 'modern_professional';
        templateSelect.addEventListener('change', function(e) {
            resumeData.template = e.target.value;
            updatePreview();
            autoSave();
            ResumeSync.showToast('Template changed!', 'success');
        });
    }

    // Personal details inputs
    const personalInputs = {
        fullName: document.getElementById('fullName'),
        email: document.getElementById('email'),
        phone: document.getElementById('phone'),
        location: document.getElementById('location'),
        summary: document.getElementById('summary')
    };

    // Add event listeners for personal details
    Object.keys(personalInputs).forEach(key => {
        if (personalInputs[key]) {
            personalInputs[key].addEventListener('input', ResumeSync.debounce(function(e) {
                resumeData.personal[key] = e.target.value;
                updatePreview();
                autoSave();
            }, 300));
        }
    });

    // Skills management
    const skillInput = document.getElementById('skillInput');
    const addSkillBtn = document.getElementById('addSkillBtn');
    const skillsContainer = document.getElementById('skillsContainer');

    if (addSkillBtn) {
        addSkillBtn.addEventListener('click', addSkill);
    }

    if (skillInput) {
        skillInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSkill();
            }
        });
    }

    function addSkill() {
        const skill = skillInput.value.trim();
        if (skill && !resumeData.skills.includes(skill)) {
            resumeData.skills.push(skill);
            renderSkills();
            updatePreview();
            autoSave();
            skillInput.value = '';
            ResumeSync.showToast('Skill added!', 'success');
        }
    }

    function removeSkill(skill) {
        resumeData.skills = resumeData.skills.filter(s => s !== skill);
        renderSkills();
        updatePreview();
        autoSave();
    }

    function renderSkills() {
        if (!skillsContainer) return;

        if (resumeData.skills.length === 0) {
            skillsContainer.innerHTML = '<p class="text-gray-500 text-sm italic">No skills added yet</p>';
            return;
        }

        skillsContainer.innerHTML = resumeData.skills.map(skill => `
            <div class="skill-tag">
                ${skill}
                <span class="remove-skill" onclick="removeSkillByName('${skill}')">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        `).join('');
    }

    // Make removeSkill available globally for onclick
    window.removeSkillByName = removeSkill;

    // Experience management
    const addExperienceBtn = document.getElementById('addExperienceBtn');
    if (addExperienceBtn) {
        addExperienceBtn.addEventListener('click', function() {
            const experienceEntry = {
                jobTitle: '',
                company: '',
                startDate: '',
                endDate: '',
                description: ''
            };
            resumeData.experience.push(experienceEntry);
            renderExperienceEntry(experienceEntry, resumeData.experience.length - 1);
            updatePreview();
            ResumeSync.showToast('Experience section added', 'success');
        });
    }

    function renderExperienceEntry(entry, index) {
        const container = document.getElementById('experienceContainer');
        if (!container) return;

        const entryDiv = document.createElement('div');
        entryDiv.className = 'experience-entry border border-gray-200 rounded-lg p-4 bg-gray-50';
        entryDiv.innerHTML = `
            <div class="space-y-3">
                <input type="text" placeholder="Job Title" value="${entry.jobTitle || ''}"
                       class="experience-job w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       data-index="${index}">
                <input type="text" placeholder="Company Name" value="${entry.company || ''}"
                       class="experience-company w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       data-index="${index}">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" placeholder="Start Date" value="${entry.startDate || ''}"
                           class="experience-start w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                           data-index="${index}">
                    <input type="text" placeholder="End Date" value="${entry.endDate || ''}"
                           class="experience-end w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                           data-index="${index}">
                </div>
                <textarea rows="3" placeholder="Job description and achievements..."
                          class="experience-desc w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                          data-index="${index}">${entry.description || ''}</textarea>
                <button class="text-red-600 text-sm hover:text-red-700 remove-experience" data-index="${index}">
                    <i class="fas fa-trash mr-1"></i>Remove
                </button>
            </div>
        `;

        container.appendChild(entryDiv);

        // Add event listeners
        entryDiv.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('input', ResumeSync.debounce(function(e) {
                const idx = parseInt(e.target.dataset.index);
                const field = e.target.classList.contains('experience-job') ? 'jobTitle' :
                             e.target.classList.contains('experience-company') ? 'company' :
                             e.target.classList.contains('experience-start') ? 'startDate' :
                             e.target.classList.contains('experience-end') ? 'endDate' : 'description';

                if (resumeData.experience[idx]) {
                    resumeData.experience[idx][field] = e.target.value;
                    updatePreview();
                    autoSave();
                }
            }, 300));
        });

        entryDiv.querySelector('.remove-experience').addEventListener('click', function() {
            const idx = parseInt(this.dataset.index);
            resumeData.experience.splice(idx, 1);
            entryDiv.remove();
            updatePreview();
            autoSave();
            ResumeSync.showToast('Experience removed', 'info');
        });
    }

    // Education management
    const addEducationBtn = document.getElementById('addEducationBtn');
    if (addEducationBtn) {
        addEducationBtn.addEventListener('click', function() {
            const educationEntry = {
                degree: '',
                institution: '',
                startYear: '',
                endYear: ''
            };
            resumeData.education.push(educationEntry);
            renderEducationEntry(educationEntry, resumeData.education.length - 1);
            updatePreview();
            ResumeSync.showToast('Education section added', 'success');
        });
    }

    function renderEducationEntry(entry, index) {
        const container = document.getElementById('educationContainer');
        if (!container) return;

        const entryDiv = document.createElement('div');
        entryDiv.className = 'education-entry border border-gray-200 rounded-lg p-4 bg-gray-50';
        entryDiv.innerHTML = `
            <div class="space-y-3">
                <input type="text" placeholder="Degree" value="${entry.degree || ''}"
                       class="education-degree w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       data-index="${index}">
                <input type="text" placeholder="Institution" value="${entry.institution || ''}"
                       class="education-institution w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       data-index="${index}">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" placeholder="Start Year" value="${entry.startYear || ''}"
                           class="education-start w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                           data-index="${index}">
                    <input type="text" placeholder="End Year" value="${entry.endYear || ''}"
                           class="education-end w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                           data-index="${index}">
                </div>
                <button class="text-red-600 text-sm hover:text-red-700 remove-education" data-index="${index}">
                    <i class="fas fa-trash mr-1"></i>Remove
                </button>
            </div>
        `;

        container.appendChild(entryDiv);

        // Add event listeners
        entryDiv.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', ResumeSync.debounce(function(e) {
                const idx = parseInt(e.target.dataset.index);
                const field = e.target.classList.contains('education-degree') ? 'degree' :
                             e.target.classList.contains('education-institution') ? 'institution' :
                             e.target.classList.contains('education-start') ? 'startYear' : 'endYear';

                if (resumeData.education[idx]) {
                    resumeData.education[idx][field] = e.target.value;
                    updatePreview();
                    autoSave();
                }
            }, 300));
        });

        entryDiv.querySelector('.remove-education').addEventListener('click', function() {
            const idx = parseInt(this.dataset.index);
            resumeData.education.splice(idx, 1);
            entryDiv.remove();
            updatePreview();
            autoSave();
            ResumeSync.showToast('Education removed', 'info');
        });
    }

    // Template styles configuration
    const templateStyles = {
        modern_professional: {
            previewContainer: 'bg-white shadow-lg rounded-lg p-8 min-h-[800px]',
            name: 'text-3xl font-bold text-gray-800 mb-2',
            contact: 'text-sm text-gray-600 space-y-1',
            sectionTitle: 'text-xl font-bold text-gray-800 mb-2 uppercase border-b pb-1 border-gray-300',
            jobTitle: 'font-bold text-gray-800',
            company: 'text-gray-700 font-medium mb-1',
            date: 'text-sm text-gray-600',
            skillTag: 'inline-block bg-gray-200 text-gray-800 px-3 py-1 rounded-sm text-sm font-medium'
        },
        clean_minimal: {
            previewContainer: 'bg-white shadow-lg rounded-lg p-8 min-h-[800px]',
            name: 'text-4xl font-light text-gray-900 mb-2 tracking-wide',
            contact: 'text-xs text-gray-500 space-y-1',
            sectionTitle: 'text-sm font-semibold text-gray-900 mb-3 uppercase tracking-widest',
            jobTitle: 'font-semibold text-gray-800',
            company: 'text-gray-600 mb-1',
            date: 'text-xs text-gray-500',
            skillTag: 'inline-block text-gray-700 text-sm mr-2'
        },
        technical_developer: {
            previewContainer: 'bg-white shadow-lg rounded-lg p-8 min-h-[800px] border-l-4 border-blue-500',
            name: 'text-3xl font-bold text-gray-900 mb-1',
            contact: 'text-xs text-gray-600 space-y-1',
            sectionTitle: 'text-lg font-bold text-gray-900 mb-3 pb-2 border-b-2 border-blue-500',
            jobTitle: 'font-bold text-gray-800',
            company: 'text-gray-600 mb-1',
            date: 'text-xs text-gray-500 italic',
            skillTag: 'inline-block bg-blue-50 text-blue-800 px-3 py-1 rounded text-sm font-medium'
        }
    };

    // Update preview
    function updatePreview() {
        const currentTemplate = resumeData.template || 'modern_professional';
        const styles = templateStyles[currentTemplate];

        if (!styles) {
            console.error('Template not found:', currentTemplate);
            return;
        }

        // Update container classes
        const previewContainer = document.getElementById('resumePreview');
        if (previewContainer) {
            previewContainer.className = styles.previewContainer;
        }

        // Update personal details
        const previewName = document.getElementById('previewName');
        if (previewName) {
            previewName.textContent = resumeData.personal.fullName || 'Your Name';
            previewName.className = styles.name;
        }

        document.getElementById('previewEmail').textContent = resumeData.personal.email || 'email@example.com';
        document.getElementById('previewPhone').textContent = resumeData.personal.phone || '+880 1234-567890';
        document.getElementById('previewLocation').textContent = resumeData.personal.location || 'Dhaka, Bangladesh';
        document.getElementById('previewSummary').textContent = resumeData.personal.summary || 'Your professional summary will appear here...';

        // Update section titles
        document.querySelectorAll('#resumePreview h2').forEach(title => {
            title.className = styles.sectionTitle;
        });

        // Update experience
        const previewExperience = document.getElementById('previewExperience');
        if (resumeData.experience.length === 0) {
            previewExperience.innerHTML = '<p class="text-gray-500 text-sm italic">No experience added yet</p>';
        } else {
            previewExperience.innerHTML = resumeData.experience.map(exp => `
                <div class="mb-4">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="${styles.jobTitle}">${exp.jobTitle || 'Job Title'}</h3>
                        <span class="${styles.date}">${exp.startDate || 'Start'} - ${exp.endDate || 'End'}</span>
                    </div>
                    <p class="${styles.company}">${exp.company || 'Company Name'}</p>
                    <p class="text-gray-600 text-sm">${exp.description || 'Job description will appear here...'}</p>
                </div>
            `).join('');
        }

        // Update education
        const previewEducation = document.getElementById('previewEducation');
        if (resumeData.education.length === 0) {
            previewEducation.innerHTML = '<p class="text-gray-500 text-sm italic">No education added yet</p>';
        } else {
            previewEducation.innerHTML = resumeData.education.map(edu => `
                <div class="mb-3">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="${styles.jobTitle}">${edu.degree || 'Degree'}</h3>
                        <span class="${styles.date}">${edu.startYear || 'Start'} - ${edu.endYear || 'End'}</span>
                    </div>
                    <p class="${styles.company}">${edu.institution || 'Institution Name'}</p>
                </div>
            `).join('');
        }

        // Update skills
        const previewSkills = document.getElementById('previewSkills');
        if (resumeData.skills.length === 0) {
            previewSkills.innerHTML = '<p class="text-gray-500 text-sm italic">No skills added yet</p>';
        } else {
            previewSkills.innerHTML = resumeData.skills.map(skill => `
                <span class="${styles.skillTag}">
                    ${skill}
                </span>
            `).join('');
        }
    }

    // Auto-save functionality
    function autoSave() {
        ResumeSync.Storage.set('currentResume', resumeData);
    }

    // Save button
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            ResumeSync.Storage.set('currentResume', resumeData);
            ResumeSync.showToast('Resume saved successfully!', 'success');
        });
    }

    // Download button
    const downloadBtn = document.getElementById('downloadBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            ResumeSync.showToast('PDF download feature will be implemented in Phase 2', 'info');
            // In Phase 2, this will trigger PDF generation via backend
        });
    }

    // Analyze button
    const analyzeBtn = document.getElementById('analyzeBtn');
    if (analyzeBtn) {
        analyzeBtn.addEventListener('click', function() {
            const jobDescription = document.getElementById('jobDescription').value;
            if (!jobDescription.trim()) {
                ResumeSync.showToast('Please paste a job description first', 'error');
                return;
            }

            ResumeSync.showToast('AI analysis will be implemented in Phase 2', 'info');
            // In Phase 2, this will call Gemini API for analysis
        });
    }

    // Populate form with existing data
    function populateForm(data) {
        // Populate template selector
        if (templateSelect && data.template) {
            templateSelect.value = data.template;
        }

        // Populate personal details
        Object.keys(personalInputs).forEach(key => {
            if (personalInputs[key] && data.personal[key]) {
                personalInputs[key].value = data.personal[key];
            }
        });

        // Populate experience
        data.experience.forEach((exp, index) => {
            renderExperienceEntry(exp, index);
        });

        // Populate education
        data.education.forEach((edu, index) => {
            renderEducationEntry(edu, index);
        });

        // Populate skills
        renderSkills();

        // Update preview
        updatePreview();
    }

    // Initial render
    renderSkills();
    updatePreview();

    // Warn before leaving if there are unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (Object.keys(resumeData.personal).some(key => resumeData.personal[key]) ||
            resumeData.experience.length > 0 ||
            resumeData.education.length > 0 ||
            resumeData.skills.length > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
