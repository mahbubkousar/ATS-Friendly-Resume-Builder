(function installAiConsentControls() {
    'use strict';

    let consentGranted = false;

    const overlay = document.createElement('div');
    overlay.className = 'ai-consent-overlay';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-labelledby', 'aiConsentTitle');

    const dialog = document.createElement('div');
    dialog.className = 'ai-consent-dialog';

    const title = document.createElement('h2');
    title.id = 'aiConsentTitle';
    title.textContent = 'AI processing privacy notice';

    const description = document.createElement('p');
    description.textContent = 'When you use an AI feature, ResumeSync sends only the resume, job description, or prompt needed for that request to Google Gemini for processing.';

    const details = document.createElement('ul');
    [
        'AI processing is optional; core resume editing remains available without it.',
        'Avoid entering information that is not needed for the requested result.',
        'Consent can be revoked here at any time; future AI requests will then be blocked.'
    ].forEach(text => {
        const item = document.createElement('li');
        item.textContent = text;
        details.appendChild(item);
    });

    const actions = document.createElement('div');
    actions.className = 'ai-consent-actions';

    const declineButton = document.createElement('button');
    declineButton.type = 'button';
    declineButton.className = 'ai-consent-decline';
    declineButton.textContent = 'Not now';

    const allowButton = document.createElement('button');
    allowButton.type = 'button';
    allowButton.className = 'ai-consent-allow';
    allowButton.textContent = 'Allow AI processing';

    actions.append(declineButton, allowButton);
    dialog.append(title, description, details, actions);
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    const privacyButton = document.createElement('button');
    privacyButton.type = 'button';
    privacyButton.className = 'ai-privacy-button';
    privacyButton.textContent = 'AI privacy';
    document.body.appendChild(privacyButton);

    function setOpen(open) {
        overlay.classList.toggle('is-open', open);
    }

    async function updateConsent(action) {
        const response = await fetch(appUrl('api/ai-consent.php'), {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action})
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.error || result.message || 'Unable to update consent');
        }
        consentGranted = result.granted === true;
        setOpen(false);
    }

    allowButton.addEventListener('click', async () => {
        allowButton.disabled = true;
        try {
            await updateConsent('grant');
        } catch (error) {
            console.error('AI consent update failed:', error);
            allowButton.disabled = false;
        }
    });

    declineButton.addEventListener('click', async () => {
        if (consentGranted) {
            try {
                await updateConsent('revoke');
            } catch (error) {
                console.error('AI consent revocation failed:', error);
            }
        } else {
            setOpen(false);
        }
    });

    privacyButton.addEventListener('click', () => {
        declineButton.textContent = consentGranted ? 'Revoke consent' : 'Not now';
        setOpen(true);
    });

    fetch(appUrl('api/ai-consent.php'))
        .then(response => response.json())
        .then(result => {
            consentGranted = result.success === true && result.granted === true;
            if (!consentGranted) setOpen(true);
        })
        .catch(error => console.error('Unable to load AI consent status:', error));
})();
