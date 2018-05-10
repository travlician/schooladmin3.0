ALTER TABLE config COMMENT='version 3.0.8';
CHARSET utf8;

CREATE TABLE IF NOT EXISTS `email_plugin_config` (`aspect` VARCHAR(20),`cfid` INTEGER(11) DEFAULT NULL,`cdata` TEXT DEFAULT NULL,UNIQUE KEY `aspectid` (`cfid`, `aspect`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;