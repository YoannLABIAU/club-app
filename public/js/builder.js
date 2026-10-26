/**
 * @fileoverview Gestionnaire du constructeur de blocs CMS.
 * Maintient l'état des blocs en mémoire, gère l'UI, l'upload d'images,
 * la prévisualisation et la sauvegarde (création & mise à jour).
 */

document.addEventListener("DOMContentLoaded", initBuilder);

/** @type {Array<Object>} État global des blocs constituant l'article */
let blocks = [];

/**
 * Initialise les écouteurs et hydrate l'état si un article existant est chargé.
 * @returns {void}
 */
function initBuilder() {
  // Hydratation depuis les variables injectées par PHP
  if (window.INITIAL_DATA && window.INITIAL_DATA.id > 0) {
    document.getElementById("article-titre").value = window.INITIAL_DATA.titre;
    blocks = window.INITIAL_DATA.blocs || [];
  }

  document
    .getElementById("btn-add-text")
    .addEventListener("click", addTextBlock);
  document
    .getElementById("btn-add-image")
    .addEventListener("click", () => triggerImageUpload());
  document.getElementById("btn-save").addEventListener("click", saveArticle);

  // Écouteurs pour la modale de prévisualisation
  document.getElementById("btn-preview").addEventListener("click", openPreview);
  document
    .getElementById("btn-close-modal")
    .addEventListener("click", closePreview);
  document
    .getElementById("btn-modal-close-bottom")
    .addEventListener("click", closePreview);
  document
    .getElementById("modal-overlay")
    .addEventListener("click", closePreview);

  renderWorkspace();
}

/**
 * Ajoute un bloc texte vide à l'état et redessine l'espace de travail.
 * @returns {void}
 */
function addTextBlock() {
  blocks.push({ id: Date.now().toString(), type: "text", content: "" });
  renderWorkspace();
}

/**
 * Ouvre la boîte de dialogue système pour choisir une image,
 * l'uploade sur le serveur, puis ajoute le bloc image si succès.
 * @returns {void}
 */
function triggerImageUpload() {
  const input = document.createElement("input");
  input.type = "file";
  input.accept = "image/png, image/jpeg, image/webp, image/gif";
  input.onchange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (window.Toast) window.Toast.show("Envoi de l'image en cours...", "info");

    const formData = new FormData();
    formData.append("image", file);

    try {
      const response = await fetch("/admin/cms/upload", {
        method: "POST",
        body: formData, // Content-Type géré automatiquement par fetch avec FormData
      });
      const result = await response.json();

      if (response.ok && result.success) {
        blocks.push({
          id: Date.now().toString(),
          type: "image",
          url: result.url,
        });
        renderWorkspace();
        if (window.Toast)
          window.Toast.show("Image ajoutée avec succès.", "success");
      } else {
        if (window.Toast)
          window.Toast.show(result.message || "Échec de l'envoi.", "error");
      }
    } catch (error) {
      console.error("Upload error:", error);
      if (window.Toast)
        window.Toast.show("Erreur réseau lors de l'upload.", "error");
    }
  };
  input.click();
}

/**
 * Redessine entièrement l'espace de travail en fonction de l'état du tableau `blocks`.
 * @returns {void}
 */
function renderWorkspace() {
  const workspace = document.getElementById("builder-workspace");

  if (blocks.length === 0) {
    workspace.innerHTML = `
            <div id="empty-state" class="m-auto text-center text-gray-400 py-10">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="font-medium text-gray-500">L'article est vide.</p>
                <p class="text-sm">Ajoutez des blocs depuis le menu de droite pour construire votre page.</p>
            </div>`;
    return;
  }

  workspace.innerHTML = "";

  blocks.forEach((block, index) => {
    const blockDiv = document.createElement("div");
    blockDiv.className =
      "bg-white p-4 rounded-md shadow-sm border border-gray-200 relative group";

    // Barre d'outils de réorganisation
    const controls = document.createElement("div");
    controls.className =
      "absolute -top-3 -right-2 hidden group-hover:flex space-x-1 bg-white border border-gray-200 shadow-md rounded-md p-1";
    controls.innerHTML = `
            <button class="p-1 hover:bg-gray-100 rounded text-gray-600" title="Monter" onclick="moveBlock(${index}, -1)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
            </button>
            <button class="p-1 hover:bg-gray-100 rounded text-gray-600" title="Descendre" onclick="moveBlock(${index}, 1)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <button class="p-1 hover:bg-red-100 rounded text-red-500" title="Supprimer" onclick="deleteBlock(${index})">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
    blockDiv.appendChild(controls);

    if (block.type === "text") {
      const textarea = document.createElement("textarea");
      textarea.className =
        "w-full min-h-[100px] border-0 focus:ring-0 resize-y text-gray-800 bg-transparent";
      textarea.placeholder = "Saisissez votre texte ici...";
      textarea.value = block.content || "";
      textarea.oninput = (e) => {
        block.content = e.target.value;
      };
      blockDiv.appendChild(textarea);
    } else if (block.type === "image") {
      const img = document.createElement("img");
      img.src = block.url;
      img.className =
        "max-w-full h-auto rounded-md mx-auto max-h-[300px] object-contain bg-gray-50";
      blockDiv.appendChild(img);
    }

    workspace.appendChild(blockDiv);
  });
}

/**
 * Déplace un bloc dans le tableau d'état.
 * @param {number} index - Index actuel du bloc.
 * @param {number} direction - -1 pour monter, 1 pour descendre.
 * @returns {void}
 */
window.moveBlock = function (index, direction) {
  if (index + direction < 0 || index + direction >= blocks.length) return;

  // Inversion des éléments
  const temp = blocks[index];
  blocks[index] = blocks[index + direction];
  blocks[index + direction] = temp;

  renderWorkspace();
};

/**
 * Supprime un bloc de l'état.
 * @param {number} index - Index du bloc.
 * @returns {void}
 */
window.deleteBlock = function (index) {
  blocks.splice(index, 1);
  renderWorkspace();
};

/**
 * Affiche la modale de prévisualisation et génère le HTML final.
 * @returns {void}
 */
function openPreview() {
  const titre = document.getElementById("article-titre").value;
  if (!titre.trim()) {
    if (window.Toast)
      window.Toast.show("Le titre est requis pour prévisualiser.", "warning");
    return;
  }

  document.getElementById("preview-title").innerText = titre;
  const contentContainer = document.getElementById("preview-content");
  contentContainer.innerHTML = ""; // Reset

  blocks.forEach((block) => {
    if (block.type === "text") {
      const p = document.createElement("p");
      p.innerText = block.content; // Protection anti-XSS native via innerText
      contentContainer.appendChild(p);
    } else if (block.type === "image") {
      const img = document.createElement("img");
      img.src = block.url;
      img.className = "max-w-full h-auto rounded-lg shadow-sm mx-auto";
      contentContainer.appendChild(img);
    }
  });

  document.getElementById("modal-preview").classList.remove("hidden");
}

/**
 * Ferme la modale de prévisualisation.
 * @returns {void}
 */
function closePreview() {
  document.getElementById("modal-preview").classList.add("hidden");
}

/**
 * Sauvegarde l'article (Création ou Mise à jour).
 * @returns {Promise<void>}
 */
async function saveArticle() {
  const titre = document.getElementById("article-titre").value.trim();
  const id = window.INITIAL_DATA ? parseInt(window.INITIAL_DATA.id, 10) : 0;

  if (!titre) {
    if (window.Toast) window.Toast.show("Le titre est requis.", "error");
    return;
  }
  if (blocks.length === 0) {
    if (window.Toast)
      window.Toast.show("Ajoutez au moins un bloc à l'article.", "error");
    return;
  }

  const btnSave = document.getElementById("btn-save");
  btnSave.disabled = true;
  btnSave.innerText = "Sauvegarde...";

  const payload = {
    id: id,
    titre: titre,
    blocs: blocks,
  };

  try {
    const response = await fetch("/admin/cms/save", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      if (window.Toast) window.Toast.show(result.message, "success", 2000);
      setTimeout(() => {
        window.location.href = "/admin/cms";
      }, 1000);
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur de sauvegarde.",
          "error",
          4000,
        );
      btnSave.disabled = false;
      btnSave.innerText =
        id > 0 ? "Mettre à jour l'article" : "Publier l'article";
    }
  } catch (error) {
    console.error("Erreur Fetch:", error);
    if (window.Toast) window.Toast.show("Erreur réseau.", "error", 4000);
    btnSave.disabled = false;
    btnSave.innerText =
      id > 0 ? "Mettre à jour l'article" : "Publier l'article";
  }
}
