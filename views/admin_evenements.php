<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Gestion des Événements</h1>
            <p class="mt-2 text-sm text-gray-500">
                Planifiez les galas, compétitions et consultez les réponses des adhérents.
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="/admin" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition">
                Retour
            </a>
            <button id="btn-open-modal-add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Créer un événement
            </button>
        </div>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="space-y-8">
        <?php if (empty($evenements)): ?>
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200 border-dashed">
                <p class="text-gray-500 italic">Aucun événement planifié.</p>
            </div>
        <?php else: ?>
            <?php foreach ($evenements as $event): ?>
                <?php 
                    $id = (int)$event['id'];
                    $nom = htmlspecialchars($event['nom'], ENT_QUOTES, 'UTF-8');
                    $type = htmlspecialchars($event['type'], ENT_QUOTES, 'UTF-8');
                    $dateEvent = htmlspecialchars(date('d/m/Y', strtotime($event['date_evenement'])), ENT_QUOTES, 'UTF-8');
                    $description = htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8');
                    $listeParticipants = $participants[$id] ?? [];
                    
                    $dataJson = htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" id="event-<?= $id ?>">
                    <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold text-gray-900"><?= $nom ?></h2>
                                <span class="px-2 py-0.5 rounded text-xs font-bold <?= $type === 'Compétition' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= $type ?>
                                </span>
                            </div>
                            <p class="text-sm font-bold text-gray-700 mt-1"><?= $dateEvent ?></p>
                            <p class="text-sm text-gray-600 mt-2"><?= $description ?></p>
                        </div>
                        <div class="flex space-x-2 shrink-0">
                            <button class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-1.5 px-3 rounded shadow-sm text-xs btn-edit-event" data-event="<?= $dataJson ?>">
                                Modifier
                            </button>
                            <button class="bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-medium py-1.5 px-3 rounded shadow-sm text-xs btn-delete-event" data-id="<?= $id ?>">
                                Supprimer
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <h3 class="text-sm font-bold text-gray-800 uppercase mb-4">Réponses et Inscriptions</h3>
                        <?php if (empty($listeParticipants)): ?>
                            <p class="text-sm text-gray-500 italic">Aucune réponse enregistrée pour le moment.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <?php foreach ($listeParticipants as $p): ?>
                                    <?php 
                                        $nomPart = htmlspecialchars($p['prenom'] . ' ' . $p['nom'], ENT_QUOTES, 'UTF-8');
                                        $statut = htmlspecialchars($p['statut'], ENT_QUOTES, 'UTF-8');
                                        $bgClass = 'bg-gray-100 text-gray-800 border-gray-200';
                                        if ($statut === 'Oui') $bgClass = 'bg-green-100 text-green-800 border-green-200';
                                        if ($statut === 'Non') $bgClass = 'bg-red-100 text-red-800 border-red-200';
                                        if ($statut === 'Peut-être') $bgClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                        if ($statut === 'Sélectionné') $bgClass = 'bg-blue-100 text-blue-800 border-blue-200'; // Ajout manuel
                                    ?>
                                    <div class="border rounded px-3 py-2 flex items-center justify-between <?= $bgClass ?>">
                                        <span class="text-sm font-medium truncate" title="<?= $nomPart ?>"><?= $nomPart ?></span>
                                        <span class="text-xs font-bold shrink-0 ml-2"><?= $statut ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Zone de recherche spécifique pour les Compétitions -->
                        <?php if ($type === 'Compétition'): ?>
                            <div class="mt-6 pt-4 border-t border-gray-100">
                                <h4 class="text-sm font-bold text-gray-700 mb-2">Convoquer / Inscrire un élève :</h4>
                                <div class="relative w-full md:w-96">
                                    <input type="text" class="search-compet block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3 pl-9" 
                                           data-event-id="<?= $id ?>" placeholder="Rechercher par nom ou prénom...">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <ul id="results-compet-<?= $id ?>" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto hidden">
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODALE AJOUT/MODIFICATION ÉVÉNEMENT -->
<div id="modal-event" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-overlay"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="form-event">
                <input type="hidden" name="id" id="event_id" value="0">
                
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-event-title">Créer un événement</h3>
                    <button type="button" id="btn-close-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-6 py-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type d'événement *</label>
                        <select name="type" id="event_type" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                            <option value="Gala">Gala / Animation (Inscriptions Famille)</option>
                            <option value="Compétition">Compétition (Inscriptions / Convocations Staff)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom de l'événement *</label>
                        <input type="text" name="nom" id="event_nom" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de l'événement *</label>
                        <input type="date" name="date_evenement" id="event_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description *</label>
                        <textarea name="description" id="event_desc" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3"></textarea>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3 border-t border-gray-200">
                    <button type="button" id="btn-cancel" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded shadow-sm text-sm">
                        Annuler
                    </button>
                    <button type="submit" id="btn-save-event" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded shadow-sm text-sm transition-colors">
                        Enregistrer
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
<script src="/public/js/admin_evenements.js"></script>
</body>
</html>