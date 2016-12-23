CREATE FUNCTION checknir(specid INT(10), pltid INT(10)) RETURNS INT(10)
BEGIN
DECLARE nnirs INT(10) DEFAULT 0;
DECLARE nnirspl INT(10) DEFAULT 0;
IF (specid>0) THEN
	SELECT COUNT(*) INTO nnirs  FROM NirSpectra WHERE EspecimenID=specid;
END IF;
IF (pltid>0) THEN
	SELECT COUNT(*) INTO nnirspl  FROM NirSpectra WHERE PlantaID=pltid;
END IF;
SET nnirs = nnirs+nnirspl;
RETURN nnirs;
END

