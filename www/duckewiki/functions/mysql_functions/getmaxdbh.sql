CREATE FUNCTION getmaxdbh(ttid int(10), pltid int(10)) RETURNS FLOAT
BEGIN
DECLARE tmvariation CHAR(100) DEFAULT '';
DECLARE tmunit CHAR(50) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE statemax FLOAT DEFAULT 0;
SELECT TraitVariation,Monitoramento.TraitUnit INTO tmvariation,tmunit FROM Monitoramento JOIN Traits USING(TraitID) WHERE Monitoramento.TraitID=ttid AND Monitoramento.PlantaID=pltid ORDER BY Monitoramento.DataObs DESC LIMIT 0,1;
IF (tmvariation<>'') THEN
	SELECT substrCount(tmvariation,';')+1 INTO ncat;
	WHILE ncatstep <= ncat DO
			SET statevar2 = 0;
			SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tmvariation,';',ncatstep),';',-1)) INTO statevar2;
			IF (tmunit='cm') THEN
				SET statevar2 = statevar2*10;
			END IF; 
			IF (ncatstep=1) THEN
				SET statemax = statevar2;
			ELSE
				IF (statevar2>statemax) THEN
					SET statemax = statevar2;
				END IF;
			END IF;
			SET ncatstep = ncatstep+1;
	END WHILE;
	SET statemax = ROUND(statemax,2);
END IF;
RETURN statemax;
END
