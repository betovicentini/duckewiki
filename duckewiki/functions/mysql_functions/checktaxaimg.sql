CREATE FUNCTION checktaxaimg(famid INT(10),genid INT(10),specid INT(10),infspid INT(10), trid INT(10)) RETURNS INT
BEGIN
DECLARE traitvar INT DEFAULT 0;
DECLARE traitvarpl INT DEFAULT 0;
DECLARE traitvarmoni INT DEFAULT 0;
DECLARE res INT DEFAULT 0;
IF (trid=0) THEN
IF (infspid>0) THEN
SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.InfraEspecieID=infspid;
SELECT count(*) INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.InfraEspecieID=infspid;
SELECT count(*) INTO traitvarmoni FROM Monitoramento JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.InfraEspecieID=infspid;
ELSE
IF (specid>0) THEN
SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.EspecieID=specid;
SELECT count(*) INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.EspecieID=specid;
SELECT count(*) INTO traitvarmoni FROM Monitoramento JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.EspecieID=specid;
ELSE 
IF (genid>0) THEN
SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.GeneroID=genid;
SELECT count(*) INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.GeneroID=genid;
SELECT count(*) INTO traitvarmoni FROM Monitoramento JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.GeneroID=genid;
ELSE
IF (famid>0) THEN
SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.FamiliaID=famid;
SELECT count(*) INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.FamiliaID=famid;
SELECT count(*) INTO traitvarmoni FROM Monitoramento JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitTipo LIKE '%Imag%' AND idd.FamiliaID=famid;
END IF;
END IF;
END IF;
END IF;
ELSE
IF (infspid>0) THEN
SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid AND idd.InfraEspecieID=infspid;
SELECT count(*) INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid  AND idd.InfraEspecieID=infspid;
SELECT count(*) INTO traitvarmoni FROM Monitoramento JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid  AND idd.InfraEspecieID=infspid;
ELSE
IF (specid>0) THEN
SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid AND idd.EspecieID=specid;
SELECT count(*) INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid AND idd.EspecieID=specid;
SELECT count(*) INTO traitvarmoni FROM Monitoramento JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid  AND idd.EspecieID=specid;
ELSE 
IF (genid>0) THEN
SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid  AND idd.GeneroID=genid;
SELECT count(*) INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid AND idd.GeneroID=genid;
SELECT count(*) INTO traitvarmoni FROM Monitoramento JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid  AND idd.GeneroID=genid;
ELSE
IF (famid>0) THEN
SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid  AND idd.FamiliaID=famid;
SELECT count(*) INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid  AND idd.FamiliaID=famid;
SELECT count(*) INTO traitvarmoni FROM Monitoramento JOIN Traits USING(TraitID) JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE Traits.TraitID=trid AND idd.FamiliaID=famid;
END IF;
END IF;
END IF;
END IF;
END IF;
SET res = traitvar+traitvarpl+traitvarmoni;
RETURN res;
END
