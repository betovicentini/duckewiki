CREATE FUNCTION checkdetbylocal(idetid INT(10),theid INT(10), theref CHAR(20)) RETURNS INT
BEGIN
DECLARE isvalid INT DEFAULT 0;
DECLARE isvalidpl INT DEFAULT 0;
DECLARE isvalidsp INT DEFAULT 0;
SELECT isvalidlocal(pltb.GazetteerID, pltb.GPSPointID, theid, theref) into isvalidpl FROM Plantas AS pltb WHERE pltb.DetID=idetid LIMIT 0,1;
SELECT isvalidlocal(spec.GazetteerID, spec.GPSPointID, theid, theref) into isvalidsp FROM Especimenes AS spec WHERE spec.DetID=idetid LIMIT 0,1;
SET isvalid= isvalidpl+isvalidsp;
RETURN isvalid;
END
