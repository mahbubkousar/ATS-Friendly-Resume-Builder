
document.documentElement.style.scrollBehavior = 'smooth';


document.addEventListener('DOMContentLoaded', function() {
    
    const navContent = document.querySelector('.nav-content');
    const navLinks = document.querySelector('.nav-links');

    if (navContent && !document.querySelector('.mobile-menu-toggle')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'mobile-menu-toggle';
        toggleBtn.setAttribute('aria-label', 'Toggle mobile menu');
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';

        
        navContent.insertBefore(toggleBtn, navLinks);

        
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            const icon = toggleBtn.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });

        
        document.addEventListener('click', function(e) {
            if (!navContent.contains(e.target) && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                toggleBtn.querySelector('i').className = 'fas fa-bars';
            }
        });

        
        document.querySelectorAll('.nav-link, .nav-cta').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 480) {
                    navLinks.classList.remove('active');
                    toggleBtn.querySelector('i').className = 'fas fa-bars';
                }
            });
        });
    }
});


const featureCards = document.querySelectorAll('.feature-card');
featureCards.forEach(card => {
    card.addEventListener('click', function(e) {
        const ripple = document.createElement('div');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(124, 58, 237, 0.2)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s ease-out';
        ripple.style.pointerEvents = 'none';

        this.style.position = 'relative';
        this.style.overflow = 'hidden';
        this.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    });
});


const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);


const getStartedBtn = document.getElementById('getStartedBtn');
const navCtaBtn = document.getElementById('navCtaBtn');

function handleStartBuilding() {
    
    showNotification('Redirecting to login...');

    
    setTimeout(() => {
        window.location.href = 'login.php';
    }, 500);
}



function showNotification(message, type = 'info') {
    
    if (typeof showNotificationModal === 'function') {
        showNotificationModal(message, type);
        return;
    }

    
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #1a202c;
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 16px;
        box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.3);
        font-family: 'Montserrat', sans-serif;
        font-weight: 400;
        letter-spacing: 0.01em;
        z-index: 1000;
        animation: slideInRight 0.4s ease, fadeOut 0.4s ease 2.6s forwards;
        max-width: 320px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    `;

    document.body.appendChild(notification);

    setTimeout(() => notification.remove(), 3000);
}


const notificationStyle = document.createElement('style');
notificationStyle.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        to {
            opacity: 0;
            transform: translateX(400px);
        }
    }
`;
document.head.appendChild(notificationStyle);

if (getStartedBtn) {
    getStartedBtn.addEventListener('click', handleStartBuilding);
}
if (navCtaBtn) {
    navCtaBtn.addEventListener('click', handleStartBuilding);
}


document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        if (link.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            const targetId = link.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});


const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);


const allFeatureCards = document.querySelectorAll('.feature-card');
allFeatureCards.forEach((card, index) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = `all 0.6s ease ${index * 0.08}s`;
    scrollObserver.observe(card);
});


function animateNumbers() {
    const numbers = document.querySelectorAll('.feature-number');

    numbers.forEach((number, index) => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !number.classList.contains('animated')) {
                    const finalNumber = index + 1;
                    const duration = 1000;
                    const steps = 20;
                    const increment = finalNumber / steps;
                    const stepDuration = duration / steps;
                    let current = 0;

                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= finalNumber) {
                            number.textContent = '0' + finalNumber;
                            clearInterval(timer);
                        } else {
                            number.textContent = '0' + Math.floor(current);
                        }
                    }, stepDuration);

                    number.classList.add('animated');
                    observer.unobserve(number);
                }
            });
        }, observerOptions);

        observer.observe(number);
    });
}


animateNumbers();


featureCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.borderColor = '#718096';
    });

    card.addEventListener('mouseleave', function() {
        this.style.borderColor = '#e2e8f0';
    });
});


document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
        if (document.activeElement.classList.contains('feature-card')) {
            e.preventDefault();
            document.activeElement.click();
        }
    }
});


featureCards.forEach(card => {
    card.setAttribute('tabindex', '0');
    card.setAttribute('role', 'button');
});

console.log('ATS Resume Builder - Landing Page Loaded');
console.log('Built with vanilla JavaScript - No frameworks or libraries!');
