CREATE FUNCTION checkplantaspecimen(pltid INT(10)) RETURNS INT(10)
BEGIN
DECLARE nspecs INT(10) DEFAULT 0;
IF (pltid>0) THEN
	SELECT COUNT(*) INTO nspecs  FROM Especimenes WHERE PlantaID=pltid;
END IF;
RETURN nspecs;
END

