CREATE FUNCTION countcensosbylocal(gazid INT(10),dapid INT(10), mindap DOUBLE) RETURNS INT
BEGIN
DECLARE ncensos INT(10) DEFAULT 0;
IF gazid>0 AND dapid>0 THEN
	IF mindap>0 THEN
		SELECT MAX(newtb.NDAPSperTREE) INTO ncensos FROM (SELECT COUNT(*) as NDAPSperTREE FROM Monitoramento as moni JOIN Plantas as pl ON pl.PlantaID=moni.PlantaID JOIN Gazetteer AS gaz ON gaz.GazetteerID=pl.GazetteerID LEFT JOIN Gazetteer as par ON par.GazetteerID=gaz.ParentID WHERE (par.GazetteerID=gazid OR gaz.GazetteerID=gazid) AND moni.TraitID=dapid  GROUP BY pl.PlantaTag) as newtb; 
	ELSE 
		SELECT MAX(newtb.NDAPSperTREE) INTO ncensos FROM (SELECT COUNT(*) as NDAPSperTREE FROM Monitoramento as moni JOIN Plantas as pl ON pl.PlantaID=moni.PlantaID JOIN Gazetteer AS gaz ON gaz.GazetteerID=pl.GazetteerID LEFT JOIN Gazetteer as par ON par.GazetteerID=gaz.ParentID WHERE (par.GazetteerID=gazid OR gaz.GazetteerID=gazid) AND moni.TraitID=dapid  AND moni.TraitVariation>=mindap GROUP BY pl.PlantaTag) as newtb; 
	END IF;
END IF;
RETURN ncensos;
END
