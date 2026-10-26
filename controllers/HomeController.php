<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Article.php';

/**
 * Contrôleur HomeController
 * Gère l'affichage de la page d'accueil (vitrine) et de ses actualités.
 */
class HomeController {

    /**
     * Initialise la session de manière sécurisée si elle n'est pas déjà démarrée.
     * 
     * @return void
     */
    private function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Récupère les articles de la base de données et affiche la vue de l'accueil.
     * 
     * @return void
     */
    public function index(): void {
        $this->initSession();
        
        $articles = [];
        $erreur = null;

        try {
            $articles = Article::getAll();
        } catch (Exception $e) {
            $erreur = "Impossible de charger les actualités pour le moment.";
        }

        // Variables disponibles pour la vue : $articles, $erreur
        require_once __DIR__ . '/../views/home_view.php';
    }
}