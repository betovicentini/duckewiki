CREATE FUNCTION getiftreeplots(gazid INT(10)) RETURNS INT
BEGIN
DECLARE eplot FLOAT DEFAULT 0;
SELECT DimX+DimY INTO eplot FROM Gazetteer WHERE GazetteerID=gazid;
IF eplot>0 THEN
RETURN gazid;
ELSE 
RETURN NULL;
END IF;
END
