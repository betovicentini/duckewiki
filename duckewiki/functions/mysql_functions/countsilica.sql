CREATE FUNCTION countsilica(silicatrait INT(10),famid INT(10),genid INT(10),specid INT(10),infspid INT(10)) RETURNS INT(10)
BEGIN
DECLARE nsilicaspp INT DEFAULT 0;
DECLARE nsilicapll INT DEFAULT 0;
DECLARE nsilicapllmoni INT DEFAULT 0;
DECLARE trsilica INT DEFAULT 0;
DECLARE trsearch1 CHAR(50) DEFAULT '';
DECLARE trsearch2 CHAR(50) DEFAULT '';
DECLARE trsearch3 CHAR(50) DEFAULT '';
DECLARE res INT DEFAULT 0;
IF (silicatrait>0) THEN
SELECT TraitID INTO trsilica FROM Traits WHERE ParentID=silicatrait AND UPPER(TraitName) LIKE '%SILICA%';
IF (trsilica>0) THEN
SET trsearch1 = CONCAT('%;',trsilica);
SET trsearch2 = CONCAT('%;',trsilica,';%');
SET trsearch3 = CONCAT(trsilica,';%');
IF (infspid>0) THEN
SELECT count(*) INTO nsilicaspp FROM Traits_variation JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.InfraEspecieID=infspid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
SELECT count(*) INTO nsilicapll FROM Traits_variation JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.InfraEspecieID=infspid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
SELECT count(*) INTO nsilicapllmoni FROM Monitoramento JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.InfraEspecieID=infspid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
ELSE
IF (specid>0) THEN
SELECT count(*) INTO nsilicaspp FROM Traits_variation JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.EspecieID=specid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
SELECT count(*) INTO nsilicapll FROM Traits_variation JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.EspecieID=specid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
SELECT count(*) INTO nsilicapllmoni FROM Monitoramento JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.EspecieID=specid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
ELSE 
IF (genid>0) THEN
SELECT count(*) INTO nsilicaspp FROM Traits_variation JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.GeneroID=genid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
SELECT count(*) INTO nsilicapll FROM Traits_variation JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.GeneroID=genid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
SELECT count(*) INTO nsilicapllmoni FROM Monitoramento JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.GeneroID=genid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
ELSE
IF (famid>0) THEN
SELECT count(*) INTO nsilicaspp FROM Traits_variation JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.FamiliaID=famid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
SELECT count(*) INTO nsilicapll FROM Traits_variation JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.FamiliaID=famid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
SELECT count(*) INTO nsilicapllmoni FROM Monitoramento JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.FamiliaID=famid AND (TraitVariation LIKE trsearch1  OR TraitVariation LIKE trsearch2 OR TraitVariation LIKE trsearch3 OR TraitVariation=trsilica);
END IF;
END IF;
END IF;
END IF;
END IF;
END IF;
SET res = nsilicaspp+nsilicapll+nsilicapllmoni;
RETURN res;
END
