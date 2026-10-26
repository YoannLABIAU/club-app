<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Adherent.php';
require_once __DIR__ . '/../models/Statistique.php';
require_once __DIR__ . '/../models/Parametre.php';

/**
 * Contrôleur AdminController
 * Gère l'authentification et l'accès au tableau de bord central pour le staff.
 */
class AdminController {

    /**
     * Initialise la session de manière sécurisée.
     * 
     * @return void
     */
    private function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Affiche la page de connexion au back-office.
     * 
     * @return void
     */
    public function login(): void {
        $this->initSession();
        
        if (isset($_SESSION['user_id'])) {
            header('Location: /admin');
            exit;
        }

        require_once __DIR__ . '/../views/login_view.php';
    }

    /**
     * Traite la tentative de connexion (requête asynchrone JSON).
     * 
     * @return void
     */
    public function processLogin(): void {
        $this->initSession();
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

        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis.']);
            return;
        }

        try {
            $user = User::authenticate($email, $password);
            
            if ($user) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_roles'] = $user['roles']; 

                $redirectUrl = in_array('parent', $user['roles'], true) && count($user['roles']) === 1 
                    ? '/espace-famille' 
                    : '/admin';

                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Connexion réussie.', 'redirect' => $redirectUrl]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Identifiants incorrects.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Une erreur interne est survenue.']);
        }
    }

    /**
     * Déconnecte l'utilisateur et détruit la session.
     * 
     * @return void
     */
    public function logout(): void {
        $this->initSession();
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header('Location: /');
        exit;
    }

    /**
     * Affiche le tableau de bord de gestion avec les statistiques et accès par rôle.
     * 
     * @return void
     */
    public function dashboard(): void {
        $this->initSession();
        
        $userRoles = $_SESSION['user_roles'] ?? [];
        
        if (!isset($_SESSION['user_id']) || empty(array_intersect(['admin', 'prof', 'redacteur', 'boutique'], $userRoles))) {
            header('Location: /admin/login');
            exit;
        }

        $userEmail = $_SESSION['user_email'];
        $erreur = null;

        $adherents = [];
        $totalAdherents = 0;
        $cotisationsValidees = 0;
        $tauxGlobal = 0;
        $tauxParSport = [];
        $tauxParCours = [];
        $parametresActuels = [];

        try {
            if (in_array('admin', $userRoles, true)) {
                $adherents = Adherent::getAll();
                $totalAdherents = count($adherents);
                $cotisationsValidees = count(array_filter($adherents, fn($a) => (int)($a['paiement_valide'] ?? 0) === 1));
                
                $tauxGlobal = Statistique::getTauxPresenceGlobal();
                $tauxParSport = Statistique::getTauxPresenceParSport();
                $parametresActuels = Parametre::getAllAsKeyValue();
            }
            if (in_array('prof', $userRoles, true)) {
                $tauxParCours = Statistique::getTauxPresenceParCours();
            }
        } catch (Exception $e) {
            $erreur = "Impossible de charger les données du tableau de bord.";
        }

        require_once __DIR__ . '/../views/admin_view.php';
    }

    /**
     * Traite la validation asynchrone d'un paiement (Restreint : Admin, Boutique).
     * 
     * @return void
     */
    public function processValidationPaiement(): void {
        $this->initSession();
        header('Content-Type: application/json; charset=utf-8');

        $userRoles = $_SESSION['user_roles'] ?? [];

        if (!isset($_SESSION['user_id']) || empty(array_intersect(['admin', 'boutique'], $userRoles))) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Action non autorisée. Droits administratifs ou trésorerie requis.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['adherent_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID adhérent invalide.']);
            return;
        }

        try {
            if (Adherent::validerPaiement((int)$input['adherent_id'])) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Paiement validé avec succès.']);
            } else {
                throw new Exception('Erreur lors de la mise à jour.');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Sauvegarde asynchrone des paramètres du site (Couleur, Logo).
     * Restreint strictement au rôle Admin.
     * 
     * @return void
     */
    public function processUpdateParametres(): void {
        $this->initSession();
        header('Content-Type: application/json; charset=utf-8');

        $userRoles = $_SESSION['user_roles'] ?? [];

        if (!isset($_SESSION['user_id']) || !in_array('admin', $userRoles, true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Action non autorisée. Droits administrateur requis.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        try {
            // 1. Mise à jour de la couleur
            if (!empty($_POST['couleur_primaire'])) {
                $couleur = trim((string)$_POST['couleur_primaire']);
                if (preg_match('/^#[a-f0-9]{6}$/i', $couleur)) {
                    Parametre::update('couleur_primaire', $couleur);
                } else {
                    throw new InvalidArgumentException("Format de couleur invalide.");
                }
            }

            // 2. Gestion de l'upload du logo s'il est présent
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['logo'];
                
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erreur lors du transfert du fichier.");
                }
                
                if ($file['size'] > 2 * 1024 * 1024) { // 2 Mo max
                    throw new Exception("Le logo dépasse la taille maximale autorisée (2 Mo).");
                }

                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $allowedMimeTypes, true)) {
                    throw new Exception("Format d'image non autorisé (JPG, PNG ou WEBP).");
                }

                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = 'logo_club_' . uniqid() . '.' . strtolower($extension);
                
                $uploadDir = __DIR__ . '/../public/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $destinationPath = $uploadDir . $newFilename;
                $publicUrl = '/public/images/' . $newFilename;

                if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                    Parametre::update('logo_url', $publicUrl);
                } else {
                    throw new Exception("Impossible de sauvegarder l'image sur le serveur.");
                }
            }

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Paramètres mis à jour avec succès.']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}