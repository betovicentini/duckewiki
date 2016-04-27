CREATE FUNCTION updatefiltrotag(FiltrosIDS varchar(200),filtroidtoadd varchar(100)) RETURNS varchar(200) CHARSET utf8
BEGIN
DECLARE novofiltrostring varchar(200) DEFAULT ' ';
DECLARE colname varchar(200) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE pessid INT(10) DEFAULT 0;
SELECT substrCount(FiltrosIDS,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(FiltrosIDS,';',ncatstep),';',-1)) INTO colname;
IF (colname<>filtroidtoadd) THEN
IF (novofiltrostring='') THEN
SET novofiltrostring = colname;
ELSE
SET novofiltrostring = CONCAT(novofiltrostring,';',colname);
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
SET novofiltrostring = CONCAT(novofiltrostring,';',filtroidtoadd);
RETURN novofiltrostring;
END
