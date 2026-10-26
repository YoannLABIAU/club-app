<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Evenement
 * Gère les événements du club (Galas, Compétitions) et le suivi des participations.
 */
class Evenement {

    /**
     * Récupère tous les événements à venir (date >= aujourd'hui).
     * 
     * @return array Liste des événements futurs.
     * @throws Exception
     */
    public static function getAllUpcoming(): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM evenements WHERE date_evenement >= :date_jour ORDER BY date_evenement ASC");
            $stmt->execute([':date_jour' => date('Y-m-d')]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Evenement::getAllUpcoming : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des événements.");
        }
    }

    /**
     * Récupère la totalité des événements.
     * 
     * @return array Liste complète.
     * @throws Exception
     */
    public static function getAll(): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT * FROM evenements ORDER BY date_evenement DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Evenement::getAll : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des événements.");
        }
    }

    /**
     * Crée un nouvel événement.
     * 
     * @param string $nom Nom de l'événement.
     * @param string $type Type ('Gala' ou 'Compétition').
     * @param string $date Date (YYYY-MM-DD).
     * @param string $description Description.
     * @return bool True en cas de succès.
     * @throws Exception
     */
    public static function create(string $nom, string $type, string $date, string $description): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("INSERT INTO evenements (nom, type, date_evenement, description) VALUES (:nom, :type, :date, :desc)");
            return $stmt->execute([
                ':nom' => trim($nom),
                ':type' => trim($type),
                ':date' => trim($date),
                ':desc' => trim($description)
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Evenement::create : " . $e->getMessage());
            throw new Exception("Erreur lors de la création de l'événement.");
        }
    }

    /**
     * Met à jour un événement existant.
     * 
     * @param int $id ID de l'événement.
     * @param string $nom Nom.
     * @param string $type Type.
     * @param string $date Date.
     * @param string $description Description.
     * @return bool True en cas de succès.
     * @throws Exception
     */
    public static function update(int $id, string $nom, string $type, string $date, string $description): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE evenements SET nom = :nom, type = :type, date_evenement = :date, description = :desc WHERE id = :id");
            return $stmt->execute([
                ':nom' => trim($nom),
                ':type' => trim($type),
                ':date' => trim($date),
                ':desc' => trim($description),
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Evenement::update : " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour de l'événement.");
        }
    }

    /**
     * Supprime un événement existant.
     * 
     * @param int $id ID de l'événement.
     * @return bool
     * @throws Exception
     */
    public static function delete(int $id): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur dans Evenement::delete : " . $e->getMessage());
            throw new Exception("Erreur lors de la suppression de l'événement.");
        }
    }

    /**
     * Récupère la liste des adhérents ayant un statut pour cet événement.
     * 
     * @param int $eventId ID de l'événement.
     * @return array
     * @throws Exception
     */
    public static function getParticipants(int $eventId): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT a.id, a.nom, a.prenom, p.statut
                FROM participations p
                INNER JOIN adherents a ON p.adherent_id = a.id
                WHERE p.evenement_id = :evenement_id
                ORDER BY p.statut ASC, a.nom ASC, a.prenom ASC
            ");
            $stmt->execute([':evenement_id' => $eventId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Evenement::getParticipants : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des participants.");
        }
    }

    /**
     * Récupère les participations d'un adhérent spécifique.
     * Requis pour l'affichage de l'Espace Famille.
     * 
     * @param int $adherentId L'identifiant de l'adhérent.
     * @return array Tableau associatif [evenement_id => statut].
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getParticipationsByAdherent(int $adherentId): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT evenement_id, statut FROM participations WHERE adherent_id = :adherent_id");
            $stmt->execute([':adherent_id' => $adherentId]);
            $lignes = $stmt->fetchAll();
            
            $participations = [];
            foreach ($lignes as $ligne) {
                $participations[(int)$ligne['evenement_id']] = $ligne['statut'];
            }
            
            return $participations;
        } catch (PDOException $e) {
            error_log("Erreur dans Evenement::getParticipationsByAdherent : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des participations.");
        }
    }

    /**
     * Enregistre ou met à jour le statut de participation d'un adhérent.
     * 
     * @param int $adherentId ID de l'adhérent.
     * @param int $evenementId ID de l'événement.
     * @param string $statut Statut ('Oui', 'Non', 'Peut-être', 'Sélectionné').
     * @return bool
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function saveParticipation(int $adherentId, int $evenementId, string $statut): bool {
        $statutsValides = ['Oui', 'Non', 'Peut-être', 'En attente', 'Sélectionné'];
        if (!in_array($statut, $statutsValides, true)) {
            throw new InvalidArgumentException("Statut de participation invalide.");
        }

        try {
            $pdo = Database::getConnection();
            
            $checkStmt = $pdo->prepare("SELECT statut FROM participations WHERE adherent_id = :adherent_id AND evenement_id = :evenement_id");
            $checkStmt->execute([':adherent_id' => $adherentId, ':evenement_id' => $evenementId]);
            
            if ($checkStmt->fetch()) {
                $updateStmt = $pdo->prepare("UPDATE participations SET statut = :statut WHERE adherent_id = :adherent_id AND evenement_id = :evenement_id");
                return $updateStmt->execute([':statut' => $statut, ':adherent_id' => $adherentId, ':evenement_id' => $evenementId]);
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO participations (adherent_id, evenement_id, statut) VALUES (:adherent_id, :evenement_id, :statut)");
                return $insertStmt->execute([':adherent_id' => $adherentId, ':evenement_id' => $evenementId, ':statut' => $statut]);
            }
        } catch (PDOException $e) {
            error_log("Erreur dans Evenement::saveParticipation : " . $e->getMessage());
            throw new Exception("Erreur lors de l'enregistrement de la participation.");
        }
    }
}