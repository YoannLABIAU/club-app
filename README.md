# 🥋 AssoCMS - Le CMS Open-Source pour Clubs Associatifs

**AssoCMS** est un système de gestion de contenu (CMS) ultra-léger, modulaire et orienté sécurité, spécialement conçu pour répondre aux besoins des clubs sportifs et associatifs (arts martiaux, sports collectifs, clubs omnisports). 

Contrairement aux solutions généralistes ou monolithiques, AssoCMS propose une architecture découplée et un stockage abstrait permettant d'adapter les fiches de membres à n'importe quelle discipline sans modifier le code source du noyau.

---

## 🛠️ Prérequis Techniques

Avant de débuter, assurez-vous d'avoir l'environnement suivant :
- **PHP** >= 8.5.x (avec extensions `pdo_sqlite` ou `pdo_mysql`, `mbstring`, `json`)
- **Composer** (pour l'autoloading PSR-4 et la gestion des dépendances)
- **Node.js & NPM** (optionnel, pour la compilation des assets Tailwind si nécessaire)

---

## 📁 Architecture du Projet

Le projet repose sur une structure rigoureuse garantissant l'isolation des responsabilités et la sécurité :

```text
📁 asso-cms/
├── 📁 core/                # Le moteur intouchable du CMS (Routeur, PDO, Sécurité, Container)
├── 📁 module/              # Fonctionnalités à la carte (Séparées en xxx-api et xxx-ui)
├── 📁 theme/               # Moteur de gabarits visuels et thèmes dynamiques
├── 📁 public/              # Front Controller (Seul dossier racine web -> public/index.php)
├── 📁 storage/             # Logs, cache, et base de données SQLite locale
├── 📁 qa/                  # Assurance Qualité et tests automatisés (Pest / Playwright)
├── .env                    # Variables d'environnement locales (exclu de Git)
├── .env.example            # Modèle de configuration par défaut
└── composer.json           # Dépendances et configuration de l'autoloader PSR-4
```

## ⚙️ Caractéristiques Techniques
- **PHP 8.5.x** : Exploitation du typage strict ( `declare(strict_types=1);` ), des structures modernes et des performances accrues.
- Sécurité par Déficit de Droits :
    - Isolation physique via le dossier `public/` (empêchant l'accès direct aux scripts du `core`).
    - Protection systématique contre les injections SQL (requêtes préparées PDO) et les failles XSS.
    - Gestion centralisée des jetons CSRF et des sessions.
- Base de Données SQLite (et compatible MySQL/MariaDB) : Stockage local ultra-rapide par défaut, idéal pour le développement local (ex: Laragon) et les environnements à faible empreinte.
- **Architecture EAV (Entity-Attribute-Value)** : Les fiches membres s'adaptent dynamiquement aux métadonnées des modules activés sans saturer la structure SQL principale.
- **Moteur de Thèmes & Personnalisation** : Surcharge visuelle totale (couleurs primaires dynamiques, logos et templates) gérée directement depuis l'espace administrateur.

---

## 🧪 Assurance Qualité (QA) & Tests
Le dossier qa intègre l'infrastructure nécessaire pour garantir la non-régression de l'application :
- Tests Unitaires & Intégration : Réalisés via Pest (surcouche moderne de PHPUnit).
- Tests de Bout en Bout (E2E) : Validés via Playwright pour simuler les parcours utilisateurs complexes (comme le pointage en direct des professeurs sur le tapis).

---

## 🚀 Installation & Déploiement Rapide
1. PCloner le dépôt et installer les dépendances PHP :
```Bash
git clone [https://github.com/votre-compte/asso-cms.git](https://github.com/votre-compte/asso-cms.git)
cd asso-cms
composer install
```

2. Configurer l'environnement :
```Bash
cp .env.example .env
```
*Éditez le fichier `.env` pour renseigner vos accès à la base de données et votre clé de chiffrement.*

3. Configuration du Serveur Web :
Pointez la racine document (DocumentRoot) de votre serveur web (Apache / Nginx / Serveur PHP natif) **exclusivement** sur le dossier `/public`.

*Exemple en dev avec le serveur PHP intégré :*

```Bash
php -S localhost:8000 -t public
```

---

## 🎯 Fonctionnalités Métier Disponibles (Modules)
- **Pointage en direct** : Interface dédiée aux professeurs pour faire l'appel des séances par créneau horaire de manière asynchrone (AJAX/Fetch).
- **Portail Famille** : Espace sécurisé de gestion multi-adhérents, suivi des présences et des compétitions.
- **Vitrine CMS** : Constructeur de blocs pour la page d'accueil et gestion des actualités.
- **Boutique** : Gestion des commandes de matériel et validation des paiements.
- **Inscriptions** : Gestion des cotisations, des adhésions et des évènements.

---

## 🎛️ Panneau d'Administration & Gestion des Modules

AssoCMS intègre un espace d'administration sécurisé et modulaire permettant aux dirigeants et administrateurs de piloter l'ensemble du club sans compétences techniques :

- **Gestionnaire d'Extensions (Modules ON/OFF) :** Activez ou désactivez à la volée les fonctionnalités selon les besoins du club (Pointage, Boutique, CMS Vitrine, Événements) directement depuis le back-office, bloquant ou autorisant l'accès aux routes correspondantes.
- **Paramétrage Visuel & Identité :** Personnalisation dynamique de l'identité du club (logo, couleurs primaires injectées en CSS).
- **Gestion des Rôles Multiples :** Attribution de droits ciblés (Administrateur, Professeur/Encadrant, Rédacteur CMS, Responsable Boutique, Parent/Adhérent) pour un cloisonnement sécurisé des données.
- **Publication & Modération :** Interface de gestion des articles de la vitrine (via un constructeur de blocs moderne) et suivi en temps réel des commandes de la boutique et des inscriptions.

---

## 🧪 Assurance Qualité (QA) & Tests
Pour lancer la suite de tests automatisés :

* Tests Unitaires & Intégration (Pest) :

```Bash
vendor/bin/pest
```
* Tests End-to-End (Playwright) :

```Bash
npx playwright test
```
---

## 📜 Licence
Projet open-source distribué sous licence MIT. Conçu pour simplifier la vie des bénévoles et des dirigeants de clubs associatifs.

