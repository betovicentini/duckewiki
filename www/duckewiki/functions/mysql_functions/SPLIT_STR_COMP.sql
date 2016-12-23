CREATE FUNCTION SPLIT_STR_COMP(valores VARCHAR(255),delim CHAR(10),minval FLOAT,maxval FLOAT) RETURNS FLOAT
BEGIN
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val1 FLOAT DEFAULT 0;
DECLARE val2 FLOAT DEFAULT 0;
SELECT substrCount(valores,delim)+1 INTO ncat;
WHILE ncatstep <= ncat DO
	SET val1 = 0;
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(valores,delim,ncatstep),delim,-1)) INTO val1;
	IF (val1>=minval AND val1<=maxval) THEN 
		SET val2 = val2+1; 
	END IF;
	SET ncatstep = ncatstep+1;
END WHILE;
RETURN val2;
END