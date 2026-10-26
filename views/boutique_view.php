<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8 relative">
    
    <div class="mb-8 border-b border-gray-200 pb-5 flex items-center justify-between">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Boutique du Club</h1>
        <div class="flex items-center gap-4">
            <?php if ($estMembreConnecte): ?>
                <a href="/mes-commandes" class="text-sm font-medium text-blue-600 hover:text-blue-800 btn-transition">Mes commandes</a>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    Mode Membre Activé
                </span>
            <?php endif; ?>
            <button id="btn-toggle-cart" class="relative bg-blue-800 hover:bg-blue-900 text-white p-2 rounded-full shadow-md btn-transition focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span id="cart-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full hidden">0</span>
            </button>
        </div>
    </div>

    <?php if (!$estMembreConnecte): ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 text-blue-700">
                    <p class="text-sm font-medium">Certains articles (équipements subventionnés) sont exclusifs aux membres du club.</p>
                    <p class="text-sm mt-1">Connectez-vous pour débloquer le catalogue complet et pouvoir passer commande.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <!-- GRILLE PRODUITS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (empty($produits)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-lg border border-gray-200 border-dashed">
                <p class="text-gray-500 italic">Aucun article n'est disponible dans la boutique pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($produits as $produit): ?>
                <?php 
                    $id = (int)$produit['id'];
                    $nom = htmlspecialchars($produit['nom'], ENT_QUOTES, 'UTF-8');
                    $prix = (float)$produit['prix'];
                    $stock = (int)$produit['stock'];
                    $desc = htmlspecialchars((string)$produit['description'], ENT_QUOTES, 'UTF-8');
                    $estEnRupture = ($stock <= 0);
                    
                    // JSON pour le bouton AddToCart
                    $dataJson = htmlspecialchars(json_encode(['id' => $id, 'nom' => $produit['nom'], 'prix' => $prix, 'stock' => $stock]), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden flex flex-col <?= $estEnRupture ? 'opacity-75 grayscale' : '' ?>">
                    <div class="bg-gray-200 h-48 w-full flex items-center justify-center border-b border-gray-200 text-gray-400">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>

                    <div class="p-5 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold text-gray-900 leading-tight"><?= $nom ?></h3>
                                <span class="text-lg font-black text-blue-700 whitespace-nowrap ml-2">
                                    <?= number_format($prix, 2, ',', ' ') ?> €
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?= $desc ?></p>
                        </div>
                        
                        <div class="mt-auto">
                            <?php if ($estEnRupture): ?>
                                <p class="text-sm font-bold text-red-600 mb-3 text-center">En rupture de stock</p>
                                <button disabled class="w-full bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded cursor-not-allowed">Indisponible</button>
                            <?php else: ?>
                                <p class="text-xs text-gray-500 mb-3 text-center">Stock disponible : <?= $stock ?></p>
                                <button class="w-full btn-add-cart bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
                                        data-product="<?= $dataJson ?>" <?= !$estMembreConnecte ? 'disabled title="Connexion requise"' : '' ?>>
                                    <?= $estMembreConnecte ? 'Ajouter au panier' : 'Connexion requise' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- PANNEAU PANIER (Off-canvas) -->
<div id="cart-sidebar" class="fixed inset-y-0 right-0 max-w-sm w-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 flex flex-col border-l border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <h2 class="text-xl font-bold text-gray-900">Mon Panier</h2>
        <button id="btn-close-cart" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    
    <div class="flex-grow p-6 overflow-y-auto" id="cart-items-container">
        <!-- Rempli dynamiquement par JS -->
        <p class="text-center text-gray-500 italic mt-10">Votre panier est vide.</p>
    </div>
    
    <div class="p-6 border-t border-gray-200 bg-gray-50">
        <div class="flex justify-between items-center mb-4">
            <span class="font-bold text-gray-700">Total :</span>
            <span class="font-black text-xl text-blue-700" id="cart-total">0,00 €</span>
        </div>
        <button id="btn-checkout" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded shadow-sm transition-colors text-center disabled:opacity-50">
            Valider la commande
        </button>
        <p class="text-xs text-gray-500 text-center mt-3">Le règlement s'effectuera directement au bureau du club.</p>
    </div>
</div>
<!-- Overlay -->
<div id="cart-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-40 hidden transition-opacity"></div>

</main>
<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> Judo Club.</p>
    </div>
</footer>

<script src="/public/js/toast.js"></script>
<?php if ($estMembreConnecte): ?>
    <script src="/public/js/boutique.js"></script>
<?php endif; ?>
</body>
</html>