CREATE FUNCTION traitvariation_monitoramento(ttid int(10), linktype char(30), linkid INT(10), trunit BOOLEAN, census int(10), tobsdat BOOLEAN) RETURNS text CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE respar TEXT DEFAULT '';
DECLARE resultado TEXT DEFAULT '';
DECLARE lixo TEXT DEFAULT '';
DECLARE contador INT DEFAULT 0;
DECLARE tvariation TEXT DEFAULT '';
DECLARE tname TEXT DEFAULT '';
DECLARE tunit TEXT DEFAULT '';
DECLARE ttipo TEXT DEFAULT '';
DECLARE toprint TEXT DEFAULT '';
DECLARE statename TEXT DEFAULT '';
DECLARE statevariation TEXT DEFAULT '';
DECLARE ncatstep INT DEFAULT 1;
DECLARE ncat INT DEFAULT 0;
DECLARE trtid INT DEFAULT 0;
DECLARE obsdate DATE DEFAULT 0;
DECLARE cur1 CURSOR FOR SELECT mm.TraitVariation,mm.TraitUnit,mm.DataObs,trr.TraitTipo FROM Monitoramento AS mm JOIN Traits AS trr USING(TraitID) WHERE mm.TraitID=ttid AND mm.PlantaID=linkid ORDER BY mm.DataObs;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
OPEN cur1;
loop1: LOOP
FETCH cur1 INTO tvariation,tunit,obsdate,ttipo;
IF done=1 THEN
CLOSE cur1;
LEAVE loop1;
END IF;
IF trunit THEN
SET toprint= tunit;
ELSE
IF tobsdat THEN
SET toprint = obsdate;
ELSE
IF (ttipo='Variavel|Categoria') THEN
SELECT substrCount(tvariation,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET statename = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO trtid;
SELECT TraitName INTO statename FROM Traits WHERE TraitID=trtid;
IF (ncatstep=1) THEN
SET statevariation = statename;
ELSE
SET statevariation = CONCAT(statevariation,'; ',statename);
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
SET toprint = statevariation;
ELSE
SET toprint = tvariation;
END IF;
END IF;
END IF;
IF contador=census THEN
SET resultado = toprint;
END IF;
SET contador=contador+1;
END LOOP loop1;
RETURN resultado;
END
