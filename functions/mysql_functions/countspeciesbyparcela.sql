CREATE FUNCTION countspeciesbyparcela(gazid INT(10), unico BOOLEAN) RETURNS INT
BEGIN
DECLARE ntrees INT(10) DEFAULT 0;
IF gazid>0  THEN
	IF unico=0 THEN
		SELECT COUNT(*) INTO ntrees FROM (SELECT DISTINCT GeneroID,EspecieID,InfraEspecieID FROM Plantas as pl LEFT JOIN Identidade as idd ON idd.DetID=pl.DetID LEFT JOIN Gazetteer AS gaz ON gaz.GazetteerID=pl.GazetteerID LEFT JOIN Gazetteer as par ON par.GazetteerID=gaz.ParentID WHERE (par.GazetteerID=gazid OR gaz.GazetteerID=gazid) AND idd.EspecieID>0) as newtb;
	ELSE 
		CREATE TEMPORARY TABLE teste (SELECT DISTINCT GeneroID,EspecieID,InfraEspecieID FROM Plantas as pl LEFT JOIN Identidade as idd ON idd.DetID=pl.DetID LEFT JOIN Gazetteer AS gaz ON gaz.GazetteerID=pl.GazetteerID LEFT JOIN Gazetteer as par ON par.GazetteerID=gaz.ParentID WHERE (par.GazetteerID=gazid OR gaz.GazetteerID=gazid) AND idd.EspecieID>0) ;
	CREATE TEMPORARY TABLE teste2 (SELECT DISTINCT GeneroID,EspecieID,InfraEspecieID FROM Plantas as pl LEFT JOIN Identidade as idd ON idd.DetID=pl.DetID LEFT JOIN Gazetteer AS gaz ON gaz.GazetteerID=pl.GazetteerID LEFT JOIN Gazetteer as par ON par.GazetteerID=gaz.ParentID WHERE (par.GazetteerID<>gazid OR gaz.GazetteerID<>gazid) AND idd.EspecieID>0) ;
SELECT COUNT(*) INTO ntrees FROM (SELECT teste.EspecieID,teste.InfraEspecieID,teste2.InfraEspecieID AS infspp,teste2.EspecieID as spp FROM teste LEFT JOIN teste2 ON teste.EspecieID=teste2.EspecieID) AS newtb WHERE (newtb.EspecieID>0 AND newtb.spp=0) OR (newtb.InfraEspecieID>0 AND newtb.infspp=0) ;
	DROP TEMPORARY TABLE teste;
	DROP TEMPORARY TABLE teste2;
	END IF;
END IF;
RETURN ntrees;
END

