CREATE FUNCTION traitvariation_forgrid(ttid int(10), specid int(10), trunit BOOLEAN, specimen BOOLEAN) RETURNS  VARCHAR(500) CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE respar CHAR(255) DEFAULT '';
DECLARE contador INT DEFAULT 0;
DECLARE tvariation CHAR(255) DEFAULT '';
DECLARE tname CHAR(100) DEFAULT '';
DECLARE tunit CHAR(50)  DEFAULT '';
DECLARE clname CHAR(100) DEFAULT '';
DECLARE toprint CHAR(255) DEFAULT '';
DECLARE ttipo  CHAR(100)  DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statename  CHAR(100)  DEFAULT '';
DECLARE statevariation VARCHAR(500)  DEFAULT '';
DECLARE statevar FLOAT DEFAULT 0;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE trtid INT(10) DEFAULT 0;
DECLARE cur1 CURSOR FOR SELECT TraitVariation,Traits_variation.TraitUnit,Traits.TraitTipo FROM Traits_variation JOIN Traits USING(TraitID) WHERE TraitID=ttid AND EspecimenID=specid;
DECLARE cur2 CURSOR FOR SELECT TraitVariation,Traits_variation.TraitUnit,Traits.TraitTipo FROM Traits_variation JOIN Traits USING(TraitID) WHERE TraitID=ttid AND PlantaID=specid;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;
IF specimen=1 THEN
	OPEN cur1;
ELSE 
	OPEN cur2;
END IF;
loop1: LOOP
IF specimen=1 THEN
	FETCH cur1 INTO tvariation,tunit,ttipo ;
ELSE 
	FETCH cur2 INTO tvariation,tunit,ttipo ;
END IF;
IF done=1 THEN
IF specimen=1 THEN
	CLOSE cur1;
ELSE 
	CLOSE cur2;
END IF;
LEAVE loop1;
END IF;
IF ttipo='Variavel|Categoria' THEN
SELECT substrCount(tvariation,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET statename = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO trtid;
SELECT TraitName INTO statename FROM Traits WHERE TraitID=trtid;
SET statename = lower(statename);
IF (ncatstep=1) THEN
SET statevariation = statename;
ELSE
SET statevariation = CONCAT(statevariation,',',statename);
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
END IF;
IF ttipo='Variavel|Quantitativo' THEN
SET statevariation = tvariation;
END IF;
IF ttipo='Variavel|Texto' THEN
SET statevariation = tvariation;
END IF;
IF trunit THEN
SET toprint= tunit;
ELSE
SET toprint = statevariation;
END IF;
END LOOP loop1;
RETURN toprint;
END