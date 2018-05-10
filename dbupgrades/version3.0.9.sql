ALTER TABLE config COMMENT='version 3.0.9';
CHARSET utf8;

ALTER TABLE archived_reports ADD COLUMN rcid int(11) DEFAULT NULL;