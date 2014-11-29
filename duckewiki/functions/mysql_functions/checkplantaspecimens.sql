CREATE FUNCTION checkplantaspecimens(pltid INT(10)) RETURNS INT
BEGIN
DECLARE temspec INT(10) DEFAULT 0;
SELECT COUNT(*) INTO temspec FROM Especimenes WHERE PlantaID=pltid;
RETURN temspec;
END
