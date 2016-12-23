CREATE FUNCTION pegacategorias(variacao VARCHAR(255)) RETURNS VARCHAR(500) CHARSET utf8
BEGIN
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statevariation VARCHAR(500) DEFAULT '';
DECLARE statename CHAR(100) DEFAULT '';
DECLARE tvariation CHAR(100) DEFAULT '';
DECLARE trtid INT(10) DEFAULT 0;


SELECT substrCount(variacao,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
	SET statename = '';
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(variacao,';',ncatstep),';',-1)) INTO trtid;
	SELECT TraitName INTO statename FROM Traits WHERE TraitID=trtid;
	SET statename = lower(statename);
	IF (ncatstep=1) THEN
		SET statevariation = statename;
	ELSE
		SET statevariation = CONCAT(statevariation,'; ',statename);
	END IF;
	SET ncatstep = ncatstep+1;
END WHILE;
RETURN statevariation;
END
