CREATE FUNCTION SPLIT_STR_MIN(valores VARCHAR(255), delim VARCHAR(12)) RETURNS FLOAT
BEGIN
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val1 FLOAT DEFAULT 0;
DECLARE val2 FLOAT DEFAULT 999999999999999;
SELECT substrCount(valores,delim)+1 INTO ncat;
WHILE ncatstep <= ncat DO
	SET val1 = 0;
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(valores,delim,ncatstep),delim,-1)) INTO val1;
	IF (val1<val2) THEN 
		SET val2=val1; 
	END IF;
	SET ncatstep = ncatstep+1;
END WHILE;
RETURN val2;
END