document.addEventListener('DOMContentLoaded', function() {
    const progressModal = document.getElementById('analysisProgressModal');


    const isEditorPage = document.getElementById('resumePreview') !== null;


    if (!progressModal && isEditorPage) {
        console.error('ERROR: Progress modal (#analysisProgressModal) not found in DOM!');
    } else if (progressModal) {
        console.log('✓ Progress modal loaded successfully');
    }
});


function showProgressModal() {
    const modal = document.getElementById('analysisProgressModal');
    if (!modal) {
        console.error('Progress modal not found in DOM');
        return;
    }
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    
    updateProgress(0, 'Initializing analysis...');
}


function hideProgressModal() {
    const modal = document.getElementById('analysisProgressModal');
    if (!modal) {
        console.error('Progress modal not found in DOM');
        return;
    }
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


function showConfirmationModal(message, title = 'Confirm Action') {
    return new Promise((resolve) => {
        console.log('showConfirmationModal called with:', { message, title });

        const modal = document.getElementById('confirmationModal');
        const modalContent = document.getElementById('confirmationModalContent');
        const modalTitle = document.getElementById('confirmationTitle');
        const modalMessage = document.getElementById('confirmationMessage');
        const okBtn = document.getElementById('confirmationOkBtn');
        const cancelBtn = document.getElementById('confirmationCancelBtn');

        console.log('Modal elements found:', {
            modal: !!modal,
            modalContent: !!modalContent,
            modalTitle: !!modalTitle,
            modalMessage: !!modalMessage,
            okBtn: !!okBtn,
            cancelBtn: !!cancelBtn
        });

        if (!modal || !modalContent || !modalTitle || !modalMessage || !okBtn || !cancelBtn) {
            console.error('Confirmation modal elements not found, falling back to browser confirm');
            console.log('Missing elements:', {
                modal: !modal ? 'confirmationModal' : null,
                modalContent: !modalContent ? 'confirmationModalContent' : null,
                modalTitle: !modalTitle ? 'confirmationTitle' : null,
                modalMessage: !modalMessage ? 'confirmationMessage' : null,
                okBtn: !okBtn ? 'confirmationOkBtn' : null,
                cancelBtn: !cancelBtn ? 'confirmationCancelBtn' : null
            });
            resolve(confirm(message));
            return;
        }

        console.log('Setting modal content and showing...');

        modalTitle.textContent = title;
        modalMessage.textContent = message;

        
        modal.classList.add('active');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        
        const handleOk = () => {
            cleanup();
            resolve(true);
        };

        
        const handleCancel = () => {
            cleanup();
            resolve(false);
        };

        
        const handleClickOutside = (e) => {
            if (e.target === modal) {
                handleCancel();
            }
        };

        
        const handleEsc = (e) => {
            if (e.key === 'Escape') {
                handleCancel();
            }
        };

        
        const cleanup = () => {
            console.log('Cleaning up confirmation modal');
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            okBtn.removeEventListener('click', handleOk);
            cancelBtn.removeEventListener('click', handleCancel);
            document.removeEventListener('keydown', handleEsc);
            modal.removeEventListener('click', handleClickOutside);
        };

        
        okBtn.addEventListener('click', handleOk);
        cancelBtn.addEventListener('click', handleCancel);
        document.addEventListener('keydown', handleEsc);
        modal.addEventListener('click', handleClickOutside);
    });
}

