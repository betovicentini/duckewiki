CREATE FUNCTION checkusertraitpermit(uuid int(10), trid int(10)) RETURNS INT(1)
BEGIN
DECLARE traitvar INT(10) DEFAULT 0;
DECLARE traitvar2 INT(10) DEFAULT 0;
SELECT COUNT(*) INTO traitvar FROM Traits_variation WHERE TraitID=trid AND AddedBy=uuid;
SELECT COUNT(*) INTO traitvar2 FROM Traits_variation WHERE TraitID=trid AND AddedBy<>uuid;
IF (traitvar=traitvar2 OR (traitvar2=0 AND traitvar>0)) THEN
	SELECT COUNT(*) INTO traitvar FROM Monitoramento WHERE TraitID=trid AND AddedBy=uuid;
	SELECT COUNT(*) INTO traitvar2 FROM Monitoramento WHERE TraitID=trid AND AddedBy<>uuid;
	IF (traitvar=traitvar2 OR (traitvar2=0 AND traitvar>0)) THEN
		SELECT COUNT(*) INTO traitvar FROM Habitat_Variation WHERE TraitID=trid AND AddedBy=uuid;
		SELECT COUNT(*) INTO traitvar2 FROM Habitat_Variation WHERE TraitID=trid AND AddedBy<>uuid;
	END IF;
END IF;
IF (traitvar=traitvar2 OR (traitvar2=0 AND traitvar>0)) THEN
	RETURN 1;
ELSE 
	RETURN 0;
END IF;
END
