CREATE FUNCTION countfert(trfert INT(10),famid INT(10),genid INT(10),specid INT(10),infspid INT(10)) RETURNS INT(10)
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE nfertspp INT DEFAULT 0;
DECLARE nfertpll INT DEFAULT 0;
DECLARE nfertpllmoni INT DEFAULT 0;
DECLARE res INT DEFAULT 0;
IF (trfert>0) THEN
	IF (infspid>0) THEN
		SELECT count(*) INTO nfertspp FROM Traits_variation JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.InfraEspecieID=infspid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert);
		SELECT count(*) INTO nfertpll FROM Traits_variation JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.InfraEspecieID=infspid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert) AND (Traits_variation.EspecimenID+0)=0;
		SELECT count(*) INTO nfertpllmoni FROM Monitoramento JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.InfraEspecieID=infspid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert);
	ELSE
		IF (specid>0) THEN
			SELECT count(*) INTO nfertspp FROM Traits_variation JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.EspecieID=specid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert);
			SELECT count(*) INTO nfertpll FROM Traits_variation JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.EspecieID=specid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert) AND (Traits_variation.EspecimenID+0)=0;
			SELECT count(*) INTO nfertpllmoni FROM Monitoramento JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.EspecieID=specid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert);
		ELSE 
			IF (genid>0) THEN
				SELECT count(*) INTO nfertspp FROM Traits_variation JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.GeneroID=genid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert);
				SELECT count(*) INTO nfertpll FROM Traits_variation JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.GeneroID=genid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert) AND (Traits_variation.EspecimenID+0)=0;
				SELECT count(*) INTO nfertpllmoni FROM Monitoramento JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.GeneroID=genid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert);
			ELSE
				IF (famid>0) THEN
					SELECT count(*) INTO nfertspp FROM Traits_variation JOIN Especimenes as spp USING(EspecimenID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE idd.FamiliaID=famid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert);
					SELECT count(*) INTO nfertpll FROM Traits_variation JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.FamiliaID=famid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert) AND (Traits_variation.EspecimenID+0)=0;
					SELECT count(*) INTO nfertpllmoni FROM Monitoramento JOIN Plantas as spp USING(PlantaID) JOIN Identidade as idd ON spp.DetID=idd.DetID WHERE  idd.FamiliaID=famid AND (TraitVariation LIKE CONCAT('%;',trfert)  OR TraitVariation LIKE CONCAT('%;',trfert,';%') OR TraitVariation LIKE CONCAT(trfert,';%') OR TraitVariation=trfert);
				END IF;
			END IF;
		END IF;
	END IF;
SET res = res+nfertspp+nfertpll+nfertpllmoni;
END IF;
RETURN res;
END
