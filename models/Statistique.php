<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Statistique
 * Gère l'agrégation des données pour les tableaux de bord (taux de présence, métriques).
 */
class Statistique {

    /**
     * Calcule le taux de présence pour chaque cours/séance.
     * Utile pour le rôle 'prof'.
     * 
     * @return array Tableau associatif structuré : [['nom_cours' => string, 'taux' => int], ...]
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getTauxPresenceParCours(): array {
        try {
            $pdo = Database::getConnection();
            $sql = "
                SELECT s.nom_cours,
                       COUNT(p.id) as total_pointages,
                       SUM(CASE WHEN p.statut = 'Present' THEN 1 ELSE 0 END) as total_presents
                FROM seances s
                INNER JOIN pointages p ON s.id = p.seance_id
                GROUP BY s.nom_cours
                ORDER BY s.nom_cours ASC
            ";
            
            $stmt = $pdo->query($sql);
            $lignes = $stmt->fetchAll();
            
            $resultat = [];
            foreach ($lignes as $ligne) {
                $total = (int)$ligne['total_pointages'];
                $presents = (int)$ligne['total_presents'];
                $taux = $total > 0 ? (int)round(($presents / $total) * 100) : 0;
                
                $resultat[] = [
                    'nom_cours' => $ligne['nom_cours'],
                    'taux' => $taux
                ];
            }
            return $resultat;
        } catch (PDOException $e) {
            error_log("Erreur dans Statistique::getTauxPresenceParCours : " . $e->getMessage());
            throw new Exception("Erreur lors de l'agrégation des statistiques par cours.");
        }
    }

    /**
     * Calcule le taux de présence global sur l'ensemble du club.
     * Utile pour le rôle 'admin'.
     * 
     * @return int Le pourcentage global de présence (0 à 100).
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getTauxPresenceGlobal(): int {
        try {
            $pdo = Database::getConnection();
            $sql = "
                SELECT COUNT(id) as total_pointages,
                       SUM(CASE WHEN statut = 'Present' THEN 1 ELSE 0 END) as total_presents
                FROM pointages
            ";
            
            $stmt = $pdo->query($sql);
            $ligne = $stmt->fetch();
            
            $total = (int)($ligne['total_pointages'] ?? 0);
            $presents = (int)($ligne['total_presents'] ?? 0);
            
            return $total > 0 ? (int)round(($presents / $total) * 100) : 0;
        } catch (PDOException $e) {
            error_log("Erreur dans Statistique::getTauxPresenceGlobal : " . $e->getMessage());
            throw new Exception("Erreur lors du calcul du taux de présence global.");
        }
    }

    /**
     * Calcule le taux de présence ventilé par sport (Judo, MMA, TFT).
     * Utile pour le rôle 'admin'.
     * 
     * @return array Tableau associatif : ['Nom du Sport' => Pourcentage, ...]
     * @throws Exception En cas d'erreur de base de données.
     */
    public static function getTauxPresenceParSport(): array {
        try {
            $pdo = Database::getConnection();
            $sql = "
                SELECT a.disciplines, p.statut
                FROM pointages p
                INNER JOIN adherents a ON p.adherent_id = a.id
            ";
            
            $stmt = $pdo->query($sql);
            $lignes = $stmt->fetchAll();
            
            $statsBrutes = [];
            
            // Compilation des présences par sport en décodant le JSON
            foreach ($lignes as $ligne) {
                $sports = json_decode((string)$ligne['disciplines'], true);
                if (!is_array($sports)) continue;
                
                $estPresent = ($ligne['statut'] === 'Present');
                
                foreach ($sports as $sport) {
                    if (!isset($statsBrutes[$sport])) {
                        $statsBrutes[$sport] = ['total' => 0, 'present' => 0];
                    }
                    $statsBrutes[$sport]['total']++;
                    if ($estPresent) {
                        $statsBrutes[$sport]['present']++;
                    }
                }
            }
            
            $resultat = [];
            foreach ($statsBrutes as $sport => $data) {
                $taux = $data['total'] > 0 ? (int)round(($data['present'] / $data['total']) * 100) : 0;
                $resultat[$sport] = $taux;
            }
            
            // Tri par ordre alphabétique des sports pour un affichage constant
            ksort($resultat);
            return $resultat;
        } catch (PDOException $e) {
            error_log("Erreur dans Statistique::getTauxPresenceParSport : " . $e->getMessage());
            throw new Exception("Erreur lors de la ventilation des présences par sport.");
        }
    }
}