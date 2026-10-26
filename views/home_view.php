<?php
declare(strict_types=1);

// Inclusion du layout d'en-tête (Head + Navigation)
require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    
    <!-- Bannière Hero informative -->
    <div class="bg-white rounded-lg shadow-md p-8 mb-10 text-center border-t-4 border-blue-700">
        <img src="/public/images/logo-panda.png" alt="Logo Judo Club" class="h-24 w-24 mx-auto mb-4 object-contain" onerror="this.style.display='none'">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-4">Bienvenue au Judo Club</h1>
        <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
            Découvrez la voie de la souplesse. Notre club vous accueille toute l'année pour pratiquer le Judo, le MMA et le TFT dans le plus grand respect des valeurs martiales.
        </p>
        <a href="/inscription" class="inline-flex items-center justify-center bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-8 rounded-lg shadow-md btn-transition text-lg">
            Rejoindre le club
        </a>
    </div>

    <!-- Gestion d'erreur éventuelle -->
    <?php if (isset($erreur) && $erreur !== null): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <p class="font-bold">Information</p>
            <p><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <!-- Section CMS : Actualités du Club -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b-2 border-blue-200 pb-2 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            Dernières Actualités
        </h2>
        
        <div class="grid grid-cols-1 gap-8">
            <?php if (empty($articles)): ?>
                <div class="text-center py-10 bg-white rounded-lg border border-gray-200 border-dashed">
                    <p class="text-gray-500 italic">Aucune actualité publiée pour le moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <article class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col hover:shadow-md btn-transition">
                        <header class="mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-2xl font-extrabold text-gray-900 mb-2">
                                <?= htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <time class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                Publié le <?= htmlspecialchars(date('d/m/Y', strtotime($article['date_publication'])), ENT_QUOTES, 'UTF-8') ?>
                            </time>
                        </header>
                        
                        <!-- Rendu dynamique des blocs CMS -->
                        <div class="text-gray-700 space-y-4">
                            <?php 
                                $contenuBrut = (string)$article['contenu'];
                                $blocs = json_decode($contenuBrut, true);
                                
                                if (is_array($blocs)) {
                                    // Affichage issu du nouveau Builder
                                    foreach ($blocs as $bloc) {
                                        if (isset($bloc['type']) && $bloc['type'] === 'text') {
                                            $texte = htmlspecialchars($bloc['content'] ?? '', ENT_QUOTES, 'UTF-8');
                                            echo '<p class="whitespace-pre-line">' . $texte . '</p>';
                                        } elseif (isset($bloc['type']) && $bloc['type'] === 'image') {
                                            $url = htmlspecialchars($bloc['url'] ?? '', ENT_QUOTES, 'UTF-8');
                                            echo '<div class="py-4"><img src="' . $url . '" alt="Illustration de l\'article" class="max-w-full h-auto rounded-lg shadow-sm mx-auto max-h-[500px] object-cover"></div>';
                                        }
                                    }
                                } else {
                                    // Fallback de rétrocompatibilité pour le texte brut (anciens articles)
                                    echo '<p class="whitespace-pre-line">' . htmlspecialchars($contenuBrut, ENT_QUOTES, 'UTF-8') . '</p>';
                                }
                            ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

</main>

<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> Judo Club. Projet MVP pour démonstration.</p>
    </div>
</footer>

<script src="/public/js/toast.js"></script>
</body>
</html>