/**
 * @fileoverview Gestionnaire asynchrone du back-office Boutique et Trésorerie.
 * Gère les mises à jour de statuts de commandes et les ajustements de stocks.
 */

document.addEventListener("DOMContentLoaded", () => {
  initStatutSelects();
  initStockButtons();
});

/**
 * Attache les écouteurs d'événements sur les listes déroulantes de statuts de commande.
 *
 * @returns {void}
 */
function initStatutSelects() {
  const selects = document.querySelectorAll(".select-statut-commande");
  selects.forEach((select) => {
    select.addEventListener("change", handleStatutChange);
  });
}

/**
 * Gère la mise à jour asynchrone du statut d'une commande via Fetch API.
 *
 * @param {Event} event
 * @returns {Promise<void>}
 */
async function handleStatutChange(event) {
  const select = event.target;
  const commandeId = select.getAttribute("data-id");
  const nouveauStatut = select.value;

  select.disabled = true;

  try {
    const response = await fetch("/admin/boutique/statut", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ commande_id: commandeId, statut: nouveauStatut }),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      if (window.Toast) window.Toast.show(result.message, "success", 2000);

      // Si annulée, on pourrait visuellement barrer la ligne, mais le statut mis à jour suffit pour le MVP
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur de mise à jour.",
          "error",
          4000,
        );
    }
  } catch (error) {
    console.error("Erreur réseau:", error);
    if (window.Toast)
      window.Toast.show(
        "Erreur de communication avec le serveur.",
        "error",
        4000,
      );
  } finally {
    select.disabled = false;
  }
}

/**
 * Attache les écouteurs d'événements sur les boutons de validation des stocks.
 *
 * @returns {void}
 */
function initStockButtons() {
  const buttons = document.querySelectorAll(".btn-update-stock");
  buttons.forEach((btn) => {
    btn.addEventListener("click", handleStockUpdate);
  });
}

/**
 * Gère l'ajustement asynchrone du stock d'un produit.
 *
 * @param {Event} event
 * @returns {Promise<void>}
 */
async function handleStockUpdate(event) {
  const bouton = event.currentTarget;
  const produitId = bouton.getAttribute("data-id");
  const inputField = document.getElementById(`stock-input-${produitId}`);

  if (!inputField) return;

  const nouveauStock = parseInt(inputField.value, 10);

  if (isNaN(nouveauStock) || nouveauStock < 0) {
    if (window.Toast)
      window.Toast.show(
        "Le stock doit être un nombre positif.",
        "warning",
        3000,
      );
    return;
  }

  bouton.disabled = true;
  bouton.classList.add("opacity-50");

  try {
    const response = await fetch("/admin/boutique/stock", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        produit_id: produitId,
        nouveau_stock: nouveauStock,
      }),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      if (window.Toast) window.Toast.show(result.message, "success", 2000);
      // Validation visuelle
      inputField.classList.add("border-green-500", "bg-green-50");
      setTimeout(() => {
        inputField.classList.remove("border-green-500", "bg-green-50");
      }, 1000);
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur de mise à jour.",
          "error",
          4000,
        );
    }
  } catch (error) {
    console.error("Erreur réseau:", error);
    if (window.Toast)
      window.Toast.show(
        "Erreur de communication avec le serveur.",
        "error",
        4000,
      );
  } finally {
    bouton.disabled = false;
    bouton.classList.remove("opacity-50");
  }
}
