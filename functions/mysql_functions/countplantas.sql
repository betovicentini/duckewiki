CREATE FUNCTION countplantas(idd INT(10),nmid CHAR(50)) RETURNS INT
BEGIN
DECLARE nspecs INT(10) DEFAULT 0;
DECLARE famid INT(10) DEFAULT 0;
DECLARE genid INT(10) DEFAULT 0;
DECLARE specid INT(10) DEFAULT 0;
DECLARE infspid INT(10) DEFAULT 0;
DECLARE nometxt CHAR(10) DEFAULT '';
SELECT SUBSTRING(nmid,1,5) INTO nometxt;
IF nometxt='famid' THEN    
	SELECT COUNT(*) INTO nspecs FROM Plantas JOIN Identidade USING(DetID) WHERE FamiliaID=idd;
END IF;
IF nometxt='genus' THEN    
	SELECT COUNT(*) INTO nspecs FROM Plantas JOIN Identidade USING(DetID) WHERE GeneroID=idd;
END IF;
IF nometxt='speci' THEN    
	SELECT COUNT(*) INTO nspecs FROM Plantas JOIN Identidade USING(DetID) WHERE EspecieID=idd;
END IF;
IF nometxt='infsp' THEN    
	SELECT COUNT(*) INTO nspecs FROM Plantas JOIN Identidade USING(DetID) WHERE InfraEspecieID=idd;
END IF;
RETURN nspecs;
END
