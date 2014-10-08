CREATE FUNCTION checklocalhabitat(infspecid INT(10), specid INT(10), genid INT(10), famid INT(10)) RETURNS INT
BEGIN
DECLARE nhab INT DEFAULT 0;
IF infspecid>0 THEN
SELECT COUNT(*) INTO nhab FROM (SELECT DISTINCT newtb.HabitatID FROM ((SELECT pltb.HabitatID FROM Plantas AS pltb JOIN Identidade as iddet USING(DetID) JOIN Habitat as hab ON hab.HabitatID=pltb.HabitatID WHERE iddet.InfraEspecieID=infspecid AND hab.HabitatTipo='local') UNION (SELECT pltbsp.HabitatID  FROM Especimenes as pltbsp JOIN Identidade as iddetsp USING(DetID) JOIN Habitat as habsp ON habsp.HabitatID=pltbsp.HabitatID WHERE iddetsp.InfraEspecieID=infspecid AND habsp.HabitatTipo='local')) AS newtb) AS lastb;
ELSE
	IF specid>0 THEN
	SELECT COUNT(*) INTO nhab FROM (SELECT DISTINCT newtb.HabitatID FROM ((SELECT pltb.HabitatID FROM Plantas AS pltb JOIN Identidade as iddet USING(DetID) JOIN Habitat as hab ON hab.HabitatID=pltb.HabitatID WHERE iddet.EspecieID=specid AND hab.HabitatTipo='local') UNION (SELECT pltb.HabitatID  FROM Especimenes as pltb JOIN Identidade as iddet USING(DetID) JOIN Habitat as hab ON hab.HabitatID=pltb.HabitatID WHERE iddet.EspecieID=specid AND hab.HabitatTipo='local')) AS newtb) AS lastb;
	ELSE
		IF genid>0 THEN
			SELECT COUNT(*) INTO nhab FROM (SELECT DISTINCT newtb.HabitatID FROM ((SELECT pltb.HabitatID FROM Plantas AS pltb JOIN Identidade as iddet USING(DetID) JOIN Habitat as hab ON hab.HabitatID=pltb.HabitatID WHERE iddet.GeneroID=genid AND hab.HabitatTipo='local') UNION (SELECT pltb.HabitatID  FROM Especimenes as pltb JOIN Identidade as iddet USING(DetID) JOIN Habitat as hab ON hab.HabitatID=pltb.HabitatID WHERE iddet.GeneroID=genid AND hab.HabitatTipo='local')) AS newtb) AS lastb;
		ELSE
			IF famid>0 THEN
			SELECT COUNT(*) INTO nhab FROM (SELECT DISTINCT newtb.HabitatID FROM ((SELECT pltb.HabitatID FROM Plantas AS pltb JOIN Identidade as iddet USING(DetID) JOIN Habitat as hab ON hab.HabitatID=pltb.HabitatID WHERE iddet.FamiliaID=famid AND hab.HabitatTipo='local') UNION (SELECT pltb.HabitatID  FROM Especimenes as pltb JOIN Identidade as iddet USING(DetID) JOIN Habitat as hab ON hab.HabitatID=pltb.HabitatID WHERE iddet.FamiliaID=famid AND hab.HabitatTipo='local')) AS newtb) AS lastb;
			END IF;
		END IF;
	END IF;
END IF;
RETURN nhab;
END
