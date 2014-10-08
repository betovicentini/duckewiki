CREATE FUNCTION checkifnotrait(trid INT(10),pltid INT(10) ,specid INT(10)) RETURNS INT
BEGIN
DECLARE lg INT(10) DEFAULT 0;
IF (pltid>0) THEN
	SELECT COUNT(*)  INTO lg FROM Monitoramento WHERE TraitID=trid AND PlantaID=pltid;
	IF (lg=0) THEN
		SELECT COUNT(*)  INTO lg FROM Traits_variation WHERE TraitID=trid AND PlantaID=pltid;
	END IF;
END IF;
IF (specid>0) THEN
	SELECT COUNT(*)  INTO lg FROM Traits_variation WHERE TraitID=trid AND EspecimenID=specid;
END IF;
return lg;
END
