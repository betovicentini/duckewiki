CREATE FUNCTION mediadapsbyplant(pltid INT(10), daptrid INT(10)) RETURNS DOUBLE
BEGIN
DECLARE resultado DOUBLE DEFAULT NULL;
SELECT AVG(mediadaps(TraitVariation)) INTO resultado FROM Monitoramento WHERE PlantaID=pltid AND TraitID=daptrid;
RETURN resultado;
END
