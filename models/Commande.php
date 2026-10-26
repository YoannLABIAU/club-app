<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Commande
 * Gère le cycle de vie des commandes e-commerce, les lignes de panier et les stocks.
 */
class Commande {

    /**
     * Crée une nouvelle commande, insère les lignes et décrémente les stocks (Transaction).
     * 
     * @param int $userId L'identifiant de l'utilisateur acheteur.
     * @param array $panier Tableau associatif du panier : [produit_id => quantite].
     * @return bool True en cas de succès de la transaction.
     * @throws Exception En cas d'erreur BDD, de stock insuffisant ou de produit introuvable.
     */
    public static function create(int $userId, array $panier): bool {
        $pdo = Database::getConnection();
        
        try {
            $pdo->beginTransaction();

            $totalCalcule = 0.0;
            $lignesAInserer = [];

            // 1. Vérification des prix, des stocks et calcul du total
            $stmtProduit = $pdo->prepare("SELECT nom, prix, stock FROM produits_boutique WHERE id = :id");
            $stmtUpdateStock = $pdo->prepare("UPDATE produits_boutique SET stock = stock - :qte WHERE id = :id AND stock >= :qte");

            foreach ($panier as $produitId => $quantite) {
                $produitId = (int)$produitId;
                $quantite = (int)$quantite;

                if ($quantite <= 0) continue;

                $stmtProduit->execute([':id' => $produitId]);
                $produit = $stmtProduit->fetch();

                if (!$produit) {
                    throw new Exception("Produit introuvable (ID: $produitId).");
                }
                if ((int)$produit['stock'] < $quantite) {
                    throw new Exception("Stock insuffisant pour le produit : " . $produit['nom']);
                }

                $totalCalcule += ((float)$produit['prix'] * $quantite);
                $lignesAInserer[] = [
                    'produit_id' => $produitId,
                    'quantite' => $quantite,
                    'prix_unitaire' => (float)$produit['prix']
                ];

                // Décrémentation du stock avec sécurité SQL (stock >= quantite)
                $stmtUpdateStock->execute([':qte' => $quantite, ':id' => $produitId]);
                if ($stmtUpdateStock->rowCount() === 0) {
                    throw new Exception("Erreur de concurrence lors de la mise à jour du stock pour : " . $produit['nom']);
                }
            }

            if (empty($lignesAInserer)) {
                throw new Exception("Le panier est vide ou invalide.");
            }

            // 2. Création de l'entête de la commande
            $stmtCommande = $pdo->prepare("
                INSERT INTO commandes (utilisateur_id, date_commande, total, statut) 
                VALUES (:uid, :date_cmd, :total, 'En attente de paiement')
            ");
            $stmtCommande->execute([
                ':uid' => $userId,
                ':date_cmd' => date('Y-m-d H:i:s'),
                ':total' => $totalCalcule
            ]);
            $commandeId = (int)$pdo->lastInsertId();

            // 3. Insertion des lignes de la commande
            $stmtLigne = $pdo->prepare("
                INSERT INTO commandes_lignes (commande_id, produit_id, quantite, prix_unitaire) 
                VALUES (:cid, :pid, :qte, :prix)
            ");
            
            foreach ($lignesAInserer as $ligne) {
                $stmtLigne->execute([
                    ':cid' => $commandeId,
                    ':pid' => $ligne['produit_id'],
                    ':qte' => $ligne['quantite'],
                    ':prix' => $ligne['prix_unitaire']
                ]);
            }

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Erreur dans Commande::create : " . $e->getMessage());
            throw $e; // On remonte l'exception pour que le contrôleur affiche le message exact (ex: "Stock insuffisant")
        }
    }

    /**
     * Récupère toutes les commandes avec leurs détails pour l'administration.
     * 
     * @return array La liste des commandes.
     * @throws Exception
     */
    public static function getAllWithDetails(): array {
        try {
            $pdo = Database::getConnection();
            $sql = "
                SELECT c.id, c.date_commande, c.total, c.statut, u.email as client_email,
                       GROUP_CONCAT(cl.quantite || 'x ' || p.nom, ', ') as details
                FROM commandes c
                INNER JOIN utilisateurs u ON c.utilisateur_id = u.id
                LEFT JOIN commandes_lignes cl ON c.id = cl.commande_id
                LEFT JOIN produits_boutique p ON cl.produit_id = p.id
                GROUP BY c.id
                ORDER BY c.date_commande DESC
            ";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Commande::getAllWithDetails : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des commandes.");
        }
    }

    /**
     * Récupère l'historique des commandes pour un utilisateur spécifique.
     * 
     * @param int $userId L'identifiant de l'utilisateur.
     * @return array La liste de ses commandes.
     * @throws Exception
     */
    public static function getByUserId(int $userId): array {
        try {
            $pdo = Database::getConnection();
            $sql = "
                SELECT c.id, c.date_commande, c.total, c.statut,
                       GROUP_CONCAT(cl.quantite || 'x ' || p.nom, ', ') as details
                FROM commandes c
                LEFT JOIN commandes_lignes cl ON c.id = cl.commande_id
                LEFT JOIN produits_boutique p ON cl.produit_id = p.id
                WHERE c.utilisateur_id = :uid
                GROUP BY c.id
                ORDER BY c.date_commande DESC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':uid' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur dans Commande::getByUserId : " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération de l'historique.");
        }
    }

    /**
     * Met à jour le statut d'une commande (ex: Payée, Distribuée).
     * 
     * @param int $id L'identifiant de la commande.
     * @param string $statut Le nouveau statut.
     * @return bool
     * @throws Exception
     */
    public static function updateStatut(int $id, string $statut): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE commandes SET statut = :statut WHERE id = :id");
            return $stmt->execute([
                ':statut' => trim($statut),
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans Commande::updateStatut : " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour du statut.");
        }
    }
}