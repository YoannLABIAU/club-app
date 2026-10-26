/**
 * @fileoverview Script frontend gérant la soumission asynchrone de l'authentification staff.
 */

document.addEventListener('DOMContentLoaded', initLoginForm);

/**
 * Initialise l'écouteur d'événement sur le formulaire de connexion.
 * 
 * @returns {void}
 */
function initLoginForm() {
    const form = document.getElementById('form-login');
    if (form) {
        form.addEventListener('submit', handleLoginSubmit);
    }
}

/**
 * Traite la tentative de connexion via l'API Fetch.
 * Gère le retour utilisateur via Toast et redirige en cas de succès.
 * 
 * @param {Event} event 
 * @returns {Promise<void>}
 */
async function handleLoginSubmit(event) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = document.getElementById('btn-submit-login');
    
    submitBtn.disabled = true;
    submitBtn.innerText = "Vérification...";

    const payload = {
        email: form.elements['email'].value,
        password: form.elements['password'].value
    };

    try {
        const response = await fetch('/admin/login/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            // Succès : Redirection immédiate
            if (window.Toast) window.Toast.show("Connexion réussie, redirection...", 'success', 1500);
            setTimeout(() => {
                window.location.href = result.redirect || '/admin';
            }, 500);
        } else {
            // Échec d'authentification
            if (window.Toast) {
                window.Toast.show(result.message || "Identifiants invalides.", 'error', 4000);
            }
            form.elements['password'].value = ''; // Réinitialisation du mot de passe
            submitBtn.disabled = false;
            submitBtn.innerText = "Se connecter";
        }
    } catch (error) {
        console.error('Erreur réseau:', error);
        if (window.Toast) {
            window.Toast.show("Erreur de communication avec le serveur.", 'error', 5000);
        }
        submitBtn.disabled = false;
        submitBtn.innerText = "Se connecter";
    }
}