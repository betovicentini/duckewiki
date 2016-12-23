CREATE FUNCTION pegadap(traitvariation VARCHAR(500), delim CHAR(10), position INT(10), daptrid INT(10), curtrid INT(10)) RETURNS DOUBLE
BEGIN
DECLARE res CHAR(255) DEFAULT NULL;
DECLARE res1 INT(10) DEFAULT 0;
IF (curtrid=daptrid) THEN
SET daptrid = daptrid+0;
IF (daptrid>0) THEN
	SELECT substrCount(traitvariation,delim)+1 INTO res1;
	IF (position<=res1) THEN
		SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(traitvariation,delim,position),delim,-1)) INTO res;
		SET res = res+0;
	END IF;
END IF;
END IF;
RETURN res;
END

