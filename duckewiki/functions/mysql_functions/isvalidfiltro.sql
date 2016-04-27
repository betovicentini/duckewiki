CREATE FUNCTION isvalidfiltro(filtid INT(10), valoresCol VARCHAR(500)) RETURNS INT(1)
BEGIN
DECLARE valoresres VARCHAR(1000) DEFAULT '';
DECLARE filtidrun INT(10) DEFAULT 0;
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val INT(1) DEFAULT 0;
SELECT substrCount(valoresCol,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET filtidrun = 0;
SELECT TRIM((SUBSTRING_INDEX(SUBSTRING_INDEX(valoresCol,';',ncatstep),';',-1))) INTO filtidrun;
IF (filtid=filtidrun) THEN
	SET val = 1;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
RETURN val;
END
