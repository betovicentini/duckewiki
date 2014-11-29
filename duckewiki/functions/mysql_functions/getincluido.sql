CREATE FUNCTION getincluido(especid INT(10), monoid INT(10)) RETURNS INT(1)
BEGIN
DECLARE parid  INT(10) DEFAULT 0;
SELECT Incluido INTO parid FROM MonografiaEspecs WHERE MonografiaID=monoid AND EspecimenID=especid;
RETURN parid;
END
