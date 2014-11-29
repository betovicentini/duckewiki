CREATE FUNCTION splitString(valores CHAR(255), delim CHAR(12), posi INT(10)) RETURNS CHAR
BEGIN
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val1 CHAR(50) DEFAULT " ";
DECLARE resul CHAR(50) DEFAULT "";
SELECT substrCount(valores,delim)+1 INTO ncat;
WHILE ncatstep <= ncat DO
	SET val1 = 0;
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(valores,delim,ncatstep),delim,-1)) INTO val1;
	IF (ncatstep=posi) THEN
	SET resul = val1;
	END IF;
	SET ncatstep = ncatstep+1;
END WHILE;
RETURN resul;
END
