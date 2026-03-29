CREATE DATABASE IF NOT EXISTS Iran_War;
USE Iran_War;

CREATE TABLE Categorie (
  categorie_id varchar(255) PRIMARY KEY,
  nom varchar(255),
  slug_cat varchar(255)
);

CREATE TABLE article (
  article_id varchar(255) PRIMARY KEY,
  titre varchar(255),
  slug varchar(255),
  resume varchar(255),
  image_principale varchar(255),
  date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  id_categorie varchar(255),
  CONSTRAINT fk_article_categorie FOREIGN KEY (id_categorie) REFERENCES Categorie (categorie_id)
);

CREATE TABLE article_details (
  details_id varchar(255) PRIMARY KEY,
  article_id varchar(255),
  sous_titre varchar(255),
  contenu TEXT, 
  slug_details varchar(255),
  CONSTRAINT fk_details_article FOREIGN KEY (article_id) REFERENCES article (article_id)
);

CREATE TABLE image (
  image_id varchar(255) PRIMARY KEY,
  details_id varchar(255),
  path varchar(255),
  alt_image varchar(255),
  CONSTRAINT fk_image_details FOREIGN KEY (details_id) REFERENCES article_details (details_id)
);

CREATE TABLE user (
  user_id varchar(255) PRIMARY KEY,
  email varchar(255) UNIQUE,
  password varchar(255)
);