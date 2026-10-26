<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Historique de mes commandes</h1>
        <p class="mt-2 text-sm text-gray-500">
            Retrouvez ici le suivi de vos achats sur la boutique du club.
        </p>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Cmd</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Détails des articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($commandes)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 italic">
                                Vous n'avez passé aucune commande pour le moment.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($commandes as $cmd): ?>
                            <?php 
                                $statut = htmlspecialchars($cmd['statut'], ENT_QUOTES, 'UTF-8');
                                $bgClass = 'bg-gray-100 text-gray-800';
                                if ($statut === 'Payée') $bgClass = 'bg-blue-100 text-blue-800';
                                if ($statut === 'Distribuée') $bgClass = 'bg-green-100 text-green-800';
                                if ($statut === 'Annulée') $bgClass = 'bg-red-100 text-red-800';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">#<?= str_pad((string)$cmd['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($cmd['date_commande'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" title="<?= htmlspecialchars((string)$cmd['details'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string)$cmd['details'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900"><?= number_format((float)$cmd['total'], 2, ',', ' ') ?> €</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $bgClass ?>">
                                        <?= $statut ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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