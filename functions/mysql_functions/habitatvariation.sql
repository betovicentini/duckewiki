CREATE FUNCTION habitatvariation(ttid int(10), hbid int(10), trunit BOOLEAN, somedia BOOLEAN) RETURNS text CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE respar TEXT DEFAULT '';
DECLARE resultado TEXT DEFAULT '';
DECLARE contador INT DEFAULT 0;
DECLARE tvariation TEXT DEFAULT '';
DECLARE tname TEXT DEFAULT '';
DECLARE tunit TEXT DEFAULT '';
DECLARE clname TEXT DEFAULT '';
DECLARE toprint TEXT DEFAULT '';
DECLARE ttipo TEXT DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statename TEXT DEFAULT '';
DECLARE statevariation TEXT DEFAULT '';
DECLARE statevar FLOAT DEFAULT 0;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE trtid INT(10) DEFAULT 0;
DECLARE cur1 CURSOR FOR SELECT HabitatVariation,Habitat_Variation.TraitUnit,Traits.TraitTipo FROM Habitat_Variation JOIN Traits USING(TraitID) WHERE TraitID=ttid AND HabitatID=hbid;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;
OPEN cur1;
loop1: LOOP
FETCH cur1 INTO tvariation,tunit,ttipo ;
IF done=1 THEN
CLOSE cur1;
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
SET statevariation = CONCAT(statevariation,'; ',statename);
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
END IF;
IF ttipo='Variavel|Quantitativo' THEN
SELECT substrCount(tvariation,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET statevar2 = 0;
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO statevar2;
IF (ncatstep=1) THEN
SET statevar = statevar2;
ELSE
SET statevar = statevar+statevar2;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
SET statevar = round(statevar/ncat,2);
IF (somedia) THEN
SET statevariation = statevar;
ELSE
SET statevariation = tvariation;
END IF;
END IF;
IF trunit THEN
SET toprint= tunit;
ELSE
SET toprint = statevariation;
END IF;
END LOOP loop1;
RETURN toprint;
END
