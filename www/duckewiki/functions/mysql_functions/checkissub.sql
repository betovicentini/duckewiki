CREATE FUNCTION checkissub(gazid INT(10),parid INT(10)) RETURNS INT
BEGIN
DECLARE gazrunid INT DEFAULT 0;
DECLARE gazparid INT DEFAULT 0;
DECLARE resultado INT DEFAULT 0;
IF (gazid>0 AND parid>0) THEN
	SET gazrunid = gazid;
	myloop: WHILE (gazrunid>0) 
	DO
	 SELECT  gs.ParentID INTO gazparid FROM Gazetteer as gs WHERE gs.GazetteerID=gazrunid;
	 IF (gazparid=0) THEN
		LEAVE myloop;
	 ELSE 
		IF (gazparid=parid) THEN
			SET resultado = 1;
			LEAVE myloop;
		END IF;
	 END IF;
	 SET gazrunid=gazparid;
	END WHILE;
END IF;
RETURN resultado;
END
