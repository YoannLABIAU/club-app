/**
 * @fileoverview Script gérant l'envoi asynchrone des pointages et la recherche hors-créneau.
 */

document.addEventListener("DOMContentLoaded", () => {
  initPointage();
  initSearchHorsCreneau();
});

function initPointage() {
  const buttons = document.querySelectorAll(".action-pointage");
  buttons.forEach((button) => {
    button.addEventListener("click", handlePointage);
  });
}

async function handlePointage(event) {
  const clickedBtn = event.currentTarget;
  const adherentId = clickedBtn.getAttribute("data-adherent-id");
  const seanceId = clickedBtn.getAttribute("data-seance-id");
  const newStatut = clickedBtn.getAttribute("data-statut");

  const card = document.getElementById(`card-adherent-${adherentId}`);

  const presentBtn = card.querySelector('[data-statut="Present"]');
  const absentBtn = card.querySelector('[data-statut="Absent"]');

  const isCurrentlyPresent = presentBtn.classList.contains("bg-green-600");
  const isCurrentlyAbsent = absentBtn.classList.contains("bg-red-600");

  let oldStatut = "None";
  if (isCurrentlyPresent) oldStatut = "Present";
  if (isCurrentlyAbsent) oldStatut = "Absent";

  if (newStatut === oldStatut) return;

  card.classList.add("is-loading");

  try {
    const response = await fetch("/pointage", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        adherent_id: adherentId,
        seance_id: seanceId,
        statut: newStatut,
      }),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      updateCardStyle(presentBtn, absentBtn, newStatut);
      updateCounters(oldStatut, newStatut);
      if (window.Toast)
        window.Toast.show(
          `Pointage enregistré (${newStatut}).`,
          "success",
          2000,
        );
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur de mise à jour.",
          "error",
          4000,
        );
    }
  } catch (error) {
    console.error("Erreur Fetch:", error);
    if (window.Toast) window.Toast.show("Erreur réseau.", "error", 4000);
  } finally {
    card.classList.remove("is-loading");
  }
}

function updateCardStyle(presentBtn, absentBtn, newStatut) {
  const classPresentActive =
    "action-pointage btn-transition flex-1 py-2 px-3 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500 bg-green-600 text-white border-transparent shadow-inner";
  const classPresentInactive =
    "action-pointage btn-transition flex-1 py-2 px-3 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500 bg-white text-gray-700 border-gray-300 hover:bg-green-50 hover:text-green-700 hover:border-green-500";

  const classAbsentActive =
    "action-pointage btn-transition flex-1 py-2 px-3 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 bg-red-600 text-white border-transparent shadow-inner";
  const classAbsentInactive =
    "action-pointage btn-transition flex-1 py-2 px-3 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 bg-white text-gray-700 border-gray-300 hover:bg-red-50 hover:text-red-700 hover:border-red-500";

  if (newStatut === "Present") {
    presentBtn.className = classPresentActive;
    absentBtn.className = classAbsentInactive;
  } else {
    presentBtn.className = classPresentInactive;
    absentBtn.className = classAbsentActive;
  }
}

function updateCounters(oldStatut, newStatut) {
  const compteur = document.getElementById("compteur-presents");
  if (!compteur) return;

  let total = parseInt(compteur.innerText, 10) || 0;

  if (newStatut === "Present" && oldStatut !== "Present") {
    compteur.innerText = (total + 1).toString();
  } else if (newStatut === "Absent" && oldStatut === "Present") {
    compteur.innerText = Math.max(0, total - 1).toString();
  }
}

/**
 * Gère la barre de recherche et l'ajout manuel d'un élève à la séance du jour.
 * @returns {void}
 */
function initSearchHorsCreneau() {
  const searchInput = document.getElementById("search-hors-creneau");
  const resultsContainer = document.getElementById("search-results");
  let debounceTimer;

  if (!searchInput || !resultsContainer) return;

  searchInput.addEventListener("input", (e) => {
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
          `/pointage/search?q=${encodeURIComponent(term)}`,
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
              // Clic sur le résultat -> Ajout en "Présent" direct et rafraîchissement
              li.addEventListener("click", () =>
                addAdherentHorsCreneau(adherent.id),
              );
              resultsContainer.appendChild(li);
            });
          }
          resultsContainer.classList.remove("hidden");
        }
      } catch (error) {
        console.error("Search error:", error);
      }
    }, 300); // 300ms debounce
  });

  // Fermer les résultats si clic à l'extérieur
  document.addEventListener("click", (e) => {
    if (
      !searchInput.contains(e.target) &&
      !resultsContainer.contains(e.target)
    ) {
      resultsContainer.classList.add("hidden");
    }
  });
}

/**
 * Pointe un élève hors-créneau et actualise la page pour le faire apparaître dans la grille.
 * @param {number} adherentId
 * @returns {Promise<void>}
 */
async function addAdherentHorsCreneau(adherentId) {
  if (!window.CURRENT_SEANCE_ID) return;

  if (window.Toast) window.Toast.show("Ajout en cours...", "info");
  const searchInput = document.getElementById("search-hors-creneau");
  searchInput.disabled = true;

  try {
    const response = await fetch("/pointage", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        adherent_id: adherentId,
        seance_id: window.CURRENT_SEANCE_ID,
        statut: "Present",
      }),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      window.location.reload(); // Rafraîchissement pour afficher la nouvelle carte proprement
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur lors de l'ajout.",
          "error",
          4000,
        );
      searchInput.disabled = false;
    }
  } catch (error) {
    console.error("Erreur Fetch:", error);
    if (window.Toast) window.Toast.show("Erreur réseau.", "error", 4000);
    searchInput.disabled = false;
  }
}
