CREATE FUNCTION checkformtaxastatus(fomnome CHAR(255), famid INT(10),genid INT(10),specid INT(10),infspid INT(10), minind INT(10)) RETURNS INT
BEGIN
DECLARE nexpec INT DEFAULT 0;
DECLARE ntramin INT DEFAULT 0;
DECLARE fomid INT DEFAULT 0;
DECLARE ntraits INT DEFAULT 0;
DECLARE res INT DEFAULT 0;
/*DEFINE EXPECTED */
SELECT COUNT(*), fof.FormID INTO ntraits,fomid  FROM FormulariosTraitsList AS ff LEFT JOIN Formularios as fof USING(FormID) WHERE UPPER(fof.FormName) LIKE UPPER(fomnome);
IF (fomid>0) THEN
/*COUNT EXSISTING */
IF (infspid>0) THEN
SELECT COUNT(*) INTO ntramin FROM (SELECT COUNT(*) as nvezes FROM Traits_variation as tv JOIN FormulariosTraitsList AS ff ON ff.TraitID=tv.TraitID LEFT JOIN Plantas as pl ON pl.PlantaID=tv.PlantaID LEFT JOIN Identidade as pldet ON pl.DetID=pldet.DetID LEFT JOIN Especimenes as spp ON spp.EspecimenID=tv.EspecimenID LEFT JOIN Identidade as idd ON idd.DetID=spp.DetID  WHERE (idd.InfraEspecieID=infspid OR pldet.InfraEspecieID=infspid) AND ff.FormID=fomid GROUP BY tv.TraitID) AS tt WHERE tt.nvezes>=minind;
ELSE
IF (specid>0) THEN
SELECT COUNT(*) INTO ntramin FROM (SELECT COUNT(*) as nvezes FROM Traits_variation as tv JOIN FormulariosTraitsList AS ff ON ff.TraitID=tv.TraitID LEFT JOIN Plantas as pl ON pl.PlantaID=tv.PlantaID LEFT JOIN Identidade as pldet ON pl.DetID=pldet.DetID LEFT JOIN Especimenes as spp ON spp.EspecimenID=tv.EspecimenID LEFT JOIN Identidade as idd ON idd.DetID=spp.DetID  WHERE (idd.EspecieID=specid OR pldet.EspecieID=specid) AND ff.FormID=fomid GROUP BY tv.TraitID) AS tt WHERE tt.nvezes>=minind;
ELSE 
IF (genid>0) THEN
SELECT COUNT(*) INTO ntramin FROM (SELECT COUNT(*) as nvezes FROM Traits_variation as tv JOIN FormulariosTraitsList AS ff ON ff.TraitID=tv.TraitID LEFT JOIN Plantas as pl ON pl.PlantaID=tv.PlantaID LEFT JOIN Identidade as pldet ON pl.DetID=pldet.DetID LEFT JOIN Especimenes as spp ON spp.EspecimenID=tv.EspecimenID LEFT JOIN Identidade as idd ON idd.DetID=spp.DetID  WHERE (idd.GeneroID=genid OR pldet.GeneroID=genid) AND ff.FormID=fomid GROUP BY tv.TraitID) AS tt WHERE tt.nvezes>=minind;
ELSE
IF (famid>0) THEN
SELECT COUNT(*) INTO ntramin FROM (SELECT COUNT(*) as nvezes FROM Traits_variation as tv JOIN FormulariosTraitsList AS ff ON ff.TraitID=tv.TraitID LEFT JOIN Plantas as pl ON pl.PlantaID=tv.PlantaID LEFT JOIN Identidade as pldet ON pl.DetID=pldet.DetID LEFT JOIN Especimenes as spp ON spp.EspecimenID=tv.EspecimenID LEFT JOIN Identidade as idd ON idd.DetID=spp.DetID  WHERE (idd.FamiliaID=famid OR pldet.FamiliaID=famid) AND ff.FormID=fomid GROUP BY tv.TraitID) AS tt WHERE tt.nvezes>=minind;
END IF;
END IF;
END IF;
END IF;
SELECT ROUND(ntramin/ntraits) INTO res;
END IF;
RETURN res;
END
