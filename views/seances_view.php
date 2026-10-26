<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Séances programmées</h1>
            <p class="mt-2 text-sm text-gray-500">
                Sélectionnez un créneau pour procéder à l'appel.
            </p>
        </div>
        
        <!-- Sélecteur de date rétroactive -->
        <form method="GET" action="/seances" class="flex items-center gap-2 bg-blue-50 p-2 rounded-lg border border-blue-100 shadow-sm">
            <label for="date-select" class="text-sm font-bold text-blue-800 whitespace-nowrap">Date de l'appel :</label>
            <input type="date" name="date" id="date-select" value="<?= htmlspecialchars($dateSeance, ENT_QUOTES, 'UTF-8') ?>" 
                   class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-1.5 px-3"
                   onchange="this.form.submit()">
        </form>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($creneaux)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-lg border border-gray-200 border-dashed">
                <p class="text-gray-500 italic">Aucun créneau régulier n'est planifié pour ce jour de la semaine.</p>
            </div>
        <?php else: ?>
            <?php foreach ($creneaux as $creneau): ?>
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6 flex flex-col hover:shadow-md btn-transition">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-600 p-3 rounded-full text-white mr-4 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 leading-tight">
                                <?= htmlspecialchars($creneau['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>
                            <p class="text-xs font-bold text-blue-600 mt-1">
                                <?= htmlspecialchars($creneau['heure_debut'] . ' - ' . $creneau['heure_fin'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Prof : <?= htmlspecialchars($creneau['professeur_email'] ?? 'Non assigné', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-auto pt-4 border-t border-gray-100">
                        <a href="/seances?creneau_id=<?= (int)$creneau['id'] ?>&date=<?= htmlspecialchars($dateSeance, ENT_QUOTES, 'UTF-8') ?>" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-700 hover:bg-blue-800 transition-colors">
                            Faire l'appel
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

</main>
<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> Judo Club.</p>
    </div>
</footer>
</body>
</html>