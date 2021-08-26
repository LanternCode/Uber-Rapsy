CREATE TABLE `TheRemnantsFighters`.`BattleEnemy` ( `BattleEnemyId` INT NOT NULL AUTO_INCREMENT COMMENT 'ID of an enemy in battle' , `BattleId` INT NOT NULL COMMENT 'ID of the battle' , `EnemyId` INT NOT NULL COMMENT 'ID of the enemy' , PRIMARY KEY (`BattleEnemyId`)) ENGINE = MyISAM;

CREATE TABLE `theremnantsfighters`.`Battle` ( `BattleId` INT NOT NULL AUTO_INCREMENT COMMENT 'ID of the battle' , `CreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of when battle was first created' , `CreatedBy` INT NOT NULL COMMENT 'ID of the user who made the battle' , `FinishedAt` TIMESTAMP NULL COMMENT 'Timestamp of when the battle was over' , PRIMARY KEY (`BattleId`)) ENGINE = MyISAM;

CREATE TABLE `theremnantsfighters`.`enemy` ( `EnemyId` INT NOT NULL AUTO_INCREMENT COMMENT 'ID of an enemy' , `Name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL , `Hp` INT NOT NULL , PRIMARY KEY (`EnemyId`)) ENGINE = MyISAM;

ALTER TABLE `enemy` ADD `Dodge` INT(2) NOT NULL AFTER `Hp`, ADD `Attack` INT NOT NULL AFTER `Dodge`, ADD `Speed` INT(3) NOT NULL AFTER `Attack`, ADD `Armor` INT NOT NULL DEFAULT '0' AFTER `Speed`, ADD `MRes` INT NOT NULL DEFAULT '0' AFTER `Armor`, ADD `ArmorPercent` INT(3) NOT NULL DEFAULT '0' AFTER `MRes`, ADD `MResPercent` INT(3) NOT NULL DEFAULT '0' AFTER `ArmorPercent`;

ALTER TABLE `battleenemy` ADD `MaxHP` INT NOT NULL AFTER `EnemyId`, ADD `CurHP` INT NOT NULL AFTER `MaxHP`, ADD `Dodge` INT(2) NOT NULL AFTER `CurHP`, ADD `CurDodge` INT(2) NOT NULL AFTER `Dodge`, ADD `Attack` INT NOT NULL AFTER `CurDodge`, ADD `CurAttack` INT NOT NULL AFTER `Attack`, ADD `Speed` INT(3) NOT NULL AFTER `CurAttack`, ADD `CurSpeed` INT(3) NOT NULL AFTER `Speed`;

ALTER TABLE `battleenemy` ADD `Armor` INT NOT NULL DEFAULT '0' AFTER `CurSpeed`, ADD `MRes` INT NOT NULL DEFAULT '0' AFTER `Armor`, ADD `ArmorPercent` INT(3) NOT NULL DEFAULT '0' AFTER `MRes`, ADD `MResPercent` INT(3) NOT NULL DEFAULT '0' AFTER `ArmorPercent`;

INSERT INTO `enemy` (`EnemyId`, `Name`, `Hp`, `Dodge`, `Attack`, `Speed`, `Armor`, `MRes`, `ArmorPercent`, `MResPercent`) VALUES (NULL, 'Żywa Skrzynia', '90', '10', '19', '75', '6', '0', '0', '0');

2x --> INSERT INTO `battleenemy` (`BattleEnemyId`, `BattleId`, `EnemyId`, `MaxHP`, `CurHP`, `Dodge`, `CurDodge`, `Attack`, `CurAttack`, `Speed`, `CurSpeed`, `Armor`, `MRes`, `ArmorPercent`, `MResPercent`) VALUES (NULL, '1', '1', '90', '90', '10', '10', '19', '19', '75', '75', '6', '0', '0', '0');

ALTER TABLE `battleenemy` ADD `EnemyName` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_polish_ci NULL DEFAULT NULL COMMENT 'Gets filled in prod' AFTER `EnemyId`;

CREATE TABLE `theremnantsfighters`.`player` ( `PlayerId` INT NOT NULL AUTO_INCREMENT COMMENT 'ID of a player' , `Name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL COMMENT 'Player name' , `Class` VARCHAR(20) NOT NULL COMMENT 'Player class' , `Level` INT(2) NOT NULL , `Hp` INT(3) NOT NULL , `Agility` INT(3) NOT NULL , `Appearance` INT(3) NOT NULL , `Speed` INT(3) NOT NULL , `Dexterity` INT(3) NOT NULL , `Strength` INT(3) NOT NULL , `Dodge` INT(3) NOT NULL , `BaseAttack` INT(3) NOT NULL , `Armor` INT(2) NOT NULL , PRIMARY KEY (`PlayerId`)) ENGINE = MyISAM;

CREATE TABLE `theremnantsfighters`.`battleplayer` ( `BattlePlayerId` INT NOT NULL AUTO_INCREMENT , `BattleId` INT NOT NULL , `PlayerId` INT NOT NULL , `PlayerName` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_polish_ci NULL COMMENT 'Gets filled in prod' , `MaxHp` INT(3) NOT NULL COMMENT '999 seems max' , `CurHp` INT(3) NOT NULL , `BaseAgility` INT(2) NOT NULL COMMENT '99 seems max' , `CurAgility` INT(2) NOT NULL , `BaseSpeed` INT(2) NOT NULL COMMENT '99 seems max' , `CurSpeed` INT(2) NOT NULL , `BaseDodge` INT(2) NOT NULL COMMENT '99 seems max' , `CurDodge` INT(2) NOT NULL , `BaseAttack` INT(2) NOT NULL COMMENT '65 seems max' , `CurAttack` INT(2) NOT NULL , `Armor` INT(2) NOT NULL COMMENT 'non-removable' , PRIMARY KEY (`BattlePlayerId`)) ENGINE = MyISAM;

INSERT INTO `player` (`PlayerId`, `Name`, `Class`, `Level`, `Hp`, `Agility`, `Appearance`, `Speed`, `Dexterity`, `Strength`, `Dodge`, `BaseAttack`, `Armor`) VALUES (NULL, 'Miles', 'Warrior', '8', '125', '70', '25', '34', '65', '88', '44', '27', '6'), (NULL, 'Kościelny', 'Dancer', '7', '90', '65', '55', '77', '88', '77', '99', '33', '2');

INSERT INTO `battleplayer` (`BattlePlayerId`, `BattleId`, `PlayerId`, `PlayerName`, `MaxHp`, `CurHp`, `BaseAgility`, `CurAgility`, `BaseSpeed`, `CurSpeed`, `BaseDodge`, `CurDodge`, `BaseAttack`, `CurAttack`, `Armor`) VALUES (NULL, '1', '1', NULL, '125', '125', '70', '70', '34', '34', '44', '44', '27', '27', '6'), (NULL, '1', '2', NULL, '90', '90', '65', '65', '77', '77', '99', '99', '33', '33', '2');
