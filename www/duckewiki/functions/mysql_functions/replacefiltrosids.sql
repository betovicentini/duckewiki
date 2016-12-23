CREATE FUNCTION replacefiltrosids(filtrocol varchar(500),filtroidtoremove CHAR(100), filtroidtoadd CHAR(100)) RETURNS varchar(500) CHARSET utf8
BEGIN
DECLARE novofiltrostring varchar(500) DEFAULT ' ';
DECLARE colname varchar(500) DEFAULT '';
DECLARE res varchar(500) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE pessid INT(10) DEFAULT 0;
SET filtroidtoadd = trim(filtroidtoadd);
SET filtroidtoremove = trim(filtroidtoremove);
IF (filtroidtoadd<>'' AND filtroidtoremove<>'') THEN
	SELECT substrCount(filtrocol,';')+1 INTO ncat;
	WHILE ncatstep <= ncat DO
		SET res = '';
		SET colname = '';
		SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(filtrocol,';',ncatstep),';',-1)) INTO colname;
		IF (colname=filtroidtoremove) THEN
			SET res = filtroidtoadd;
		ELSE
			SET res = colname;
		END IF;
		IF (novofiltrostring='') THEN
			SET novofiltrostring = res;
		ELSE
			SET novofiltrostring = CONCAT(novofiltrostring,';',res);
		END IF;
		SET ncatstep = ncatstep+1;
	END WHILE;
ELSE
	SET novofiltrostring = filtrocol;
END IF;
RETURN novofiltrostring;
END
