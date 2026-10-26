<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Liaison
 * Gère les relations familiales entre les comptes Utilisateurs (parents) et les fiches Adhérents.
 */
class Liaison {

    /**
     * Récupère tous les utilisateurs ayant le rôle "parent" avec leurs adhérents rattachés.
     * 
     * @return array Liste des parents et de leurs liaisons.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getAllParentsWithLinks(): array {
        try {
            $pdo = Database::getConnection();
            
            // Étape 1 : Récupérer tous les utilisateurs
            $stmtUsers = $pdo->query("SELECT id, email, roles FROM utilisateurs");
            $users = $stmtUsers->fetchAll();

            $parents = [];
            // Filtrage PHP natif pour pallier aux limitations JSON des anciennes versions SQLite
            foreach($users as $user) {
                $roles = json_decode((string)$user['roles'], true);
                if (is_array($roles) && in_array('parent', $roles, true)) {
                    $user['adherents'] = [];
                    $parents[(int)$user['id']] = $user;
                }
            }

            // Étape 2 : Récupérer et attacher les liaisons existantes
            $stmtLinks = $pdo->query("
                SELECT ua.utilisateur_id, ua.adherent_id, ua.relation, a.nom, a.prenom
                FROM utilisateurs_adherents ua
                INNER JOIN adherents a ON ua.adherent_id = a.id
            ");
            $links = $stmtLinks->fetchAll();

            foreach($links as $link) {
                $uid = (int)$link['utilisateur_id'];
                if (isset($parents[$uid])) {
                    $parents[$uid]['adherents'][] = $link;
                }
            }

            return array_values($parents);
        } catch (PDOException $e) {
            error_log("Erreur dans Liaison::getAllParentsWithLinks : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des liaisons familiales.");
        }
    }

    /**
     * Récupère la liste de tous les adhérents pour alimenter le menu de sélection.
     * 
     * @return array
     * @throws Exception
     */
    public static function getAllAdherents(): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT id, nom, prenom FROM adherents ORDER BY nom ASC, prenom ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Liaison::getAllAdherents : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération de la liste des adhérents.");
        }
    }

    /**
     * Crée une nouvelle liaison entre un compte et un adhérent.
     * 
     * @param int $userId ID du parent.
     * @param int $adherentId ID de l'adhérent.
     * @param string $relation Ex: 'Père', 'Mère'.
     * @return bool
     * @throws Exception
     */
    public static function create(int $userId, int $adherentId, string $relation): bool {
        try {
            $pdo = Database::getConnection();
            
            // Protection anti-doublon
            $check = $pdo->prepare("SELECT 1 FROM utilisateurs_adherents WHERE utilisateur_id = :uid AND adherent_id = :aid");
            $check->execute([':uid' => $userId, ':aid' => $adherentId]);
            if ($check->fetch()) {
                throw new Exception("Cette liaison existe déjà.");
            }

            $stmt = $pdo->prepare("INSERT INTO utilisateurs_adherents (utilisateur_id, adherent_id, relation) VALUES (:uid, :aid, :relation)");
            return $stmt->execute([
                ':uid' => $userId,
                ':aid' => $adherentId,
                ':relation' => trim($relation)
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Liaison::create : " . $e->getMessage());
            throw new Exception("Erreur lors de la création de la liaison.");
        }
    }

    /**
     * Supprime une liaison existante.
     * 
     * @param int $userId ID du parent.
     * @param int $adherentId ID de l'adhérent.
     * @return bool
     * @throws Exception
     */
    public static function delete(int $userId, int $adherentId): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM utilisateurs_adherents WHERE utilisateur_id = :uid AND adherent_id = :aid");
            return $stmt->execute([':uid' => $userId, ':aid' => $adherentId]);
        } catch (PDOException $e) {
            error_log("Erreur dans Liaison::delete : " . $e->getMessage());
            throw new Exception("Erreur lors de la suppression de la liaison.");
        }
    }
}