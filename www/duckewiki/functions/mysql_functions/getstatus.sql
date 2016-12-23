CREATE FUNCTION getstatus(ttid int(10), pltid int(10)) RETURNS CHAR(100) CHARSET utf8
BEGIN
DECLARE tmvariation CHAR(100) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statename CHAR(50) DEFAULT '';
DECLARE statevariation CHAR(100) DEFAULT '';
DECLARE trtid INT(10) DEFAULT 0;
DECLARE trtidtxt CHAR(100) DEFAULT '';
SELECT TraitVariation INTO tmvariation FROM Monitoramento JOIN Traits USING(TraitID) WHERE Monitoramento.TraitID=ttid AND Monitoramento.PlantaID=pltid ORDER BY Monitoramento.DataObs DESC LIMIT 0,1;
	SELECT substrCount(tmvariation,';')+1 INTO ncat;
	WHILE ncatstep <= ncat DO
		SET statename = '';
		SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tmvariation,';',ncatstep),';',-1)) INTO trtidtxt;
		SET trtid = trtidtxt+0;
		SELECT TraitName INTO statename FROM Traits WHERE TraitID=trtid;
		SET statename = lower(statename);
		IF (ncatstep=1) THEN
			SET statevariation = statename;
		ELSE
			SET statevariation = CONCAT(statevariation,'; ',statename);
		END IF;
		SET ncatstep = ncatstep+1;
	END WHILE;
IF (statevariation="")  THEN
SET statevariation = "Viva";
END IF;
RETURN statevariation;
END
