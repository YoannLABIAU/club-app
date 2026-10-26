<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Commande.php';
require_once __DIR__ . '/../models/Produit.php';

/**
 * Contrôleur AdminBoutiqueController
 * Gère l'administration de la boutique (Gestion des commandes et suivi des stocks).
 */
class AdminBoutiqueController {

    private function checkAuth(bool $isApi = false): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userRoles = $_SESSION['user_roles'] ?? [];
        if (!isset($_SESSION['user_id']) || empty(array_intersect(['admin', 'boutique'], $userRoles))) {
            if ($isApi) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Action non autorisée. Droits Trésorerie requis.']);
                exit;
            } else {
                header('Location: /admin/login');
                exit;
            }
        }
    }

    /**
     * Affiche le tableau de bord e-commerce.
     */
    public function index(): void {
        $this->checkAuth();
        $commandes = [];
        $produits = [];
        $erreur = null;

        try {
            $commandes = Commande::getAllWithDetails();
            $produits = Produit::getAll(true); // true = l'admin voit tout le catalogue
        } catch (Exception $e) {
            $erreur = "Impossible de charger les données de la boutique.";
        }

        require_once __DIR__ . '/../views/admin_boutique.php';
    }

    /**
     * Traite la mise à jour asynchrone du statut d'une commande.
     */
    public function processStatutUpdate(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['commande_id']) || empty($input['statut'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            return;
        }

        try {
            Commande::updateStatut((int)$input['commande_id'], trim((string)$input['statut']));
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Statut de la commande mis à jour.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Traite l'ajustement manuel du stock d'un produit (ex: réassort).
     */
    public function processStockUpdate(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['produit_id']) || !isset($input['nouveau_stock'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            return;
        }

        try {
            Produit::updateStock((int)$input['produit_id'], (int)$input['nouveau_stock']);
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Stock mis à jour avec succès.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}