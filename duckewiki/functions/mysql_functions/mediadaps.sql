CREATE FUNCTION mediadaps(ttvariation varchar(100)) RETURNS text CHARSET utf8
BEGIN
DECLARE resultado FLOAT DEFAULT 0;
DECLARE parres FLOAT DEFAULT 0;
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE nvalor FLOAT DEFAULT 0;
SELECT substrCount(ttvariation,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(ttvariation,';',ncatstep),';',-1)) INTO nvalor;
SET nvalor = nvalor+0;
SET parres = parres+nvalor;
SET ncatstep = ncatstep+1;
END WHILE;
SET parres = parres/ncat;
RETURN parres;
END
