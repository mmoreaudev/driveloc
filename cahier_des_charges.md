# Cahier des Charges Complet – DriveLoc

## 1. Présentation du Projet

### 1.1 Nom du projet

**DriveLoc**

### 1.2 Contexte

DriveLoc est une plateforme web de mise en relation entre propriétaires de véhicules et clients souhaitant louer un véhicule pour une durée déterminée.

La plateforme doit permettre à des particuliers ou professionnels de proposer leurs véhicules à la location et à des utilisateurs de rechercher, réserver et gérer leurs locations en ligne.

L'application doit être accessible depuis un navigateur web et proposer des interfaces adaptées aux différents profils utilisateurs.

---

# 2. Objectifs

L'objectif principal est de développer une application web permettant :

* La publication de véhicules à louer.
* La recherche de véhicules disponibles.
* La réservation de véhicules.
* La gestion des locations.
* L'administration de la plateforme.
* Le contrôle des disponibilités des véhicules.

---

# 3. Profils Utilisateurs

## 3.1 Visiteur

Un visiteur est un utilisateur non connecté.

Il peut :

* Consulter la page d'accueil.
* Parcourir les véhicules disponibles.
* Effectuer une recherche.
* Consulter les détails d'un véhicule.
* Créer un compte.
* Se connecter.

Il ne peut pas :

* Réserver un véhicule.
* Accéder aux tableaux de bord.

---

## 3.2 Client

Un client est un utilisateur connecté souhaitant louer un véhicule.

Il peut :

* Rechercher un véhicule.
* Réserver un véhicule.
* Consulter son historique.
* Annuler une réservation future.
* Modifier ses informations personnelles.

---

## 3.3 Propriétaire

Un propriétaire est un utilisateur autorisé à proposer des véhicules à la location.

Il peut :

* Ajouter un véhicule.
* Modifier un véhicule.
* Supprimer un véhicule.
* Consulter les réservations reçues.
* Gérer sa flotte.

---

## 3.4 Administrateur

L'administrateur dispose de tous les droits.

Il peut :

* Gérer les utilisateurs.
* Désactiver un compte.
* Réactiver un compte.
* Supprimer un véhicule.
* Consulter les statistiques globales.

---

# 4. Fonctionnalités

## 4.1 Gestion des Comptes

### Inscription

Champs obligatoires :

* Nom
* Prénom
* Email
* Mot de passe
* Confirmation du mot de passe

Contraintes :

* Email unique
* Mot de passe minimum 8 caractères

---

### Connexion

L'utilisateur peut se connecter via :

* Email
* Mot de passe

Sécurité :

* Mot de passe hashé avec password_hash()
* Vérification via password_verify()

---

### Déconnexion

Fermeture de session sécurisée.

---

# 5. Gestion des Véhicules

## Ajout d'un véhicule

Le propriétaire doit pouvoir saisir :

* Titre
* Catégorie
* Marque
* Modèle
* Immatriculation ou Référence
* Prix par jour
* Description
* Photo principale

---

## Modification d'un véhicule

Le propriétaire peut modifier :

* Informations
* Prix
* Description
* Photos

---

## Suppression d'un véhicule

Suppression logique ou physique selon l'architecture choisie.

---

# 6. Catégories de Véhicules

Catégories minimales :

* Citadine
* Berline
* SUV
* Utilitaire
* Moto
* Vélo électrique

L'administrateur peut en ajouter de nouvelles.

---

# 7. Recherche de Véhicules

## Critères de recherche

L'utilisateur peut rechercher selon :

### Catégorie

Exemples :

* Voiture
* Utilitaire
* Vélo électrique

### Disponibilité

* Date de début
* Date de fin

### Marque (optionnel)

### Prix maximum (optionnel)

---

## Résultats

Chaque résultat affiche :

* Photo
* Nom
* Catégorie
* Marque
* Modèle
* Prix par jour
* Disponibilité

---

# 8. Réservation

## Création

Le client sélectionne :

* Date début
* Date fin

Le système calcule automatiquement :

Prix total = Prix journalier × Nombre de jours

---

## Vérification de disponibilité

Avant validation :

Le système vérifie qu'aucune réservation ne chevauche la période demandée.

Une réservation doit être refusée si :

* Le véhicule est déjà réservé.
* Le véhicule est désactivé.
* Les dates sont invalides.

---

## États d'une réservation

* À venir
* En cours
* Terminée
* Annulée

---

# 9. Tableau de Bord Client

Le client dispose d'un espace personnel.

Affichage :

## Réservations futures

Informations :

* Véhicule
* Dates
* Prix

Actions :

* Annuler

---

## Réservations en cours

Informations :

* Véhicule
* Date début
* Date fin

---

## Réservations passées

Historique complet.

---

# 10. Tableau de Bord Propriétaire

## Gestion de flotte

Liste de ses véhicules.

Affichage :

* Photo
* Titre
* Catégorie
* Prix

Actions :

* Modifier
* Supprimer

---

## Réservations reçues

Affichage :

* Nom du client
* Véhicule
* Dates
* Prix total
* Statut

Utilisation d'une jointure SQL.

---

# 11. Tableau de Bord Administrateur

## Statistiques

Afficher :

### Nombre total de véhicules

### Nombre total d'utilisateurs

### Réservations en cours

### Réservations terminées

### Chiffre d'affaires théorique

Calcul :

Somme des prix de toutes les réservations validées.

---

# 12. Modération

## Gestion Utilisateurs

Actions :

* Activer
* Désactiver
* Supprimer

---

## Gestion Véhicules

Actions :

* Consulter
* Désactiver
* Supprimer

---

# 13. Gestion des Disponibilités

Fonctionnalité avancée.

Lorsqu'un client souhaite réserver :

Le système vérifie :

* date_debut
* date_fin

Aucune réservation existante ne doit chevaucher l'intervalle demandé.

Exemple :

Réservation existante :

01/06/2026 → 05/06/2026

Nouvelle réservation :

03/06/2026 → 07/06/2026

Résultat :

Réservation refusée.

---

# 14. Structure de Base de Données

## Table users

* id
* firstname
* lastname
* email
* password
* role
* status
* created_at

---

## Table categories

* id
* name

---

## Table vehicles

* id
* owner_id
* category_id
* title
* brand
* model
* registration
* price_per_day
* description
* image
* status
* created_at

---

## Table reservations

* id
* vehicle_id
* client_id
* start_date
* end_date
* total_price
* status
* created_at

---

# 15. Sécurité

## Authentification

Utilisation de :

* password_hash()
* password_verify()

---

## Sessions

Utilisation de :

* $_SESSION

Contrôle d'accès par rôle :

* Client
* Owner
* Admin

---

## Protection SQL

Obligation :

* PDO
* Requêtes préparées

---

## Protection XSS

Utilisation systématique :

htmlspecialchars()

---

## Protection CSRF

Jetons CSRF sur tous les formulaires sensibles.

---

# 16. Interface Utilisateur

Technologies :

* HTML5
* CSS3
* Bootstrap 5
* JavaScript

Design :

* Responsive
* Compatible mobile
* Compatible tablette
* Compatible ordinateur

---

# 17. Technologies Imposées

Back-End :

* PHP 8

Base de données :

* MySQL

Front-End :

* HTML5
* CSS3
* Bootstrap 5
* JavaScript

Serveur :

* Apache

Environnement :

* XAMPP ou WAMP

---

# 18. Livrables Attendus

Le projet final devra comporter :

* Base de données MySQL
* Script SQL complet
* Authentification
* Gestion des rôles
* Gestion des véhicules
* Recherche avancée
* Réservation
* Gestion des disponibilités
* Tableau de bord Client
* Tableau de bord Propriétaire
* Tableau de bord Administrateur
* Sécurisation complète
* Documentation d'installation

Fin du cahier des charges.
