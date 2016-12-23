CREATE FUNCTION checkoleo(specid INT(10), pltid INT(10), trid INT(10), txt CHAR(20)) RETURNS INT(10)
BEGIN
DECLARE oleoconta INT(10) DEFAULT 0;
DECLARE catgid INT(10) DEFAULT 0;
SELECT TraitID INTO catgid FROM Traits WHERE ParentID=trid AND LOWER(TraitName) LIKE  CONCAT('%',txt,'%');
IF (specid>0 AND pltid>0) THEN
	SELECT COUNT(*) INTO oleoconta FROM Traits_variation WHERE TraitID=trid AND (EspecimenID=specid  OR PlantaID=pltid) AND (TraitVariation LIKE CONCAT(catgid,'%')  OR TraitVariation LIKE  CONCAT('%;',catgid,'%' ));
ELSE 
	IF (specid>0) THEN
		SELECT COUNT(*) INTO oleoconta FROM Traits_variation WHERE TraitID=trid AND EspecimenID=specid AND (TraitVariation LIKE CONCAT(catgid,'%')  OR TraitVariation LIKE  CONCAT('%;',catgid,'%' ));
	ELSE
		IF (pltid>0) THEN
			SELECT COUNT(*) INTO oleoconta FROM Traits_variation WHERE TraitID=trid AND PlantaID=pltid AND (TraitVariation LIKE CONCAT(catgid,'%')  OR TraitVariation LIKE  CONCAT('%;',catgid,'%' ));
		END IF;
	END IF;
END IF;
RETURN oleoconta;
END

