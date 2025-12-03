


const TEMPLATE_CONFIGS = {
    'classic': {
        name: 'Classic',
        type: 'professional',
        fields: ['personal_details', 'summary', 'experience', 'education', 'skills'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location', 'linkedin'] },
            summary: { required: false, type: 'text' },
            experience: { required: true, type: 'array', min: 1 },
            education: { required: true, type: 'array', min: 1 },
            skills: { required: true, type: 'text' }
        }
    },
    'modern': {
        name: 'Modern',
        type: 'professional',
        fields: ['personal_details', 'summary', 'experience', 'education', 'skills'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location', 'linkedin'] },
            summary: { required: false, type: 'text' },
            experience: { required: true, type: 'array', min: 1 },
            education: { required: true, type: 'array', min: 1 },
            skills: { required: true, type: 'text' }
        }
    },
    'professional': {
        name: 'Professional',
        type: 'professional',
        fields: ['personal_details', 'summary', 'experience', 'education', 'skills'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location', 'linkedin'] },
            summary: { required: true, type: 'text' },
            experience: { required: true, type: 'array', min: 1 },
            education: { required: true, type: 'array', min: 1 },
            skills: { required: true, type: 'text' }
        }
    },
    'technical': {
        name: 'Technical',
        type: 'professional',
        fields: ['personal_details', 'summary', 'experience', 'education', 'skills', 'projects'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location', 'linkedin'] },
            summary: { required: false, type: 'text' },
            experience: { required: true, type: 'array', min: 1 },
            education: { required: true, type: 'array', min: 1 },
            skills: { required: true, type: 'text' },
            projects: { required: false, type: 'array', min: 0 }
        }
    },
    'executive': {
        name: 'Executive',
        type: 'professional',
        fields: ['personal_details', 'summary', 'achievements', 'experience', 'education', 'skills', 'board'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location', 'linkedin'] },
            summary: { required: true, type: 'text' },
            achievements: { required: true, type: 'text' },
            experience: { required: true, type: 'array', min: 1 },
            education: { required: true, type: 'array', min: 1 },
            skills: { required: true, type: 'text' },
            board: { required: false, type: 'array', min: 0 }
        }
    },
    'creative': {
        name: 'Creative Professional',
        type: 'professional',
        fields: ['personal_details', 'summary', 'experience', 'education', 'skills', 'portfolio'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location', 'linkedin', 'portfolio_url'] },
            summary: { required: true, type: 'text' },
            experience: { required: true, type: 'array', min: 1 },
            education: { required: true, type: 'array', min: 1 },
            skills: { required: true, type: 'text' },
            portfolio: { required: false, type: 'array', min: 0 }
        }
    },
    'academic-standard': {
        name: 'Academic Standard CV',
        type: 'academic',
        fields: ['personal_details', 'research_interests', 'education', 'experience', 'publications', 'grants', 'teaching', 'references'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location'] },
            research_interests: { required: true, type: 'text' },
            education: { required: true, type: 'array', min: 1 },
            experience: { required: false, type: 'array', min: 0 },
            publications: { required: false, type: 'array', min: 0 },
            grants: { required: false, type: 'array', min: 0 },
            teaching: { required: false, type: 'array', min: 0 },
            references: { required: true, type: 'array', min: 2 }
        }
    },
    'research-scientist': {
        name: 'Research Scientist CV',
        type: 'academic',
        fields: ['personal_details', 'research_interests', 'education', 'experience', 'publications', 'grants', 'references'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location'] },
            research_interests: { required: true, type: 'text' },
            education: { required: true, type: 'array', min: 1 },
            experience: { required: true, type: 'array', min: 1 },
            publications: { required: true, type: 'array', min: 1 },
            grants: { required: false, type: 'array', min: 0 },
            references: { required: true, type: 'array', min: 2 }
        }
    },
    'teaching-faculty': {
        name: 'Teaching-Focused Faculty CV',
        type: 'academic',
        fields: ['personal_details', 'research_interests', 'education', 'teaching', 'publications', 'references'],
        field_details: {
            personal_details: { required: true, type: 'object', fields: ['fullName', 'email', 'phone', 'location'] },
            research_interests: { required: true, type: 'text' },
            education: { required: true, type: 'array', min: 1 },
            teaching: { required: true, type: 'array', min: 1 },
            publications: { required: false, type: 'array', min: 0 },
            references: { required: true, type: 'array', min: 2 }
        }
    }
};


const CONVERSATION_FLOW = {
    professional: [
        'welcome',
        'personal_details',
        'summary',
        'experience',
        'education',
        'skills',
        'template_specific', 
        'completion'
    ],
    academic: [
        'welcome',
        'personal_details',
        'research_interests',
        'education',
        'experience',
        'publications',
        'grants',
        'teaching',
        'references',
        'completion'
    ]
};


const FIELD_LABELS = {
    personal_details: 'Personal Information',
    summary: 'Professional Summary',
    experience: 'Work Experience',
    education: 'Education',
    skills: 'Skills',
    projects: 'Projects',
    achievements: 'Key Achievements',
    portfolio: 'Portfolio Items',
    board: 'Board Memberships',
    research_interests: 'Research Interests',
    publications: 'Publications',
    grants: 'Grants & Funding',
    teaching: 'Teaching Experience',
    references: 'References'
};


function getTemplateConfig(templateName) {
    return TEMPLATE_CONFIGS[templateName] || TEMPLATE_CONFIGS['classic'];
}


function getNextStage(currentStage, templateType) {
    const flow = CONVERSATION_FLOW[templateType] || CONVERSATION_FLOW.professional;
    const currentIndex = flow.indexOf(currentStage);
    if (currentIndex === -1 || currentIndex === flow.length - 1) {
        return 'completion';
    }
    return flow[currentIndex + 1];
}


function isFieldRequired(templateName, fieldName) {
    const config = getTemplateConfig(templateName);
    return config.field_details[fieldName]?.required || false;
}
