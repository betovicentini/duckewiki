CREATE FUNCTION checkunlinkedimgs(imgid int(10)) RETURNS int
BEGIN
DECLARE nlinks INT(10) DEFAULT 0;
DECLARE linked INT(10) DEFAULT 0;
DECLARE nlinkshab INT(10) DEFAULT 0;
SELECT count(*) INTO nlinks FROM Traits_variation JOIN Traits as tt USING(TraitID) WHERE tt.TraitTipo LIKE '%imag%' AND (TraitVariation LIKE imgid OR TraitVariation LIKE CONCAT('%;',imgid) OR TraitVariation LIKE CONCAT('%',imgid,';%') OR TraitVariation=imgid);
IF (nlinks>0) THEN
SET linked = 1;
ELSE
SELECT count(*) INTO nlinkshab FROM Habitat_Variation JOIN Traits as tt USING(TraitID) WHERE tt.TraitTipo LIKE '%imag%' AND (HabitatVariation LIKE imgid OR HabitatVariation LIKE CONCAT('%;',imgid) OR HabitatVariation LIKE CONCAT('%',imgid,';%') OR HabitatVariation=imgid);
IF (nlinkshab>0) THEN
SET linked = 1;
ELSE
SET linked = 0;
END IF;
END IF;
RETURN linked;
END
