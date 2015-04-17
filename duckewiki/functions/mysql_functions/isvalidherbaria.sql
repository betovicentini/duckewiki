CREATE FUNCTION isvalidherbaria(trid char(50), valoresCol VARCHAR(500)) RETURNS INT(1)
BEGIN
DECLARE valoresres VARCHAR(1000) DEFAULT '';
DECLARE procid char(50) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val INT(1) DEFAULT 0;
SELECT substrCount(valoresCol,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET procid = '';
SELECT TRIM((SUBSTRING_INDEX(SUBSTRING_INDEX(valoresCol,';',ncatstep),';',-1))) INTO procid;
IF (trid=procid) THEN
	SET val = 1;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
RETURN val;
END
