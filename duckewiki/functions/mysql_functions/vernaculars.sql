CREATE FUNCTION vernaculars(vernaids VARCHAR(200)) RETURNS text CHARSET utf8
BEGIN
DECLARE ntaxa INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 0;
DECLARE otax CHAR(100) DEFAULT '';
DECLARE basvar CHAR(100) DEFAULT '';
DECLARE idvar INT(10) DEFAULT 0;
DECLARE resultado VARCHAR(1000) DEFAULT '';
DECLARE respar VARCHAR(200) DEFAULT '';
SELECT substrCount(vernaids,';')+1 INTO ntaxa;
WHILE ncatstep <= ntaxa DO
SET respar = '';
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(vernaids,';',ncatstep),';',-1)) INTO otax;
SELECT Vernacular INTO respar FROM Vernacular WHERE VernacularID=otax;
IF (respar<>'' AND resultado='') THEN
SET resultado = respar;
ELSE
IF (respar<>'') THEN
SET resultado = CONCAT(resultado,'; ',respar);
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
RETURN resultado;
END
