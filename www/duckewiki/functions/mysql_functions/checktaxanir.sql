CREATE FUNCTION checktaxanir(famid INT(10),genid INT(10),specid INT(10),infspid INT(10)) RETURNS INT(10)
BEGIN
DECLARE nnirspp INT DEFAULT 0;
DECLARE nnirpll INT DEFAULT 0;
DECLARE res INT DEFAULT 0;
IF (infspid>0) THEN
SELECT count(*) INTO nnirspp FROM NirSpectra JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.InfraEspecieID=infspid;
SELECT count(*) INTO nnirpll FROM NirSpectra JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.InfraEspecieID=infspid;
ELSE
IF (specid>0) THEN
SELECT count(*) INTO nnirspp FROM NirSpectra JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.EspecieID=specid;
SELECT count(*) INTO nnirpll FROM NirSpectra JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.EspecieID=specid;
ELSE 
IF (genid>0) THEN
SELECT count(*) INTO nnirspp FROM NirSpectra JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.GeneroID=genid;
SELECT count(*) INTO nnirpll FROM NirSpectra JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.GeneroID=genid;
ELSE
IF (famid>0) THEN
SELECT count(*) INTO nnirspp FROM NirSpectra JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.FamiliaID=famid;
SELECT count(*) INTO nnirpll FROM NirSpectra JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.FamiliaID=famid;
END IF;
END IF;
END IF;
END IF;
SET res = nnirspp+nnirpll;
RETURN res;
END
