CREATE FUNCTION getparentname(parid int(10)) RETURNS text CHARSET utf8
BEGIN
DECLARE respar TEXT DEFAULT '';
IF (parid>0) THEN
SELECT LOWER(TraitName) INTO respar FROM Traits WHERE TraitID=parid;
END IF;
RETURN respar;
END