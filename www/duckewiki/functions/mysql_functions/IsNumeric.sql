CREATE FUNCTION IsNumeric (sIn varchar(1024)) RETURNS tinyint
BEGIN
RETURN sIn REGEXP '^(-|\\+){0,1}([0-9]+\\.[0-9]*|[0-9]*\\.[0-9]+|[0-9]+)$';
END
