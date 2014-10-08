CREATE FUNCTION getdetdate(fulldate DATE, detyy INT(10), detmm INT(10), detdd INT(10) ) RETURNS CHAR(50) CHARSET utf8
BEGIN
DECLARE rr CHAR(100) DEFAULT '';
IF (fulldate>0) THEN
	SET rr = DATE_FORMAT(fulldate,'%d-%b-%Y'); 
ELSE 
	IF (detdd>0 AND detmm>0 AND detyy>0) THEN
		SET rr = CONCAT(detdd,'-',detmm,'-',detyy);
	ELSE
		IF (detmm>0 AND detyy>0) THEN
			SET rr = CONCAT(detmm,'-',detyy);
		ELSE
			IF (detyy>0) THEN
				SET rr = detyy;
			END IF;
		END IF;
	END IF;
END IF;
RETURN rr;
END
