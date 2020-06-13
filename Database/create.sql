CREATE DATABASE chess_data;
USE chess_data;
CREATE TABLE game_status (
  `id` int NOT NULL AUTO_INCREMENT,
  `player_number` tinyint DEFAULT '1',
  `black` text,
  `white` text,
  PRIMARY KEY (`id`)
)