<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/Commande.php';

/**
 * Contrôleur BoutiqueController
 * Gère le catalogue public, le passage en caisse (checkout) et l'historique client.
 */
class BoutiqueController {

    private function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Affiche la page de la boutique avec les produits adaptés aux droits.
     */
    public function index(): void {
        $this->initSession();
        $produits = [];
        $erreur = null;
        $estMembreConnecte = isset($_SESSION['user_id']);

        try {
            $produits = Produit::getAll($estMembreConnecte);
        } catch (Exception $e) {
            $erreur = "Impossible de charger le catalogue de la boutique.";
        }

        require_once __DIR__ . '/../views/boutique_view.php';
    }

    /**
     * Affiche l'historique des commandes de l'utilisateur connecté.
     */
    public function mesCommandes(): void {
        $this->initSession();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $commandes = [];
        $erreur = null;
        try {
            $commandes = Commande::getByUserId((int)$_SESSION['user_id']);
        } catch (Exception $e) {
            $erreur = "Impossible de charger votre historique de commandes.";
        }

        require_once __DIR__ . '/../views/mes_commandes_view.php';
    }

    /**
     * Traite asynchronement le passage en caisse (Création de commande).
     */
    public function processCheckout(): void {
        $this->initSession();
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour commander.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['panier']) || !is_array($input['panier'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Panier invalide ou vide.']);
            return;
        }

        try {
            if (Commande::create((int)$_SESSION['user_id'], $input['panier'])) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Commande validée avec succès !']);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}