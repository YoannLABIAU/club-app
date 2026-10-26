/**
 * @fileoverview Gestionnaire asynchrone du CRUD des articles de la boutique.
 * Gère la modale (FormData pour l'image) et la suppression.
 */

document.addEventListener("DOMContentLoaded", () => {
  initCatalogueModal();
  initDeleteProductButtons();
});

/**
 * Initialise le comportement de la modale d'édition des produits.
 * @returns {void}
 */
function initCatalogueModal() {
  const modal = document.getElementById("modal-product");
  const form = document.getElementById("form-product");
  const title = document.getElementById("modal-prod-title");

  // Bouton Ajouter
  document
    .getElementById("btn-open-modal-add")
    .addEventListener("click", () => {
      form.reset();
      document.getElementById("prod_id").value = "0";
      document.getElementById("prod_restreint").checked = false;
      title.innerText = "Ajouter un produit";
      modal.classList.remove("hidden");
    });

  // Boutons Éditer
  document.querySelectorAll(".btn-edit-prod").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const dataRaw = e.currentTarget.getAttribute("data-product");
      if (dataRaw) {
        const data = JSON.parse(dataRaw);
        form.reset();
        document.getElementById("prod_id").value = data.id;
        document.getElementById("prod_nom").value = data.nom;
        document.getElementById("prod_prix").value = data.prix;
        document.getElementById("prod_stock").value = data.stock;
        document.getElementById("prod_desc").value = data.description;
        document.getElementById("prod_restreint").checked =
          parseInt(data.acces_restreint, 10) === 1;

        title.innerText = "Modifier l'article";
        modal.classList.remove("hidden");
      }
    });
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
    form.addEventListener("submit", handleProductSubmit);
  }
}

/**
 * Traite la sauvegarde asynchrone (Multipart FormData).
 * @param {Event} event
 * @returns {Promise<void>}
 */
async function handleProductSubmit(event) {
  event.preventDefault();
  const form = event.target;
  const btnSave = document.getElementById("btn-save-prod");

  btnSave.disabled = true;
  btnSave.innerText = "Traitement...";

  const formData = new FormData(form);

  try {
    const response = await fetch("/admin/catalogue/save", {
      method: "POST",
      body: formData, // Content-Type automatiquement géré par le navigateur pour FormData
    });

    const result = await response.json();

    if (response.ok && result.success) {
      if (window.Toast) window.Toast.show(result.message, "success", 2000);
      setTimeout(() => window.location.reload(), 1000);
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur de sauvegarde.",
          "error",
          4000,
        );
      btnSave.disabled = false;
      btnSave.innerText = "Enregistrer l'article";
    }
  } catch (error) {
    console.error("Fetch error:", error);
    if (window.Toast) window.Toast.show("Erreur réseau.", "error", 4000);
    btnSave.disabled = false;
    btnSave.innerText = "Enregistrer l'article";
  }
}

/**
 * Initialise les boutons de suppression avec demande de confirmation.
 * @returns {void}
 */
function initDeleteProductButtons() {
  document.querySelectorAll(".btn-delete-prod").forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      const id = e.currentTarget.getAttribute("data-id");

      if (
        !confirm(
          "Voulez-vous vraiment retirer ce produit du catalogue ? (Les historiques de commandes seront conservés)",
        )
      )
        return;

      try {
        const response = await fetch("/admin/catalogue/delete", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: id }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
          document.getElementById(`prod-${id}`).remove();
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
