/**
 * @fileoverview Script frontend gérant les interactions asynchrones du tableau de bord.
 * Inclut la validation des paiements et la mise à jour asynchrone des paramètres du site (Logo/Couleur).
 */

document.addEventListener("DOMContentLoaded", () => {
  initAdminPanel();
  initSettingsForm();
});

/**
 * Initialise les écouteurs d'événements pour la validation financière.
 *
 * @returns {void}
 */
function initAdminPanel() {
  const boutonsValidation = document.querySelectorAll(".btn-valider-paiement");
  boutonsValidation.forEach((bouton) => {
    bouton.addEventListener("click", handleValidationPaiement);
  });
}

/**
 * Initialise le formulaire des paramètres d'apparence (Upload File + Couleur).
 *
 * @returns {void}
 */
function initSettingsForm() {
  const formParams = document.getElementById("form-parametres");
  const colorPicker = document.getElementById("couleur_primaire");
  const hexDisplay = document.getElementById("hex-display");

  if (colorPicker && hexDisplay) {
    colorPicker.addEventListener("input", (e) => {
      hexDisplay.innerText = e.target.value;
    });
  }

  if (formParams) {
    formParams.addEventListener("submit", handleSettingsSubmit);
  }
}

/**
 * Traite la sauvegarde asynchrone des paramètres du site.
 * Utilise FormData pour supporter l'upload binaire du logo.
 *
 * @param {Event} event
 * @returns {Promise<void>}
 */
async function handleSettingsSubmit(event) {
  event.preventDefault();
  const form = event.target;
  const btnSave = document.getElementById("btn-save-parametres");

  btnSave.disabled = true;
  btnSave.innerText = "Application...";

  const formData = new FormData(form);

  try {
    const response = await fetch("/admin/parametres", {
      method: "POST",
      body: formData, // Content-Type géré automatiquement par Fetch
    });

    const result = await response.json();

    if (response.ok && result.success) {
      if (window.Toast) window.Toast.show(result.message, "success", 2000);
      // Rechargement après 1s pour que le PHP injecte le nouveau CSS et Logo dans le header
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur de sauvegarde.",
          "error",
          5000,
        );
      btnSave.disabled = false;
      btnSave.innerText = "Appliquer les modifications";
    }
  } catch (error) {
    console.error("Erreur réseau:", error);
    if (window.Toast)
      window.Toast.show(
        "Erreur de communication avec le serveur.",
        "error",
        5000,
      );
    btnSave.disabled = false;
    btnSave.innerText = "Appliquer les modifications";
  }
}

/**
 * Gère le clic sur un badge "En attente" pour valider un paiement via l'API Fetch.
 *
 * @param {Event} event - L'événement de clic.
 * @returns {Promise<void>}
 */
async function handleValidationPaiement(event) {
  const bouton = event.currentTarget;
  const adherentId = bouton.getAttribute("data-id");

  if (!confirm("Confirmer la réception de la cotisation pour cet adhérent ?")) {
    return;
  }

  bouton.disabled = true;
  bouton.classList.add("opacity-50", "cursor-not-allowed");

  try {
    const response = await fetch("/admin/valider-paiement", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ adherent_id: adherentId }),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      const conteneurCellule = document.getElementById(
        `cell-paiement-${adherentId}`,
      );
      if (conteneurCellule) {
        conteneurCellule.innerHTML = `<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Validé</span>`;
      }

      const compteurElement = document.getElementById("compteur-cotisations");
      if (compteurElement) {
        let currentCount = parseInt(compteurElement.innerText, 10);
        if (!isNaN(currentCount)) {
          compteurElement.innerText = (currentCount + 1).toString();
        }
      }

      if (window.Toast) window.Toast.show(result.message, "success", 3000);
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur lors de la validation.",
          "error",
          5000,
        );
      bouton.disabled = false;
      bouton.classList.remove("opacity-50", "cursor-not-allowed");
    }
  } catch (error) {
    console.error("Erreur réseau:", error);
    if (window.Toast)
      window.Toast.show(
        "Erreur de communication avec le serveur.",
        "error",
        5000,
      );
    bouton.disabled = false;
    bouton.classList.remove("opacity-50", "cursor-not-allowed");
  }
}
