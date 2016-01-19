<?php

//OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('openwall')->getRootDir() . 'langs.zip', 'openwall');

//installing database
$sql = "CREATE TABLE `" . OW_DB_PREFIX . "openwall_provider` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(200) NOT NULL,
	`api_url` VARCHAR(200) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=MyISAM
ROW_FORMAT=DEFAULT";
OW::getDbo()->query($sql);

//adding admin settings page
OW::getPluginManager()->addPluginSettingsRouteName('openwall', 'openwall.admin');