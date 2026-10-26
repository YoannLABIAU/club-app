/**
 * @fileoverview Script gérant les interactions du portail famille.
 * Gère l'édition des fiches enfants et la soumission asynchrone des participations aux événements.
 */

document.addEventListener("DOMContentLoaded", () => {
  initModalEvents();
  initParticipationEvents();
});

/**
 * Attache les événements aux menus déroulants de participation.
 *
 * @returns {void}
 */
function initParticipationEvents() {
  const selects = document.querySelectorAll(".select-participation");
  selects.forEach((select) => {
    select.addEventListener("change", handleParticipationChange);
  });
}

/**
 * Gère l'envoi asynchrone de la réponse à un événement.
 *
 * @param {Event} event
 * @returns {Promise<void>}
 */
async function handleParticipationChange(event) {
  const select = event.target;
  const adherentId = select.getAttribute("data-adherent-id");
  const evenementId = select.getAttribute("data-evenement-id");
  const statut = select.value;

  select.disabled = true;

  const payload = {
    adherent_id: adherentId,
    evenement_id: evenementId,
    statut: statut,
  };

  try {
    const response = await fetch("/espace-famille/participation", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      if (window.Toast)
        window.Toast.show("Réponse enregistrée.", "success", 2000);

      // Mise à jour visuelle optionnelle (couleur de fond selon la réponse)
      select.classList.remove("bg-green-50", "bg-red-50", "bg-yellow-50");
      if (statut === "Oui") select.classList.add("bg-green-50");
      else if (statut === "Non") select.classList.add("bg-red-50");
      else if (statut === "Peut-être") select.classList.add("bg-yellow-50");
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur d'enregistrement.",
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
 * Attache les événements aux boutons d'édition et à la modale.
 *
 * @returns {void}
 */
function initModalEvents() {
  const editButtons = document.querySelectorAll(".btn-edit-adherent");
  const modal = document.getElementById("modal-edit");
  const form = document.getElementById("form-edit-adherent");

  // Ouverture de la modale
  editButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const dataRaw = e.currentTarget.getAttribute("data-adherent");
      if (dataRaw) {
        const data = JSON.parse(dataRaw);
        hydrateForm(data);
        modal.classList.remove("hidden");
      }
    });
  });

  // Fermeture de la modale
  document
    .getElementById("btn-close-modal")
    .addEventListener("click", () => modal.classList.add("hidden"));
  document
    .getElementById("btn-cancel")
    .addEventListener("click", () => modal.classList.add("hidden"));
  document
    .getElementById("modal-overlay")
    .addEventListener("click", () => modal.classList.add("hidden"));

  // Soumission du formulaire
  if (form) {
    form.addEventListener("submit", handleFormSubmit);
  }
}

/**
 * Remplit les champs du formulaire avec les données de l'adhérent sélectionné.
 *
 * @param {Object} data Données de l'adhérent.
 * @returns {void}
 */
function hydrateForm(data) {
  document.getElementById("edit_adherent_id").value = data.id || "";
  document.getElementById("edit_adresse").value = data.adresse || "";
  document.getElementById("edit_email").value = data.email || "";
  document.getElementById("edit_telephone").value = data.telephone || "";
  document.getElementById("edit_telephone_pere").value =
    data.telephone_pere || "";
  document.getElementById("edit_telephone_mere").value =
    data.telephone_mere || "";
  document.getElementById("edit_problemes_sante").value =
    data.problemes_sante || "";
  document.getElementById("edit_urgence_nom").value = data.urgence_nom || "";
  document.getElementById("edit_urgence_telephone").value =
    data.urgence_telephone || "";
}

/**
 * Gère l'envoi asynchrone des modifications.
 *
 * @param {Event} event
 * @returns {Promise<void>}
 */
async function handleFormSubmit(event) {
  event.preventDefault();
  const form = event.target;
  const btnSave = document.getElementById("btn-save-edit");

  btnSave.disabled = true;
  btnSave.innerText = "Sauvegarde...";

  const formData = new FormData(form);
  const payload = Object.fromEntries(formData.entries());

  try {
    const response = await fetch("/espace-famille/update", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      if (window.Toast) window.Toast.show(result.message, "success", 2000);
      setTimeout(() => window.location.reload(), 1000);
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur de mise à jour.",
          "error",
          4000,
        );
      btnSave.disabled = false;
      btnSave.innerText = "Enregistrer les modifications";
    }
  } catch (error) {
    console.error("Erreur réseau:", error);
    if (window.Toast)
      window.Toast.show(
        "Erreur de communication avec le serveur.",
        "error",
        4000,
      );
    btnSave.disabled = false;
    btnSave.innerText = "Enregistrer les modifications";
  }
}
