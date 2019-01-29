;Se connecter comme root
show databases;
create database prenom;
create user 'user_prenom'@'localhost' identified by 'pwd_prenom';
flush privileges;

;Se connecter comme "user_prenom"
use prenom;
show tables;

CREATE TABLE prenom
(
    prenom   VARCHAR(250),
    sexe     VARCHAR(1),
    annais   VARCHAR(4),
    dep_code VARCHAR(3),
    nombre   INT,
    PRIMARY KEY (prenom,sexe,annais,dep_code)
);

CREATE TABLE departement
(
    dep_code VARCHAR(3),
    dep_name      VARCHAR(250),
    PRIMARY KEY (dep_code)
);
