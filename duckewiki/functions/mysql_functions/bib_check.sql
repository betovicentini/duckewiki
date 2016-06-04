CREATE FUNCTION bib_check(obibkey VARCHAR(200)) RETURNS INT(10) 
BEGIN
DECLARE resultado INT(10) DEFAULT NULL;
DECLARE bbid INT(10) DEFAULT NULL;
IF ((obibkey IS NOT NULL) AND obibkey<>"") THEN
SELECT BibID INTO bbid FROM `BiblioRefs` WHERE UPPER(BibKey) LIKE UPPER(obibkey);
IF (bbid>0) THEN
SET resultado = bbid;
END IF;
END IF;
RETURN resultado;
END