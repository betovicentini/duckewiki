CREATE FUNCTION checkbib(bibids CHAR(255), idd INT(10)) RETURNS CHAR
BEGIN
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val1 INT(10) DEFAULT 0;
DECLARE resul INT(1) DEFAULT 0;
SELECT substrCount(bibids,";")+1 INTO ncat;
WHILE ncatstep <= ncat DO
	SET val1 = 0;
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(bibids,";",ncatstep),";",-1))+0 INTO val1;
	IF (val1=idd) THEN
		SET resul = 1;
	END IF;
END WHILE;
RETURN resul;
END