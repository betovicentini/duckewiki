CREATE FUNCTION traitvalue (tvariation varchar(500),ttid int(10), cutrid INT(10)) RETURNS varchar(500) CHARSET utf8
BEGIN
DECLARE statesids INT DEFAULT 0;
DECLARE ncatstep INT DEFAULT 1;
DECLARE ncat INT DEFAULT 0;
DECLARE statename CHAR(255) DEFAULT '';
DECLARE statevariation CHAR(255) DEFAULT '';
DECLARE ttipo CHAR(100) DEFAULT '';
DECLARE resultado VARCHAR(500)  DEFAULT NULL;
IF (cutrid=ttid) THEN
	SELECT tr.TraitTipo INTO ttipo FROM Traits as tr WHERE tr.TraitID=ttid;
	IF (ttipo='Variavel|Categoria') THEN
		SELECT substrCount(tvariation,';')+1 INTO ncat;
		WHILE ncatstep <= ncat DO
			SET statename = '';
			SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO statesids;
			SELECT TraitName INTO statename FROM Traits WHERE TraitID=statesids;
			IF (ncatstep=1) THEN
				SET statevariation = statename;
			ELSE
				SET statevariation = CONCAT(statevariation,'; ',statename);
			END IF;
			SET ncatstep = ncatstep+1;
		END WHILE;
		SET resultado = statevariation;
	ELSE
		SET resultado = tvariation;
	END IF;
END IF;
RETURN resultado;
END
