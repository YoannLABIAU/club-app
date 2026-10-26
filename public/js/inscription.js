/**
 * @fileoverview Gère la soumission asynchrone du formulaire d'inscription complet.
 * Intercepte les données, formate les tableaux (checkboxes multiples) et transmet via Fetch.
 */

document.addEventListener('DOMContentLoaded', initInscriptionForm);

/**
 * Initialise l'écouteur d'événement sur le formulaire d'inscription complexe.
 * 
 * @returns {void}
 */
function initInscriptionForm() {
    const form = document.getElementById('form-inscription');
    if (form) {
        form.addEventListener('submit', handleInscriptionSubmit);
    }
}

/**
 * Compile les données du formulaire et exécute la requête POST.
 * 
 * @param {Event} event 
 * @returns {Promise<void>}
 */
async function handleInscriptionSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('btn-submit-inscription');
    
    submitBtn.disabled = true;
    submitBtn.innerText = "Traitement en cours...";

    // Création dynamique du payload depuis FormData
    const formData = new FormData(form);
    const payload = {};

    // Parcours de tous les champs
    for (let [key, value] of formData.entries()) {
        // Gestion spécifique des tableaux (ex: disciplines[])
        if (key.endsWith('[]')) {
            const cleanKey = key.slice(0, -2);
            if (!payload[cleanKey]) payload[cleanKey] = [];
            payload[cleanKey].push(value);
        } else {
            payload[key] = value;
        }
    }

    // Gestion des checkboxes booléennes non cochées (FormData les ignore nativement)
    // Le champ paiement_valide a été retiré, il sera géré côté administration.
    const booleans = ['autorisation_soins', 'droit_image', 'reduction_appliquee'];
    booleans.forEach(field => {
        if (!payload[field]) payload[field] = "0";
    });

    try {
        const response = await fetch('/inscription', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            if (window.Toast) window.Toast.show(result.message, 'success', 5000);
            form.reset();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            if (window.Toast) window.Toast.show(result.message || "Erreur lors de l'enregistrement.", 'error', 6000);
        }
    } catch (error) {
        console.error('Erreur réseau:', error);
        if (window.Toast) window.Toast.show("Erreur de communication avec le serveur.", 'error', 6000);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = "Enregistrer l'inscription";
    }
}