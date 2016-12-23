CREATE FUNCTION isdetvalid(thedetid INT(10), famid INT(10), genid INT(10), spid  INT(10), infspid INT(10) ) RETURNS INT(10)
BEGIN
DECLARE resultado INT(10) DEFAULT 0;
if (Infspid>0)  THEN
SELECT COUNT(*) INTO resultado FROM Identidade WHERE DetID=thedetid AND InfraEspecieID=infspid;
ELSE
	if (spid>0)  THEN
	SELECT COUNT(*) INTO resultado FROM Identidade WHERE DetID=thedetid AND EspecieID=spid;
	ELSE
		if (genid>0)  THEN
		SELECT COUNT(*) INTO resultado FROM Identidade WHERE DetID=thedetid AND GeneroID=genid;
		ELSE
				if (famid>0)  THEN
				SELECT COUNT(*) INTO resultado FROM Identidade WHERE DetID=thedetid AND FamiliaID=famid;
				END IF;
		END IF;
	END IF;
END IF;
RETURN resultado;
END
