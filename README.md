# Introduction

hpb-website est un projet de stage d'une période de 6 semaines au sein d'une formation chez WebForce3 Dijon, ayant pour objectif de rénover le site de Handicaps Pays-Beaujolais. <br>
Ce projet est réalisé par Virgil TATU, David SIMOINE, Etienne RUYNAT ainsi que Jean-Claude TATU, notre maître de stage.<br>
<br>
Les outils et frameworks utilisés sont :
  - PHPstorm, notre IDE
  - PHP 8.0.12
  - Symfony 5.3
  - Twig
  - jQuery
  - Bootstrap 5 (pour CSS et JS)
  
# Installation

1. Cloner le projet depuis le repository hpb-website, dans un emplacement souhaité :

Choisir l'emplacement voulu depuis la console :

```
git clone https://github.com/Etimo285/hpb-website.git
```

Puis sélectionner l'emplacement de votre projet avec :

```
cd hpb-website
```

2. Une fois dans votre projet, Installer composer (pour extensions et bundles) :

```
composer install
```

OU composer update, si nécéssaire, et si vous avez déjà installé composer :

```
composer update
```

3. Modifier paramètres d'environnement dans le fichier .env (adresse de votre BDD) :

```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/hpb-website?serverVersion=5.7"
```

4. Créer la base de données, puis migrer les entités avec les commandes suivantes :

```
symfony console doctrine:database:create
symfony console make:migration
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
symfony console assets:install public
```

Les fixtures créeront :
* Un compte admin (email: admin@a.a , password : Password1*)
* 25 comptes adhérents (email aléatoire "vérifiés", password : Password1*)
* 10 comptes utilisateurs (email aléatoire, password : Password1*)
* 40 articles
* 5 événements
* 10 alertes