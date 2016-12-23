CREATE FUNCTION mudaunidade(tvariation varchar(500),ttipo char(50),trunit char(50), tunit char(50)) RETURNS varchar(500) CHARSET UTF8
BEGIN
DECLARE ncatstep INT DEFAULT 1;
DECLARE ncat INT DEFAULT 0;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE newtvariation varchar(500) DEFAULT tvariation;
IF (ttipo='Variavel|Quantitativo') THEN
		SET ncatstep=1;
		SELECT substrCount(tvariation,';')+1 INTO ncat;
		WHILE ncatstep <= ncat DO
			SET statevar2 = 0;
			SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO statevar2;
			IF (trunit='mm' AND tunit='cm') THEN
				SET statevar2 = statevar2/10;
			END IF; 
			IF (trunit='cm' AND tunit='mm') THEN
				SET statevar2 = statevar2*10;
			END IF; 
			IF ((trunit='m' OR trunit='metros') AND tunit='cm') THEN
				SET statevar2 = (statevar2)*100;
			END IF; 
			IF (trunit='cm' AND (tunit='m' OR tunit='metros')) THEN
				SET statevar2 = (statevar2)/100;
			END IF; 
			IF (trunit='mm' AND (tunit='m' OR tunit='metros'))  THEN
				SET statevar2 = (statevar2)/1000;
			END IF; 
			IF ((trunit='m' OR trunit='metros') AND tunit='mm') THEN
				SET statevar2 = (statevar2)*1000;
			END IF; 
			IF (ncatstep=1) THEN
				SET newtvariation  = statevar2;
			ELSE
				SET newtvariation = CONCAT(newtvariation,";",statevar2);
			END IF;
			SET ncatstep = ncatstep+1;
		END WHILE;
END IF;
RETURN newtvariation;
END

