CREATE FUNCTION checktrait(specid INT(10), pltid INT(10), trid int(10)) RETURNS CHAR(20)
BEGIN
DECLARE traitvar TEXT DEFAULT '';
DECLARE traitvarpl TEXT DEFAULT '';
IF (specid>0) THEN
	SELECT TraitVariation INTO traitvar FROM Traits_variation WHERE TraitID=trid AND EspecimenID=specid LIMIT 0,1;
END IF;
IF (pltid>0) THEN
	SELECT TraitVariation INTO traitvarpl FROM Traits_variation WHERE TraitID=trid AND PlantaID=pltid LIMIT 0,1;
END IF;
IF (traitvar<>'' OR traitvarpl<>'')  THEN
RETURN 'OK';
ELSE 
RETURN 'MISSING';
END IF;
END
