CREATE FUNCTION removeformularioidfromtraits(FormulariosIDS varchar(200),formidtoremove varchar(100)) RETURNS text CHARSET utf8
BEGIN
DECLARE novofiltrostring TEXT DEFAULT '';
DECLARE colname TEXT DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE ffid INT(10) DEFAULT 0;
SELECT substrCount(FormulariosIDS,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(FormulariosIDS,';',ncatstep),';',-1)) INTO colname;
IF (colname<>formidtoremove) THEN
IF (novofiltrostring='') THEN
SET novofiltrostring = colname;
ELSE
SET novofiltrostring = CONCAT(novofiltrostring,';',colname);
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
RETURN novofiltrostring;
END
