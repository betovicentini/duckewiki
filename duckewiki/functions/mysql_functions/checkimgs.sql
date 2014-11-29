CREATE FUNCTION checkimgs(specid INT(10), pltid INT(10)) RETURNS INT
BEGIN
DECLARE traitvar INT DEFAULT 0;
DECLARE traitvarpl INT DEFAULT 0;
DECLARE res INT DEFAULT 0;
IF (specid>0) THEN
	SELECT count(*) INTO traitvar FROM Traits_variation JOIN Traits USING(TraitID) WHERE Traits.TraitTipo LIKE '%Imag%' AND Traits_variation.EspecimenID=specid LIMIT 0,1;
END IF;
IF (pltid>0) THEN
	SELECT count(*)  INTO traitvarpl FROM Traits_variation JOIN Traits USING(TraitID) WHERE Traits.TraitTipo LIKE '%Imag%' AND Traits_variation.PlantaID=pltid LIMIT 0,1;
END IF;
SET res = traitvar+traitvarpl;
RETURN res;
END
