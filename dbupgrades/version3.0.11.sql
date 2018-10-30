ALTER TABLE config COMMENT='version 3.0.11';
CHARSET utf8;

INSERT INTO tt_nederlands (short,full) VALUES("cpw_err_pwnocomply","wachtwoord voldoet niet aan alle voorwaarden");
INSERT INTO tt_english (short,full) VALUES("cpw_err_pwnocomply","password doen not comply with all criteria");
INSERT INTO tt_Español (short,full) VALUES("cpw_err_pwnocomply","contraseña no esta conforme todas la condiciones");

INSERT INTO tt_nederlands (short,full) VALUES("cpw_change_pw_group_submit","Genereer nieuwe wachtwoorden voor deze groep");
INSERT INTO tt_english (short,full) VALUES("cpw_change_pw_group_submit","Generate new passwords for this group");
INSERT INTO tt_Español (short,full) VALUES("cpw_change_pw_group_submit","Genera contraseñas neuvas por esta grupo");

INSERT INTO tt_nederlands (short,full) VALUES("cpw_change_pw_students_submit","Genereer nieuwe wachtwoorden voor alle leerlingen");
INSERT INTO tt_english (short,full) VALUES("cpw_change_pw_students_submit","Generate new passwords for all students");
INSERT INTO tt_Español (short,full) VALUES("cpw_change_pw_students_submit","Genera contraseñas neuvas por todos los alumnos");

INSERT INTO tt_nederlands (short,full) VALUES("cpw_set_expiry_period","Geldigheidstermijn wachtwoorden docenten (dagen)");
INSERT INTO tt_english (short,full) VALUES("cpw_set_expiry_period","Validity period for teacher passwords (days)");
INSERT INTO tt_Español (short,full) VALUES("cpw_set_expiry_period","Termino de validez de las contraseñas por docentes (dias)");

INSERT INTO tt_nederlands (short,full) VALUES("cpw_min_size","Minimaal aantal karakters in docenten wachtwoorden");
INSERT INTO tt_english (short,full) VALUES("cpw_min_size","Minimum amount of characters in a teacher password");
INSERT INTO tt_Español (short,full) VALUES("cpw_min_size","Cantidad minimo de caracters en las contraseñas de los docentes");

INSERT INTO tt_nederlands (short,full) VALUES("cpw_need_lowercase","Docentenwachtwoorden moeten kleine letters bevatten");
INSERT INTO tt_english (short,full) VALUES("cpw_need_lowercase","Teacher password must have lowercase characters");
INSERT INTO tt_Español (short,full) VALUES("cpw_need_lowercase","Contraseñas de docentes deben tener minúsculas");

INSERT INTO tt_nederlands (short,full) VALUES("cpw_need_uppercase","Docentenwachtwoorden moeten hoofdletters bevatten");
INSERT INTO tt_english (short,full) VALUES("cpw_need_uppercase","Teacher password must have uppercase characters");
INSERT INTO tt_Español (short,full) VALUES("cpw_need_uppercase","Contraseñas de docentes deben tener mayúsculas");

INSERT INTO tt_nederlands (short,full) VALUES("cpw_need_specialchar","Docentenwachtwoorden moeten leestekens bevatten (.,:;?!)");
INSERT INTO tt_english (short,full) VALUES("cpw_need_specialchar","Teacher password must have punctuation marks (.,:;?!)");
INSERT INTO tt_Español (short,full) VALUES("cpw_need_specialchar","Contraseñas de docentes deben tener signos de puntuacion (.,:;?!)");

INSERT INTO tt_nederlands (short,full) VALUES("cpw_stud_confirm","LET OP! Wilt U echt alle wachtwoorden van ouders en leerlingen veranderen?");
INSERT INTO tt_english (short,full) VALUES("cpw_stud_confirm","ATTENTION! Do you really want to change all passwords for students and parents?");
INSERT INTO tt_Español (short,full) VALUES("cpw_stud_confirm","¡ATENCIÓN! ¿Realmente quiere cambiar todas las contraseñas para estudiantes y padres?");

ALTER TABLE teacher ADD COLUMN pwexpirydate DATE;

INSERT INTO teacherpreferences (tid,aspect,avalue) VALUES(1,10001,6);
INSERT INTO teacherpreferences (tid,aspect,avalue) VALUES(1,10002,0);
INSERT INTO teacherpreferences (tid,aspect,avalue) VALUES(1,10003,0);
INSERT INTO teacherpreferences (tid,aspect,avalue) VALUES(1,10004,0);