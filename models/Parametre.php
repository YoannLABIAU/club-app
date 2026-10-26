<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Parametre
 * Gère les paramètres de configuration globaux du site (couleurs, logos, etc.).
 */
class Parametre {

    /**
     * Récupère l'ensemble des paramètres sous forme de tableau associatif [clé => valeur].
     * 
     * @return array Les paramètres du site.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getAllAsKeyValue(): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT cle, valeur FROM parametres_site");
            $lignes = $stmt->fetchAll();
            
            $parametres = [];
            foreach ($lignes as $ligne) {
                $parametres[$ligne['cle']] = $ligne['valeur'];
            }
            
            return $parametres;
        } catch (PDOException $e) {
            error_log("Erreur dans Parametre::getAllAsKeyValue : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des paramètres.");
        }
    }

    /**
     * Met à jour la valeur d'un paramètre spécifique.
     * 
     * @param string $cle La clé du paramètre (ex: 'couleur_primaire').
     * @param string $valeur La nouvelle valeur à enregistrer.
     * @return bool True en cas de succès, False sinon.
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function update(string $cle, string $valeur): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE parametres_site SET valeur = :valeur WHERE cle = :cle");
            return $stmt->execute([
                ':valeur' => trim($valeur),
                ':cle' => trim($cle)
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Parametre::update : " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour du paramètre.");
        }
    }
}