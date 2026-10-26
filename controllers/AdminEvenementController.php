<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Evenement.php';
require_once __DIR__ . '/../models/Adherent.php';

/**
 * Contrôleur AdminEvenementController
 * Gère la création, modification et suppression des événements ainsi que l'affichage et l'ajout manuel des participants.
 */
class AdminEvenementController {

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
     * Affiche l'interface de gestion des événements.
     */
    public function index(): void {
        $this->checkAuth();
        $evenements = [];
        $participants = [];
        $erreur = null;

        try {
            $evenements = Evenement::getAll();
            foreach ($evenements as $e) {
                $participants[(int)$e['id']] = Evenement::getParticipants((int)$e['id']);
            }
        } catch (Exception $e) {
            $erreur = "Impossible de charger les événements.";
        }

        require_once __DIR__ . '/../views/admin_evenements.php';
    }

    /**
     * Traite l'ajout ou la mise à jour asynchrone d'un événement.
     */
    public function processSave(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['nom']) || empty($input['type']) || empty($input['date_evenement']) || empty($input['description'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
            return;
        }

        $id = isset($input['id']) ? (int)$input['id'] : 0;

        try {
            if ($id > 0) {
                Evenement::update($id, $input['nom'], $input['type'], $input['date_evenement'], $input['description']);
                $message = "Événement mis à jour.";
            } else {
                Evenement::create($input['nom'], $input['type'], $input['date_evenement'], $input['description']);
                $message = "Événement créé avec succès.";
            }
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Traite la suppression asynchrone d'un événement.
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

        if (!$input || empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Identifiant manquant.']);
            return;
        }

        try {
            if (Evenement::delete((int)$input['id'])) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Événement supprimé.']);
            } else {
                throw new Exception('Échec de la suppression.');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Recherche asynchrone d'un adhérent par nom ou prénom pour la convocation aux compétitions.
     */
    public function searchAdherent(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        $term = trim((string)($_GET['q'] ?? ''));

        if (strlen($term) < 2) {
            echo json_encode(['success' => true, 'results' => []]);
            return;
        }

        try {
            $results = Adherent::search($term);
            echo json_encode(['success' => true, 'results' => $results]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la recherche.']);
        }
    }

    /**
     * Ajoute manuellement un participant à un événement avec le statut "Sélectionné".
     */
    public function addParticipant(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['evenement_id']) || empty($input['adherent_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
            return;
        }

        try {
            if (Evenement::saveParticipation((int)$input['adherent_id'], (int)$input['evenement_id'], 'Sélectionné')) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Adhérent convoqué avec succès.']);
            } else {
                throw new Exception('Échec de l\'enregistrement.');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}