CREATE FUNCTION maketraitname(charid INT(10)) RETURNS CHAR(100) CHARSET utf8
BEGIN
DECLARE gazrunid INT(10) DEFAULT 0;
DECLARE resultado CHAR(200) DEFAULT '';
DECLARE parentpath CHAR(200) DEFAULT '';
DECLARE gazparid INT(10) DEFAULT 0;
DECLARE rss CHAR(100) DEFAULT '';
DECLARE rss2 CHAR(100) DEFAULT '';
IF (charid>0) THEN
	SET gazrunid = charid;
	myloop: WHILE (gazrunid>0) 
	DO
	 SELECT tr.ParentID,acentostosemacentos(tr.TraitName)  INTO gazparid,rss FROM Traits as tr WHERE tr.TraitID=gazrunid;
	 SET gazparid = gazparid+0;
	 SET rss = REPLACE(rss," ","_");
	SET rss = UPPER(rss);
	SET rss = REPLACE(rss, '  ', ' ');
	SET rss = REPLACE(rss, '  ', ' ');
	SET rss = REPLACE(rss, '  ', ' ');
	SET rss = REPLACE(rss, '.', ' ');
	SET rss = REPLACE(rss, '  ', '_');
	 SET rss2 = UPPER(SUBSTRING(rss,1,2));
	 IF resultado='' THEN
	 	SET resultado = rss;
	ELSE 
	 IF parentpath='' THEN
		 SET parentpath = rss2;
	 ELSE
		SET parentpath = CONCAT(rss2,'_',parentpath);
	 END IF;
	 END IF;
	 IF (gazparid=0) THEN
		LEAVE myloop;
	 END IF;
	 SET gazrunid=gazparid;
	END WHILE;
END IF;
IF parentpath<>'' THEN
SET resultado = CONCAT(parentpath,"_",resultado);
END IF;
RETURN resultado;
END
