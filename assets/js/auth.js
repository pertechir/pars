class ThreeDEffect {
    constructor() {
        // فقط در صفحات auth اجرا شود
        if (this.isAuthPage()) {
            this.init();
        }
    }

    isAuthPage() {
        return window.location.pathname.includes('auth') || 
               window.location.pathname.includes('login.php') || 
               window.location.pathname.includes('register.php');
    }

    init() {
        this.container = document.querySelector('.auth-container');
        this.card = document.querySelector('.auth-card');

        if (this.container && this.card) {
            this.initializeFloatingEffects();
            this.initializeMouseMove();
        }
    }

    animate(element, options) {
        if (element && window.gsap) {
            gsap.to(element, {
                duration: 0.3,
                ...options
            });
        }
    }

    initializeFloatingEffects() {
        if (this.card && window.gsap) {
            gsap.to(this.card, {
                y: 15,
                duration: 2,
                repeat: -1,
                yoyo: true,
                ease: "power1.inOut"
            });
        }
    }

    initializeMouseMove() {
        if (!this.container || !this.card) return;

        this.container.addEventListener('mousemove', (e) => {
            const rect = this.container.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const moveX = (mouseX - centerX) / 25;
            const moveY = (mouseY - centerY) / 25;

            this.animate(this.card, {
                rotateY: moveX,
                rotateX: -moveY,
                transformPerspective: 1000,
                ease: "power2.out"
            });
        });

        this.container.addEventListener('mouseleave', () => {
            this.animate(this.card, {
                rotateY: 0,
                rotateX: 0,
                transformPerspective: 1000,
                ease: "power2.out"
            });
        });
    }
}

// اجرای کلاس فقط در صورتی که در صفحه auth باشیم
document.addEventListener('DOMContentLoaded', () => {
    const currentPath = window.location.pathname;
    if (currentPath.includes('auth') || 
        currentPath.includes('login.php') || 
        currentPath.includes('register.php')) {
        new ThreeDEffect();
    }
});

// Additional utility functions
const createRipple = (e) => {
    const button = e.currentTarget;
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = `${size}px`;
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;
    ripple.classList.add('ripple');

    const existingRipple = button.querySelector('.ripple');
    if (existingRipple) {
        existingRipple.remove();
    }

    button.appendChild(ripple);
};

// Form validation utilities
const validateEmail = (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const validatePassword = (password) => {
    return password.length >= 8;
};

// Password strength indicator
class PasswordStrengthIndicator {
    constructor(passwordInput) {
        this.passwordInput = passwordInput;
        this.indicator = document.createElement('div');
        this.indicator.className = 'password-strength';
        this.passwordInput.parentElement.appendChild(this.indicator);
        
        this.init();
    }

    init() {
        this.passwordInput.addEventListener('input', () => {
            this.updateStrength(this.calculateStrength(this.passwordInput.value));
        });
    }

    calculateStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^A-Za-z0-9]/)) strength++;
        
        return strength;
    }

    updateStrength(strength) {
        const colors = ['#ef4444', '#f59e0b', '#10b981', '#6366f1'];
        const texts = ['ضعیف', 'متوسط', 'خوب', 'عالی'];
        
        this.indicator.style.width = `${(strength / 4) * 100}%`;
        this.indicator.style.backgroundColor = colors[strength - 1];
        this.indicator.textContent = texts[strength - 1];
    }
}

// Initialize password strength indicator
document.querySelectorAll('input[type="password"]').forEach(input => {
    new PasswordStrengthIndicator(input);
});

// Add ripple effect to all buttons
document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', createRipple);
});