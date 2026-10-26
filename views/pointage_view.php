<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';

$totalAdherents = count($adherents);
$presentsAujourdhui = count(array_filter($adherents, function($a) {
    return $a['statut_actuel'] === 'Present';
}));
?>
<style>
    .is-loading { opacity: 0.5; pointer-events: none; }
</style>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-6 flex flex-col md:flex-row md:items-start justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <a href="/seances?date=<?= htmlspecialchars($dateSeance, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 mb-2 btn-transition">
                <svg class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Retour aux créneaux
            </a>
            <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                Appel : <?= htmlspecialchars($creneau['nom'] ?? 'Séance Inconnue', ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="text-sm font-bold text-blue-700 mt-1">Date : <?= htmlspecialchars(date('d/m/Y', strtotime($dateSeance)), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        
        <div class="flex flex-col items-end gap-3 w-full md:w-auto">
            <!-- Statistiques rapides -->
            <div class="flex gap-4">
                <div class="bg-white rounded-lg shadow px-4 py-2 border-l-4 border-blue-700">
                    <span class="text-xs text-gray-500 uppercase font-bold block">Inscrits ciblés</span>
                    <span class="text-xl font-black text-gray-900"><?= $totalAdherents ?></span>
                </div>
                <div class="bg-white rounded-lg shadow px-4 py-2 border-l-4 border-green-500">
                    <span class="text-xs text-gray-500 uppercase font-bold block">Présents</span>
                    <span class="text-xl font-black text-gray-900" id="compteur-presents"><?= $presentsAujourdhui ?></span>
                </div>
            </div>

            <!-- Barre de recherche Hors-Créneau -->
            <div class="relative w-full md:w-72">
                <input type="text" id="search-hors-creneau" placeholder="Ajouter un élève (Hors-créneau)..." 
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border py-2 px-3 pl-9">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <!-- Résultats de recherche -->
                <ul id="search-results" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm hidden">
                </ul>
            </div>
        </div>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($adherents as $adherent): ?>
            <?php 
                $id = (int)$adherent['id'];
                $nom = htmlspecialchars($adherent['nom'], ENT_QUOTES, 'UTF-8');
                $prenom = htmlspecialchars($adherent['prenom'], ENT_QUOTES, 'UTF-8');
                $discipline = htmlspecialchars($adherent['discipline'], ENT_QUOTES, 'UTF-8');
                $statut = htmlspecialchars($adherent['statut_actuel'], ENT_QUOTES, 'UTF-8');
                
                $isPresent = ($statut === 'Present');
                $isAbsent = ($statut === 'Absent');
                
                $btnPresentClass = $isPresent ? 'bg-green-600 text-white border-transparent shadow-inner' : 'bg-white text-gray-700 border-gray-300 hover:bg-green-50 hover:text-green-700 hover:border-green-500';
                $btnAbsentClass = $isAbsent ? 'bg-red-600 text-white border-transparent shadow-inner' : 'bg-white text-gray-700 border-gray-300 hover:bg-red-50 hover:text-red-700 hover:border-red-500';
            ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col justify-between" id="card-adherent-<?= $id ?>">
                
                <div class="mb-4">
                    <div class="flex justify-between items-start">
                        <h2 class="text-lg font-bold text-gray-900"><?= $nom ?> <?= $prenom ?></h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 truncate max-w-[100px]" title="<?= $discipline ?>">
                            <?= $discipline ?>
                        </span>
                    </div>
                </div>

                <div class="flex space-x-2 mt-auto">
                    <button class="action-pointage btn-transition flex-1 py-2 px-3 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500 <?= $btnPresentClass ?>"
                            data-adherent-id="<?= $id ?>" 
                            data-seance-id="<?= $seanceId ?>"
                            data-statut="Present">
                        Présent
                    </button>
                    <button class="action-pointage btn-transition flex-1 py-2 px-3 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 <?= $btnAbsentClass ?>"
                            data-adherent-id="<?= $id ?>" 
                            data-seance-id="<?= $seanceId ?>"
                            data-statut="Absent">
                        Absent
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</main>
<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> Judo Club.</p>
    </div>
</footer>

<script>
    // Variables globales pour le script pointage.js
    window.CURRENT_SEANCE_ID = <?= (int)$seanceId ?>;
</script>
<script src="/public/js/toast.js"></script>
<script src="/public/js/pointage.js"></script>
</body>
</html>