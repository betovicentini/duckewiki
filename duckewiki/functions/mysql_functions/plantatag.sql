CREATE FUNCTION plantatag(plid int(10)) RETURNS text CHARSET utf8
BEGIN
DECLARE resultado CHAR(255) DEFAULT '';
SELECT IF((InSituExSitu='' OR InSituExSitu IS NULL),PlantaTag,IF(InSituExSitu LIKE 'Insitu',CONCAT('JB-X-',PlantaTag),IF(InSituExSitu LIKE 'Exsitu',CONCAT('JB-N-',PlantaTag),''))) INTO resultado FROM Plantas WHERE PlantaID=plid;
RETURN resultado;
END
