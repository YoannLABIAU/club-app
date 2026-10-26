<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Adherent.php';
require_once __DIR__ . '/../models/Seance.php';
require_once __DIR__ . '/../models/Creneau.php';

/**
 * Contrôleur PointageController
 * Gère la sélection rétroactive de date, l'affichage des listes d'appel et l'ajout d'élèves hors-créneau.
 */
class PointageController {
    
    private function checkAuth(bool $isApi = false): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userRoles = $_SESSION['user_roles'] ?? [];
        if (!isset($_SESSION['user_id']) || empty(array_intersect(['admin', 'prof'], $userRoles))) {
            if ($isApi) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Action non autorisée.']);
                exit;
            } else {
                header('Location: /admin/login');
                exit;
            }
        }
    }

    public function index(): void {
        $this->checkAuth();
        
        // Gestion rétroactive de la date (Aujourd'hui par défaut)
        $dateSeance = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateSeance)) {
            $dateSeance = date('Y-m-d');
        }

        $creneauId = isset($_GET['creneau_id']) ? (int)$_GET['creneau_id'] : 0;
        $erreur = null;

        if ($creneauId > 0) {
            $adherents = [];
            $creneau = null;
            $seanceId = 0;
            
            try {
                $creneau = Creneau::getById($creneauId);
                if (!$creneau) {
                    $erreur = "Le créneau demandé n'existe pas.";
                } else {
                    $seanceId = Seance::getOrCreate($creneauId, $dateSeance);
                    $adherents = Adherent::getForCreneauAndSeance($creneauId, $seanceId);
                }
            } catch (Exception $e) {
                $erreur = "Impossible de charger la liste d'appel.";
            }

            require_once __DIR__ . '/../views/pointage_view.php';
        } else {
            $creneaux = [];
            $jourSemaine = (int)date('N', strtotime($dateSeance)); // 1=Lundi, 7=Dimanche
            
            try {
                $creneaux = Creneau::getByJour($jourSemaine);
            } catch (Exception $e) {
                $erreur = "Impossible de charger le calendrier des séances.";
            }

            require_once __DIR__ . '/../views/seances_view.php';
        }
    }

    public function updatePointage(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données JSON invalides.']);
            return;
        }

        $adherentId = isset($input['adherent_id']) ? (int) $input['adherent_id'] : 0;
        $seanceId = isset($input['seance_id']) ? (int) $input['seance_id'] : 0;
        $statut = isset($input['statut']) ? trim((string) $input['statut']) : '';

        if ($adherentId <= 0 || $seanceId <= 0 || !in_array($statut, ['Present', 'Absent'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants ou invalides.']);
            return;
        }

        try {
            if (Adherent::savePointage($adherentId, $seanceId, $statut)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Pointage mis à jour.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour en base de données.']);
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Une erreur interne est survenue.']);
        }
    }

    /**
     * Recherche asynchrone d'un élève pour le rajouter manuellement à une séance.
     */
    public function searchHorsCreneau(): void {
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
}