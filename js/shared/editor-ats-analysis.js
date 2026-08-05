/**
 * ATS analysis workflow shared by professional-style resume editors.
 */
(function installEditorAtsAnalysis() {
async function performAIAnalysis() {
    const analyzeBtn = document.querySelector('.analyze-btn');
    if (!analyzeBtn) return;

    try {
        const jobDescText = document.getElementById('jobDescText')?.value.trim();
        const jobDescFile = document.getElementById('jobDescFile')?.files[0];

        if (!jobDescText && !jobDescFile) {
            showNotificationModal('Please provide a job description to analyze against', 'error');
            return;
        }

        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Analyzing...';
        showProgressModal();
        updateScoreDisplay('analyzing', '--', 'Analyzing your resume...');

        const formData = new FormData();
        formData.append('resume_text', generateResumeText());
        if (jobDescText) {
            formData.append('job_description', jobDescText);
        } else {
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

        const response = await fetch(appUrl('api/analyze-ats-score.php'), {
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

    if (resumeState.experience?.length) {
        text += 'EXPERIENCE\n';
        resumeState.experience.forEach(exp => {
            text += exp.title + '\n' + exp.company + '\n' + exp.dates + '\n'
                + exp.description + '\n\n';
        });
    }

    if (resumeState.education?.length) {
        text += 'EDUCATION\n';
        resumeState.education.forEach(edu => {
            text += edu.degree + '\n' + edu.school + '\n' + edu.graduationDate + '\n';
            if (edu.gpa) text += 'GPA: ' + edu.gpa + '\n';
            text += '\n';
        });
    }

    if (resumeState.skills?.length) {
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
        scoreFill.style.strokeDashoffset = circumference - (score / 100) * circumference;
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
    if (keywordsFound?.length) {
        html += '<div class="suggestion-box success-box"><h4><i class="fa-solid fa-check-circle"></i> Keywords Found</h4><div class="keywords-list">';
        keywordsFound.forEach(keyword => {
            html += `<span class="keyword-badge found">${escapeHtml(keyword)}</span>`;
        });
        html += '</div></div>';
    }

    if (keywordsMissing?.length) {
        html += '<div class="suggestion-box warning-box"><h4><i class="fa-solid fa-exclamation-triangle"></i> Missing Keywords</h4><div class="keywords-list">';
        keywordsMissing.forEach(keyword => {
            html += `<span class="keyword-badge missing">${escapeHtml(keyword)}</span>`;
        });
        html += '</div></div>';
    }

    if (improvements?.length) {
        html += '<div class="suggestion-box info-box"><h4><i class="fa-solid fa-lightbulb"></i> Improvements</h4><ul class="suggestions-list">';
        improvements.forEach(item => {
            html += `<li><strong>${escapeHtml(item.category || 'General')}:</strong> ${escapeHtml(item.issue || '')} - ${escapeHtml(item.suggestion || '')}</li>`;
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

    textToggle?.addEventListener('click', () => {
        textToggle.classList.add('active');
        fileToggle?.classList.remove('active');
        textInput?.classList.remove('hidden');
        fileInput?.classList.add('hidden');
    });

    fileToggle?.addEventListener('click', () => {
        fileToggle.classList.add('active');
        textToggle?.classList.remove('active');
        fileInput?.classList.remove('hidden');
        textInput?.classList.add('hidden');
    });

    const jobDescFileInput = document.getElementById('jobDescFile');
    jobDescFileInput?.addEventListener('change', event => {
        const file = event.target.files[0];
        if (!file) return;

        const fileName = document.getElementById('fileName');
        if (fileName) fileName.textContent = file.name;
        const uploadLabel = document.querySelector('.file-upload-label');
        if (uploadLabel) uploadLabel.style.display = 'none';
        document.getElementById('fileInfo')?.classList.remove('hidden');
    });

    document.getElementById('removeFile')?.addEventListener('click', () => {
        if (jobDescFileInput) jobDescFileInput.value = '';
        const uploadLabel = document.querySelector('.file-upload-label');
        if (uploadLabel) uploadLabel.style.display = 'flex';
        document.getElementById('fileInfo')?.classList.add('hidden');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.analyze-btn')?.addEventListener('click', performAIAnalysis);
    setupJobDescriptionToggle();
});
})();
