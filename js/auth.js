/**
 * Validation côté client pour les formulaires d'authentification
 */

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');

    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }

    if (registerForm) {
        registerForm.addEventListener('submit', validateRegisterForm);
        
        // Validation en temps réel pour la confirmation de mot de passe
        const passwordConfirm = document.getElementById('password_confirm');
        const password = document.getElementById('password');
        
        if (passwordConfirm && password) {
            passwordConfirm.addEventListener('input', () => {
                validatePasswordMatch(password, passwordConfirm);
            });
            
            password.addEventListener('input', () => {
                validatePasswordMatch(password, passwordConfirm);
            });
        }
    }

    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', validateForgotPasswordForm);
    }

    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', validateResetPasswordForm);
        
        // Validation en temps réel pour la confirmation de mot de passe
        const passwordConfirm = document.getElementById('password_confirm');
        const password = document.getElementById('password');
        
        if (passwordConfirm && password) {
            passwordConfirm.addEventListener('input', () => {
                validatePasswordMatch(password, passwordConfirm);
            });
            
            password.addEventListener('input', () => {
                validatePasswordMatch(password, passwordConfirm);
            });
        }
    }
});

/**
 * Valide le formulaire de connexion
 */
function validateLoginForm(e) {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    let isValid = true;

    // Validation email
    if (!email.value || !isValidEmail(email.value)) {
        showError(email, 'Veuillez entrer une adresse email valide');
        isValid = false;
    } else {
        clearError(email);
    }

    // Validation mot de passe
    if (!password.value || password.value.length < 1) {
        showError(password, 'Le mot de passe est obligatoire');
        isValid = false;
    } else {
        clearError(password);
    }

    if (!isValid) {
        e.preventDefault();
    }
}

/**
 * Valide le formulaire d'inscription
 */
function validateRegisterForm(e) {
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    let isValid = true;

    // Validation nom
    if (!name.value || name.value.trim().length < 2) {
        showError(name, 'Le nom doit contenir au moins 2 caractères');
        isValid = false;
    } else {
        clearError(name);
    }

    // Validation email
    if (!email.value || !isValidEmail(email.value)) {
        showError(email, 'Veuillez entrer une adresse email valide');
        isValid = false;
    } else {
        clearError(email);
    }

    // Validation mot de passe
    if (!password.value || password.value.length < 8) {
        showError(password, 'Le mot de passe doit contenir au moins 8 caractères');
        isValid = false;
    } else {
        clearError(password);
    }

    // Validation confirmation mot de passe
    if (!passwordConfirm.value) {
        showError(passwordConfirm, 'Veuillez confirmer votre mot de passe');
        isValid = false;
    } else if (password.value !== passwordConfirm.value) {
        showError(passwordConfirm, 'Les mots de passe ne correspondent pas');
        isValid = false;
    } else {
        clearError(passwordConfirm);
    }

    if (!isValid) {
        e.preventDefault();
    }
}

/**
 * Valide la correspondance des mots de passe
 */
function validatePasswordMatch(password, passwordConfirm) {
    if (passwordConfirm.value && password.value !== passwordConfirm.value) {
        showError(passwordConfirm, 'Les mots de passe ne correspondent pas');
    } else if (passwordConfirm.value) {
        clearError(passwordConfirm);
    }
}

/**
 * Vérifie si une adresse email est valide
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Affiche une erreur sur un champ
 */
function showError(field, message) {
    field.classList.add('is-invalid');
    const feedback = field.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    }
}

/**
 * Efface l'erreur d'un champ
 */
function clearError(field) {
    field.classList.remove('is-invalid');
}

/**
 * Valide le formulaire de mot de passe oublié
 */
function validateForgotPasswordForm(e) {
    const email = document.getElementById('email');
    let isValid = true;

    if (!email.value || !isValidEmail(email.value)) {
        showError(email, 'Veuillez entrer une adresse email valide');
        isValid = false;
    } else {
        clearError(email);
    }

    if (!isValid) {
        e.preventDefault();
    }
}

/**
 * Valide le formulaire de réinitialisation de mot de passe
 */
function validateResetPasswordForm(e) {
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    let isValid = true;

    // Validation mot de passe
    if (!password.value || password.value.length < 8) {
        showError(password, 'Le mot de passe doit contenir au moins 8 caractères');
        isValid = false;
    } else {
        clearError(password);
    }

    // Validation confirmation mot de passe
    if (!passwordConfirm.value) {
        showError(passwordConfirm, 'Veuillez confirmer votre mot de passe');
        isValid = false;
    } else if (password.value !== passwordConfirm.value) {
        showError(passwordConfirm, 'Les mots de passe ne correspondent pas');
        isValid = false;
    } else {
        clearError(passwordConfirm);
    }

    if (!isValid) {
        e.preventDefault();
    }
}

