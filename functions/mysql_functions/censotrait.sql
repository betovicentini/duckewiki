CREATE FUNCTION censotrait(ttid int(10), linkid INT(10), census int(10), tobsdat BOOLEAN, trunit BOOLEAN) RETURNS text CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE resultado TEXT DEFAULT '';
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
SELECT mm.TraitVariation,mm.TraitUnit,mm.DataObs,trr.TraitTipo INTO tvariation,tunit,obsdate,ttipo  FROM Monitoramento AS mm JOIN Traits AS trr USING(TraitID) WHERE mm.TraitID=ttid AND mm.PlantaID=linkid AND mm.CensoID=census ORDER BY mm.DataObs DESC LIMIT 0,1;
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
SET resultado = toprint;
RETURN resultado;
END
