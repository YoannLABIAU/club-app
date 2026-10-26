<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Catalogue Boutique</h1>
            <p class="mt-2 text-sm text-gray-500">
                Gérez les articles de la boutique en ligne.
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="/admin/boutique" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition">
                Retour Trésorerie
            </a>
            <button id="btn-open-modal-add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Ajouter un article
            </button>
        </div>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (empty($produits)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-lg border border-gray-200 border-dashed">
                <p class="text-gray-500 italic">Le catalogue est actuellement vide.</p>
            </div>
        <?php else: ?>
            <?php foreach ($produits as $produit): ?>
                <?php 
                    $id = (int)$produit['id'];
                    $nom = htmlspecialchars($produit['nom'], ENT_QUOTES, 'UTF-8');
                    $prix = (float)$produit['prix'];
                    $stock = (int)$produit['stock'];
                    $desc = htmlspecialchars((string)$produit['description'], ENT_QUOTES, 'UTF-8');
                    $restreint = (int)$produit['acces_restreint'] === 1;
                    $imageUrl = htmlspecialchars((string)$produit['image_url'], ENT_QUOTES, 'UTF-8');
                    if (empty($imageUrl)) $imageUrl = '/public/images/default-product.png';
                    
                    $dataJson = htmlspecialchars(json_encode($produit), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col relative" id="prod-<?= $id ?>">
                    <?php if ($restreint): ?>
                        <div class="absolute top-2 right-2 bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded shadow-sm z-10">
                            Membres uniquement
                        </div>
                    <?php endif; ?>
                    
                    <div class="h-48 w-full bg-gray-50 border-b border-gray-200 overflow-hidden flex items-center justify-center p-2">
                        <img src="<?= $imageUrl ?>" alt="Image de <?= $nom ?>" class="max-h-full max-w-full object-contain" onerror="this.src='/public/images/default-product.png'">
                    </div>

                    <div class="p-5 flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight mb-1"><?= $nom ?></h3>
                            <p class="text-lg font-black text-blue-700 mb-2"><?= number_format($prix, 2, ',', ' ') ?> €</p>
                            <p class="text-xs text-gray-500 font-medium mb-3">En stock : <?= $stock ?></p>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?= $desc ?></p>
                        </div>
                        
                        <div class="flex space-x-2 mt-auto pt-2 border-t border-gray-100">
                            <button class="flex-1 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-1.5 rounded shadow-sm text-xs btn-edit-prod" data-product="<?= $dataJson ?>">
                                Éditer
                            </button>
                            <button class="flex-1 bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 font-medium py-1.5 rounded shadow-sm text-xs btn-delete-prod" data-id="<?= $id ?>">
                                Retirer
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODALE AJOUT/MODIFICATION PRODUIT -->
<div id="modal-product" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-overlay"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="form-product" enctype="multipart/form-data">
                <input type="hidden" name="id" id="prod_id" value="0">
                
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-prod-title">Ajouter un produit</h3>
                    <button type="button" id="btn-close-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-6 py-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom de l'article *</label>
                        <input type="text" name="nom" id="prod_nom" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prix unitaire (€) *</label>
                            <input type="number" step="0.01" min="0.01" name="prix" id="prod_prix" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock initial</label>
                            <input type="number" min="0" name="stock" id="prod_stock" placeholder="Optionnel (Défaut 0)" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description détaillée *</label>
                        <textarea name="description" id="prod_desc" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3"></textarea>
                    </div>

                    <div class="pt-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image du produit</label>
                        <input type="file" name="image" id="prod_image" accept="image/png, image/jpeg, image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded">
                        <p class="text-xs text-gray-500 mt-1">Laissez vide pour utiliser l'image par défaut. (Formats : JPG, PNG, WEBP)</p>
                    </div>

                    <div class="flex items-center pt-2">
                        <input type="checkbox" name="acces_restreint" id="prod_restreint" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="prod_restreint" class="ml-2 block text-sm text-gray-900 font-medium">
                            Article réservé exclusivement aux membres inscrits
                        </label>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3 border-t border-gray-200">
                    <button type="button" id="btn-cancel" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded shadow-sm text-sm">
                        Annuler
                    </button>
                    <button type="submit" id="btn-save-prod" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded shadow-sm text-sm transition-colors">
                        Enregistrer l'article
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</main>
<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-400">
        &copy; <?= date('Y') ?> Judo Club. Environnement Trésorerie.
    </div>
</footer>

<script src="/public/js/toast.js"></script>
<script src="/public/js/admin_catalogue.js"></script>
</body>
</html>