<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Adherent
 * Gère les inscriptions complètes, la liste des membres, leurs pointages et liaisons familiales.
 */
class Adherent {
    
    /**
     * Récupère les adhérents inscrits dans la discipline du créneau, 
     * AINSI QUE tous les adhérents (même hors-créneau) ayant déjà un pointage pour cette séance.
     * 
     * @param int $creneauId L'identifiant du créneau régulier.
     * @param int $seanceId L'identifiant de la séance du jour.
     * @return array La liste des adhérents et leur statut actuel.
     * @throws Exception
     */
    public static function getForCreneauAndSeance(int $creneauId, int $seanceId): array {
        try {
            $pdo = Database::getConnection();
            
            // 1. Récupérer la discipline cible du créneau
            $stmtC = $pdo->prepare("SELECT discipline FROM creneaux WHERE id = :id");
            $stmtC->execute([':id' => $creneauId]);
            $creneau = $stmtC->fetch();
            $discipline = $creneau ? $creneau['discipline'] : '';

            // 2. Récupérer les adhérents correspondants ou déjà pointés
            $sql = "
                SELECT a.id, a.nom, a.prenom, a.ceinture as grade, a.disciplines as discipline, a.saison,
                       COALESCE(p.statut, 'Non pointé') as statut_actuel
                FROM adherents a
                LEFT JOIN pointages p ON a.id = p.adherent_id AND p.seance_id = :seance_id
                WHERE a.disciplines LIKE :disc
                   OR p.id IS NOT NULL
                ORDER BY a.nom ASC, a.prenom ASC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':seance_id' => $seanceId,
                ':disc' => '%"' . $discipline . '"%'
            ]);
            
            $result = $stmt->fetchAll();
            
            foreach ($result as &$row) {
                $disciplinesArray = json_decode((string)$row['discipline'], true);
                if (is_array($disciplinesArray)) {
                    $row['discipline'] = implode(', ', $disciplinesArray);
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur dans Adherent::getForCreneauAndSeance : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération de la liste d'appel.");
        }
    }

    /**
     * Recherche des adhérents par nom ou prénom (Pour l'ajout hors-créneau).
     * 
     * @param string $term Le terme de recherche.
     * @return array Les résultats correspondants.
     * @throws Exception
     */
    public static function search(string $term): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT id, nom, prenom, disciplines FROM adherents WHERE nom LIKE :term OR prenom LIKE :term ORDER BY nom ASC LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':term' => '%' . $term . '%']);
            
            $result = $stmt->fetchAll();
            foreach ($result as &$row) {
                $disciplinesArray = json_decode((string)$row['disciplines'], true);
                $row['disciplines'] = is_array($disciplinesArray) ? implode(', ', $disciplinesArray) : '';
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur dans Adherent::search : " . $e->getMessage());
            throw new Exception("Erreur lors de la recherche d'adhérents.");
        }
    }

    public static function getAll(): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT * FROM adherents ORDER BY nom ASC, prenom ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Adherent::getAll : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération de l'annuaire des membres.");
        }
    }

    public static function getByUtilisateurId(int $utilisateurId): array {
        try {
            $pdo = Database::getConnection();
            $sql = "
                SELECT a.*, ua.relation 
                FROM adherents a
                INNER JOIN utilisateurs_adherents ua ON a.id = ua.adherent_id
                WHERE ua.utilisateur_id = :utilisateur_id
                ORDER BY a.prenom ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':utilisateur_id' => $utilisateurId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Adherent::getByUtilisateurId : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des fiches familiales.");
        }
    }

    public static function savePointage(int $adherentId, int $seanceId, string $statut): bool {
        if (!in_array($statut, ['Present', 'Absent'], true)) {
            throw new InvalidArgumentException("Statut de pointage invalide.");
        }

        try {
            $pdo = Database::getConnection();
            
            $checkStmt = $pdo->prepare("SELECT id FROM pointages WHERE adherent_id = :adherent_id AND seance_id = :seance_id");
            $checkStmt->execute([':adherent_id' => $adherentId, ':seance_id' => $seanceId]);
            $existing = $checkStmt->fetch();
            
            if ($existing) {
                $updateStmt = $pdo->prepare("UPDATE pointages SET statut = :statut WHERE id = :id");
                return $updateStmt->execute([':statut' => $statut, ':id' => $existing['id']]);
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO pointages (seance_id, adherent_id, statut) VALUES (:seance_id, :adherent_id, :statut)");
                return $insertStmt->execute([
                    ':seance_id' => $seanceId,
                    ':adherent_id' => $adherentId,
                    ':statut' => $statut
                ]);
            }
        } catch (PDOException $e) {
            error_log("Erreur dans Adherent::savePointage : " . $e->getMessage());
            throw new Exception("Erreur lors de l'enregistrement du pointage.");
        }
    }

    public static function create(array $donnees): bool {
        try {
            $pdo = Database::getConnection();
            $sql = "
                INSERT INTO adherents (
                    saison, nom, prenom, sexe, date_naissance, lieu_naissance, adresse, 
                    email, telephone, disciplines, ceinture, telephone_pere, telephone_mere, 
                    profession_pere, profession_mere, profession_adulte, problemes_sante, 
                    urgence_nom, urgence_prenom, urgence_telephone, type_certificat, 
                    autorisation_soins, droit_image, mode_paiement, montant_total, 
                    reduction_appliquee, paiement_valide
                ) VALUES (
                    :saison, :nom, :prenom, :sexe, :date_naissance, :lieu_naissance, :adresse, 
                    :email, :telephone, :disciplines, :ceinture, :telephone_pere, :telephone_mere, 
                    :profession_pere, :profession_mere, :profession_adulte, :problemes_sante, 
                    :urgence_nom, :urgence_prenom, :urgence_telephone, :type_certificat, 
                    :autorisation_soins, :droit_image, :mode_paiement, :montant_total, 
                    :reduction_appliquee, :paiement_valide
                )
            ";
            $stmt = $pdo->prepare($sql);
            $disciplines = isset($donnees['disciplines']) && is_array($donnees['disciplines']) ? json_encode($donnees['disciplines']) : json_encode([]);

            return $stmt->execute([
                ':saison' => trim((string)($donnees['saison'] ?? '')),
                ':nom' => trim(strtoupper((string)($donnees['nom'] ?? ''))),
                ':prenom' => trim((string)($donnees['prenom'] ?? '')),
                ':sexe' => trim((string)($donnees['sexe'] ?? '')),
                ':date_naissance' => trim((string)($donnees['date_naissance'] ?? '')),
                ':lieu_naissance' => trim((string)($donnees['lieu_naissance'] ?? '')),
                ':adresse' => trim((string)($donnees['adresse'] ?? '')),
                ':email' => trim((string)($donnees['email'] ?? '')),
                ':telephone' => trim((string)($donnees['telephone'] ?? '')),
                ':disciplines' => $disciplines,
                ':ceinture' => trim((string)($donnees['ceinture'] ?? '')),
                ':telephone_pere' => trim((string)($donnees['telephone_pere'] ?? '')),
                ':telephone_mere' => trim((string)($donnees['telephone_mere'] ?? '')),
                ':profession_pere' => trim((string)($donnees['profession_pere'] ?? '')),
                ':profession_mere' => trim((string)($donnees['profession_mere'] ?? '')),
                ':profession_adulte' => trim((string)($donnees['profession_adulte'] ?? '')),
                ':problemes_sante' => trim((string)($donnees['problemes_sante'] ?? '')),
                ':urgence_nom' => trim((string)($donnees['urgence_nom'] ?? '')),
                ':urgence_prenom' => trim((string)($donnees['urgence_prenom'] ?? '')),
                ':urgence_telephone' => trim((string)($donnees['urgence_telephone'] ?? '')),
                ':type_certificat' => trim((string)($donnees['type_certificat'] ?? '')),
                ':autorisation_soins' => empty($donnees['autorisation_soins']) ? 0 : 1,
                ':droit_image' => empty($donnees['droit_image']) ? 0 : 1,
                ':mode_paiement' => trim((string)($donnees['mode_paiement'] ?? '')),
                ':montant_total' => (float)($donnees['montant_total'] ?? 0.0),
                ':reduction_appliquee' => empty($donnees['reduction_appliquee']) ? 0 : 1,
                ':paiement_valide' => empty($donnees['paiement_valide']) ? 0 : 1,
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Adherent::create : " . $e->getMessage());
            throw new Exception("Erreur lors de l'enregistrement en base de données.");
        }
    }

    public static function validerPaiement(int $id): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE adherents SET paiement_valide = 1 WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur dans Adherent::validerPaiement : " . $e->getMessage());
            throw new Exception("Erreur lors de la validation du paiement en base de données.");
        }
    }

    public static function updateContactMedical(int $id, array $donnees): bool {
        try {
            $pdo = Database::getConnection();
            $sql = "UPDATE adherents SET adresse = :adresse, email = :email, telephone = :telephone, telephone_pere = :telephone_pere, telephone_mere = :telephone_mere, problemes_sante = :problemes_sante, urgence_nom = :urgence_nom, urgence_prenom = :urgence_prenom, urgence_telephone = :urgence_telephone WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':adresse' => trim((string)($donnees['adresse'] ?? '')),
                ':email' => trim((string)($donnees['email'] ?? '')),
                ':telephone' => trim((string)($donnees['telephone'] ?? '')),
                ':telephone_pere' => trim((string)($donnees['telephone_pere'] ?? '')),
                ':telephone_mere' => trim((string)($donnees['telephone_mere'] ?? '')),
                ':problemes_sante' => trim((string)($donnees['problemes_sante'] ?? '')),
                ':urgence_nom' => trim((string)($donnees['urgence_nom'] ?? '')),
                ':urgence_prenom' => trim((string)($donnees['urgence_prenom'] ?? '')),
                ':urgence_telephone' => trim((string)($donnees['urgence_telephone'] ?? '')),
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Adherent::updateContactMedical : " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour des informations.");
        }
    }
}