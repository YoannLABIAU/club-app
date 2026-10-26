/**
 * @fileoverview Gestionnaire asynchrone du back-office des liaisons familiales.
 * Gère l'ajout via la modale et la suppression des liens en base.
 */

document.addEventListener("DOMContentLoaded", () => {
  initLiaisonModal();
  initDeleteLiaisonButtons();
});

/**
 * Initialise le comportement de la modale d'ajout de liaison.
 * @returns {void}
 */
function initLiaisonModal() {
  const modal = document.getElementById("modal-liaison");
  const form = document.getElementById("form-liaison");

  // Ouverture
  document
    .getElementById("btn-open-modal-add")
    .addEventListener("click", () => {
      form.reset();
      modal.classList.remove("hidden");
    });

  // Fermeture
  const closeModal = () => modal.classList.add("hidden");
  document
    .getElementById("btn-close-modal")
    .addEventListener("click", closeModal);
  document.getElementById("btn-cancel").addEventListener("click", closeModal);
  document
    .getElementById("modal-overlay")
    .addEventListener("click", closeModal);

  // Soumission asynchrone
  if (form) {
    form.addEventListener("submit", handleLiaisonSubmit);
  }
}

/**
 * Traite l'ajout d'une nouvelle liaison via l'API Fetch.
 * @param {Event} event
 * @returns {Promise<void>}
 */
async function handleLiaisonSubmit(event) {
  event.preventDefault();
  const form = event.target;
  const btnSave = document.getElementById("btn-save-liaison");

  btnSave.disabled = true;
  btnSave.innerText = "Création...";

  const payload = Object.fromEntries(new FormData(form).entries());

  try {
    const response = await fetch("/admin/liaisons/add", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      if (window.Toast) window.Toast.show(result.message, "success", 2000);
      setTimeout(() => window.location.reload(), 1000);
    } else {
      if (window.Toast)
        window.Toast.show(result.message || "Erreur d'ajout.", "error", 4000);
      btnSave.disabled = false;
      btnSave.innerText = "Lier les fiches";
    }
  } catch (error) {
    console.error("Fetch error:", error);
    if (window.Toast) window.Toast.show("Erreur réseau.", "error", 4000);
    btnSave.disabled = false;
    btnSave.innerText = "Lier les fiches";
  }
}

/**
 * Initialise les boutons de suppression de liaison.
 * @returns {void}
 */
function initDeleteLiaisonButtons() {
  document.querySelectorAll(".btn-delete-liaison").forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      const uid = e.currentTarget.getAttribute("data-uid");
      const aid = e.currentTarget.getAttribute("data-aid");

      if (
        !confirm(
          "Voulez-vous vraiment rompre le lien entre ce compte et cet adhérent ?",
        )
      )
        return;

      try {
        const response = await fetch("/admin/liaisons/delete", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ utilisateur_id: uid, adherent_id: aid }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
          const listItem = document.getElementById(`liaison-${uid}-${aid}`);
          if (listItem) listItem.remove();
          if (window.Toast) window.Toast.show(result.message, "success", 3000);
        } else {
          if (window.Toast)
            window.Toast.show(
              result.message || "Erreur de suppression.",
              "error",
              4000,
            );
        }
      } catch (error) {
        console.error("Fetch error:", error);
        if (window.Toast) window.Toast.show("Erreur réseau.", "error", 4000);
      }
    });
  });
}
