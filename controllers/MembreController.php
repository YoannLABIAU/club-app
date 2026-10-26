<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Adherent.php';
require_once __DIR__ . '/../models/Evenement.php';

/**
 * Contrôleur MembreController
 * Gère l'espace personnel (Portail Famille) permettant aux parents de consulter
 * les fiches de leurs enfants et de répondre aux événements.
 */
class MembreController {

    /**
     * Initialise la session et vérifie les droits d'accès 'parent'.
     * 
     * @param bool $isApi Définit si la réponse doit être en JSON.
     * @return void
     */
    private function checkAuth(bool $isApi = false): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userRoles = $_SESSION['user_roles'] ?? [];

        if (!isset($_SESSION['user_id']) || !in_array('parent', $userRoles, true)) {
            if ($isApi) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Accès refusé. Profil parent requis.']);
                exit;
            } else {
                header('Location: /admin/login');
                exit;
            }
        }
    }

    /**
     * Affiche le tableau de bord de l'espace famille (Fiches + Événements).
     * 
     * @return void
     */
    public function dashboard(): void {
        $this->checkAuth();
        
        $userId = (int)$_SESSION['user_id'];
        $adherentsLiees = [];
        $evenements = [];
        $participations = [];
        $erreur = null;

        try {
            // 1. Récupération des fiches enfants
            $adherentsLiees = Adherent::getByUtilisateurId($userId);
            
            // 2. Récupération des événements à venir
            $evenements = Evenement::getAllUpcoming();
            
            // 3. Récupération des statuts de participation pour chaque enfant
            foreach ($adherentsLiees as $adherent) {
                $id = (int)$adherent['id'];
                $participations[$id] = Evenement::getParticipationsByAdherent($id);
            }

        } catch (Exception $e) {
            $erreur = "Impossible de récupérer les données de votre espace.";
        }

        require_once __DIR__ . '/../views/espace_famille_view.php';
    }

    /**
     * Traite la mise à jour des informations de contact et médicales.
     * 
     * @return void
     */
    public function updateInfo(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['adherent_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            return;
        }

        $adherentId = (int)$input['adherent_id'];
        $userId = (int)$_SESSION['user_id'];

        try {
            $mesAdherents = Adherent::getByUtilisateurId($userId);
            $isProprietaire = false;
            foreach ($mesAdherents as $ad) {
                if ((int)$ad['id'] === $adherentId) {
                    $isProprietaire = true;
                    break;
                }
            }

            if (!$isProprietaire) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits pour modifier cette fiche.']);
                return;
            }

            if (Adherent::updateContactMedical($adherentId, $input)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Informations mises à jour avec succès.']);
            } else {
                throw new Exception('Échec de la sauvegarde.');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Traite la mise à jour asynchrone d'une participation à un événement.
     * 
     * @return void
     */
    public function updateParticipation(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['adherent_id']) || empty($input['evenement_id']) || empty($input['statut'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données de participation invalides.']);
            return;
        }

        $adherentId = (int)$input['adherent_id'];
        $evenementId = (int)$input['evenement_id'];
        $statut = trim((string)$input['statut']);
        $userId = (int)$_SESSION['user_id'];

        try {
            // Vérification de sécurité (IDOR)
            $mesAdherents = Adherent::getByUtilisateurId($userId);
            $isProprietaire = false;
            foreach ($mesAdherents as $ad) {
                if ((int)$ad['id'] === $adherentId) {
                    $isProprietaire = true;
                    break;
                }
            }

            if (!$isProprietaire) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Accès refusé pour cet adhérent.']);
                return;
            }

            if (Evenement::saveParticipation($adherentId, $evenementId, $statut)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Participation enregistrée.']);
            } else {
                throw new Exception('Échec de la sauvegarde en base de données.');
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}