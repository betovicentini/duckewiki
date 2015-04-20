CREATE FUNCTION getplanta_imgname(arqnome char(255), separador char(100), padrao INT(2), filtro INT(10)) RETURNS INT(10)
BEGIN
DECLARE resultado INT DEFAULT 0;
DECLARE nn INT DEFAULT 0;
DECLARE plantan CHAR(255) DEFAULT '';
IF padrao>=1 THEN
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(arqnome,separador,1),separador,-1)) INTO plantan;
IF padrao=2 THEN
	SET plantan = REPLACE(plantan,"WikiPlantaID-","");
	SELECT PlantaID,count(*)  into resultado,nn FROM Plantas WHERE PlantaID=plantan;
END IF;
IF padrao=1 THEN
	SELECT PlantaID, count(*) into resultado, nn  FROM Plantas WHERE PlantaTag=plantan AND (FiltrosIDS LIKE CONCAT('%filtroid_',filtro) OR FiltrosIDS LIKE CONCAT('%filtroid_',filtro,';%'));
END IF;
IF nn=1 THEN
	RETURN resultado;
ELSE
	RETURN NULL;
END IF;
ELSE
	RETURN NULL;
END IF;
END
