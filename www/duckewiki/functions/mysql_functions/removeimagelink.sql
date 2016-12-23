CREATE FUNCTION removeimagelink(variationcol char(200),imgidtoremove int(10)) RETURNS char(200)
BEGIN
DECLARE novavariation char(200) DEFAULT ' ';
DECLARE imgidrun  int(10) DEFAULT 0;
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
SELECT substrCount(variationcol,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET imgidrun = '';
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(variationcol,';',ncatstep),';',-1)) INTO imgidrun;
IF (imgidrun<>imgidtoremove) THEN
IF (novavariation='') THEN
SET novavariation = imgidrun;
ELSE
SET novavariation = CONCAT(novavariation,';',imgidrun);
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
RETURN novavariation;
END