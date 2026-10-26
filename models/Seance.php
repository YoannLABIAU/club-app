<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Seance
 * Gère les occurrences réelles des créneaux à une date donnée pour le pointage.
 */
class Seance {

    /**
     * Récupère ou crée la séance correspondante à un créneau pour une date précise.
     * 
     * @param int $creneauId Identifiant du créneau régulier.
     * @param string $date Date ciblée (YYYY-MM-DD).
     * @return int L'identifiant de la séance (existante ou nouvellement créée).
     * @throws Exception
     */
    public static function getOrCreate(int $creneauId, string $date): int {
        try {
            $pdo = Database::getConnection();
            
            // Vérification de l'existence
            $stmt = $pdo->prepare("SELECT id FROM seances WHERE creneau_id = :creneau_id AND date_seance = :date");
            $stmt->execute([':creneau_id' => $creneauId, ':date' => $date]);
            $seance = $stmt->fetch();
            
            if ($seance) {
                return (int)$seance['id'];
            }
            
            // Création de l'occurrence si elle n'existe pas encore
            $insert = $pdo->prepare("INSERT INTO seances (creneau_id, date_seance) VALUES (:creneau_id, :date)");
            $insert->execute([':creneau_id' => $creneauId, ':date' => $date]);
            
            return (int)$pdo->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Erreur dans Seance::getOrCreate : " . $e->getMessage());
            throw new Exception("Erreur lors de l'initialisation de la séance.");
        }
    }
}