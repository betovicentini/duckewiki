CREATE FUNCTION vernacularschecks(vernaids VARCHAR(200)) RETURNS text CHARSET utf8
BEGIN
DECLARE ntaxa INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 0;
DECLARE otax CHAR(200) DEFAULT '';
DECLARE basvar CHAR(100) DEFAULT '';
DECLARE idvar INT(10) DEFAULT 0;
DECLARE resultado VARCHAR(1000) DEFAULT '';
DECLARE respar VARCHAR(200) DEFAULT '';
DECLARE res1 VARCHAR(50) DEFAULT '';
SELECT substrCount(vernaids,';')+1 INTO ntaxa;
WHILE ncatstep <= ntaxa DO
SET respar = '';
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(vernaids,';',ncatstep),';',-1)) INTO otax;
IF (otax<>'') THEN
SELECT VernacularID INTO respar FROM Vernacular WHERE LOWER(Vernacular) LIKE LOWER(otax);
IF (respar>0) THEN
IF (resultado='') THEN
SET resultado = respar;
ELSE
SET resultado = CONCAT(resultado,';',respar);
END IF;
ELSE
SET res1 = 'ERRO';
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
IF res1='ERRO' THEN
SET resultado = res1;
END IF;
RETURN resultado;
END
