<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';

// Variables d'hydratation (Modification)
$editId = 0;
$editTitre = '';
$editBlocs = '[]';

if (isset($article) && $article !== null) {
    $editId = (int)$article['id'];
    $editTitre = $article['titre'];
    $editBlocs = $article['contenu'];
}
?>

<div class="max-w-5xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <a href="/admin/cms" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 mb-2 btn-transition">
                <svg class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Retour aux articles
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Éditeur d'Article (CMS)</h1>
        </div>
        <div class="flex space-x-3">
            <button id="btn-preview" class="bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition">
                Prévisualiser
            </button>
            <button id="btn-save" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm font-medium text-sm btn-transition">
                <?= $editId > 0 ? 'Mettre à jour l\'article' : 'Publier l\'article' ?>
            </button>
        </div>
    </div>

    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <label for="article-titre" class="block text-sm font-bold text-gray-700 mb-2">Titre de l'article *</label>
        <input type="text" id="article-titre" value="<?= htmlspecialchars($editTitre, ENT_QUOTES, 'UTF-8') ?>" placeholder="Saisissez un titre percutant..." class="block w-full text-lg shadow-sm border-gray-300 rounded-md py-3 px-4 border focus:ring-blue-500 focus:border-blue-500">
    </div>

    <!-- Interface du Builder -->
    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Espace de travail central -->
        <div class="flex-grow">
            <div id="builder-workspace" class="space-y-4 min-h-[300px] p-4 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex flex-col">
                <!-- Rempli dynamiquement par JavaScript -->
            </div>
        </div>

        <!-- Panneau latéral : Outils -->
        <div class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sticky top-6">
                <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Ajouter un bloc</h3>
                <div class="space-y-3">
                    <button id="btn-add-text" class="w-full flex items-center gap-3 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 text-gray-700 hover:text-blue-700 px-4 py-3 rounded-md font-medium text-sm transition-colors text-left">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                        Paragraphe
                    </button>
                    <button id="btn-add-image" class="w-full flex items-center gap-3 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 text-gray-700 hover:text-blue-700 px-4 py-3 rounded-md font-medium text-sm transition-colors text-left">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Image Upload
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modale de Prévisualisation (Masquée par défaut) -->
<div id="modal-preview" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-overlay"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-gray-100 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
            
            <div class="bg-white px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Prévisualisation</h3>
                <button id="btn-close-modal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="px-4 py-8 sm:px-6 lg:px-8 max-h-[75vh] overflow-y-auto">
                <article class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    <h1 id="preview-title" class="text-3xl font-extrabold text-gray-900 mb-6"></h1>
                    <div id="preview-content" class="text-gray-700 whitespace-pre-line space-y-4"></div>
                </article>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                <button type="button" id="btn-modal-close-bottom" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm">
                    Fermer la prévisualisation
                </button>
            </div>
        </div>
    </div>
</div>

</main>

<!-- Transfert des données existantes vers le JS -->
<script>
    window.INITIAL_DATA = {
        id: <?= $editId ?>,
        titre: <?= json_encode($editTitre) ?>,
        blocs: <?= $editBlocs ?: '[]' ?>
    };
</script>

<script src="/public/js/toast.js"></script>
<script src="/public/js/builder.js"></script>
</body>
</html>