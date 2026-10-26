<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Mon Espace Famille</h1>
        <p class="mt-2 text-sm text-gray-500">
            Gérez les informations de contact de vos enfants et répondez aux invitations du club.
        </p>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <!-- SECTION 1 : Fiches Adhérents -->
    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Fiches Adhérents</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        <?php if (empty($adherentsLiees)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-lg border border-gray-200 border-dashed">
                <p class="text-gray-500 italic">Aucune fiche adhérent n'est rattachée à votre compte.</p>
                <p class="text-sm text-gray-400 mt-2">Veuillez contacter le club pour effectuer la liaison de vos dossiers.</p>
            </div>
        <?php else: ?>
            <?php foreach ($adherentsLiees as $adherent): ?>
                <?php 
                    $id = (int)$adherent['id'];
                    $nom = htmlspecialchars($adherent['nom'] ?? '', ENT_QUOTES, 'UTF-8');
                    $prenom = htmlspecialchars($adherent['prenom'] ?? '', ENT_QUOTES, 'UTF-8');
                    $relation = htmlspecialchars($adherent['relation'] ?? 'Membre', ENT_QUOTES, 'UTF-8');
                    $grade = htmlspecialchars($adherent['ceinture'] ?? '', ENT_QUOTES, 'UTF-8');
                    $sante = htmlspecialchars($adherent['problemes_sante'] ?? 'Aucun', ENT_QUOTES, 'UTF-8');
                    $telephone = htmlspecialchars($adherent['telephone'] ?? '', ENT_QUOTES, 'UTF-8');
                    $urgenceNom = htmlspecialchars($adherent['urgence_nom'] ?? '', ENT_QUOTES, 'UTF-8');
                    $urgenceTel = htmlspecialchars($adherent['urgence_telephone'] ?? '', ENT_QUOTES, 'UTF-8');
                    
                    $dataJson = htmlspecialchars(json_encode($adherent), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-start bg-gray-50">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900"><?= $prenom ?> <?= $nom ?></h2>
                            <!-- CORRECTION UX : Contextualisation de la relation -->
                            <p class="text-xs font-medium text-blue-600 uppercase mt-1">Votre rôle : <?= $relation ?></p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                            <?= $grade ?>
                        </span>
                    </div>
                    
                    <div class="p-5 flex-grow space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Contact principal</p>
                            <p class="text-sm text-gray-900"><?= $telephone ?: 'Non renseigné' ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Problèmes de santé signalés</p>
                            <p class="text-sm text-gray-900"><?= $sante ?: 'Aucun' ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Contact d'urgence</p>
                            <p class="text-sm text-gray-900"><?= $urgenceNom ?> (<?= $urgenceTel ?>)</p>
                        </div>
                    </div>

                    <div class="p-4 border-t border-gray-100 bg-gray-50 mt-auto">
                        <button class="w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium py-2 px-4 rounded shadow-sm transition-colors text-sm btn-edit-adherent"
                                data-adherent="<?= $dataJson ?>">
                            Modifier les informations
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- SECTION 2 : Événements à venir -->
    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Événements à venir (Galas, Compétitions)</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if (empty($evenements)): ?>
            <div class="col-span-full text-center py-8 bg-white rounded-lg border border-gray-200 border-dashed">
                <p class="text-gray-500 italic">Aucun événement n'est planifié pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($evenements as $event): ?>
                <?php 
                    $eventId = (int)$event['id'];
                    $dateEvent = date('d/m/Y', strtotime($event['date_evenement']));
                ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-blue-50">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($event['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <span class="text-sm font-bold text-blue-800"><?= htmlspecialchars($dateEvent, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <p class="text-sm text-gray-700"><?= htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    
                    <div class="p-5">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase">Participation :</h4>
                        <?php if (empty($adherentsLiees)): ?>
                            <p class="text-sm text-red-500 italic">Aucun adhérent lié pour répondre.</p>
                        <?php else: ?>
                            <ul class="space-y-3">
                                <?php foreach ($adherentsLiees as $adherent): ?>
                                    <?php 
                                        $adId = (int)$adherent['id'];
                                        $statutActuel = $participations[$adId][$eventId] ?? 'En attente';
                                        $nomEnfant = htmlspecialchars($adherent['prenom'] . ' ' . $adherent['nom'], ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <li class="flex items-center justify-between bg-gray-50 p-3 rounded border border-gray-100">
                                        <span class="text-sm font-medium text-gray-900"><?= $nomEnfant ?></span>
                                        <select class="select-participation block w-40 pl-3 pr-10 py-1 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm"
                                                data-adherent-id="<?= $adId ?>" 
                                                data-evenement-id="<?= $eventId ?>">
                                            <option value="En attente" <?= $statutActuel === 'En attente' ? 'selected' : '' ?> disabled>À confirmer</option>
                                            <option value="Oui" <?= $statutActuel === 'Oui' ? 'selected' : '' ?>>Oui, sera présent</option>
                                            <option value="Non" <?= $statutActuel === 'Non' ? 'selected' : '' ?>>Non, absent</option>
                                            <option value="Peut-être" <?= $statutActuel === 'Peut-être' ? 'selected' : '' ?>>Peut-être</option>
                                        </select>
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

<!-- MODALE D'ÉDITION -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-overlay"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <form id="form-edit-adherent">
                <input type="hidden" name="adherent_id" id="edit_adherent_id">
                
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Mise à jour des informations</h3>
                    <button type="button" id="btn-close-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-6 py-6 space-y-6 max-h-[60vh] overflow-y-auto">
                    <div>
                        <h4 class="font-bold text-blue-800 border-b pb-1 mb-3">Coordonnées personnelles</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Adresse</label>
                                <input type="text" name="adresse" id="edit_adresse" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="edit_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone Adhérent</label>
                                <input type="tel" name="telephone" id="edit_telephone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone Père</label>
                                <input type="tel" name="telephone_pere" id="edit_telephone_pere" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone Mère</label>
                                <input type="tel" name="telephone_mere" id="edit_telephone_mere" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-bold text-red-700 border-b border-red-200 pb-1 mb-3">Santé et Urgence</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Problèmes de santé (Allergies, etc.)</label>
                                <input type="text" name="problemes_sante" id="edit_problemes_sante" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom Contact Urgence</label>
                                <input type="text" name="urgence_nom" id="edit_urgence_nom" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone Urgence</label>
                                <input type="tel" name="urgence_telephone" id="edit_urgence_telephone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3 border-t border-gray-200">
                    <button type="button" id="btn-cancel" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded shadow-sm text-sm">
                        Annuler
                    </button>
                    <button type="submit" id="btn-save-edit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded shadow-sm text-sm transition-colors">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</main>
<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> Judo Club. Espace sécurisé.</p>
    </div>
</footer>

<script src="/public/js/toast.js"></script>
<script src="/public/js/famille.js"></script>
</body>
</html>