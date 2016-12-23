CREATE FUNCTION getdbhstring(ttid int(10), pltid int(10)) RETURNS CHAR(100) CHARSET utf8
BEGIN
DECLARE tmvariation CHAR(100) DEFAULT "";
SELECT TraitVariation INTO tmvariation FROM Monitoramento JOIN Traits USING(TraitID) WHERE Monitoramento.TraitID=ttid AND Monitoramento.PlantaID=pltid ORDER BY Monitoramento.DataObs DESC LIMIT 0,1;
RETURN tmvariation;
END
