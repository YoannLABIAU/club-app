<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-md mx-auto px-4 py-16 sm:px-6 lg:px-8 flex-grow flex items-center justify-center">
    
    <div class="bg-white rounded-lg shadow-md border-t-4 border-blue-700 w-full overflow-hidden">
        <div class="px-6 py-6 border-b border-gray-200 bg-gray-50 text-center">
            <h1 class="text-2xl font-bold text-gray-900">Espace Staff</h1>
            <p class="mt-1 text-sm text-gray-600">
                Connectez-vous pour accéder au tableau de bord.
            </p>
        </div>

        <form id="form-login" class="px-6 py-8 space-y-6">
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Adresse Email</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                    </div>
                    <input type="email" name="email" id="email" required 
                           class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-2 border"
                           placeholder="admin@judoclub.fr">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <input type="password" name="password" id="password" required 
                           class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-2 border"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" id="btn-submit-login" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 btn-transition disabled:opacity-50">
                    Se connecter
                </button>
            </div>
        </form>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 text-center">
            Accès réservé au corps professoral et à la direction du club.
        </div>
    </div>

</div>

</main> <!-- Fermeture de <main> -->

<footer class="bg-gray-800 text-white mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> Judo Club.</p>
    </div>
</footer>

<script src="/public/js/toast.js"></script>
<script src="/public/js/login.js"></script>
</body>
</html>