document.addEventListener('DOMContentLoaded', () => {
    class ThreeDEffect {
        constructor() {
            this.container = document.querySelector('.auth-container');
            this.box = document.querySelector('.auth-box');
            this.title = document.querySelector('h2');
            this.inputs = document.querySelectorAll('.floating input');
            this.button = document.querySelector('.btn-primary');
            this.mouseMoveActive = true;
            
            this.init();
        }

        init() {
            this.initializeFloatingEffects();
            this.initializeMouseMove();
            this.initializeInputEffects();
            this.initializeButtonEffects();
            this.initializeParallax();
            this.initializeEntryAnimation();
        }

        initializeFloatingEffects() {
            const animate = (element, delay = 0) => {
                gsap.to(element, {
                    duration: 2,
                    y: -10,
                    z: 20,
                    delay,
                    yoyo: true,
                    repeat: -1,
                    ease: "power1.inOut"
                });
            };

            animate(this.title, 0);
            this.inputs.forEach((input, index) => animate(input.parentElement, index * 0.2));
            animate(this.button, this.inputs.length * 0.2);
        }

        initializeMouseMove() {
            let rect = this.container.getBoundingClientRect();
            let mouseX = 0;
            let mouseY = 0;
            let centerX = rect.left + rect.width / 2;
            let centerY = rect.top + rect.height / 2;

            document.addEventListener('mousemove', (e) => {
                if (!this.mouseMoveActive) return;

                mouseX = e.clientX - centerX;
                mouseY = e.clientY - centerY;

                const rotateX = (mouseY / centerY) * 10;
                const rotateY = (mouseX / centerX) * 10;

                gsap.to(this.box, {
                    duration: 0.5,
                    rotateX: -rotateX,
                    rotateY: rotateY,
                    ease: "power2.out"
                });
            });

            this.container.addEventListener('mouseleave', () => {
                gsap.to(this.box, {
                    duration: 1,
                    rotateX: 0,
                    rotateY: 0,
                    ease: "elastic.out(1, 0.5)"
                });
            });
        }

        initializeInputEffects() {
            this.inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    this.mouseMoveActive = false;
                    const parent = input.parentElement;
                    
                    gsap.to(parent, {
                        duration: 0.3,
                        z: 50,
                        scale: 1.05,
                        ease: "power2.out"
                    });

                    gsap.to(input, {
                        duration: 0.3,
                        boxShadow: "0 15px 30px rgba(99, 102, 241, 0.2)",
                        ease: "power2.out"
                    });
                });

                input.addEventListener('blur', () => {
                    this.mouseMoveActive = true;
                    const parent = input.parentElement;
                    
                    gsap.to(parent, {
                        duration: 0.3,
                        z: 0,
                        scale: 1,
                        ease: "power2.in"
                    });

                    gsap.to(input, {
                        duration: 0.3,
                        boxShadow: "none",
                        ease: "power2.in"
                    });
                });
            });
        }

        initializeButtonEffects() {
            this.button.addEventListener('mouseenter', () => {
                gsap.to(this.button, {
                    duration: 0.3,
                    z: 50,
                    scale: 1.05,
                    ease: "power2.out"
                });
            });

            this.button.addEventListener('mouseleave', () => {
                gsap.to(this.button, {
                    duration: 0.3,
                    z: 0,
                    scale: 1,
                    ease: "power2.in"
                });
            });

            this.button.addEventListener('click', (e) => {
                if (!this.button.classList.contains('loading')) {
                    e.preventDefault();
                    this.button.classList.add('loading');

                    gsap.to(this.button, {
                        duration: 0.1,
                        scale: 0.95,
                        ease: "power2.in",
                        onComplete: () => {
                            gsap.to(this.button, {
                                duration: 0.1,
                                scale: 1,
                                ease: "power2.out"
                            });
                        }
                    });

                    // Simulating form validation
                    this.validateForm().then(isValid => {
                        if (isValid) {
                            this.submitForm();
                        } else {
                            this.showError();
                        }
                    });
                }
            });
        }

        initializeParallax() {
            const parallaxElements = document.querySelectorAll('[data-parallax]');
            
            document.addEventListener('mousemove', (e) => {
                const centerX = window.innerWidth / 2;
                const centerY = window.innerHeight / 2;
                const moveX = (e.clientX - centerX) / 50;
                const moveY = (e.clientY - centerY) / 50;

                parallaxElements.forEach(element => {
                    const speed = element.getAttribute('data-parallax');
                    const x = moveX * speed;
                    const y = moveY * speed;

                    gsap.to(element, {
                        duration: 0.5,
                        x,
                        y,
                        ease: "power2.out"
                    });
                });
            });
        }

        initializeEntryAnimation() {
            gsap.from(this.box, {
                duration: 1,
                y: 100,
                opacity: 0,
                rotateX: 20,
                ease: "power4.out"
            });

            gsap.from(this.inputs, {
                duration: 0.8,
                y: 50,
                opacity: 0,
                stagger: 0.1,
                delay: 0.5,
                ease: "power3.out"
            });

            gsap.from(this.button, {
                duration: 0.8,
                y: 50,
                opacity: 0,
                delay: 0.8,
                ease: "power3.out"
            });
        }

        async validateForm() {
            const inputs = Array.from(this.inputs);
            let isValid = true;

            for (let input of inputs) {
                if (!input.value) {
                    this.shakeElement(input);
                    isValid = false;
                }
            }

            return isValid;
        }

        shakeElement(element) {
            gsap.to(element, {
                duration: 0.1,
                x: 10,
                yoyo: true,
                repeat: 3,
                ease: "power2.inOut"
            });
        }

        async submitForm() {
            // Add your form submission logic here
            setTimeout(() => {
                this.button.classList.remove('loading');
                document.querySelector('form').submit();
            }, 1000);
        }

        showError() {
            this.button.classList.remove('loading');
            gsap.to(this.button, {
                duration: 0.2,
                backgroundColor: "#ef4444",
                ease: "power2.in",
                onComplete: () => {
                    setTimeout(() => {
                        gsap.to(this.button, {
                            duration: 0.2,
                            backgroundColor: "#6366f1",
                            ease: "power2.out"
                        });
                    }, 1000);
                }
            });
        }
    }

    // Initialize effects
    new ThreeDEffect();
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