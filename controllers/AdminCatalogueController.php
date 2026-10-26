<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Produit.php';

/**
 * Contrôleur AdminCatalogueController
 * Gère le catalogue (CRUD des produits) pour l'administration de la boutique.
 */
class AdminCatalogueController {

    /**
     * Vérifie les droits d'administration (Trésorerie / Boutique).
     */
    private function checkAuth(bool $isApi = false): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userRoles = $_SESSION['user_roles'] ?? [];
        if (!isset($_SESSION['user_id']) || empty(array_intersect(['admin', 'boutique'], $userRoles))) {
            if ($isApi) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Action non autorisée. Droits de trésorerie requis.']);
                exit;
            } else {
                header('Location: /admin/login');
                exit;
            }
        }
    }

    /**
     * Affiche l'interface de gestion du catalogue.
     */
    public function index(): void {
        $this->checkAuth();
        $produits = [];
        $erreur = null;

        try {
            $produits = Produit::getAll(true); // true = l'admin voit même les produits restreints
        } catch (Exception $e) {
            $erreur = "Impossible de charger le catalogue.";
        }

        require_once __DIR__ . '/../views/admin_catalogue.php';
    }

    /**
     * Traite l'ajout ou la mise à jour asynchrone d'un produit (multipart/form-data).
     */
    public function processSave(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        // Vérification des champs requis
        $nom = trim((string)($_POST['nom'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $prix = (float)($_POST['prix'] ?? 0.0);

        if (empty($nom) || empty($description) || $prix <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le nom, la description et un prix valide sont obligatoires.']);
            return;
        }

        $donnees = [
            'nom' => $nom,
            'description' => $description,
            'prix' => $prix,
            'stock' => isset($_POST['stock']) ? (int)$_POST['stock'] : 0,
            'acces_restreint' => isset($_POST['acces_restreint']) ? 1 : 0,
            'image_url' => ''
        ];

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        // Gestion de l'upload d'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Erreur lors du transfert de l\'image.']);
                return;
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'L\'image dépasse 2 Mo.']);
                return;
            }

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Format d\'image non autorisé.']);
                return;
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilename = 'prod_' . uniqid() . '.' . strtolower($extension);
            $uploadDir = __DIR__ . '/../public/uploads/';
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
                $donnees['image_url'] = '/public/uploads/' . $newFilename;
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Impossible de sauvegarder l\'image.']);
                return;
            }
        } elseif ($id === 0) {
            // Création sans image fournie : on assigne l'image par défaut
            $donnees['image_url'] = '/public/images/default-product.png';
        }

        try {
            if ($id > 0) {
                Produit::update($id, $donnees);
                $message = "Produit mis à jour.";
            } else {
                Produit::create($donnees);
                $message = "Produit ajouté au catalogue.";
            }
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Traite la suppression asynchrone d'un produit.
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
            if (Produit::delete((int)$input['id'])) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Produit retiré du catalogue.']);
            } else {
                throw new Exception('Échec de la suppression.');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}