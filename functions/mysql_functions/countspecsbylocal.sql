CREATE FUNCTION countspecsbylocal(theid INT(10), theref CHAR(100)) RETURNS INT
BEGIN
DECLARE nspecs INT DEFAULT 0;
SELECT COUNT(DISTINCT pltbbs.EspecimenID) INTO nspecs FROM Especimenes as pltbbs WHERE isvalidlocal(pltbbs.GazetteerID, pltbbs.GPSPointID, theid, theref)>0;
RETURN nspecs;
END
