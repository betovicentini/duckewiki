CREATE FUNCTION checkspecsilica(specid INT(10), partrid INT(10)) RETURNS INT
BEGIN
DECLARE trid INT DEFAULT 0;
DECLARE traitvarpl INT DEFAULT 0;
DECLARE traitvarmoni INT DEFAULT 0;
DECLARE res INT DEFAULT 0;
SELECT TraitID INTO trid FROM Traits WHERE ParentID=partrid AND LOWER(TraitName) LIKE '%silica%';
SELECT COUNT(*) INTO res FROM Traits_variation WHERE TraitID=partrid AND EspecimenID=specid AND (TraitVariation LIKE CONCAT(trid,';%')  OR TraitVariation LIKE CONCAT('%;',trid,';%') OR TraitVariation LIKE CONCAT('%;',trid));
IF res=0 THEN
 SET res = NULL;
END IF;
RETURN res;
END
