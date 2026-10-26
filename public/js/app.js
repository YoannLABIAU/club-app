/**
 * @fileoverview Script frontend gérant le pointage asynchrone des adhérents.
 * Implémente l'API Fetch pour des mises à jour en temps réel sans rechargement.
 */

document.addEventListener('DOMContentLoaded', initPointage);

/**
 * Initialise les écouteurs d'événements sur tous les boutons d'action de pointage.
 * 
 * @returns {void}
 */
function initPointage() {
    const buttons = document.querySelectorAll('.action-pointage');
    buttons.forEach(button => {
        button.addEventListener('click', handlePointageClick);
    });
}

/**
 * Gère l'événement de clic sur un bouton de pointage.
 * Envoie la requête au serveur et déclenche la mise à jour visuelle.
 * 
 * @param {Event} event - L'événement de clic déclenché par l'utilisateur.
 * @returns {Promise<void>}
 */
async function handlePointageClick(event) {
    const clickedButton = event.currentTarget;
    const adherentId = clickedButton.getAttribute('data-id');
    const newStatut = clickedButton.getAttribute('data-statut'); // 'Present' ou 'Absent'
    const card = document.getElementById(`card-adherent-${adherentId}`);
    
    // Détection du statut actuel pour éviter les requêtes inutiles et calculer le compteur
    const presentButton = card.querySelector('[data-statut="Present"]');
    const isCurrentlyPresent = presentButton.classList.contains('bg-green-600');
    const oldStatut = isCurrentlyPresent ? 'Present' : (card.querySelector('[data-statut="Absent"]').classList.contains('bg-red-600') ? 'Absent' : 'None');

    if (newStatut === oldStatut) {
        return; // Le statut sélectionné est déjà actif, aucune action requise.
    }

    // Verrouillage de la carte pendant la requête (Anti-spam)
    card.classList.add('is-loading');

    try {
        const response = await fetch('/pointage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                adherent_id: adherentId,
                statut: newStatut
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            updateCardUI(card, newStatut);
            updateCounter(oldStatut, newStatut);
        } else {
            console.error('Erreur serveur:', result.message);
            alert("Erreur lors du pointage : " + (result.message || "Erreur inconnue."));
        }
    } catch (error) {
        console.error('Erreur réseau:', error);
        alert("Erreur de communication avec le serveur. Vérifiez votre connexion.");
    } finally {
        // Déverrouillage de la carte une fois la requête terminée (succès ou échec)
        card.classList.remove('is-loading');
    }
}

/**
 * Met à jour les classes CSS des boutons de la carte ciblée selon le nouveau statut.
 * 
 * @param {HTMLElement} card - L'élément HTML représentant la carte de l'adhérent.
 * @param {string} newStatut - Le statut appliqué ('Present' ou 'Absent').
 * @returns {void}
 */
function updateCardUI(card, newStatut) {
    const presentBtn = card.querySelector('[data-statut="Present"]');
    const absentBtn = card.querySelector('[data-statut="Absent"]');

    if (newStatut === 'Present') {
        // Activation visuelle Présent
        presentBtn.className = 'action-pointage btn-transition flex-1 py-2 px-4 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 bg-green-600 text-white border-transparent';
        // Désactivation visuelle Absent
        absentBtn.className = 'action-pointage btn-transition flex-1 py-2 px-4 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 bg-white text-gray-700 border-gray-300 hover:bg-red-50 hover:text-red-700 hover:border-red-500';
    } else if (newStatut === 'Absent') {
        // Désactivation visuelle Présent
        presentBtn.className = 'action-pointage btn-transition flex-1 py-2 px-4 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 bg-white text-gray-700 border-gray-300 hover:bg-green-50 hover:text-green-700 hover:border-green-500';
        // Activation visuelle Absent
        absentBtn.className = 'action-pointage btn-transition flex-1 py-2 px-4 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 bg-red-600 text-white border-transparent';
    }
}

/**
 * Met à jour le compteur global des adhérents présents aujourd'hui.
 * 
 * @param {string} oldStatut - L'ancien statut ('Present', 'Absent' ou 'None').
 * @param {string} newStatut - Le nouveau statut ('Present' ou 'Absent').
 * @returns {void}
 */
function updateCounter(oldStatut, newStatut) {
    const counterElement = document.getElementById('compteur-presents');
    if (!counterElement) return;

    let currentValue = parseInt(counterElement.innerText, 10);
    if (isNaN(currentValue)) currentValue = 0;

    if (newStatut === 'Present' && oldStatut !== 'Present') {
        counterElement.innerText = (currentValue + 1).toString();
    } else if (newStatut === 'Absent' && oldStatut === 'Present') {
        counterElement.innerText = Math.max(0, currentValue - 1).toString();
    }
}