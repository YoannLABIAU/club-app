<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Parametre.php';

// Récupération sécurisée des paramètres du site
$parametres = [];
try {
    $parametres = Parametre::getAllAsKeyValue();
} catch (Exception $e) {
    // Fallback silencieux en cas d'erreur de base de données
}

$couleurPrimaire = htmlspecialchars($parametres['couleur_primaire'] ?? '#1d4ed8', ENT_QUOTES, 'UTF-8');
$logoUrl = htmlspecialchars($parametres['logo_url'] ?? '', ENT_QUOTES, 'UTF-8');

// Récupération des rôles (Tableau)
$userRoles = $_SESSION['user_roles'] ?? [];
$isLoggedIn = isset($_SESSION['user_id']);

$isStaff = !empty(array_intersect(['admin', 'prof', 'redacteur', 'boutique'], $userRoles));
$isParent = in_array('parent', $userRoles, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judo Club</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Redéfinition dynamique de la palette de couleurs Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blue: {
                            50: '<?= $couleurPrimaire ?>10', // Transparences simulées
                            100: '<?= $couleurPrimaire ?>20',
                            200: '<?= $couleurPrimaire ?>30',
                            500: '<?= $couleurPrimaire ?>',
                            600: '<?= $couleurPrimaire ?>',
                            700: '<?= $couleurPrimaire ?>',
                            800: '<?= $couleurPrimaire ?>',
                            900: '<?= $couleurPrimaire ?>'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .btn-transition { transition: all 0.2s ease-in-out; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased flex flex-col min-h-screen">

    <nav class="bg-blue-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <?php if (!empty($logoUrl)): ?>
                            <img src="<?= $logoUrl ?>" alt="Logo" class="h-10 w-10 object-contain bg-white rounded-full p-1">
                        <?php else: ?>
                            <span>🥋</span>
                        <?php endif; ?>
                        <span class="hidden sm:block">Judo Club</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-1 sm:space-x-2 overflow-x-auto">
                    <a href="/" class="hover:text-blue-200 hover:bg-blue-900 px-3 py-2 rounded-md text-sm font-medium btn-transition">Accueil</a>
                    <a href="/inscription" class="hover:text-blue-200 hover:bg-blue-900 px-3 py-2 rounded-md text-sm font-medium btn-transition">S'inscrire</a>
                    <a href="/boutique" class="hover:text-blue-200 hover:bg-blue-900 px-3 py-2 rounded-md text-sm font-medium btn-transition">Boutique</a>
                    
                    <?php if ($isLoggedIn): ?>
                        <div class="border-l border-blue-600 mx-2 h-6"></div>
                        
                        <?php if ($isParent): ?>
                            <a href="/espace-famille" class="hover:text-blue-200 hover:bg-blue-900 px-3 py-2 rounded-md text-sm font-medium btn-transition">Mon Espace</a>
                        <?php endif; ?>
                        
                        <?php if ($isStaff): ?>
                            <a href="/admin" class="hover:text-blue-200 hover:bg-blue-900 px-3 py-2 rounded-md text-sm font-medium btn-transition flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Espace Staff
                            </a>
                        <?php endif; ?>
                        
                        <a href="/admin/logout" class="bg-red-600 hover:bg-red-700 px-3 py-2 rounded-md text-sm font-medium btn-transition ml-2">Quitter</a>
                    <?php else: ?>
                        <a href="/admin/login" class="bg-blue-600 hover:bg-blue-700 border border-blue-500 px-3 py-2 rounded-md text-sm font-medium btn-transition ml-2">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow">