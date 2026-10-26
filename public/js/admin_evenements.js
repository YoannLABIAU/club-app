/**
 * @fileoverview Gestionnaire asynchrone du back-office des événements.
 * Gère la modale (Création/Modification), la suppression et la recherche asynchrone pour les compétitions.
 */

document.addEventListener("DOMContentLoaded", () => {
  initEventModal();
  initDeleteButtons();
  initCompetSearch();
});

/**
 * Initialise le comportement de la modale d'édition des événements.
 * @returns {void}
 */
function initEventModal() {
  const modal = document.getElementById("modal-event");
  const form = document.getElementById("form-event");
  const title = document.getElementById("modal-event-title");

  // Bouton Ajouter
  document
    .getElementById("btn-open-modal-add")
    .addEventListener("click", () => {
      form.reset();
      document.getElementById("event_id").value = "0";
      document.getElementById("event_type").value = "Gala";
      title.innerText = "Créer un événement";
      modal.classList.remove("hidden");
    });

  // Boutons Éditer
  document.querySelectorAll(".btn-edit-event").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const dataRaw = e.currentTarget.getAttribute("data-event");
      if (dataRaw) {
        const data = JSON.parse(dataRaw);
        document.getElementById("event_id").value = data.id;
        document.getElementById("event_type").value = data.type || "Gala";
        document.getElementById("event_nom").value = data.nom;
        document.getElementById("event_date").value = data.date_evenement;
        document.getElementById("event_desc").value = data.description;
        title.innerText = "Modifier l'événement";
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
    form.addEventListener("submit", handleEventSubmit);
  }
}

/**
 * Traite la sauvegarde asynchrone (création/modification).
 * @param {Event} event
 * @returns {Promise<void>}
 */
async function handleEventSubmit(event) {
  event.preventDefault();
  const form = event.target;
  const btnSave = document.getElementById("btn-save-event");

  btnSave.disabled = true;
  btnSave.innerText = "Enregistrement...";

  const payload = Object.fromEntries(new FormData(form).entries());

  try {
    const response = await fetch("/admin/evenements/save", {
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
        window.Toast.show(result.message || "Erreur.", "error", 4000);
      btnSave.disabled = false;
      btnSave.innerText = "Enregistrer";
    }
  } catch (error) {
    console.error("Fetch error:", error);
    if (window.Toast) window.Toast.show("Erreur réseau.", "error", 4000);
    btnSave.disabled = false;
    btnSave.innerText = "Enregistrer";
  }
}

/**
 * Initialise les boutons de suppression avec demande de confirmation.
 * @returns {void}
 */
function initDeleteButtons() {
  document.querySelectorAll(".btn-delete-event").forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      const id = e.currentTarget.getAttribute("data-id");

      if (
        !confirm(
          "Voulez-vous vraiment supprimer cet événement ? Toutes les réponses des adhérents seront perdues.",
        )
      )
        return;

      try {
        const response = await fetch("/admin/evenements/delete", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: id }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
          document.getElementById(`event-${id}`).remove();
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

/**
 * Gère la barre de recherche asynchrone pour inscrire un élève à une compétition.
 * @returns {void}
 */
function initCompetSearch() {
  const searchInputs = document.querySelectorAll(".search-compet");

  searchInputs.forEach((input) => {
    let debounceTimer;
    const eventId = input.getAttribute("data-event-id");
    const resultsContainer = document.getElementById(
      `results-compet-${eventId}`,
    );

    if (!resultsContainer) return;

    input.addEventListener("input", (e) => {
      clearTimeout(debounceTimer);
      const term = e.target.value.trim();

      if (term.length < 2) {
        resultsContainer.innerHTML = "";
        resultsContainer.classList.add("hidden");
        return;
      }

      debounceTimer = setTimeout(async () => {
        try {
          const response = await fetch(
            `/admin/evenements/search?q=${encodeURIComponent(term)}`,
          );
          const result = await response.json();

          if (response.ok && result.success) {
            resultsContainer.innerHTML = "";

            if (result.results.length === 0) {
              resultsContainer.innerHTML =
                '<li class="text-gray-500 py-2 px-3 text-sm italic">Aucun résultat trouvé.</li>';
            } else {
              result.results.forEach((adherent) => {
                const li = document.createElement("li");
                li.className =
                  "text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 border-b border-gray-100 last:border-0";
                li.innerHTML = `
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold truncate">${adherent.nom} ${adherent.prenom}</span>
                                        <span class="text-xs text-gray-500">${adherent.disciplines}</span>
                                    </div>
                                `;
                li.addEventListener("click", () =>
                  addParticipant(eventId, adherent.id),
                );
                resultsContainer.appendChild(li);
              });
            }
            resultsContainer.classList.remove("hidden");
          }
        } catch (error) {
          console.error("Search error:", error);
        }
      }, 300);
    });

    // Fermer les résultats si clic à l'extérieur
    document.addEventListener("click", (e) => {
      if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
        resultsContainer.classList.add("hidden");
      }
    });
  });
}

/**
 * Envoie une requête POST pour inscrire manuellement un élève à un événement.
 * @param {string} eventId
 * @param {number} adherentId
 * @returns {Promise<void>}
 */
async function addParticipant(eventId, adherentId) {
  if (window.Toast) window.Toast.show("Ajout en cours...", "info");

  try {
    const response = await fetch("/admin/evenements/participant/add", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ evenement_id: eventId, adherent_id: adherentId }),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      window.location.reload(); // Rafraîchissement pour intégrer visuellement l'élève à la liste
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur lors de l'ajout.",
          "error",
          4000,
        );
    }
  } catch (error) {
    console.error("Erreur Fetch:", error);
    if (window.Toast) window.Toast.show("Erreur réseau.", "error", 4000);
  }
}
