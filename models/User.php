<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle User
 * Gère l'authentification et la récupération des informations des utilisateurs,
 * avec prise en charge d'un système de rôles multiples (JSON).
 */
class User {

    /**
     * Authentifie un utilisateur via son email et son mot de passe.
     * 
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $password Le mot de passe en clair.
     * @return array|null Les données de l'utilisateur (sans le hash) ou null si échec.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function authenticate(string $email, string $password): ?array {
        try {
            $pdo = Database::getConnection();
            
            $stmt = $pdo->prepare("SELECT id, email, mot_de_passe_hash, roles FROM utilisateurs WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe_hash'])) {
                unset($user['mot_de_passe_hash']);
                
                // Décodage sécurisé du tableau JSON des rôles
                $decodedRoles = json_decode((string)$user['roles'], true);
                $user['roles'] = is_array($decodedRoles) ? $decodedRoles : [];
                
                return $user;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Erreur dans User::authenticate : " . $e->getMessage());
            throw new Exception("Erreur lors de la tentative de connexion.");
        }
    }

    /**
     * Récupère un utilisateur par son identifiant.
     * 
     * @param int $id L'identifiant de l'utilisateur.
     * @return array|null Les données de l'utilisateur ou null s'il n'existe pas.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getById(int $id): ?array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id, email, roles FROM utilisateurs WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            
            if ($user) {
                $decodedRoles = json_decode((string)$user['roles'], true);
                $user['roles'] = is_array($decodedRoles) ? $decodedRoles : [];
                return $user;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Erreur dans User::getById : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération de l'utilisateur.");
        }
    }
}