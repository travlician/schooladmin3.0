UPDATE `student` SET password=MD5(password) WHERE password IS NOT NULL;
UPDATE `teacher` SET password=MD5(password) WHERE password IS NOT NULL;
