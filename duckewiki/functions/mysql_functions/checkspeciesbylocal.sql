CREATE FUNCTION checkspeciesbylocal(theid INT(10), theref CHAR(20)) RETURNS INT
BEGIN
DECLARE nspecs INT DEFAULT 0;
SELECT SUM(tt.nn)  INTO nspecs FROM (SELECT 1 as nn FROM Identidade as iddet  LEFT JOIN Especimenes as especs ON especs.DetID=iddet.DetID   LEFT JOIN Plantas as pltbbs ON iddet.DetID=pltbbs.DetID WHERE (isvalidlocal(especs.GazetteerID, especs.GPSPointID, theid, theref)>0 OR isvalidlocal(pltbbs.GazetteerID, pltbbs.GPSPointID, theid, theref)>0)  AND iddet.EspecieID>0 GROUP BY iddet.FamiliaID,iddet.GeneroID,iddet.EspecieID) AS tt;
RETURN nspecs;
END
