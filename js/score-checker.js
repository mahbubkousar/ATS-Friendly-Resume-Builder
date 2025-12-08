


function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}


const fileUploadToggle = document.getElementById('fileUploadToggle');
const pasteTextToggle = document.getElementById('pasteTextToggle');
const fileUploadOption = document.getElementById('fileUploadOption');
const pasteTextOption = document.getElementById('pasteTextOption');

if (fileUploadToggle && pasteTextToggle) {
    fileUploadToggle.addEventListener('click', () => {
        fileUploadToggle.classList.add('active');
        pasteTextToggle.classList.remove('active');
        fileUploadOption.classList.remove('hidden');
        pasteTextOption.classList.add('hidden');
    });

    pasteTextToggle.addEventListener('click', () => {
        pasteTextToggle.classList.add('active');
        fileUploadToggle.classList.remove('active');
        pasteTextOption.classList.remove('hidden');
        fileUploadOption.classList.add('hidden');
    });
}


const resumeFile = document.getElementById('resumeFile');
const dropZoneLabel = document.querySelector('.drop-zone-label');
const fileSelected = document.getElementById('fileSelected');
const selectedFileName = document.getElementById('selectedFileName');
const selectedFileSize = document.getElementById('selectedFileSize');
const removeFileBtn = document.getElementById('removeFileBtn');

if (resumeFile) {
    resumeFile.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            handleFileSelection(file);
        }
    });
}

function handleFileSelection(file) {
    
    const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!validTypes.includes(file.type)) {
        console.log('Please upload a PDF or DOC file');
        resumeFile.value = '';
        return;
    }

    
    if (file.size > 5 * 1024 * 1024) {
        console.log('File size must be less than 5MB');
        resumeFile.value = '';
        return;
    }

    
    selectedFileName.textContent = file.name;
    selectedFileSize.textContent = formatFileSize(file.size);
    dropZoneLabel.style.display = 'none';
    fileSelected.classList.add('show');
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' bytes';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}


if (removeFileBtn) {
    removeFileBtn.addEventListener('click', () => {
        resumeFile.value = '';
        dropZoneLabel.style.display = 'flex';
        fileSelected.classList.remove('show');
        console.log('File removed');
    });
}


if (dropZoneLabel) {
    const fileInput = document.getElementById('resumeFile');

    dropZoneLabel.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZoneLabel.style.borderColor = 'var(--accent-color)';
        dropZoneLabel.style.background = 'rgba(124, 58, 237, 0.05)';
    });

    dropZoneLabel.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZoneLabel.style.borderColor = '';
        dropZoneLabel.style.background = '';
    });

    dropZoneLabel.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZoneLabel.style.borderColor = '';
        dropZoneLabel.style.background = '';

        const file = e.dataTransfer.files[0];
        if (file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            handleFileSelection(file);
        }
    });
}


const resumeText = document.getElementById('resumeText');
const charCount = document.getElementById('charCount');

if (resumeText) {
    resumeText.addEventListener('input', () => {
        const count = resumeText.value.length;
        charCount.textContent = count.toLocaleString();
    });
}


const jobTextToggle = document.getElementById('jobTextToggle');
const jobFileToggle = document.getElementById('jobFileToggle');
const jobTextInput = document.getElementById('jobTextInput');
const jobFileInput = document.getElementById('jobFileInput');

if (jobTextToggle && jobFileToggle) {
    jobTextToggle.addEventListener('click', () => {
        jobTextToggle.classList.add('active');
        jobFileToggle.classList.remove('active');
        jobTextInput.classList.remove('hidden');
        jobFileInput.classList.add('hidden');
    });

    jobFileToggle.addEventListener('click', () => {
        jobFileToggle.classList.add('active');
        jobTextToggle.classList.remove('active');
        jobFileInput.classList.remove('hidden');
        jobTextInput.classList.add('hidden');
    });
}


const jobDescText = document.getElementById('jobDescText');
const jobCharCount = document.getElementById('jobCharCount');

if (jobDescText) {
    jobDescText.addEventListener('input', () => {
        const count = jobDescText.value.length;
        jobCharCount.textContent = count.toLocaleString();
    });
}


const jobDescFile = document.getElementById('jobDescFile');
const jobFileLabel = document.querySelector('.job-file-label');
const jobFileSelected = document.getElementById('jobFileSelected');
const jobFileName = document.getElementById('jobFileName');
const jobFileSize = document.getElementById('jobFileSize');
const removeJobFileBtn = document.getElementById('removeJobFileBtn');

if (jobDescFile) {
    jobDescFile.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            handleJobFileSelection(file);
        }
    });
}

function handleJobFileSelection(file) {
    
    if (file.type !== 'application/pdf') {
        console.log('Please upload a PDF file for job description');
        jobDescFile.value = '';
        return;
    }

    
    if (file.size > 5 * 1024 * 1024) {
        console.log('File size must be less than 5MB');
        jobDescFile.value = '';
        return;
    }

    
    jobFileName.textContent = file.name;
    jobFileSize.textContent = formatFileSize(file.size);
    jobFileLabel.style.display = 'none';
    jobFileSelected.classList.add('show');
    console.log('Job description uploaded');
}


if (removeJobFileBtn) {
    removeJobFileBtn.addEventListener('click', () => {
        jobDescFile.value = '';
        jobFileLabel.style.display = 'flex';
        jobFileSelected.classList.remove('show');
        console.log('Job description file removed');
    });
}


if (jobFileLabel) {
    jobFileLabel.addEventListener('dragover', (e) => {
        e.preventDefault();
        jobFileLabel.style.borderColor = 'var(--accent-color)';
        jobFileLabel.style.background = 'rgba(124, 58, 237, 0.05)';
    });

    jobFileLabel.addEventListener('dragleave', (e) => {
        e.preventDefault();
        jobFileLabel.style.borderColor = '';
        jobFileLabel.style.background = '';
    });

    jobFileLabel.addEventListener('drop', (e) => {
        e.preventDefault();
        jobFileLabel.style.borderColor = '';
        jobFileLabel.style.background = '';

        const file = e.dataTransfer.files[0];
        if (file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            jobDescFile.files = dataTransfer.files;
            handleJobFileSelection(file);
        }
    });
}


const analyzeBtn = document.getElementById('analyzeBtn');
const uploadSection = document.getElementById('uploadSection');
const resultsSection = document.getElementById('resultsSection');

if (analyzeBtn) {
    analyzeBtn.addEventListener('click', async () => {
        
        const hasFile = resumeFile && resumeFile.files.length > 0;
        const hasText = resumeText && resumeText.value.trim().length > 0;

        if (!hasFile && !hasText) {
            console.log('Please upload a resume or paste your resume text');
            return;
        }

        
        const hasJobText = jobDescText && jobDescText.value.trim().length > 0;
        const hasJobFile = jobDescFile && jobDescFile.files.length > 0;

        if (!hasJobText && !hasJobFile) {
            console.log('Tip: Add a job description for more accurate keyword matching!');
        }

        
        showProgressModal();

        
        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Analyzing...</span>';

        try {
            await performAnalysis();
        } catch (error) {
            console.error('Analysis error:', error);
            hideProgressModal();
            console.log('Analysis failed. Please try again.');
        } finally {
            
            analyzeBtn.disabled = false;
            analyzeBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> <span>Analyze Resume</span>';
        }
    });
}


function showProgressModal() {
    const modal = document.getElementById('analysisProgressModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    
    updateProgress(0, 'Initializing analysis...');
}

function hideProgressModal() {
    const modal = document.getElementById('analysisProgressModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';

    
    updateProgress(0, 'Initializing analysis...');
    resetProgressSteps();
}

function updateProgress(percentage, stage) {
    const progressBar = document.getElementById('progressBarFill');
    const progressPercentage = document.getElementById('progressPercentage');
    const progressStage = document.getElementById('progressStage');

    if (progressBar) progressBar.style.width = percentage + '%';
    if (progressPercentage) progressPercentage.textContent = Math.round(percentage) + '%';
    if (progressStage) progressStage.textContent = stage;
}

function setStepActive(stepNumber) {
    for (let i = 1; i <= 5; i++) {
        const step = document.getElementById('step' + i);
        if (step) {
            step.classList.remove('active', 'completed');
            if (i < stepNumber) {
                step.classList.add('completed');
            } else if (i === stepNumber) {
                step.classList.add('active');
            }
        }
    }
}

function resetProgressSteps() {
    for (let i = 1; i <= 5; i++) {
        const step = document.getElementById('step' + i);
        if (step) {
            step.classList.remove('active', 'completed');
        }
    }
}

async function performAnalysis() {
    
    const formData = new FormData();

    
    const hasFile = resumeFile && resumeFile.files.length > 0;
    const hasText = resumeText && resumeText.value.trim().length > 0;

    if (hasFile) {
        formData.append('resume_file', resumeFile.files[0]);
    } else if (hasText) {
        formData.append('resume_text', resumeText.value.trim());
    }

    
    const hasJobText = jobDescText && jobDescText.value.trim().length > 0;
    const hasJobFile = jobDescFile && jobDescFile.files.length > 0;

    if (hasJobText) {
        formData.append('job_description', jobDescText.value.trim());
    } else if (hasJobFile) {
        formData.append('job_description_file', jobDescFile.files[0]);
    }

    try {
        
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

        
        displayResults(result.analysis);

    } catch (error) {
        console.error('Analysis error:', error);
        throw error;
    }
}

function displayResults(analysis) {
    
    uploadSection.classList.add('hidden');
    const jobSection = document.querySelector('.job-section');
    if (jobSection) jobSection.classList.add('hidden');
    resultsSection.classList.remove('hidden');

    
    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

    
    const overallScore = analysis.overall_score;
    const formattingScore = Math.round((analysis.formatting_score / 25) * 100);
    const keywordsScore = Math.round((analysis.keywords_score / 25) * 100);
    const sectionsScore = Math.round((analysis.content_structure_score / 20) * 100);
    const contactScore = Math.round((analysis.contact_info_score / 10) * 100);
    const parsabilityScore = Math.round((analysis.experience_format_score / 10) * 100);

    
    animateScore(overallScore);

    
    setTimeout(() => {
        updateBreakdownScore('formatting', formattingScore);
        updateBreakdownScore('keywords', keywordsScore);
        updateBreakdownScore('sections', sectionsScore);
        updateBreakdownScore('contact', contactScore);
        updateBreakdownScore('parsability', parsabilityScore);
    }, 1000);

    
    updateInsights(analysis);


    updateMetricDetails(analysis);

    // Load and show ATS guidelines references
    loadATSGuidelines();
    setTimeout(() => {
        showGuidelinesSection();
    }, 2000);

    console.log('Analysis complete!');
}


function updateMetricDetails(analysis) {
    console.log('Updating metric details with analysis:', analysis);

    
    const keywordsDetails = document.getElementById('keywordsDetails');
    if (keywordsDetails) {
        let html = '';

        if (analysis.keywords_found && analysis.keywords_found.length > 0) {
            html += '<p class="details-title"><i class="fa-solid fa-check-circle" style="color: #10b981;"></i> Keywords Found (' + analysis.keywords_found.length + ')</p>';
            html += '<div style="margin-bottom: 1rem;">';
            analysis.keywords_found.slice(0, 10).forEach(keyword => {
                html += '<span class="keyword-badge found">' + escapeHtml(keyword) + '</span>';
            });
            html += '</div>';
        }

        if (analysis.keywords_missing && analysis.keywords_missing.length > 0) {
            html += '<p class="details-title"><i class="fa-solid fa-exclamation-circle" style="color: #ef4444;"></i> Missing Keywords (' + analysis.keywords_missing.length + ')</p>';
            html += '<div>';
            analysis.keywords_missing.slice(0, 10).forEach(keyword => {
                html += '<span class="keyword-badge missing">' + escapeHtml(keyword) + '</span>';
            });
            html += '</div>';
        }

        if (html) {
            keywordsDetails.innerHTML = html;
        } else {
            keywordsDetails.innerHTML = '<p class="details-empty">Add a job description to see keyword matching analysis</p>';
        }
    }

    
    const categoryMap = {
        'Formatting': 'formattingDetails',
        'Keywords': 'keywordsDetails',
        'Content Structure': 'sectionsDetails',
        'Sections': 'sectionsDetails',
        'Content': 'sectionsDetails',
        'Contact Information': 'contactDetails',
        'Contact Info': 'contactDetails',
        'Contact': 'contactDetails',
        'Experience Format': 'parsabilityDetails',
        'Experience': 'parsabilityDetails',
        'Technical': 'parsabilityDetails',
        'Parsability': 'parsabilityDetails'
    };

    console.log('Improvements data:', analysis.improvements);

    if (analysis.improvements && Array.isArray(analysis.improvements)) {
        const categoryIssues = {};

        analysis.improvements.forEach(improvement => {
            const category = improvement.category || 'General';
            if (!categoryIssues[category]) {
                categoryIssues[category] = [];
            }
            categoryIssues[category].push(improvement);
        });

        console.log('Categorized issues:', categoryIssues);

        Object.keys(categoryIssues).forEach(category => {
            const detailsId = categoryMap[category];
            if (detailsId && detailsId !== 'keywordsDetails') { 
                const detailsElement = document.getElementById(detailsId);
                if (detailsElement) {
                    let html = '<ul>';
                    categoryIssues[category].forEach(item => {
                        const text = item.suggestion || item.issue || String(item);
                        html += '<li class="bad"><i class="fa-solid fa-circle-xmark"></i><span>' + escapeHtml(text) + '</span></li>';
                    });
                    html += '</ul>';

                    
                    const currentContent = detailsElement.innerHTML;
                    if (currentContent.includes('details-empty')) {
                        detailsElement.innerHTML = html;
                    } else {
                        
                        detailsElement.innerHTML = currentContent + html;
                    }
                }
            }
        });
    }

    
    const allDetailsElements = [
        { id: 'formattingDetails', name: 'formatting' },
        { id: 'sectionsDetails', name: 'content structure' },
        { id: 'contactDetails', name: 'contact information' },
        { id: 'parsabilityDetails', name: 'parsability' }
    ];

    allDetailsElements.forEach(({ id, name }) => {
        const element = document.getElementById(id);
        if (element && element.innerHTML.includes('details-empty')) {
            element.innerHTML = '<p class="details-empty"><i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 0.5rem;"></i>No major issues found in ' + name + '. Great job!</p>';
        }
    });

    
    if (analysis.strengths && Array.isArray(analysis.strengths)) {
        analysis.strengths.forEach(strength => {
            
            const strengthLower = strength.toLowerCase();
            let targetId = null;

            if (strengthLower.includes('format') || strengthLower.includes('font') || strengthLower.includes('layout')) {
                targetId = 'formattingDetails';
            } else if (strengthLower.includes('section') || strengthLower.includes('structure')) {
                targetId = 'sectionsDetails';
            } else if (strengthLower.includes('contact') || strengthLower.includes('email') || strengthLower.includes('phone')) {
                targetId = 'contactDetails';
            }

            if (targetId) {
                const detailsElement = document.getElementById(targetId);
                if (detailsElement) {
                    const existingContent = detailsElement.innerHTML;
                    if (existingContent.includes('details-empty')) {
                        detailsElement.innerHTML = '<ul><li class="good"><i class="fa-solid fa-circle-check"></i><span>' + strength + '</span></li></ul>';
                    } else if (!existingContent.includes(strength)) {
                        const ul = detailsElement.querySelector('ul') || document.createElement('ul');
                        const li = document.createElement('li');
                        li.className = 'good';
                        li.innerHTML = '<i class="fa-solid fa-circle-check"></i><span>' + strength + '</span>';
                        ul.insertBefore(li, ul.firstChild);
                        if (!detailsElement.querySelector('ul')) {
                            detailsElement.insertBefore(ul, detailsElement.firstChild);
                        }
                    }
                }
            }
        });
    }
}

function animateScore(score) {
    const scoreElement = document.getElementById('overallScore');
    const progressCircle = document.getElementById('scoreProgress');
    const statusElement = document.getElementById('scoreStatus');
    const messageElement = document.getElementById('scoreMessage');

    
    let currentScore = 0;
    const increment = score / 50;
    const timer = setInterval(() => {
        currentScore += increment;
        if (currentScore >= score) {
            currentScore = score;
            clearInterval(timer);
        }
        scoreElement.textContent = Math.floor(currentScore);
    }, 30);

    
    const circumference = 2 * Math.PI * 85;
    const offset = circumference - (score / 100) * circumference;
    progressCircle.style.strokeDashoffset = offset;

    
    if (score >= 90) {
        statusElement.textContent = 'Excellent';
        messageElement.textContent = 'Your resume is highly optimized and will pass most ATS systems with ease!';
        progressCircle.style.stroke = '#10b981';
    } else if (score >= 80) {
        statusElement.textContent = 'Very Good';
        messageElement.textContent = 'Your resume is well-structured and ATS-friendly with minor areas to polish';
        progressCircle.style.stroke = '#22c55e';
    } else if (score >= 70) {
        statusElement.textContent = 'Good';
        messageElement.textContent = 'Your resume meets ATS requirements but could be further optimized';
        progressCircle.style.stroke = '#3b82f6';
    } else if (score >= 60) {
        statusElement.textContent = 'Fair';
        messageElement.textContent = 'Your resume may pass some ATS but needs improvements for better results';
        progressCircle.style.stroke = '#f59e0b';
    } else {
        statusElement.textContent = 'Needs Work';
        messageElement.textContent = 'Your resume needs significant optimization to pass ATS screening effectively';
        progressCircle.style.stroke = '#ef4444';
    }
}

function updateBreakdownScore(category, score) {
    const scoreElement = document.getElementById(`${category}Score`);
    const progressElement = document.getElementById(`${category}Progress`);

    if (scoreElement && progressElement) {
        scoreElement.textContent = score + '%';
        progressElement.style.width = score + '%';

        
        if (score >= 80) {
            progressElement.style.background = '#10b981';
        } else if (score >= 60) {
            progressElement.style.background = '#3b82f6';
        } else if (score >= 40) {
            progressElement.style.background = '#f59e0b';
        } else {
            progressElement.style.background = '#ef4444';
        }
    }
}


function updateInsights(analysis) {
    
    const positiveInsight = document.querySelector('.insight-card.positive ul');
    const warningInsight = document.querySelector('.insight-card.warning ul');
    const infoInsight = document.querySelector('.insight-card.info ul');

    
    if (positiveInsight && analysis.strengths && analysis.strengths.length > 0) {
        positiveInsight.innerHTML = '';
        analysis.strengths.slice(0, 5).forEach(strength => {
            const li = document.createElement('li');
            li.textContent = strength;
            positiveInsight.appendChild(li);
        });
    }

    
    if (warningInsight && analysis.improvements && analysis.improvements.length > 0) {
        warningInsight.innerHTML = '';
        const improvementsToShow = analysis.improvements.slice(0, 5);

        improvementsToShow.forEach(improvement => {
            const li = document.createElement('li');
            if (typeof improvement === 'object') {
                
                li.innerHTML = `<strong>${improvement.category}:</strong> ${improvement.suggestion || improvement.issue}`;
            } else {
                li.textContent = improvement;
            }
            warningInsight.appendChild(li);
        });
    }

    
    if (infoInsight) {
        const hasKeywordsData = (analysis.keywords_found && analysis.keywords_found.length > 0) ||
                               (analysis.keywords_missing && analysis.keywords_missing.length > 0);

        if (hasKeywordsData) {
            infoInsight.innerHTML = '';

            
            if (analysis.keywords_found && analysis.keywords_found.length > 0) {
                const li = document.createElement('li');
                li.innerHTML = `<strong>Strong Keywords Found:</strong> ${analysis.keywords_found.slice(0, 5).join(', ')}`;
                infoInsight.appendChild(li);
            }

            
            if (analysis.keywords_missing && analysis.keywords_missing.length > 0) {
                const li = document.createElement('li');
                li.innerHTML = `<strong>Consider Adding:</strong> ${analysis.keywords_missing.slice(0, 5).join(', ')}`;
                infoInsight.appendChild(li);
            }

            
            const generalTip = document.createElement('li');
            generalTip.textContent = 'Use PDF format for final submission to preserve formatting';
            infoInsight.appendChild(generalTip);
        }
    }
}


const checkAnotherBtn = document.getElementById('checkAnotherBtn');

if (checkAnotherBtn) {
    checkAnotherBtn.addEventListener('click', () => {
        
        resumeFile.value = '';
        resumeText.value = '';
        charCount.textContent = '0';
        dropZoneLabel.style.display = 'flex';
        fileSelected.classList.remove('show');

        
        if (jobDescFile) jobDescFile.value = '';
        if (jobDescText) jobDescText.value = '';
        if (jobCharCount) jobCharCount.textContent = '0';
        if (jobFileLabel) jobFileLabel.style.display = 'flex';
        if (jobFileSelected) jobFileSelected.classList.remove('show');

        
        uploadSection.classList.remove('hidden');
        const jobSection = document.querySelector('.job-section');
        if (jobSection) jobSection.classList.remove('hidden');
        resultsSection.classList.add('hidden');

        
        window.scrollTo({ top: 0, behavior: 'smooth' });

        console.log('Ready to analyze another resume');
    });
}





const calculationModal = document.getElementById('calculationModal');
const howWeCalculateBtn = document.getElementById('howWeCalculateBtn');
const closeCalcModal = document.getElementById('closeCalcModal');

console.log('Modal elements:', { calculationModal, howWeCalculateBtn, closeCalcModal });

if (howWeCalculateBtn) {
    howWeCalculateBtn.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('How We Calculate button clicked');
        if (calculationModal) {
            calculationModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            console.log('Modal should be visible now');
        } else {
            console.error('calculationModal not found');
        }
    });
} else {
    console.error('howWeCalculateBtn not found');
}

if (closeCalcModal) {
    closeCalcModal.addEventListener('click', () => {
        calculationModal.classList.remove('active');
        document.body.style.overflow = '';
    });
}


if (calculationModal) {
    calculationModal.addEventListener('click', (e) => {
        if (e.target === calculationModal) {
            calculationModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}


document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && calculationModal.classList.contains('active')) {
        calculationModal.classList.remove('active');
        document.body.style.overflow = '';
    }
});






document.addEventListener('click', (e) => {
    const expandBtn = e.target.closest('.metric-expand-btn');
    if (expandBtn) {
        e.preventDefault();
        e.stopPropagation();

        const targetId = expandBtn.getAttribute('data-target');
        const detailsElement = document.getElementById(targetId);

        console.log('Expand button clicked:', targetId, detailsElement);

        if (detailsElement) {
            const wasHidden = detailsElement.classList.contains('hidden');
            detailsElement.classList.toggle('hidden');
            expandBtn.classList.toggle('expanded');

            console.log('Toggled visibility. Was hidden:', wasHidden, 'Now hidden:', detailsElement.classList.contains('hidden'));
            console.log('Details content:', detailsElement.innerHTML);
        } else {
            console.error('Details element not found:', targetId);
        }
    }
});

console.log('ATS Score Checker loaded');

/**
 * Load ATS Guidelines References
 * Displays source documents used for scoring calculation
 */
async function loadATSGuidelines() {
    try {
        const response = await fetch('/ATS/api/get-ats-guidelines.php');
        const data = await response.json();

        if (data.success && data.guidelines && data.guidelines.length > 0) {
            const guidelinesSection = document.getElementById('guidelinesSection');
            const guidelinesGrid = document.getElementById('guidelinesGrid');

            // Create guideline cards
            let html = '';
            data.guidelines.forEach((guideline, index) => {
                html += `
                    <div class="guideline-card" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                        <div style="display: flex; align-items: start; gap: 1rem;">
                            <div style="flex-shrink: 0; width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="font-size: 1rem; color: #1e293b; margin: 0 0 0.5rem 0; font-weight: 600;">
                                    ${guideline.title}
                                </h4>
                                <p style="font-size: 0.875rem; color: #64748b; margin: 0 0 1rem 0;">
                                    ${guideline.size_formatted} • Updated ${guideline.modified}
                                </p>
                                <a href="${guideline.download_url}"
                                   class="download-guideline-btn"
                                   download="${guideline.file_name}"
                                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #7c3aed; color: white; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: all 0.2s;">
                                    <i class="fas fa-download"></i>
                                    Download Reference
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });

            guidelinesGrid.innerHTML = html;

            // Add hover effect to download buttons
            const style = document.createElement('style');
            style.textContent = `
                .download-guideline-btn:hover {
                    background: #6d28d9 !important;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
                }
            `;
            document.head.appendChild(style);
        }
    } catch (error) {
        console.error('Failed to load ATS guidelines:', error);
    }
}

/**
 * Show guidelines section after analysis
 */
function showGuidelinesSection() {
    const guidelinesSection = document.getElementById('guidelinesSection');
    if (guidelinesSection) {
        guidelinesSection.style.display = 'block';

        // Smooth scroll to guidelines
        setTimeout(() => {
            guidelinesSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 500);
    }
}
