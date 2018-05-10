ALTER TABLE config COMMENT='version 3.0.7';
CHARSET utf8;

CREATE TABLE `messages`
(
	`msid` int(11) NOT NULL AUTO_INCREMENT,
	`destid` int(11),
	`targets` ENUM('t','a','o','m','am','c','gt','at','s','p','sp','gs','gp','gsp'),
	`desttype` ENUM('t','p','s'),
	`message` TEXT,
	`sendertype` ENUM('t','p','s','sys') DEFAULT 'sys',
	`senderid` int(11),
	`sent` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`read` TIMESTAMP,
	PRIMARY KEY (`msid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `messagerights`
(
	`role` VARCHAR(20),
	`destination` VARCHAR(20),
	PRIMARY KEY(`role`,`destination`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* We now insert message rights for all admins */
INSERT INTO messagerights VALUES('admin','singlestudent');
INSERT INTO messagerights VALUES('admin','studentsgroup');
INSERT INTO messagerights VALUES('admin','singleparent');
INSERT INTO messagerights VALUES('admin','parentsgroup');
INSERT INTO messagerights VALUES('admin','singleteacher');
INSERT INTO messagerights VALUES('admin','administrators');
INSERT INTO messagerights VALUES('admin','office');
INSERT INTO messagerights VALUES('admin','mentors');
INSERT INTO messagerights VALUES('admin','absencemanagers');
INSERT INTO messagerights VALUES('admin','counselers');
INSERT INTO messagerights VALUES('admin','groupteachers');
INSERT INTO messagerights VALUES('admin','allteachers');

INSERT INTO tt_nederlands (short,full) VALUES('SendMessage','Stuur bericht');
INSERT INTO tt_english (short,full) VALUES('SendMessage','Send message');
INSERT INTO tt_Español (short,full) VALUES('SendMessage','Mandar mensaje');

INSERT INTO tt_nederlands (short,full) VALUES('MessageRightsTitle','Berichtenverkeer toegang instellingen');
INSERT INTO tt_english (short,full) VALUES('MessageRightsTitle','Setup messaging rights');
INSERT INTO tt_Español (short,full) VALUES('MessageRightsTitle','Configurar derechos de mensajes');

INSERT INTO tt_nederlands (short,full) VALUES('MessageDestination','Bericht bestemming');
INSERT INTO tt_english (short,full) VALUES('MessageDestination','Message destination');
INSERT INTO tt_Español (short,full) VALUES('MessageDestination','Destino del mensaje');

INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_singlestudent','Een leerling');
INSERT INTO tt_english (short,full) VALUES('mesgdest_singlestudent','A student');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_singlestudent','Un alumno');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_studentsgroup','Een groep leerlingen');
INSERT INTO tt_english (short,full) VALUES('mesgdest_studentsgroup','A group of students');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_studentsgroup','Un grupo de alumnos');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_singleparent','Een ouder');
INSERT INTO tt_english (short,full) VALUES('mesgdest_singleparent','A parent');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_singleparent','Un padre');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_parentsgroup','Een groep ouders');
INSERT INTO tt_english (short,full) VALUES('mesgdest_parentsgroup','A group of parents');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_parentsgroup','Un grupo de padres');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_singleteacher','Een leeraar');
INSERT INTO tt_english (short,full) VALUES('mesgdest_singleteacher','A teacher');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_singleteacher','Un docente');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_administrators','Beheerders');
INSERT INTO tt_english (short,full) VALUES('mesgdest_administrators','Administrators');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_administrators','Administradores');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_office','Administratie');
INSERT INTO tt_english (short,full) VALUES('mesgdest_office','Office');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_office','Administración');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_mentors','Mentoren');
INSERT INTO tt_english (short,full) VALUES('mesgdest_mentors','Mentors');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_mentors','Mentores');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_absencemanagers','Afwezigheid registratie');
INSERT INTO tt_english (short,full) VALUES('mesgdest_absencemanagers','Absence administration');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_absencemanagers','Registradores de ausencia');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_counselers','Vertrouwenspersonen');
INSERT INTO tt_english (short,full) VALUES('mesgdest_counselers','Counselors');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_counselers','Asesores confidenciales');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_groupteachers','Leraren van een klas');
INSERT INTO tt_english (short,full) VALUES('mesgdest_groupteachers','Teachers of a group');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_groupteachers','Maestros de una clase');
INSERT INTO tt_nederlands (short,full) VALUES('mesgdest_allteachers','Alle leraren');
INSERT INTO tt_english (short,full) VALUES('mesgdest_allteachers','All teachers');
INSERT INTO tt_Español (short,full) VALUES('mesgdest_allteachers','Todos los maestros');

INSERT INTO tt_nederlands (short,full) VALUES('mesgdest','Aan');
INSERT INTO tt_english (short,full) VALUES('mesgdest','To');
INSERT INTO tt_Español (short,full) VALUES('mesgdest','Para');
INSERT INTO tt_nederlands (short,full) VALUES('MarkRead','Markeer als gelezen');
INSERT INTO tt_english (short,full) VALUES('MarkRead','Mark as read');
INSERT INTO tt_Español (short,full) VALUES('MarkRead','Marcar como leido');
INSERT INTO tt_nederlands (short,full) VALUES('NextMessage','Volgend bericht');
INSERT INTO tt_english (short,full) VALUES('NextMessage','Next message');
INSERT INTO tt_Español (short,full) VALUES('NextMessage','Siguiente mensaje');
INSERT INTO tt_nederlands (short,full) VALUES('NoMoreMessages','Er zijn geen nieuwe berichten');
INSERT INTO tt_english (short,full) VALUES('NoMoreMessages','There a no new messages');
INSERT INTO tt_Español (short,full) VALUES('NoMoreMessages','No hay mensajes nuevos');
INSERT INTO tt_nederlands (short,full) VALUES('Close','Sluiten');
INSERT INTO tt_english (short,full) VALUES('Close','Close');
INSERT INTO tt_Español (short,full) VALUES('Close','Cerrar');
