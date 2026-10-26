<?php
declare(strict_types=1);

/**
 * Fichier de configuration et de connexion à la base de données SQLite.
 * Initialise le schéma relationnel complet et un jeu de données réaliste ciblé pour la démonstration.
 */
class Database {
    /**
     * @var PDO|null Instance unique de la connexion PDO.
     */
    private static ?PDO $pdo = null;

    /**
     * Retourne l'instance PDO unique.
     * 
     * @return PDO
     * @throws Exception Si la connexion échoue.
     */
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            try {
                $dbPath = __DIR__ . '/../database.sqlite';
                self::$pdo = new PDO('sqlite:' . $dbPath);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->exec('PRAGMA foreign_keys = ON;');
                
                self::initDb();
            } catch (PDOException $e) {
                error_log("Erreur critique BDD : " . $e->getMessage());
                throw new Exception("Erreur critique de connexion à la base de données.");
            }
        }
        return self::$pdo;
    }

    /**
     * Crée les tables si elles n'existent pas et lance l'injection du jeu de données.
     * 
     * @return void
     */
    private static function initDb(): void {
        $queries = [
            "CREATE TABLE IF NOT EXISTS parametres_site (
                cle TEXT PRIMARY KEY,
                valeur TEXT NOT NULL
            );",
            "CREATE TABLE IF NOT EXISTS utilisateurs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                mot_de_passe_hash TEXT NOT NULL,
                roles TEXT NOT NULL
            );",
            "CREATE TABLE IF NOT EXISTS adherents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                saison TEXT NOT NULL,
                nom TEXT NOT NULL,
                prenom TEXT NOT NULL,
                sexe TEXT NOT NULL,
                date_naissance TEXT,
                lieu_naissance TEXT,
                adresse TEXT,
                email TEXT,
                telephone TEXT,
                disciplines TEXT NOT NULL,
                ceinture TEXT NOT NULL,
                telephone_pere TEXT,
                telephone_mere TEXT,
                profession_pere TEXT,
                profession_mere TEXT,
                profession_adulte TEXT,
                problemes_sante TEXT,
                urgence_nom TEXT,
                urgence_prenom TEXT,
                urgence_telephone TEXT,
                type_certificat TEXT,
                autorisation_soins INTEGER NOT NULL DEFAULT 0,
                droit_image INTEGER NOT NULL DEFAULT 0,
                mode_paiement TEXT,
                montant_total REAL,
                reduction_appliquee INTEGER NOT NULL DEFAULT 0,
                paiement_valide INTEGER NOT NULL DEFAULT 0
            );",
            "CREATE TABLE IF NOT EXISTS utilisateurs_adherents (
                utilisateur_id INTEGER NOT NULL,
                adherent_id INTEGER NOT NULL,
                relation TEXT NOT NULL,
                PRIMARY KEY (utilisateur_id, adherent_id),
                FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
                FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE
            );",
            "CREATE TABLE IF NOT EXISTS creneaux (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL,
                discipline TEXT NOT NULL,
                jour_semaine INTEGER NOT NULL,
                heure_debut TEXT NOT NULL,
                heure_fin TEXT NOT NULL,
                professeur_id INTEGER,
                FOREIGN KEY (professeur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
            );",
            "CREATE TABLE IF NOT EXISTS seances (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                creneau_id INTEGER NOT NULL,
                date_seance TEXT NOT NULL,
                FOREIGN KEY (creneau_id) REFERENCES creneaux(id) ON DELETE CASCADE
            );",
            "CREATE TABLE IF NOT EXISTS pointages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                seance_id INTEGER NOT NULL,
                adherent_id INTEGER NOT NULL,
                statut TEXT NOT NULL,
                FOREIGN KEY (seance_id) REFERENCES seances(id) ON DELETE CASCADE,
                FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE
            );",
            "CREATE TABLE IF NOT EXISTS articles_vitrine (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titre TEXT NOT NULL,
                contenu TEXT NOT NULL,
                date_publication TEXT NOT NULL
            );",
            "CREATE TABLE IF NOT EXISTS evenements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL,
                type TEXT NOT NULL,
                date_evenement TEXT NOT NULL,
                description TEXT NOT NULL
            );",
            "CREATE TABLE IF NOT EXISTS participations (
                adherent_id INTEGER NOT NULL,
                evenement_id INTEGER NOT NULL,
                statut TEXT NOT NULL,
                PRIMARY KEY (adherent_id, evenement_id),
                FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE,
                FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE
            );",
            "CREATE TABLE IF NOT EXISTS produits_boutique (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL,
                description TEXT,
                prix REAL NOT NULL,
                stock INTEGER NOT NULL,
                acces_restreint INTEGER DEFAULT 0,
                image_url TEXT
            );",
            "CREATE TABLE IF NOT EXISTS commandes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                utilisateur_id INTEGER NOT NULL,
                date_commande TEXT NOT NULL,
                total REAL NOT NULL,
                statut TEXT NOT NULL,
                FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
            );",
            "CREATE TABLE IF NOT EXISTS commandes_lignes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                commande_id INTEGER NOT NULL,
                produit_id INTEGER NOT NULL,
                quantite INTEGER NOT NULL,
                prix_unitaire REAL NOT NULL,
                FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
                FOREIGN KEY (produit_id) REFERENCES produits_boutique(id) ON DELETE CASCADE
            );"
        ];

        foreach ($queries as $sql) {
            self::$pdo->exec($sql);
        }

        self::seedData();
    }

    /**
     * Injecte les données de démonstration : 50 Adhérents, 3 Profils Familles, 3 Articles, 4 Produits.
     * 
     * @return void
     */
    private static function seedData(): void {
        $stmt = self::$pdo->query("SELECT COUNT(*) FROM utilisateurs");
        if ((int)$stmt->fetchColumn() === 0) {
            
            // --- 1. PARAMÈTRES DU SITE ---
            $stmtParam = self::$pdo->prepare("INSERT INTO parametres_site (cle, valeur) VALUES (?, ?)");
            $stmtParam->execute(['couleur_primaire', '#1d4ed8']);
            $stmtParam->execute(['logo_url', '/public/images/logo-panda.png']);

            // --- 2. UTILISATEURS (Mot de passe commun : password123) ---
            $hash = password_hash('password123', PASSWORD_DEFAULT);
            $stmtUser = self::$pdo->prepare("INSERT INTO utilisateurs (email, mot_de_passe_hash, roles) VALUES (?, ?, ?)");
            
            // Staff (IDs 1 à 4)
            $stmtUser->execute(['admin@judoclub.fr', $hash, json_encode(['admin'])]); 
            $stmtUser->execute(['prof@judoclub.fr', $hash, json_encode(['prof'])]); 
            $stmtUser->execute(['redacteur@judoclub.fr', $hash, json_encode(['redacteur'])]); 
            $stmtUser->execute(['boutique@judoclub.fr', $hash, json_encode(['boutique'])]); 
            
            // Familles de Démonstration (IDs 5 à 8)
            $stmtUser->execute(['adulte@judoclub.fr', $hash, json_encode(['parent'])]); // Profil A (ID 5)
            $stmtUser->execute(['famille2@judoclub.fr', $hash, json_encode(['parent'])]); // Profil B (ID 6)
            $stmtUser->execute(['pere5@judoclub.fr', $hash, json_encode(['parent'])]); // Profil C Père (ID 7)
            $stmtUser->execute(['mere5@judoclub.fr', $hash, json_encode(['parent'])]); // Profil C Mère (ID 8)
            
            // --- 3. ADHÉRENTS (50 Profils) ---
            $stmtAdherent = self::$pdo->prepare("
                INSERT INTO adherents (
                    saison, nom, prenom, sexe, date_naissance, lieu_naissance, adresse, 
                    email, telephone, disciplines, ceinture, telephone_pere, telephone_mere, 
                    profession_pere, profession_mere, profession_adulte, problemes_sante, 
                    urgence_nom, urgence_prenom, urgence_telephone, type_certificat, 
                    autorisation_soins, droit_image, mode_paiement, montant_total, 
                    reduction_appliquee, paiement_valide
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $creerAdherent = function($nom, $prenom, $sexe, $dateNaissance, $disciplines, $ceinture) use ($stmtAdherent) {
                $stmtAdherent->execute([
                    '2026-2027', strtoupper($nom), $prenom, $sexe, $dateNaissance, 'Sainte-Verge', '10 Rue de la Diligence',
                    '', '0600000000', json_encode($disciplines), $ceinture, '0611223344', '0655667788', 
                    'Employé', 'Employée', 'Ingénieur', 'Aucun', 'Urgence', 'Contact', '0699887766', 
                    'Certificat médical', 1, 1, 'Virement', 150.00, 0, 1
                ]);
            };

            // Insertion Profils de Démonstration (IDs 1 à 8)
            $creerAdherent('Leroy', 'Marc', 'Masculin', '1990-05-15', ['MMA'], 'Ceinture Bleue'); // Profil A
            $creerAdherent('Moreau', 'Léo', 'Masculin', '2015-08-22', ['Judo'], 'Ceinture Jaune'); // Profil B - Enf 1
            $creerAdherent('Moreau', 'Mia', 'Féminin', '2012-03-10', ['TFT'], 'Ceinture Blanche'); // Profil B - Enf 2
            $creerAdherent('Girard', 'Paul', 'Masculin', '1985-11-05', ['Judo', 'MMA'], 'Ceinture Noire 1er Dan'); // Profil C - Père
            $creerAdherent('Girard', 'Sophie', 'Féminin', '1987-02-14', ['TFT'], 'Ceinture Verte'); // Profil C - Mère
            $creerAdherent('Girard', 'Tom', 'Masculin', '2014-07-30', ['Judo'], 'Ceinture Orange'); // Profil C - Enf 1
            $creerAdherent('Girard', 'Zoé', 'Féminin', '2016-12-01', ['Judo'], 'Ceinture Blanche-Jaune'); // Profil C - Enf 2
            $creerAdherent('Girard', 'Max', 'Masculin', '2018-09-18', ['Judo'], 'Ceinture Blanche'); // Profil C - Enf 3

            // Insertion de 42 adhérents aléatoires pour saturer les listes (IDs 9 à 50)
            $prenoms = ['Jean', 'Alice', 'Paul', 'Sophie', 'Lucas', 'Emma', 'Thomas', 'Chloe', 'Hugo', 'Lea', 'Arthur', 'Manon', 'Louis', 'Camille', 'Jules', 'Sarah', 'Gabriel', 'Clara', 'Leo', 'Anna'];
            $noms = ['Dupont', 'Martin', 'Legrand', 'Petit', 'Dubois', 'Lefebvre', 'Moreau', 'Laurent', 'Simon', 'Michel', 'Leroy', 'Roux', 'David', 'Bertrand', 'Morel', 'Fournier', 'Girard', 'Bonnet', 'Garnier', 'Lambert'];
            $grades = ['Ceinture Blanche', 'Ceinture Jaune', 'Ceinture Orange', 'Ceinture Verte', 'Ceinture Bleue', 'Ceinture Marron', 'Ceinture Noire 1er Dan'];
            $combosDisciplines = [['Judo'], ['Judo', 'MMA'], ['TFT'], ['MMA'], ['Judo', 'TFT']];
            
            for ($i = 0; $i < 42; $i++) {
                $sexe = ($i % 2 === 0) ? 'Masculin' : 'Féminin';
                $creerAdherent(
                    $noms[$i % count($noms)], 
                    $prenoms[$i % count($prenoms)] . ' ' . $i, 
                    $sexe, 
                    '201'.($i%9).'-0'.(($i%8)+1).'-15', 
                    $combosDisciplines[$i % count($combosDisciplines)], 
                    $grades[$i % count($grades)]
                );
            }

            // --- 4. LIAISONS FAMILIALES ---
            $stmtLiaison = self::$pdo->prepare("INSERT INTO utilisateurs_adherents (utilisateur_id, adherent_id, relation) VALUES (?, ?, ?)");
            
            // Profil A (Utilisateur 5 -> Adhérent 1)
            $stmtLiaison->execute([5, 1, 'Lui-même']);
            
            // Profil B (Utilisateur 6 -> Adhérents 2, 3)
            $stmtLiaison->execute([6, 2, 'Père']);
            $stmtLiaison->execute([6, 3, 'Père']);
            
            // Profil C (Utilisateur 7 [Père] et Utilisateur 8 [Mère] -> Adhérents 4, 5, 6, 7, 8)
            $relationsPere = [[7, 4, 'Lui-même'], [7, 5, 'Conjoint'], [7, 6, 'Père'], [7, 7, 'Père'], [7, 8, 'Père']];
            $relationsMere = [[8, 4, 'Conjoint'], [8, 5, 'Elle-même'], [8, 6, 'Mère'], [8, 7, 'Mère'], [8, 8, 'Mère']];
            foreach (array_merge($relationsPere, $relationsMere) as $rel) {
                $stmtLiaison->execute($rel);
            }

            // --- 5. CRÉNEAUX, SÉANCES & POINTAGES ---
            $jourActuel = (int)date('N');
            $dateDuJour = date('Y-m-d');
            
            $stmtCreneau = self::$pdo->prepare("INSERT INTO creneaux (nom, discipline, jour_semaine, heure_debut, heure_fin, professeur_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtCreneau->execute(['Judo Enfants', 'Judo', $jourActuel, '17:00', '18:30', 2]); // ID 1
            $stmtCreneau->execute(['Judo Adultes', 'Judo', $jourActuel, '18:30', '20:00', 2]); // ID 2
            $stmtCreneau->execute(['MMA Intermédiaire', 'MMA', $jourActuel, '20:00', '21:30', 2]); // ID 3
            
            $stmtSeance = self::$pdo->prepare("INSERT INTO seances (creneau_id, date_seance) VALUES (?, ?)");
            $stmtSeance->execute([1, $dateDuJour]); // ID 1
            $stmtSeance->execute([2, $dateDuJour]); // ID 2
            $stmtSeance->execute([3, $dateDuJour]); // ID 3

            $stmtPointage = self::$pdo->prepare("INSERT INTO pointages (seance_id, adherent_id, statut) VALUES (?, ?, ?)");
            // On sature les pointages avec les adhérents 9 à 50
            for ($id = 9; $id <= 30; $id++) {
                $stmtPointage->execute([1, $id, (rand(1, 100) <= 85) ? 'Present' : 'Absent']);
            }
            for ($id = 31; $id <= 50; $id++) {
                $stmtPointage->execute([3, $id, (rand(1, 100) <= 70) ? 'Present' : 'Absent']);
            }

            // --- 6. CMS (Articles JSON valides pour le Builder) ---
            $stmtArticle = self::$pdo->prepare("INSERT INTO articles_vitrine (titre, contenu, date_publication) VALUES (?, ?, ?)");
            
            $article1 = json_encode([
                ['type' => 'text', 'content' => "Toute l'équipe dirigeante est heureuse de vous présenter notre nouveau portail numérique.\nIl vous permettra de suivre nos actualités et de gérer vos inscriptions en ligne !"]
            ]);
            $stmtArticle->execute(['Bienvenue sur notre nouveau site !', $article1, date('Y-m-d H:i:s')]);
            
            $article2 = json_encode([
                ['type' => 'text', 'content' => "Le tournoi interclubs régional a été un immense succès ce week-end. Félicitations à nos médaillés !"],
                ['type' => 'text', 'content' => "N'oubliez pas de ramener vos passeports sportifs lors du prochain cours."]
            ]);
            $stmtArticle->execute(['Résultats du Tournoi Régional', $article2, date('Y-m-d H:i:s', strtotime('-3 days'))]);
            
            $article3 = json_encode([
                ['type' => 'text', 'content' => "Attention, en raison d'une intervention sur les tatamis, les cours de TFT du jeudi sont décalés de 30 minutes."]
            ]);
            $stmtArticle->execute(['Changement d\'horaire exceptionnel', $article3, date('Y-m-d H:i:s', strtotime('-1 week'))]);

            // --- 7. BOUTIQUE (4 Produits) ---
            $stmtProd = self::$pdo->prepare("INSERT INTO produits_boutique (nom, description, prix, stock, acces_restreint, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtProd->execute(['Judogi Initiation Enfant', 'Kimono de judo résistant, idéal pour les débutants (Taille 110 à 150). Ceinture blanche incluse.', 25.00, 15, 0, '']);
            $stmtProd->execute(['Écusson Officiel du Club', 'Écusson brodé à coudre sur le judogi. Réservé aux membres inscrits.', 5.00, 50, 1, '']);
            $stmtProd->execute(['T-Shirt Athlétique', 'T-Shirt technique respirant aux couleurs du club. Parfait pour le TFT.', 15.00, 10, 0, '']);
            $stmtProd->execute(['Ceinture de Couleur', 'Ceinture piquée 8 rangées. Tailles disponibles de 2m50 à 3m10.', 8.50, 30, 0, '']);

            // --- 8. ÉVÉNEMENTS ---
            $stmtEvent = self::$pdo->prepare("INSERT INTO evenements (nom, type, date_evenement, description) VALUES (?, ?, ?, ?)");
            $stmtEvent->execute(['Gala de fin d\'année', 'Gala', date('Y-m-d', strtotime('+1 month')), 'Démonstrations et remise des grades pour toutes les catégories.']);
            $stmtEvent->execute(['Championnat Départemental Minimes', 'Compétition', date('Y-m-d', strtotime('+2 weeks')), 'Épreuve qualificative pour les ligues.']);
        }
    }
}