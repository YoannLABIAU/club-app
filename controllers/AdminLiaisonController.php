<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Liaison.php';

/**
 * Contrôleur AdminLiaisonController
 * Gère les attributions entre les comptes parents et les fiches adhérents.
 */
class AdminLiaisonController {

    /**
     * Vérifie les droits d'administration.
     */
    private function checkAuth(bool $isApi = false): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userRoles = $_SESSION['user_roles'] ?? [];
        if (!isset($_SESSION['user_id']) || !in_array('admin', $userRoles, true)) {
            if ($isApi) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Action non autorisée. Droits administrateur requis.']);
                exit;
            } else {
                header('Location: /admin/login');
                exit;
            }
        }
    }

    /**
     * Affiche l'interface de gestion des liaisons familiales.
     */
    public function index(): void {
        $this->checkAuth();
        $parents = [];
        $adherents = [];
        $erreur = null;

        try {
            $parents = Liaison::getAllParentsWithLinks();
            $adherents = Liaison::getAllAdherents();
        } catch (Exception $e) {
            $erreur = "Impossible de charger les données des liaisons.";
        }

        require_once __DIR__ . '/../views/admin_liaisons.php';
    }

    /**
     * Traite l'ajout asynchrone d'une nouvelle liaison.
     */
    public function processAdd(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['utilisateur_id']) || empty($input['adherent_id']) || empty($input['relation'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
            return;
        }

        try {
            Liaison::create((int)$input['utilisateur_id'], (int)$input['adherent_id'], trim((string)$input['relation']));
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Liaison ajoutée avec succès.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Traite la suppression asynchrone d'une liaison.
     */
    public function processDelete(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['utilisateur_id']) || empty($input['adherent_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Identifiants manquants.']);
            return;
        }

        try {
            if (Liaison::delete((int)$input['utilisateur_id'], (int)$input['adherent_id'])) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Liaison supprimée.']);
            } else {
                throw new Exception('Échec de la suppression.');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}