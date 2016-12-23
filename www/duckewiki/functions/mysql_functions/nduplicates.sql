CREATE FUNCTION nduplicates(tid int(10),linkid int(10),linktype char(100)) RETURNS text CHARSET utf8
BEGIN
DECLARE resultado INT(10) DEFAULT 0;
IF (linktype='Especimenes') THEN
SELECT TraitVariation INTO resultado FROM Traits_variation WHERE EspecimenID=linkid AND TraitID=tid;
ELSE
SELECT TraitVariation INTO resultado FROM Traits_variation WHERE PlantaID=linkid AND TraitID=tid;
END IF;
IF (resultado=0) THEN
SET resultado=1;
END IF;
RETURN resultado;
END
