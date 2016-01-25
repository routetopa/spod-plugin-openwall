<?php

//OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('openwall')->getRootDir() . 'langs.zip', 'openwall');

$path = OW::getPluginManager()->getPlugin('openwall')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'openwall');

//installing database
$sql = "

DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'openwall_provider`;

CREATE TABLE `" . OW_DB_PREFIX . "openwall_provider` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(200) NOT NULL,
	`api_url` VARCHAR(200) NOT NULL,
	`image_hash` varchar(200) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `" . OW_DB_PREFIX . "openwall_provider` (`id`, `title`, `api_url`, `image_hash`) VALUES (1, 'CKAN', 'http://ckan.routetopa.eu', '11');
INSERT INTO `" . OW_DB_PREFIX . "openwall_provider` (`id`, `title`, `api_url`, `image_hash`) VALUES (2, 'Issy-les-Moulineaux', 'https://data.issy.com', '22');
INSERT INTO `" . OW_DB_PREFIX . "openwall_provider` (`id`, `title`, `api_url`, `image_hash`) VALUES (3, 'Regione Lazio', 'https://dati.lazio.it/catalog', '33');
INSERT INTO `" . OW_DB_PREFIX . "openwall_provider` (`id`, `title`, `api_url`, `image_hash`) VALUES (4, 'UK Open Data Portal', 'https://data.gov.uk', '44');
INSERT INTO `" . OW_DB_PREFIX . "openwall_provider` (`id`, `title`, `api_url`, `image_hash`) VALUES (5, 'Dataportaal van de Nederlandse overheid', 'https://data.overheid.nl/data', '55');
INSERT INTO `" . OW_DB_PREFIX . "openwall_provider` (`id`, `title`, `api_url`, `image_hash`) VALUES (6, 'Région Île-de-France', 'http://data.iledefrance.fr', '66');
INSERT INTO `" . OW_DB_PREFIX . "openwall_provider` (`id`, `title`, `api_url`, `image_hash`) VALUES (7, 'Ireland`s Open Data Portal', 'https://data.gov.ie', '77');
INSERT INTO `" . OW_DB_PREFIX . "openwall_provider` (`id`, `title`, `api_url`, `image_hash`) VALUES (8, 'TET', 'http://vmdatagov01.deri.ie:8080', '88');

";
OW::getDbo()->query($sql);

//adding admin settings page
OW::getPluginManager()->addPluginSettingsRouteName('openwall', 'openwall.admin');