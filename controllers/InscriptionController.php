<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Adherent.php';

/**
 * Contrôleur InscriptionController
 * Gère le formulaire d'adhésion complet et son traitement asynchrone sécurisé.
 */
class InscriptionController {

    /**
     * Affiche la page contenant le formulaire d'inscription détaillé.
     * 
     * @return void
     */
    public function index(): void {
        require_once __DIR__ . '/../views/inscription_view.php';
    }

    /**
     * Traite la soumission du formulaire d'inscription via API Fetch (JSON).
     * 
     * @return void
     */
    public function process(): void {
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

        // Vérification des champs obligatoires minimums
        if (empty($input['nom']) || empty($input['prenom']) || empty($input['sexe'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le nom, le prénom et le sexe sont obligatoires.']);
            return;
        }

        try {
            // Le modèle Adherent::create se charge du nettoyage final (trim, cast) via PDO
            $result = Adherent::create($input);
            
            if ($result) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Inscription enregistrée avec succès.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement en base de données.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}