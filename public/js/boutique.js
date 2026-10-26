/**
 * @fileoverview Gestionnaire asynchrone du panier e-commerce et processus de commande.
 * Utilise localStorage pour persister le panier entre les navigations.
 */

document.addEventListener("DOMContentLoaded", initBoutique);

/** @type {Object<string, Object>} État global du panier. Format: { "id": { id, nom, prix, quantite, stock_max } } */
let cart = JSON.parse(localStorage.getItem("judo_cart")) || {};

/**
 * Initialise l'interface de la boutique et du panier.
 * @returns {void}
 */
function initBoutique() {
  const btnToggleCart = document.getElementById("btn-toggle-cart");
  const btnCloseCart = document.getElementById("btn-close-cart");
  const overlay = document.getElementById("cart-overlay");
  const btnCheckout = document.getElementById("btn-checkout");

  // Gestion du panneau latéral
  const toggleCart = () => {
    document
      .getElementById("cart-sidebar")
      .classList.toggle("translate-x-full");
    overlay.classList.toggle("hidden");
  };

  if (btnToggleCart) btnToggleCart.addEventListener("click", toggleCart);
  if (btnCloseCart) btnCloseCart.addEventListener("click", toggleCart);
  if (overlay) overlay.addEventListener("click", toggleCart);

  // Ajout au panier
  document.querySelectorAll(".btn-add-cart").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const dataRaw = e.currentTarget.getAttribute("data-product");
      if (dataRaw) {
        const product = JSON.parse(dataRaw);
        addToCart(product);
      }
    });
  });

  // Validation commande
  if (btnCheckout) btnCheckout.addEventListener("click", processCheckout);

  // Rendu initial
  renderCart();
}

/**
 * Ajoute un produit au panier ou incrémente sa quantité.
 * @param {Object} product - Les informations du produit.
 * @returns {void}
 */
function addToCart(product) {
  const id = product.id.toString();

  if (cart[id]) {
    if (cart[id].quantite < product.stock) {
      cart[id].quantite += 1;
      if (window.Toast)
        window.Toast.show("Quantité mise à jour.", "info", 1500);
    } else {
      if (window.Toast)
        window.Toast.show("Stock maximum atteint.", "warning", 2000);
    }
  } else {
    cart[id] = {
      id: product.id,
      nom: product.nom,
      prix: product.prix,
      quantite: 1,
      stock_max: product.stock,
    };
    if (window.Toast)
      window.Toast.show("Produit ajouté au panier.", "success", 2000);
  }

  saveCart();
  renderCart();
}

/**
 * Modifie manuellement la quantité d'une ligne du panier.
 * @param {string} id - L'identifiant du produit.
 * @param {number} delta - Incrément ou décrément (1 ou -1).
 * @returns {void}
 */
window.updateCartQuantity = function (id, delta) {
  if (!cart[id]) return;

  const newQuantity = cart[id].quantite + delta;
  if (newQuantity <= 0) {
    delete cart[id];
  } else if (newQuantity <= cart[id].stock_max) {
    cart[id].quantite = newQuantity;
  } else {
    if (window.Toast) window.Toast.show("Stock insuffisant.", "warning", 2000);
    return;
  }

  saveCart();
  renderCart();
};

/**
 * Sauvegarde le panier dans le localStorage.
 * @returns {void}
 */
function saveCart() {
  localStorage.setItem("judo_cart", JSON.stringify(cart));
}

/**
 * Met à jour l'interface visuelle du panier (Lignes, Badge, Total).
 * @returns {void}
 */
function renderCart() {
  const container = document.getElementById("cart-items-container");
  const badge = document.getElementById("cart-badge");
  const totalEl = document.getElementById("cart-total");
  const btnCheckout = document.getElementById("btn-checkout");

  const itemKeys = Object.keys(cart);
  let total = 0;
  let itemCount = 0;

  if (itemKeys.length === 0) {
    container.innerHTML =
      '<p class="text-center text-gray-500 italic mt-10">Votre panier est vide.</p>';
    badge.classList.add("hidden");
    totalEl.innerText = "0,00 €";
    btnCheckout.disabled = true;
    return;
  }

  container.innerHTML = "";
  btnCheckout.disabled = false;

  itemKeys.forEach((key) => {
    const item = cart[key];
    total += item.prix * item.quantite;
    itemCount += item.quantite;

    const row = document.createElement("div");
    row.className =
      "flex justify-between items-center mb-4 pb-4 border-b border-gray-100";
    row.innerHTML = `
            <div class="flex-grow pr-3">
                <p class="text-sm font-bold text-gray-900 leading-tight">${item.nom}</p>
                <p class="text-xs text-blue-600 font-bold mt-1">${item.prix.toFixed(2).replace(".", ",")} € / unité</p>
            </div>
            <div class="flex items-center space-x-2 bg-gray-50 rounded border border-gray-200 px-2 py-1 shrink-0">
                <button onclick="updateCartQuantity('${item.id}', -1)" class="text-gray-500 hover:text-red-500 font-bold px-1 focus:outline-none">-</button>
                <span class="text-sm font-bold text-gray-900 w-4 text-center">${item.quantite}</span>
                <button onclick="updateCartQuantity('${item.id}', 1)" class="text-gray-500 hover:text-green-500 font-bold px-1 focus:outline-none">+</button>
            </div>
        `;
    container.appendChild(row);
  });

  badge.innerText = itemCount;
  badge.classList.remove("hidden");
  totalEl.innerText = total.toFixed(2).replace(".", ",") + " €";
}

/**
 * Traite la commande asynchrone (Envoi du payload JSON au serveur).
 * @returns {Promise<void>}
 */
async function processCheckout() {
  const itemKeys = Object.keys(cart);
  if (itemKeys.length === 0) return;

  const btnCheckout = document.getElementById("btn-checkout");
  btnCheckout.disabled = true;
  btnCheckout.innerText = "Validation...";

  // Structuration du payload attendu par le contrôleur : { "id_produit": quantite }
  const payloadPanier = {};
  itemKeys.forEach((key) => {
    payloadPanier[key] = cart[key].quantite;
  });

  try {
    const response = await fetch("/boutique/checkout", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ panier: payloadPanier }),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      cart = {};
      saveCart();
      renderCart();

      if (window.Toast) window.Toast.show(result.message, "success", 3000);

      // Fermeture du panier et redirection vers l'historique
      document.getElementById("cart-sidebar").classList.add("translate-x-full");
      document.getElementById("cart-overlay").classList.add("hidden");
      setTimeout(() => {
        window.location.href = "/mes-commandes";
      }, 1500);
    } else {
      if (window.Toast)
        window.Toast.show(
          result.message || "Erreur lors de la commande.",
          "error",
          5000,
        );
      btnCheckout.disabled = false;
      btnCheckout.innerText = "Valider la commande";
    }
  } catch (error) {
    console.error("Erreur réseau:", error);
    if (window.Toast)
      window.Toast.show(
        "Erreur de communication avec le serveur.",
        "error",
        5000,
      );
    btnCheckout.disabled = false;
    btnCheckout.innerText = "Valider la commande";
  }
}
