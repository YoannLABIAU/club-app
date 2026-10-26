<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Article
 * Gère la lecture et l'écriture du contenu de la page d'accueil (CMS par blocs).
 */
class Article {

    /**
     * Récupère tous les articles publiés, du plus récent au plus ancien.
     * 
     * @return array La liste des articles.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getAll(): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT * FROM articles_vitrine ORDER BY date_publication DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Article::getAll : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des actualités.");
        }
    }

    /**
     * Récupère un article spécifique par son identifiant.
     * 
     * @param int $id L'identifiant de l'article.
     * @return array|null L'article ou null s'il n'existe pas.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getById(int $id): ?array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM articles_vitrine WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur dans Article::getById : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération de l'article.");
        }
    }

    /**
     * Crée un nouvel article pour la page d'accueil.
     * 
     * @param string $titre Le titre de l'article.
     * @param string $contenuJson Le contenu structuré au format JSON.
     * @return bool True si l'insertion a réussi, False sinon.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function create(string $titre, string $contenuJson): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("INSERT INTO articles_vitrine (titre, contenu, date_publication) VALUES (:titre, :contenu, :date_publication)");
            return $stmt->execute([
                ':titre' => trim($titre),
                ':contenu' => $contenuJson,
                ':date_publication' => date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Article::create : " . $e->getMessage());
            throw new Exception("Erreur lors de la publication de l'article.");
        }
    }

    /**
     * Met à jour un article existant.
     * 
     * @param int $id L'identifiant de l'article.
     * @param string $titre Le nouveau titre.
     * @param string $contenuJson Le nouveau contenu structuré au format JSON.
     * @return bool True si la mise à jour a réussi, False sinon.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function update(int $id, string $titre, string $contenuJson): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE articles_vitrine SET titre = :titre, contenu = :contenu WHERE id = :id");
            return $stmt->execute([
                ':titre' => trim($titre),
                ':contenu' => $contenuJson,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Article::update : " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour de l'article.");
        }
    }

    /**
     * Supprime un article existant de la base de données.
     * 
     * @param int $id L'identifiant de l'article à supprimer.
     * @return bool True en cas de succès, False sinon.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function delete(int $id): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM articles_vitrine WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur dans Article::delete : " . $e->getMessage());
            throw new Exception("Erreur lors de la suppression de l'article.");
        }
    }
}