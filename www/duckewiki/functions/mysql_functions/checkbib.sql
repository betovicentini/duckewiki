CREATE FUNCTION checkbib(bibids CHAR(255), delim CHAR(12), idd INT(10)) RETURNS INT(1)
BEGIN
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val1 INT(10) DEFAULT 0;
DECLARE resul INT(1) DEFAULT 0;
SELECT substrCount(bibids,delim)+1 INTO ncat;
LOOPS: WHILE ncatstep <= ncat DO
	SET val1 = 0;
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(bibids,delim,ncatstep),delim,-1)) INTO val1;
	IF (val1=idd) THEN
		SET resul = 1;
		LEAVE LOOPS;
	END IF;
	SET ncatstep = ncatstep+1;
END WHILE;
RETURN resul;
END