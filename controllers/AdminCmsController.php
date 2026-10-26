<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Article.php';

/**
 * Contrôleur AdminCmsController
 * Gère l'interface de création/modification d'articles par blocs (Builder) et le traitement des images.
 */
class AdminCmsController {

    /**
     * Initialise et vérifie la session administrateur/rédacteur.
     * 
     * @param bool $isApi Définit si la réponse doit être en JSON.
     * @return void
     */
    private function checkAuth(bool $isApi = false): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userRoles = $_SESSION['user_roles'] ?? [];

        if (!isset($_SESSION['user_id']) || empty(array_intersect(['admin', 'redacteur'], $userRoles))) {
            if ($isApi) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Action non autorisée. Droits rédacteur requis.']);
                exit;
            } else {
                header('Location: /admin/login');
                exit;
            }
        }
    }

    /**
     * Affiche la liste de tous les articles avec possibilité d'édition ou de suppression.
     * 
     * @return void
     */
    public function index(): void {
        $this->checkAuth();
        
        $articles = [];
        $erreur = null;
        try {
            $articles = Article::getAll();
        } catch (Exception $e) {
            $erreur = "Impossible de charger la liste des articles.";
        }

        require_once __DIR__ . '/../views/admin_cms_list.php';
    }

    /**
     * Affiche l'interface du constructeur de blocs (Builder) pour créer ou modifier un article.
     * 
     * @return void
     */
    public function builder(): void {
        $this->checkAuth();
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $article = null;
        $erreur = null;

        if ($id > 0) {
            try {
                $article = Article::getById($id);
                if (!$article) {
                    $erreur = "L'article demandé n'existe pas ou a été supprimé.";
                }
            } catch (Exception $e) {
                $erreur = "Impossible de charger l'article pour modification.";
            }
        }

        require_once __DIR__ . '/../views/admin_cms_builder.php';
    }

    /**
     * Reçoit le payload JSON du Builder et sauvegarde (création ou mise à jour) l'article en base.
     * 
     * @return void
     */
    public function save(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['titre']) || empty($input['blocs']) || !is_array($input['blocs'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données invalides ou incomplètes.']);
            return;
        }

        $id = isset($input['id']) ? (int)$input['id'] : 0;
        $titre = trim((string)$input['titre']);
        $contenuJson = json_encode($input['blocs']);

        try {
            if ($id > 0) {
                Article::update($id, $titre, $contenuJson);
                $message = 'Article mis à jour avec succès.';
            } else {
                Article::create($titre, $contenuJson);
                $message = 'Article publié avec succès.';
            }
            
            http_response_code($id > 0 ? 200 : 201);
            echo json_encode(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde de l\'article.']);
        }
    }

    /**
     * Traite l'upload asynchrone d'une image depuis le Builder.
     * 
     * @return void
     */
    public function upload(): void {
        $this->checkAuth(true);
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Aucune image reçue.']);
            return;
        }

        $file = $_FILES['image'];
        $maxSize = 5 * 1024 * 1024; // 5 Mo

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Erreur lors du transfert du fichier.']);
            return;
        }
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le fichier dépasse la taille maximale autorisée (5 Mo).']);
            return;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Format de fichier non autorisé.']);
            return;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid('cms_', true) . '.' . strtolower($extension);
        
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destinationPath = $uploadDir . $newFilename;
        $publicUrl = '/public/uploads/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'url' => $publicUrl]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Impossible de sauvegarder l\'image sur le serveur.']);
        }
    }

    /**
     * Traite la suppression asynchrone d'un article.
     * 
     * @return void
     */
    public function delete(): void {
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
            if (Article::delete((int)$input['id'])) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Article supprimé.']);
            } else {
                throw new Exception('Échec de la suppression.');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de l\'article.']);
        }
    }
}