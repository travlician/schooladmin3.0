ALTER TABLE config COMMENT='version 3.0.1';
CHARSET utf8;
CREATE TABLE studentview (
  `viewid` int(11) NOT NULL AUTO_INCREMENT,
	`tid` int(11) unsigned,
	`description` TEXT,
	`protect` enum('A','T','M','C','N','O','P') DEFAULT NULL,
	PRIMARY KEY(`viewid`)
) ENGINE=InnoDB;

CREATE TABLE studentviewitems (
	`viewid` int(11),
	`seqno` int(11) unsigned,
	`fieldname` VARCHAR(255),
	`filter` TEXT,
	`sortseq` int(11) unsigned,
	PRIMARY KEY (`viewid`,`seqno`)
) ENGINE=InnoDB;

INSERT INTO tt_nederlands (short,full) VALUES('studentoverviews','Leerlingenoverzicht');
INSERT INTO tt_english (short,full) VALUES('studentoverviews','Student overviews');
INSERT INTO tt_Español (short,full) VALUES('studentoverviews','Listas dedicadas de alumnos');
INSERT INTO tt_nederlands (short,full) VALUES('tpage_studentoverviews',"<IMG src='PNG/Knowledge.png' title='Leerlingenoverzicht'>");
INSERT INTO tt_english (short,full) VALUES('tpage_studentoverviews',"<IMG src='PNG/Knowledge.png' title='Student overviews'>");
INSERT INTO tt_Español (short,full) VALUES('tpage_studentoverviews',"<IMG src='PNG/Knowledge.png' title='Listas dedicadas de alumnos'>");

INSERT INTO tt_nederlands (short,full) VALUES('Filter',"Filter");
INSERT INTO tt_english (short,full) VALUES('Filter',"Filter");
INSERT INTO tt_Español (short,full) VALUES('Filter',"Filtro");

INSERT INTO tt_nederlands (short,full) VALUES('SortSequence',"Sorteervolgorde");
INSERT INTO tt_english (short,full) VALUES('SortSequence',"Sorting sequence");
INSERT INTO tt_Español (short,full) VALUES('SortSequence',"Sequencia de sortar");

INSERT INTO tt_nederlands (short,full) VALUES('FieldError',"Er zijn geen velden of velden die niet toegankelijk zijn");
INSERT INTO tt_english (short,full) VALUES('FieldError',"No fields are available or fields are defined that can not be accessed");
INSERT INTO tt_Español (short,full) VALUES('FieldError',"No hay campos o hay campos que no son accesible");

INSERT INTO tt_nederlands (short,full) VALUES('StudentsPerPage',"Aantal leerlingen per pagina");
INSERT INTO tt_english (short,full) VALUES('StudentsPerPage',"Number of students per page");
INSERT INTO tt_Español (short,full) VALUES('StudentsPerPage',"Cantidad de alumnos por pagina");

INSERT INTO tt_nederlands (short,full) VALUES('AllGroups',"Alle groepen");
INSERT INTO tt_english (short,full) VALUES('AllGroups',"All groups");
INSERT INTO tt_Español (short,full) VALUES('AllGroups',"Todos lo grupos");

INSERT INTO tt_nederlands (short,full) VALUES('CurrentGroup',"Huidige groep");
INSERT INTO tt_english (short,full) VALUES('CurrentGroup',"Current group");
INSERT INTO tt_Español (short,full) VALUES('CurrentGroup',"Grupo selectada");