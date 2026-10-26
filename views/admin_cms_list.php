<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-8 border-b border-gray-200 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Gestion de la Vitrine (CMS)</h1>
            <p class="mt-2 text-sm text-gray-500">
                Gérez les articles affichés sur la page d'accueil.
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="/admin" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition">
                Retour au tableau de bord
            </a>
            <a href="/admin/cms/builder" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Créer un article
            </a>
        </div>
    </div>

    <?php if (isset($erreur) &&$erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre de l'article</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de publication</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center italic">
                                Aucun article publié pour le moment.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($articles as$article): ?>
                            <?php $id = (int)$article['id']; ?>
                            <tr id="row-article-<?= $id ?>" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">
                                        <?= htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($article['date_publication'])), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="/admin/cms/builder?id=<?= $id ?>" class="text-blue-600 hover:text-blue-900 font-bold mr-4">
                                        Éditer
                                    </a>
                                    <button class="text-red-600 hover:text-red-900 font-bold" onclick="supprimerArticle(<?= $id ?>)">
                                        Supprimer
                                    </button>
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
    <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-400">
        &copy; <?= date('Y') ?> Judo Club. Environnement staff.
    </div>
</footer>

<script src="/public/js/toast.js"></script>
<script>
/**
 * Demande confirmation et supprime un article via l'API.
 * @param {number} id - L'identifiant de l'article.
 * @returns {Promise<void>}
 */
async function supprimerArticle(id) {
    if (!confirm("Voulez-vous vraiment supprimer cet article ? Cette action est irréversible.")) return;

    try {
        const response = await fetch('/admin/cms/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();

        if (response.ok && result.success) {
            document.getElementById(`row-article-${id}`).remove();
            if (window.Toast) window.Toast.show(result.message, 'success', 3000);
        } else {
            if (window.Toast) window.Toast.show(result.message || "Erreur de suppression.", 'error', 4000);
        }
    } catch (error) {
        console.error("Erreur Fetch:", error);
        if (window.Toast) window.Toast.show("Erreur réseau.", 'error', 4000);
    }
}
</script>
</body>
</html>