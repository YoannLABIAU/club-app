<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
$rolesAffiches = array_map('strtoupper', $userRoles);
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Tableau de bord Staff</h1>
            <p class="mt-2 text-sm text-gray-500">
                Bienvenue <span class="font-bold text-blue-700"><?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?></span>.
                Accès : <span class="font-bold text-gray-700"><?= htmlspecialchars(implode(', ', $rolesAffiches), ENT_QUOTES, 'UTF-8') ?></span>.
            </p>
        </div>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <!-- PANNEAU D'ACCÈS RAPIDES (Modulaire selon rôles) -->
    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Outils disponibles</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        
        <?php if (in_array('prof', $userRoles, true) || in_array('admin', $userRoles, true)): ?>
        <a href="/seances" class="bg-white border border-gray-200 hover:border-blue-500 rounded-lg p-5 shadow-sm hover:shadow-md btn-transition flex items-center gap-4 group">
            <div class="bg-blue-100 p-3 rounded-full text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900">Appels & Présences</h3>
                <p class="text-xs text-gray-500 mt-1">Gérer les cours du jour</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if (in_array('redacteur', $userRoles, true) || in_array('admin', $userRoles, true)): ?>
        <a href="/admin/cms" class="bg-white border border-gray-200 hover:border-green-500 rounded-lg p-5 shadow-sm hover:shadow-md btn-transition flex items-center gap-4 group">
            <div class="bg-green-100 p-3 rounded-full text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900">Éditeur CMS</h3>
                <p class="text-xs text-gray-500 mt-1">Gérer les articles de l'accueil</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if (in_array('boutique', $userRoles, true) || in_array('admin', $userRoles, true)): ?>
        <a href="/admin/boutique" class="bg-white border border-gray-200 hover:border-purple-500 rounded-lg p-5 shadow-sm hover:shadow-md btn-transition flex items-center gap-4 group">
            <div class="bg-purple-100 p-3 rounded-full text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900">Gestion Boutique</h3>
                <p class="text-xs text-gray-500 mt-1">Commandes et Catalogue</p>
            </div>
        </a>
        <?php endif; ?>
    </div>

    <!-- SECTIONS PROFESSEUR -->
    <?php if (in_array('prof', $userRoles, true)): ?>
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Vos cours</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <?php if (empty($tauxParCours)): ?>
                <div class="col-span-full text-sm text-gray-500 italic">Aucune donnée disponible.</div>
            <?php else: ?>
                <?php foreach ($tauxParCours as $cours): ?>
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <p class="text-sm font-medium text-gray-600 mb-2 truncate" title="<?= htmlspecialchars($cours['nom_cours'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($cours['nom_cours'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p class="text-3xl font-bold text-gray-900"><?= $cours['taux'] ?>%</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $cours['taux'] ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- SECTIONS ADMINISTRATEUR -->
    <?php if (in_array('admin', $userRoles, true)): ?>
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Vue d'ensemble Club</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-800">
                <p class="text-sm font-medium text-gray-500 uppercase">Adhérents Inscrits</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= $totalAdherents ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-sm font-medium text-gray-500 uppercase">Cotisations Validées</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="compteur-cotisations"><?= $cotisationsValidees ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-sm font-medium text-gray-500 uppercase">Assiduité Globale</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= $tauxGlobal ?>%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <!-- Annuaire & Cotisations -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Annuaire & Trésorerie</h3>
                    <div class="flex gap-2">
                        <a href="/admin/liaisons" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Gérer les liaisons familiales</a>
                    </div>
                </div>
                <div class="overflow-x-auto max-h-96">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Identité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disciplines</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paiement</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($adherents as $adherent): ?>
                                <?php 
                                    $id = (int)$adherent['id'];
                                    $nomComplet = htmlspecialchars($adherent['nom'] . ' ' . $adherent['prenom'], ENT_QUOTES, 'UTF-8');
                                    $disciplinesArray = json_decode((string)($adherent['disciplines'] ?? '[]'), true);
                                    $disciplinesTexte = htmlspecialchars(is_array($disciplinesArray) ? implode(', ', $disciplinesArray) : '', ENT_QUOTES, 'UTF-8');
                                    $paiementValide = (int)($adherent['paiement_valide'] ?? 0) === 1;
                                ?>
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?= $nomComplet ?></td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= $disciplinesTexte ?></td>
                                    <td class="px-6 py-3 whitespace-nowrap" id="cell-paiement-<?= $id ?>">
                                        <?php if ($paiementValide): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Validé</span>
                                        <?php else: ?>
                                            <button class="btn-valider-paiement px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 hover:bg-green-100 border border-red-200 transition-colors" data-id="<?= $id ?>">En attente</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paramètres d'apparence -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Paramètres du Site</h3>
                    <a href="/admin/evenements" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Gérer les événements</a>
                </div>
                <div class="p-6">
                    <form id="form-parametres" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Couleur principale du Club</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="couleur_primaire" id="couleur_primaire" 
                                       value="<?= htmlspecialchars($parametresActuels['couleur_primaire'] ?? '#1d4ed8', ENT_QUOTES, 'UTF-8') ?>" 
                                       class="h-10 w-14 border border-gray-300 rounded cursor-pointer">
                                <span class="text-sm text-gray-500" id="hex-display"><?= htmlspecialchars($parametresActuels['couleur_primaire'] ?? '#1d4ed8', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo Officiel</label>
                            <?php if (!empty($parametresActuels['logo_url'])): ?>
                                <img src="<?= htmlspecialchars($parametresActuels['logo_url'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo actuel" class="h-16 w-16 object-contain mb-3 bg-gray-50 p-1 border rounded">
                            <?php endif; ?>
                            <input type="file" name="logo" id="logo" accept="image/png, image/jpeg, image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded">
                        </div>

                        <button type="submit" id="btn-save-parametres" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                            Appliquer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

</main>
<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> Judo Club. Environnement staff.</p>
    </div>
</footer>

<script src="/public/js/toast.js"></script>
<?php if (in_array('admin', $userRoles, true) || in_array('boutique', $userRoles, true)): ?>
    <script src="/public/js/admin.js"></script>
<?php endif; ?>
</body>
</html>