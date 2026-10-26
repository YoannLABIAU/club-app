<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Creneau
 * Gère les créneaux horaires réguliers du club.
 */
class Creneau {
    
    /**
     * Récupère tous les créneaux programmés pour un jour de la semaine spécifique.
     * 
     * @param int $jourSemaine Numéro du jour (1 = Lundi, 7 = Dimanche).
     * @return array Liste des créneaux.
     * @throws Exception
     */
    public static function getByJour(int $jourSemaine): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT c.*, u.email as professeur_email 
                FROM creneaux c
                LEFT JOIN utilisateurs u ON c.professeur_id = u.id
                WHERE c.jour_semaine = :jour
                ORDER BY c.heure_debut ASC
            ");
            $stmt->execute([':jour' => $jourSemaine]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Creneau::getByJour : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des créneaux.");
        }
    }

    /**
     * Récupère un créneau spécifique.
     * 
     * @param int $id Identifiant du créneau.
     * @return array|null
     * @throws Exception
     */
    public static function getById(int $id): ?array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM creneaux WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur dans Creneau::getById : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération du créneau.");
        }
    }
}