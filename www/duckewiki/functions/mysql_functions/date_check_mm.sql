CREATE FUNCTION date_check_mm(mesmm VARCHAR(200)) RETURNS INT
BEGIN
DECLARE yyll INT(10) DEFAULT 0;
DECLARE yyan INT(10) DEFAULT 0;
DECLARE ress INT(10) DEFAULT 0;
SELECT CHAR_LENGTH(TRIM(mesmm)) INTO yyll;
SET yyan = TRIM(mesmm)+0;
IF (yyan>0 AND yyan<=12) THEN
SET ress = yyan;
ELSE
SET ress = 0;
END IF;
RETURN ress;
END
