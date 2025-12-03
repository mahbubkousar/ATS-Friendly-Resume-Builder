<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$templateName = $_GET['template'] ?? 'classic';
$mode = $_GET['mode'] ?? 'full';
$allowedTemplates = [
    'classic', 'modern', 'professional', 'technical',
    'executive', 'creative', 'academic-standard',
    'research-scientist', 'teaching-faculty'
];

if (!in_array($templateName, $allowedTemplates)) {
    $templateName = 'classic';
}

$templatePath = __DIR__ . "/{$templateName}.html";
if (!file_exists($templatePath)) {
    echo "Template not found";
    exit;
}

$templateHTML = file_get_contents($templatePath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Preview</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        <?php if ($mode === 'thumbnail'): ?>
        body {
            transform: scale(0.3);
            transform-origin: top left;
            width: 333.33%;
            height: 333.33%;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <div id="templateContainer">
        <?php echo $templateHTML; ?>
    </div>

    <script>
    console.log('Preview handler loaded');

    window.addEventListener('message', function(event) {
        console.log('Received message:', event.data);
        if (event.data.type === 'updateResume') {
            console.log('Updating resume with data:', event.data.data);
            updateResumePreview(event.data.data);
        }
    });

    function updateResumePreview(resumeState) {
        console.log('updateResumePreview called with:', resumeState);

        if (resumeState.personal_details) {
            const pd = resumeState.personal_details;
            console.log('Updating personal details:', pd);

            const isValidValue = (val) => val && val !== '' && val !== '...' && val !== 'N/A';
            const nameEl = document.querySelector('[data-field="name"]');
            if (nameEl && isValidValue(pd.fullName)) {
                nameEl.textContent = pd.fullName;
                console.log('Updated name to:', pd.fullName);
            }

            const emailEl = document.querySelector('[data-field="email"]');
            if (emailEl && isValidValue(pd.email)) {
                emailEl.textContent = pd.email;
                console.log('Updated email to:', pd.email);
            }

            const phoneEl = document.querySelector('[data-field="phone"]');
            if (phoneEl && isValidValue(pd.phone)) {
                phoneEl.textContent = pd.phone;
                console.log('Updated phone to:', pd.phone);
            }

            const locationEl = document.querySelector('[data-field="location"]');
            if (locationEl && isValidValue(pd.location)) {
                locationEl.textContent = pd.location;
                console.log('Updated location to:', pd.location);
            }

            const linkedinEl = document.querySelector('[data-field="linkedin"]');
            if (linkedinEl && isValidValue(pd.linkedin)) {
                linkedinEl.textContent = pd.linkedin;
                linkedinEl.href = pd.linkedin;
                console.log('Updated linkedin to:', pd.linkedin);
            }

            const titleEl = document.querySelector('[data-field="title"]');
            if (titleEl && isValidValue(pd.professionalTitle)) {
                titleEl.textContent = pd.professionalTitle;
                console.log('Updated professional title to:', pd.professionalTitle);
            }
        }

        if (resumeState.summary_text && resumeState.summary_text !== '...' && resumeState.summary_text !== 'N/A') {
            const summaryEl = document.querySelector('[data-field="summary"]');
            if (summaryEl) {
                summaryEl.textContent = resumeState.summary_text;
                console.log('Updated summary');
            }
        }

        if (resumeState.experience && resumeState.experience.length > 0) {
            const experienceContainer = document.querySelector('[data-field="experience-list"]');
            if (experienceContainer) {
                experienceContainer.innerHTML = '';
                resumeState.experience.forEach(exp => {
                    const entry = createExperienceEntry(exp);
                    experienceContainer.appendChild(entry);
                });
            }
        }

        if (resumeState.education && resumeState.education.length > 0) {
            const educationContainer = document.querySelector('[data-field="education-list"]');
            if (educationContainer) {
                educationContainer.innerHTML = '';
                resumeState.education.forEach(edu => {
                    const entry = createEducationEntry(edu);
                    educationContainer.appendChild(entry);
                });
            }
        }

        if (resumeState.skills) {
            const skillsEl = document.querySelector('[data-field="skills"]');
            if (skillsEl) {
                const skillsArray = typeof resumeState.skills === 'string'
                    ? resumeState.skills.split(',').map(s => s.trim()).filter(s => s)
                    : resumeState.skills;

                skillsEl.innerHTML = '';
                skillsArray.forEach(skill => {
                    const skillItem = document.createElement('span');
                    skillItem.className = 'skill-item';
                    skillItem.textContent = skill;
                    skillsEl.appendChild(skillItem);
                });

                console.log('Updated skills:', skillsArray);
            }
        }

        if (resumeState.projects && resumeState.projects.length > 0) {
            const projectsContainer = document.querySelector('[data-field="projects-list"]');
            if (projectsContainer) {
                projectsContainer.innerHTML = '';
                resumeState.projects.forEach(proj => {
                    const entry = createProjectEntry(proj);
                    projectsContainer.appendChild(entry);
                });
            }
        }

        if (resumeState.research_interests) {
            const researchEl = document.querySelector('[data-field="research-interests"]');
            if (researchEl) {
                researchEl.textContent = resumeState.research_interests;
            }
        }

        if (resumeState.publications && resumeState.publications.length > 0) {
            const pubContainer = document.querySelector('[data-field="publications-list"]');
            if (pubContainer) {
                pubContainer.innerHTML = '';
                resumeState.publications.forEach(pub => {
                    const entry = createPublicationEntry(pub);
                    pubContainer.appendChild(entry);
                });
            }
        }

        if (resumeState.references && resumeState.references.length > 0) {
            const refContainer = document.querySelector('[data-field="references-list"]');
            if (refContainer) {
                refContainer.innerHTML = '';
                resumeState.references.forEach(ref => {
                    const entry = createReferenceEntry(ref);
                    refContainer.appendChild(entry);
                });
            }
        }
    }

    function createExperienceEntry(exp) {
        const div = document.createElement('div');
        div.className = 'entry';
        div.innerHTML = `
            <div class="entry-header">
                <div class="entry-title">${exp.title || ''}</div>
                <div class="entry-date">${formatDate(exp.start_date)} - ${exp.end_date === 'Present' ? 'Present' : formatDate(exp.end_date)}</div>
            </div>
            <div class="entry-company">${exp.company || ''}</div>
            <div class="entry-description">${exp.description || ''}</div>
        `;
        return div;
    }

    function createEducationEntry(edu) {
        const div = document.createElement('div');
        div.className = 'entry';
        div.innerHTML = `
            <div class="entry-header">
                <div class="entry-title">${edu.degree || ''} ${edu.field ? 'in ' + edu.field : ''}</div>
                <div class="entry-date">${formatDate(edu.graduation_date)}</div>
            </div>
            <div class="entry-company">${edu.institution || ''}</div>
            ${edu.gpa ? `<div class="entry-description">GPA: ${edu.gpa}</div>` : ''}
        `;
        return div;
    }

    function createProjectEntry(proj) {
        const div = document.createElement('div');
        div.className = 'entry';
        div.innerHTML = `
            <div class="entry-title">${proj.name || ''}</div>
            <div class="entry-description">${proj.description || ''}</div>
            ${proj.technologies ? `<div class="entry-company">Technologies: ${proj.technologies}</div>` : ''}
            ${proj.link ? `<div class="entry-company"><a href="${proj.link}" target="_blank">${proj.link}</a></div>` : ''}
        `;
        return div;
    }

    function createPublicationEntry(pub) {
        const div = document.createElement('div');
        div.className = 'entry';
        div.innerHTML = `
            <div class="entry-description">
                ${pub.authors || ''}. "${pub.title || ''}." <em>${pub.venue || ''}</em>, ${pub.year || ''}.
                ${pub.link ? `<a href="${pub.link}" target="_blank">Link</a>` : ''}
            </div>
        `;
        return div;
    }

    function createReferenceEntry(ref) {
        const div = document.createElement('div');
        div.className = 'entry';
        div.innerHTML = `
            <div class="entry-title">${ref.name || ''}</div>
            <div class="entry-company">${ref.title || ''}, ${ref.institution || ''}</div>
            <div class="entry-description">
                ${ref.email || ''} ${ref.phone ? '| ' + ref.phone : ''}
            </div>
        `;
        return div;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        if (dateStr === 'Present') return 'Present';

        // Try to parse and format the date
        const date = new Date(dateStr);
        if (!isNaN(date.getTime())) {
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        }
        return dateStr;
    }

    console.log('Preview handler loaded for template: <?php echo $templateName; ?>');
    </script>
</body>
</html>
