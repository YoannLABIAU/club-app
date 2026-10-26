<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Liaisons Familiales</h1>
            <p class="mt-2 text-sm text-gray-500">
                Associez les fiches adhérents aux comptes utilisateurs (parents) pour le portail famille.
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="/admin" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition">
                Retour
            </a>
            <button id="btn-open-modal-add" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Créer une liaison
            </button>
        </div>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($parents)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-lg border border-gray-200 border-dashed">
                <p class="text-gray-500 italic">Aucun compte parent détecté ou configuré.</p>
            </div>
        <?php else: ?>
            <?php foreach ($parents as $parent): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <h2 class="text-md font-bold text-gray-900 truncate" title="<?= htmlspecialchars($parent['email'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($parent['email'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-3">Fiches rattachées</h3>
                        <?php if (empty($parent['adherents'])): ?>
                            <p class="text-sm text-gray-400 italic">Aucune fiche liée.</p>
                        <?php else: ?>
                            <ul class="space-y-3">
                                <?php foreach ($parent['adherents'] as $lien): ?>
                                    <?php 
                                        $uid = (int)$lien['utilisateur_id'];
                                        $aid = (int)$lien['adherent_id'];
                                        $nomComplet = htmlspecialchars($lien['prenom'] . ' ' . $lien['nom'], ENT_QUOTES, 'UTF-8');
                                        $relation = htmlspecialchars($lien['relation'], ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <li class="flex items-center justify-between text-sm bg-gray-50 px-3 py-2 rounded border border-gray-100" id="liaison-<?= $uid ?>-<?= $aid ?>">
                                        <div>
                                            <span class="font-medium text-gray-900 block"><?= $nomComplet ?></span>
                                            <span class="text-xs text-blue-600 font-bold"><?= $relation ?></span>
                                        </div>
                                        <button class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded btn-delete-liaison" 
                                                data-uid="<?= $uid ?>" data-aid="<?= $aid ?>" title="Retirer la liaison">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODALE D'AJOUT DE LIAISON -->
<div id="modal-liaison" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-overlay"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="form-liaison">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-gray-900">Nouvelle Liaison</h3>
                    <button type="button" id="btn-close-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-6 py-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Compte Utilisateur (Parent) *</label>
                        <select name="utilisateur_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            <option value="" disabled selected>Sélectionner un compte parent</option>
                            <?php foreach ($parents as $parent): ?>
                                <option value="<?= (int)$parent['id'] ?>"><?= htmlspecialchars($parent['email'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fiche Adhérent *</label>
                        <select name="adherent_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            <option value="" disabled selected>Sélectionner une fiche adhérent</option>
                            <?php foreach ($adherents as $adherent): ?>
                                <option value="<?= (int)$adherent['id'] ?>"><?= htmlspecialchars($adherent['prenom'] . ' ' . $adherent['nom'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lien de parenté / Relation *</label>
                        <select name="relation" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            <option value="Père">Père</option>
                            <option value="Mère">Mère</option>
                            <option value="Tuteur légal">Tuteur légal</option>
                            <option value="Lui-même">Lui-même (Adulte)</option>
                        </select>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3 border-t border-gray-200">
                    <button type="button" id="btn-cancel" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded shadow-sm text-sm">
                        Annuler
                    </button>
                    <button type="submit" id="btn-save-liaison" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded shadow-sm text-sm transition-colors">
                        Lier les fiches
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</main>
<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-400">
        &copy; <?= date('Y') ?> Judo Club. Environnement staff.
    </div>
</footer>

<script src="/public/js/toast.js"></script>
<script src="/public/js/admin_liaisons.js"></script>
</body>
</html>