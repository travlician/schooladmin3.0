ALTER TABLE config COMMENT='version 3.0.5';
CHARSET utf8;

INSERT INTO tt_nederlands (short,full) VALUES('NewOverview','Nieuw overzicht aanmaken');
INSERT INTO tt_english (short,full) VALUES('NewOverview','Create a new overview');
INSERT INTO tt_Español (short,full) VALUES('NewOverview','Crear una lista nueva');

INSERT INTO tt_nederlands (short,full) VALUES('Aggregation','Aggregatie');
INSERT INTO tt_english (short,full) VALUES('Aggregation','Aggregation');
INSERT INTO tt_Español (short,full) VALUES('Aggregation','Agregación');

INSERT INTO tt_nederlands (short,full) VALUES('Agg_AVG','Gemiddelde');
INSERT INTO tt_english (short,full) VALUES('Agg_AVG','Average');
INSERT INTO tt_Español (short,full) VALUES('Agg_AVG','Promedio');

INSERT INTO tt_nederlands (short,full) VALUES('Agg_SUM','Totaal');
INSERT INTO tt_english (short,full) VALUES('Agg_SUM','Sum');
INSERT INTO tt_Español (short,full) VALUES('Agg_SUM','Suma');

INSERT INTO tt_nederlands (short,full) VALUES('Agg_MAX','Maximum');
INSERT INTO tt_english (short,full) VALUES('Agg_MAX','Maximum');
INSERT INTO tt_Español (short,full) VALUES('Agg_MAX','Máximo');

INSERT INTO tt_nederlands (short,full) VALUES('Agg_MIN','Minimum');
INSERT INTO tt_english (short,full) VALUES('Agg_MIN','Minimum');
INSERT INTO tt_Español (short,full) VALUES('Agg_MIN','Mínimo');

INSERT INTO tt_nederlands (short,full) VALUES('Agg_COUNT','Aantal');
INSERT INTO tt_english (short,full) VALUES('Agg_COUNT','Count');
INSERT INTO tt_Español (short,full) VALUES('Agg_COUNT','Contar');

INSERT INTO tt_nederlands (short,full) VALUES('Agg_MODUS','Modus');
INSERT INTO tt_english (short,full) VALUES('Agg_MODUS','Modus');
INSERT INTO tt_Español (short,full) VALUES('Agg_MODUS','Modus');

INSERT INTO tt_nederlands (short,full) VALUES('Agg_MEDIAN','Mediaan');
INSERT INTO tt_english (short,full) VALUES('Agg_MEDIAN','Median');
INSERT INTO tt_Español (short,full) VALUES('Agg_MEDIAN','Mediana');

INSERT INTO tt_nederlands (short,full) VALUES('EditQfields','Bewerk de query velden');
INSERT INTO tt_english (short,full) VALUES('EditQfields','Edit the query fields');
INSERT INTO tt_Español (short,full) VALUES('EditQfields','Editar los campos de consulta');

INSERT INTO tt_nederlands (short,full) VALUES('Query','Query');
INSERT INTO tt_english (short,full) VALUES('Query','Query');
INSERT INTO tt_Español (short,full) VALUES('Query','Consulta');

INSERT INTO tt_nederlands (short,full) VALUES('EditQfieldsExpl','Van de query wordt het eerste record gebruikt en wel de kolom data. De string {sid} wordt in de query vervangen door het betreffende student nummer zoals intern in de database wordt gebruikt.');
INSERT INTO tt_english (short,full) VALUES('EditQfieldsExpl','The query uses the first record and the data column. The string {sid} is replaced in the query by the relevant student number as used internally in the database.');
INSERT INTO tt_Español (short,full) VALUES('EditQfieldsExpl','De la consulta se utiliza el primer registro, es decir, los datos de la columna data. La cadena {sid} se sustituye en la consulta por el número de estudiante utilizado como internamente en la base de datos.');

ALTER TABLE studentviewitems ADD COLUMN aggregate VARCHAR(10) AFTER sortseq;

CREATE TABLE queryfield (
  `fieldid` int(11) NOT NULL AUTO_INCREMENT,
	`fieldname` VARCHAR(255),
	`fquery` TEXT,
	PRIMARY KEY(`fieldid`),
	UNIQUE KEY (`fieldname`)
) ENGINE=InnoDB CHARSET UTF8;

