// Fonctions pour register.php
function togglePassword(id, icon) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-fill');
        icon.classList.add('bi-eye-slash-fill');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye-fill');
    }
}

function handleClose() {
    if (confirm('Voulez-vous vraiment quitter? Toutes les donnees non enregistrees seront perdues.')) {
        window.location.href = 'index.php';
    }
}

// Password strength meter
function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function(e) {
            const password = e.target.value;
            const meter = document.getElementById('strengthMeter');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            meter.className = 'strength-meter';
            if (strength === 0) {
                meter.style.width = '0%';
            } else if (strength <= 2) {
                meter.classList.add('strength-weak');
            } else if (strength === 3) {
                meter.classList.add('strength-medium');
            } else {
                meter.classList.add('strength-strong');
            }
        });
    }
}

// Form validation
function initFormValidation() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caracteres!');
                return false;
            }

            // Check reCAPTCHA
            if (typeof grecaptcha !== 'undefined') {
                const recaptchaResponse = grecaptcha.getResponse();
                if (recaptchaResponse.length === 0) {
                    e.preventDefault();
                    alert('Veuillez completer le reCAPTCHA!');
                    return false;
                }
            }
        });
    }
}

// Auto-hide success message
function initAutoHideSuccess() {
    setTimeout(function() {
        const alert = document.querySelector('.alert-success');
        if (alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 5000);
}

// Fonctions pour verify.php
function createConfetti() {
    const colors = ['#667eea', '#764ba2', '#00C851', '#ffbb33', '#ff4444'];
    const confettiCount = 50;

    for (let i = 0; i < confettiCount; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.top = '-10px';
            confetti.style.opacity = '1';
            confetti.style.borderRadius = '50%';
            confetti.style.pointerEvents = 'none';
            confetti.style.zIndex = '9999';
            
            document.body.appendChild(confetti);

            const animation = confetti.animate([
                { 
                    transform: 'translateY(0) rotate(0deg)',
                    opacity: 1
                },
                { 
                    transform: `translateY(${window.innerHeight + 20}px) rotate(${Math.random() * 720}deg)`,
                    opacity: 0
                }
            ], {
                duration: 3000 + Math.random() * 2000,
                easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
            });

            animation.onfinish = () => confetti.remove();
        }, i * 30);
    }
}

function initVerifyPage() {
    // confetti
    if (document.querySelector('.success-icon')) {
        setTimeout(createConfetti, 500);
    }
}

// Initialisation 
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser  register.php
    initPasswordStrength();
    initFormValidation();
    initAutoHideSuccess();
    
    // Initialiser verify.php
    initVerifyPage();
});