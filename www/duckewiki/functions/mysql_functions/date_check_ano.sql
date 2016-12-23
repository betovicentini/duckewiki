CREATE FUNCTION date_check_ano(anoyy VARCHAR(200)) RETURNS INT
BEGIN
DECLARE yyll INT(10) DEFAULT 0;
DECLARE yyan INT(10) DEFAULT 0;
DECLARE annno INT(10) DEFAULT 0;
DECLARE ress INT(10) DEFAULT 0;
DECLARE anlim INT(10) DEFAULT 0;
SELECT CHAR_LENGTH(TRIM(anoyy)) INTO yyll;
SELECT YEAR(CURDATE()) INTO anlim;
SET yyan = TRIM(anoyy)+0;
IF (yyll<=2 AND (yyan+2000)<=anlim) THEN
SET annno = yyan+2000;
END IF;
IF (yyll<=2 AND annno=0) THEN
SET annno = yyan+1900;
END IF;
IF (yyll=4) THEN
SET annno = yyan;
END IF;
IF (annno>=1500 AND annno<=anlim) THEN
SET ress = annno;
ELSE
SET ress = 0;
END IF;
RETURN ress;
END
