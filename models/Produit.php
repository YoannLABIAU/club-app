<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Produit
 * Gère le catalogue de la boutique du club avec gestion des accès restreints et images.
 */
class Produit {

    /**
     * Récupère la liste des produits disponibles.
     * 
     * @param bool $estMembreConnecte True si un utilisateur est authentifié.
     * @return array La liste des produits.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getAll(bool $estMembreConnecte = false): array {
        try {
            $pdo = Database::getConnection();
            
            if ($estMembreConnecte) {
                $sql = "SELECT * FROM produits_boutique ORDER BY nom ASC";
                $stmt = $pdo->query($sql);
            } else {
                $sql = "SELECT * FROM produits_boutique WHERE acces_restreint = 0 ORDER BY nom ASC";
                $stmt = $pdo->query($sql);
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Produit::getAll : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des produits de la boutique.");
        }
    }

    /**
     * Récupère un produit spécifique par son identifiant.
     * 
     * @param int $id L'identifiant du produit.
     * @return array|null Le produit ou null s'il n'existe pas.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getById(int $id): ?array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM produits_boutique WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur dans Produit::getById : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération du produit.");
        }
    }

    /**
     * Crée un nouveau produit dans le catalogue.
     * 
     * @param array $donnees Données du produit.
     * @return bool True en cas de succès.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function create(array $donnees): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO produits_boutique (nom, description, prix, stock, acces_restreint, image_url) 
                VALUES (:nom, :description, :prix, :stock, :acces_restreint, :image_url)
            ");
            
            return $stmt->execute([
                ':nom' => trim((string)($donnees['nom'] ?? '')),
                ':description' => trim((string)($donnees['description'] ?? '')),
                ':prix' => (float)($donnees['prix'] ?? 0.0),
                ':stock' => (int)($donnees['stock'] ?? 0),
                ':acces_restreint' => empty($donnees['acces_restreint']) ? 0 : 1,
                ':image_url' => trim((string)($donnees['image_url'] ?? ''))
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Produit::create : " . $e->getMessage());
            throw new Exception("Erreur lors de la création du produit.");
        }
    }

    /**
     * Met à jour un produit existant.
     * 
     * @param int $id L'identifiant du produit.
     * @param array $donnees Les nouvelles données (si image_url est absent, on garde l'ancienne).
     * @return bool True en cas de succès.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function update(int $id, array $donnees): bool {
        try {
            $pdo = Database::getConnection();
            
            // Si aucune nouvelle image n'est fournie, on ne met pas à jour la colonne image_url
            if (isset($donnees['image_url']) && $donnees['image_url'] !== '') {
                $sql = "UPDATE produits_boutique SET nom = :nom, description = :description, prix = :prix, stock = :stock, acces_restreint = :acces_restreint, image_url = :image_url WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $params = [
                    ':image_url' => trim((string)$donnees['image_url']),
                    ':id' => $id
                ];
            } else {
                $sql = "UPDATE produits_boutique SET nom = :nom, description = :description, prix = :prix, stock = :stock, acces_restreint = :acces_restreint WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $params = [':id' => $id];
            }
            
            // Ajout des paramètres communs
            $params[':nom'] = trim((string)($donnees['nom'] ?? ''));
            $params[':description'] = trim((string)($donnees['description'] ?? ''));
            $params[':prix'] = (float)($donnees['prix'] ?? 0.0);
            $params[':stock'] = (int)($donnees['stock'] ?? 0);
            $params[':acces_restreint'] = empty($donnees['acces_restreint']) ? 0 : 1;

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur dans Produit::update : " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour du produit.");
        }
    }

    /**
     * Supprime un produit du catalogue.
     * 
     * @param int $id L'identifiant du produit.
     * @return bool True en cas de succès.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function delete(int $id): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM produits_boutique WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur dans Produit::delete : " . $e->getMessage());
            throw new Exception("Erreur lors de la suppression du produit.");
        }
    }

    /**
     * Met à jour uniquement le stock d'un produit (Utilisé par le tableau de bord des commandes).
     * 
     * @param int $id L'identifiant du produit.
     * @param int $nouveauStock La nouvelle quantité en stock.
     * @return bool True en cas de succès.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function updateStock(int $id, int $nouveauStock): bool {
        if ($nouveauStock < 0) {
            $nouveauStock = 0;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE produits_boutique SET stock = :stock WHERE id = :id");
            return $stmt->execute([
                ':stock' => $nouveauStock,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Produit::updateStock : " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour du stock.");
        }
    }
}