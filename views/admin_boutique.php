<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Gestion Boutique & Trésorerie</h1>
            <p class="mt-2 text-sm text-gray-500">
                Suivez les commandes, mettez à jour les statuts de paiement et ajustez vos stocks.
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="/admin" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition">
                Retour
            </a>
            <!-- AJOUT DU BOUTON MANQUANT VERS LA GESTION DU CATALOGUE -->
            <a href="/admin/catalogue" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Gérer le catalogue
            </a>
        </div>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <!-- SECTION 1 : Suivi des Commandes -->
    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Suivi des commandes</h2>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-12">
        <div class="overflow-x-auto max-h-96">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($commandes)): ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 italic">Aucune commande enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($commandes as $cmd): ?>
                            <?php 
                                $id = (int)$cmd['id'];
                                $statut = htmlspecialchars($cmd['statut'], ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-900">
                                    #<?= str_pad((string)$id, 5, '0', STR_PAD_LEFT) ?><br>
                                    <span class="text-xs font-normal text-gray-500"><?= htmlspecialchars(date('d/m/Y', strtotime($cmd['date_commande'])), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars((string)$cmd['client_email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-3 text-sm text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars((string)$cmd['details'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string)$cmd['details'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-black text-gray-900"><?= number_format((float)$cmd['total'], 2, ',', ' ') ?> €</td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <select class="select-statut-commande block w-full pl-3 pr-8 py-1 text-xs border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md shadow-sm"
                                            data-id="<?= $id ?>">
                                        <option value="En attente de paiement" <?= $statut === 'En attente de paiement' ? 'selected' : '' ?>>En attente</option>
                                        <option value="Payée" <?= $statut === 'Payée' ? 'selected' : '' ?>>Payée</option>
                                        <option value="Distribuée" <?= $statut === 'Distribuée' ? 'selected' : '' ?>>Distribuée</option>
                                        <option value="Annulée" <?= $statut === 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 2 : Gestion des Stocks Rapide -->
    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Ajustement Rapide des Stocks</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($produits as $produit): ?>
            <?php 
                $pid = (int)$produit['id'];
                $stock = (int)$produit['stock'];
                $nom = htmlspecialchars($produit['nom'], ENT_QUOTES, 'UTF-8');
            ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col justify-between">
                <div class="mb-4">
                    <h3 class="text-md font-bold text-gray-900"><?= $nom ?></h3>
                    <p class="text-xs text-gray-500 mt-1">Prix unitaire : <?= number_format((float)$produit['prix'], 2, ',', ' ') ?> €</p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Stock :</label>
                    <input type="number" min="0" value="<?= $stock ?>" id="stock-input-<?= $pid ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-1 px-2 text-center">
                    <button class="btn-update-stock bg-blue-600 hover:bg-blue-700 text-white p-1.5 rounded shadow-sm transition-colors" data-id="<?= $pid ?>" title="Mettre à jour le stock">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</main>
<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> Judo Club. Environnement Trésorerie.</p>
    </div>
</footer>

<script src="/public/js/toast.js"></script>
<script src="/public/js/admin_boutique.js"></script>
</body>
</html>