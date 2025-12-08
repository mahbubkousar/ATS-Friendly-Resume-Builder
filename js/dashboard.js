





































const sidebarLinks = document.querySelectorAll('.sidebar-link');
const tabContents = document.querySelectorAll('.tab-content');


function switchTab(tabId) {
    
    tabContents.forEach(tab => tab.classList.remove('active'));

    
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }

    
    sidebarLinks.forEach(l => l.classList.remove('active'));
    const activeLink = document.querySelector(`.sidebar-link[href="#${tabId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

sidebarLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        
        if (link.getAttribute('href').startsWith('#')) {
            e.preventDefault();

            const section = link.getAttribute('href').substring(1);
            switchTab(section);
        }
    });
});


const shareButtons = document.querySelectorAll('.btn-share');
const deleteButtons = document.querySelectorAll('.btn-delete');

// Download buttons removed - downloads only available from editor panel

shareButtons.forEach((btn) => {
    btn.addEventListener('click', async (e) => {
        const resumeId = e.target.closest('.btn-share-resume')?.dataset.resumeId;

        if (resumeId) {
            console.log('Share button clicked for resume ID:', resumeId);
            openShareModal(resumeId);
        } else {
            console.error('Share button clicked but no resume ID found');
            console.log('Share feature coming soon!');
        }
    });
});


deleteButtons.forEach((btn) => {
    btn.addEventListener('click', async (e) => {
        e.preventDefault(); 

        const button = e.target.closest('.btn-delete-resume');

        if (!button) {
            console.log('Delete button not found');
            return;
        }

        const resumeId = button.dataset.resumeId;
        const resumeTitle = button.dataset.resumeTitle;

        console.log('Delete clicked:', { resumeId, resumeTitle });

        if (resumeId) {
            
            const confirmed = await showConfirmationModal(
                `Are you sure you want to delete "${resumeTitle}"?\n\nThis action cannot be undone.`,
                'Delete Resume'
            );

            console.log('User confirmed:', confirmed);

            if (confirmed) {
                await deleteResume(resumeId, button);
            }
        }
    });
});















async function deleteResume(resumeId, button) {
    try {
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

        
        const response = await fetch('/ATS/api/delete-resume.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ resume_id: resumeId })
        });

        const result = await response.json();

        if (result.success) {
            console.log(`Resume "${result.resume_title}" deleted successfully!`, 'success');

            
            
            
            
            
            const resumeCard = button.closest('.resume-card');
            if (resumeCard) {
                
                resumeCard.style.opacity = '0';
                resumeCard.style.transform = 'scale(0.9)';
                resumeCard.style.transition = 'all 0.3s ease';

                
                setTimeout(() => {
                    resumeCard.remove();

                    
                    const remainingCards = document.querySelectorAll('.resume-card');
                    if (remainingCards.length === 0) {
                        
                        location.reload();
                    }
                }, 300);
            }
        } else {
            
            console.log('Error: ' + result.message, 'error');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash"></i> Delete';
        }
    } catch (error) {
        
        console.error('Delete error:', error);
        console.log('Failed to delete resume. Please try again.', 'error');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }
}
































let currentResumeId = null;    
let currentShareUrl = null;     


async function openShareModal(resumeId) {
    console.log('=== Opening Share Modal ===');
    console.log('Resume ID received:', resumeId);

    
    
    
    
    
    currentResumeId = resumeId;
    currentShareUrl = null;

    console.log('Current resume ID set to:', currentResumeId);

    
    document.getElementById('shareLink').value = '';
    document.getElementById('viewCount').textContent = '0';
    document.getElementById('downloadCount').textContent = '0';
    document.getElementById('statusBadge').textContent = 'Private';
    document.getElementById('shareContent').style.display = 'block';
    document.getElementById('shareLinkSection').style.display = 'none';

    
    document.getElementById('shareModal').style.display = 'flex';

    
    
    
    
    
    
    try {
        console.log('Fetching share stats for resume ID:', resumeId);
        const response = await fetch(`/ATS/api/get-share-stats.php?resume_id=${resumeId}`);
        const result = await response.json();
        console.log('Share stats response:', result);

        
        
        
        
        
        if (currentResumeId !== resumeId) {
            console.log('Resume ID changed while loading, ignoring stale response');
            return; 
        }

        if (result.success && result.has_link) {
            
            
            
            currentShareUrl = result.share_url;
            document.getElementById('shareLink').value = result.share_url;
            document.getElementById('viewCount').textContent = result.stats.view_count;
            document.getElementById('downloadCount').textContent = result.stats.download_count;
            document.getElementById('statusBadge').textContent = result.is_public ? 'Public' : 'Private';

            
            const toggleBtn = document.getElementById('togglePublicBtn');
            if (result.is_public) {
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> <span id="publicToggleText">Make Private</span>';
            } else {
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i> <span id="publicToggleText">Make Public</span>';
            }

            
            document.getElementById('shareContent').style.display = 'none';
            document.getElementById('shareLinkSection').style.display = 'block';
        } else {
            
            
            
            document.getElementById('shareContent').style.display = 'block';
            document.getElementById('shareLinkSection').style.display = 'none';
        }
    } catch (error) {
        console.error('Error fetching share stats:', error);
        
        document.getElementById('shareContent').style.display = 'block';
        document.getElementById('shareLinkSection').style.display = 'none';
    }
}

function closeShareModal() {
    document.getElementById('shareModal').style.display = 'none';
    currentResumeId = null;
    currentShareUrl = null;
}


document.getElementById('generateLinkBtn')?.addEventListener('click', async () => {
    if (!currentResumeId) {
        console.error('No resume ID set for share link generation');
        console.log('Error: No resume selected');
        return;
    }

    const resumeIdToGenerate = currentResumeId; 

    try {
        const response = await fetch('/ATS/api/generate-share-link.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ resume_id: resumeIdToGenerate })
        });

        const result = await response.json();

        
        if (currentResumeId !== resumeIdToGenerate) {
            console.log('Resume ID changed while generating link, ignoring stale response');
            return;
        }

        if (result.success) {
            currentShareUrl = result.share_url;
            document.getElementById('shareLink').value = result.share_url;
            document.getElementById('viewCount').textContent = result.stats.view_count;
            document.getElementById('downloadCount').textContent = result.stats.download_count;
            document.getElementById('statusBadge').textContent = result.is_public ? 'Public' : 'Private';

            
            const toggleBtn = document.getElementById('togglePublicBtn');
            if (result.is_public) {
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> <span id="publicToggleText">Make Private</span>';
            } else {
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i> <span id="publicToggleText">Make Public</span>';
            }

            document.getElementById('shareContent').style.display = 'none';
            document.getElementById('shareLinkSection').style.display = 'block';

            if (result.is_new_link) {
                console.log('Share link generated successfully!');
            } else {
                console.log('Existing share link loaded!');
            }
        } else {
            console.log('Failed to generate share link: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error generating share link:', error);
        console.log('Error generating share link');
    }
});


document.getElementById('copyLinkBtn')?.addEventListener('click', () => {
    const linkInput = document.getElementById('shareLink');
    linkInput.select();
    document.execCommand('copy');
    console.log('Link copied to clipboard!');
});


document.getElementById('togglePublicBtn')?.addEventListener('click', async () => {
    if (!currentResumeId) {
        console.error('No resume ID set for toggle');
        console.log('Error: No resume selected');
        return;
    }

    const resumeIdToToggle = currentResumeId; 

    try {
        const response = await fetch('/ATS/api/toggle-resume-public.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ resume_id: resumeIdToToggle })
        });

        const result = await response.json();

        
        if (currentResumeId !== resumeIdToToggle) {
            console.log('Resume ID changed while toggling, ignoring stale response');
            return;
        }

        if (result.success) {
            const toggleBtn = document.getElementById('togglePublicBtn');
            const statusBadge = document.getElementById('statusBadge');

            if (result.is_public) {
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> <span id="publicToggleText">Make Private</span>';
                statusBadge.textContent = 'Public';
                statusBadge.parentElement.style.background = '#d1fae5';
                statusBadge.style.color = '#065f46';
            } else {
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i> <span id="publicToggleText">Make Public</span>';
                statusBadge.textContent = 'Private';
                statusBadge.parentElement.style.background = '#fee2e2';
                statusBadge.style.color = '#991b1b';
            }

            console.log(result.message);
        } else {
            console.log('Failed to toggle resume visibility');
        }
    } catch (error) {
        console.error('Error toggling visibility:', error);
        console.log('Error toggling resume visibility');
    }
});


document.getElementById('openLinkBtn')?.addEventListener('click', () => {
    if (currentShareUrl) {
        window.open(currentShareUrl, '_blank');
    }
});


const createResumeBtn = document.getElementById('navCtaBtn');
if (createResumeBtn) {
    createResumeBtn.addEventListener('click', () => {
        console.log('Opening resume builder...');
        setTimeout(() => {
            window.location.href = 'editor.php';
        }, 500);
    });
}


function animateStats() {
    const statNumbers = document.querySelectorAll('.stat-number');

    statNumbers.forEach(stat => {
        const text = stat.textContent;
        const isPercentage = text.includes('%');
        const finalValue = parseInt(text);

        if (!isNaN(finalValue)) {
            let current = 0;
            const increment = finalValue / 30;
            const duration = 1000;
            const stepTime = duration / 30;

            const timer = setInterval(() => {
                current += increment;
                if (current >= finalValue) {
                    stat.textContent = isPercentage ? finalValue + '%' : finalValue;
                    clearInterval(timer);
                } else {
                    stat.textContent = isPercentage ? Math.floor(current) + '%' : Math.floor(current);
                }
            }, stepTime);
        }
    });
}


window.addEventListener('load', () => {
    setTimeout(animateStats, 300);
});


const resumeCards = document.querySelectorAll('.resume-card');

resumeCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
    });

    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});


function updateGreeting() {
    const greeting = document.querySelector('.dashboard-greeting');
    if (!greeting) return;

    const hour = new Date().getHours();
    let timeGreeting;

    if (hour < 12) {
        timeGreeting = 'Good morning';
    } else if (hour < 18) {
        timeGreeting = 'Good afternoon';
    } else {
        timeGreeting = 'Good evening';
    }

    
    const currentText = greeting.textContent;
    const nameMatch = currentText.match(/Welcome back, (.+?)!/);
    const userName = nameMatch ? nameMatch[1] : 'User';

    greeting.textContent = `${timeGreeting}, ${userName}!`;
}

updateGreeting();


document.addEventListener('keydown', (e) => {
    
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        createResumeBtn.click();
    }

    
    if ((e.ctrlKey || e.metaKey) && e.key >= '1' && e.key <= '6') {
        e.preventDefault();
        const index = parseInt(e.key) - 1;
        if (sidebarLinks[index]) {
            sidebarLinks[index].click();
        }
    }
});


const filterButtons = document.querySelectorAll('.filter-btn');
filterButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        filterButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filter = this.getAttribute('data-filter');
        console.log(`Filtering resumes: ${filter}`);
    });
});


const searchInput = document.querySelector('.search-input');
if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        
        console.log('Searching for:', searchTerm);
    });
}


const templateButtons = document.querySelectorAll('.btn-use-template');
templateButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        const templateName = this.closest('.template-card').querySelector('.template-name').textContent;
        console.log(`Opening ${templateName} template...`);
        setTimeout(() => {
            window.location.href = 'editor.php';
        }, 500);
    });
});


const saveProfileBtn = document.querySelector('.btn-save-profile');
if (saveProfileBtn) {
    saveProfileBtn.addEventListener('click', function() {
        console.log('Profile saved successfully!');
    });
}


const changePhotoBtn = document.querySelector('.btn-change-photo');
if (changePhotoBtn) {
    changePhotoBtn.addEventListener('click', function() {
        console.log('Photo upload feature coming soon!');
    });
}


const toggleSwitches = document.querySelectorAll('.toggle-switch input');
toggleSwitches.forEach(toggle => {
    toggle.addEventListener('change', function() {
        const settingName = this.closest('.setting-item').querySelector('.setting-name').textContent;
        const status = this.checked ? 'enabled' : 'disabled';
        console.log(`${settingName} ${status}`);
    });
});


const settingActionBtns = document.querySelectorAll('.btn-setting-action');
settingActionBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        const settingName = this.closest('.setting-item').querySelector('.setting-name').textContent;
        console.log(`Opening ${settingName}...`);
    });
});


const deleteBtns = document.querySelectorAll('.btn-danger');
deleteBtns.forEach(btn => {
    btn.addEventListener('click', async function() {
        const resumeCard = this.closest('.resume-card');
        if (resumeCard) {
            const resumeTitle = resumeCard.querySelector('.resume-title').textContent;
            const confirmed = await showConfirmationModal(
                `Are you sure you want to delete "${resumeTitle}"?`,
                'Delete Resume'
            );
            if (confirmed) {
                resumeCard.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    resumeCard.remove();
                    console.log('Resume deleted successfully');
                }, 300);
            }
        } else {
            
            const confirmed = await showConfirmationModal(
                'Are you sure you want to delete your account? This action cannot be undone.',
                'Delete Account'
            );
            if (confirmed) {
                console.log('Account deletion initiated. Please check your email.');
            }
        }
    });
});


const fadeOutStyle = document.createElement('style');
fadeOutStyle.textContent = `
    @keyframes fadeOut {
        to {
            opacity: 0;
            transform: scale(0.9);
        }
    }
`;
document.head.appendChild(fadeOutStyle);


function loadProfileData() {
    const registrationData = localStorage.getItem('registrationData');
    if (registrationData) {
        const data = JSON.parse(registrationData);

        
        if (data.personal) {
            document.getElementById('profileFullname').value = data.account?.fullname || '';
            document.getElementById('profileEmail').value = data.account?.email || '';
            document.getElementById('profilePhone').value = data.personal?.phone || '';
            document.getElementById('profileDob').value = data.personal?.dob || '';
            document.getElementById('profileAddress').value = data.personal?.address || '';
            document.getElementById('profileCity').value = data.personal?.city || '';
            document.getElementById('profileState').value = data.personal?.state || '';
            document.getElementById('profileZipcode').value = data.personal?.zipcode || '';
            document.getElementById('profileCountry').value = data.personal?.country || '';
            document.getElementById('profileProfessionalTitle').value = data.personal?.['professional-title'] || '';
            document.getElementById('profileBio').value = data.personal?.bio || '';
        }

        
        loadProfileEducationEntries(data.education || []);

        
        loadProfileExperienceEntries(data.experience || []);
    }
}


function loadProfileEducationEntries(educations) {
    const list = document.getElementById('profileEducationList');

    if (educations.length === 0) {
        list.innerHTML = '<div class="empty-state"><i class="fas fa-graduation-cap"></i><p>No education added yet</p></div>';
        return;
    }

    list.innerHTML = '';
    educations.forEach((edu, index) => {
        const entry = createEducationEntry(edu, index);
        list.appendChild(entry);
    });
}


function createEducationEntry(edu, index) {
    const div = document.createElement('div');
    div.className = 'profile-entry';
    div.innerHTML = `
        <div class="profile-entry-header">
            <div class="profile-entry-info">
                <h4>${edu.institution || 'Institution Name'}</h4>
                <p>${edu.degree || 'Degree'} in ${edu.field || 'Field of Study'}</p>
                ${edu.startDate || edu.endDate ? `<p class="entry-duration">${edu.startDate || ''} - ${edu.endDate || 'Present'}</p>` : ''}
                ${edu.gpa ? `<p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0.25rem;">GPA: ${edu.gpa}</p>` : ''}
            </div>
            <div class="profile-entry-actions">
                <button class="btn-entry-action" onclick="editEducation(${index})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-entry-action delete" onclick="deleteEducation(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    return div;
}


function loadProfileExperienceEntries(experiences) {
    const list = document.getElementById('profileExperienceList');

    if (experiences.length === 0) {
        list.innerHTML = '<div class="empty-state"><i class="fas fa-briefcase"></i><p>No work experience added yet</p></div>';
        return;
    }

    list.innerHTML = '';
    experiences.forEach((exp, index) => {
        const entry = createExperienceEntry(exp, index);
        list.appendChild(entry);
    });
}


function createExperienceEntry(exp, index) {
    const div = document.createElement('div');
    div.className = 'profile-entry';
    div.innerHTML = `
        <div class="profile-entry-header">
            <div class="profile-entry-info">
                <h4>${exp.title || 'Job Title'}</h4>
                <p>${exp.company || 'Company Name'}${exp.location ? ` • ${exp.location}` : ''}</p>
                ${exp.startDate || exp.endDate ? `<p class="entry-duration">${exp.startDate || ''} - ${exp.current ? 'Present' : (exp.endDate || '')}</p>` : ''}
            </div>
            <div class="profile-entry-actions">
                <button class="btn-entry-action" onclick="editExperience(${index})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-entry-action delete" onclick="deleteExperience(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        ${exp.description ? `<div class="profile-entry-description">${exp.description}</div>` : ''}
    `;
    return div;
}


function addProfileEducation() {
    console.log('Add education form feature coming soon!');
}


function addProfileExperience() {
    console.log('Add experience form feature coming soon!');
}


function editEducation(index) {
    console.log('Edit education feature coming soon!');
}


async function deleteEducation(index) {
    const confirmed = await showConfirmationModal(
        'Are you sure you want to delete this education entry?',
        'Delete Education'
    );
    if (confirmed) {
        const data = JSON.parse(localStorage.getItem('registrationData') || '{}');
        data.education.splice(index, 1);
        localStorage.setItem('registrationData', JSON.stringify(data));
        loadProfileEducationEntries(data.education);
        console.log('Education entry deleted');
    }
}


function editExperience(index) {
    console.log('Edit experience feature coming soon!');
}


async function deleteExperience(index) {
    const confirmed = await showConfirmationModal(
        'Are you sure you want to delete this experience entry?',
        'Delete Experience'
    );
    if (confirmed) {
        const data = JSON.parse(localStorage.getItem('registrationData') || '{}');
        data.experience.splice(index, 1);
        localStorage.setItem('registrationData', JSON.stringify(data));
        loadProfileExperienceEntries(data.experience);
        console.log('Experience entry deleted');
    }
}


document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', (e) => {
        if (link.getAttribute('href') === '#profile') {
            loadProfileData();
        }
    });
});


window.addEventListener('load', () => {
    const currentTab = document.querySelector('.tab-content.active');
    if (currentTab && currentTab.id === 'profile') {
        loadProfileData();
    }
});

console.log('Dashboard loaded successfully');
console.log('Keyboard shortcuts:');
console.log('- Ctrl/Cmd + N: Create new resume');
console.log('- Ctrl/Cmd + 1-6: Navigate to sidebar sections');


const notificationBtn = document.getElementById('notificationBtn');
const notificationModal = document.getElementById('notificationModal');
const notificationOverlay = document.getElementById('notificationOverlay');
const closeNotificationModal = document.getElementById('closeNotificationModal');

function openNotificationModal() {
    notificationModal.classList.add('active');
    notificationOverlay.classList.add('active');
}

function closeNotificationModalFunc() {
    notificationModal.classList.remove('active');
    notificationOverlay.classList.remove('active');
}

if (notificationBtn) {
    notificationBtn.addEventListener('click', openNotificationModal);
}

if (closeNotificationModal) {
    closeNotificationModal.addEventListener('click', closeNotificationModalFunc);
}

if (notificationOverlay) {
    notificationOverlay.addEventListener('click', closeNotificationModalFunc);
}


const notificationItems = document.querySelectorAll('.notification-item');
notificationItems.forEach(item => {
    item.addEventListener('click', function() {
        this.classList.remove('unread');
        updateNotificationBadge();
    });
});


function updateNotificationBadge() {
    const unreadCount = document.querySelectorAll('.notification-item.unread').length;
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
}


document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && notificationModal.classList.contains('active')) {
        closeNotificationModalFunc();
    }
});


async function markAsReadAndView(notificationId, applicationId) {
    
    try {
        await fetch('/ATS/api/mark-notification-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        });
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }

    
    closeNotificationModalFunc();

    
    switchTab('applications');

    
    setTimeout(() => {
        const applicationCard = document.querySelector(`.application-card[data-application-id="${applicationId}"]`);
        if (applicationCard) {
            applicationCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            applicationCard.style.boxShadow = '0 0 0 3px rgba(124, 58, 237, 0.3)';
            setTimeout(() => {
                applicationCard.style.boxShadow = '';
            }, 2000);
        }
    }, 300);

    
    setTimeout(() => {
        location.reload();
    }, 2500);
}


function viewApplication(applicationId) {
    markAsReadAndView(null, applicationId);
}
