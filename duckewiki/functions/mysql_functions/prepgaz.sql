CREATE PROCEDURE prepgaz(faztudo BOOLEAN)
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE gazid INT(10) DEFAULT 0;
DECLARE val INT(10) DEFAULT 0;
DECLARE oparentid INT(10) DEFAULT 0;
DECLARE temppar INT(10) DEFAULT 0;
DECLARE fimm INT(10) DEFAULT 0;
DECLARE onome VARCHAR(500) DEFAULT '';
DECLARE osearchname VARCHAR(500) DEFAULT '';
DECLARE onomeid VARCHAR(100) DEFAULT '';
DECLARE resultado TEXT DEFAULT '';
DECLARE olat DOUBLE DEFAULT 0;
DECLARE ologitude DOUBLE DEFAULT 0;
DECLARE oalt DOUBLE DEFAULT 0;
DECLARE odataupdated DATE;
DECLARE cur2 CURSOR FOR SELECT DISTINCT ftb.GazetteerID FROM  (SELECT DISTINCT pl.GazetteerID FROM Plantas AS pl WHERE pl.GazetteerID>0  UNION  SELECT DISTINCT pl2.GazetteerID FROM Especimenes as pl2 WHERE pl2.GazetteerID>0 UNION  SELECT DISTINCT gps1.GazetteerID FROM Plantas as plt JOIN GPS_DATA as gps1 ON plt.GPSPointID=gps1.PointID WHERE gps1.GazetteerID>0 UNION  SELECT DISTINCT gps2.GazetteerID FROM Especimenes as spt JOIN GPS_DATA as gps2 ON spt.GPSPointID=gps2.PointID WHERE gps2.GazetteerID>0 ) as ftb ORDER BY ftb.GazetteerID;
DECLARE cur1 CURSOR FOR SELECT DISTINCT Gazetteer.GazetteerID FROM Gazetteer WHERE GazetteerID>0 ORDER BY GazetteerID;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
SET @drp= "DROP TABLE IF EXISTS templocalitypre";
PREPARE drptb FROM @drp;
EXECUTE drptb;
DEALLOCATE PREPARE drptb;
SET @drp= "CREATE TABLE  templocalitypre ( tempid INT(10) unsigned NOT NULL auto_increment, nome VARCHAR(500), searchname VARCHAR(500), lat FLOAT(20), logitude FLOAT(20), alt  FLOAT(10), nomeid VARCHAR(100), dataupdated DATE, PRIMARY KEY (tempid)) CHARACTER SET utf8";
PREPARE drptb FROM @drp;
EXECUTE drptb;
DEALLOCATE PREPARE drptb;
IF (faztudo>0) THEN
	OPEN cur1;
ELSE
	OPEN cur2;
END IF;
loop1: LOOP
	IF (faztudo>0) THEN
		FETCH cur1 INTO gazid;
	ELSE
		FETCH cur2 INTO gazid;
	END IF;
	SELECT CONCAT(gaz.PathName,' - ',muni.Municipio,' - ',prov.Province,' - ',UPPER(crt.Country)), gaz.PathName, gaz.Latitude, gaz.Longitude, gaz.Altitude, CONCAT('gazetteerid_',gaz.GazetteerID), CURDATE(), gaz.ParentID INTO onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated,oparentid FROM Gazetteer as gaz JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID WHERE gaz.GazetteerID=gazid;
	SELECT COUNT(*) INTO val FROM templocalitypre WHERE nomeid=onomeid;
	IF (val=0) THEN
	INSERT INTO templocalitypre (`nome`, `searchname`,`lat`,`logitude`,`alt`,`nomeid`,`dataupdated`) VALUES (onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated);
	END IF;
	SELECT CONCAT(muni.Municipio,' - ',prov.Province,' - ',UPPER(crt.Country)), muni.Municipio, muni.Latitude, muni.Longitude, 0, CONCAT('municipioid_',muni.MunicipioID), CURDATE(), gaz.ParentID INTO onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated,oparentid FROM Gazetteer as gaz JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID WHERE gaz.GazetteerID=gazid;
	SELECT COUNT(*) INTO val FROM templocalitypre WHERE nomeid=onomeid;
	IF (val=0) THEN
	INSERT INTO templocalitypre (`nome`, `searchname`,`lat`,`logitude`,`alt`,`nomeid`,`dataupdated`) VALUES (onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated);
	END IF;
	SELECT CONCAT(prov.Province,' - ',UPPER(crt.Country)), prov.Province, 0, 0, 0, CONCAT('provinceid_',prov.ProvinceID), CURDATE(), gaz.ParentID INTO onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated,oparentid FROM Gazetteer as gaz JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID WHERE gaz.GazetteerID=gazid;
	SELECT COUNT(*) INTO val FROM templocalitypre WHERE nomeid=onomeid;
	IF (val=0) THEN
	INSERT INTO templocalitypre (`nome`, `searchname`,`lat`,`logitude`,`alt`,`nomeid`,`dataupdated`) VALUES (onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated);
	END IF;
	SELECT crt.Country, crt.Country, 0, 0, 0, CONCAT('paisid_',crt.CountryID), CURDATE(), gaz.ParentID INTO onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated,oparentid FROM Gazetteer as gaz JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID WHERE gaz.GazetteerID=gazid;
	SELECT COUNT(*) INTO val FROM templocalitypre WHERE nomeid=onomeid;
	IF (val=0) THEN
	INSERT INTO templocalitypre (`nome`, `searchname`,`lat`,`logitude`,`alt`,`nomeid`,`dataupdated`) VALUES (onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated);
	END IF;
	SET temppar = oparentid;
	WHILE temppar>0 DO 
		SELECT CONCAT(gaz.PathName,' - ',muni.Municipio,' - ',prov.Province,' - ',UPPER(crt.Country)), gaz.PathName, gaz.Latitude, gaz.Longitude, gaz.Altitude, CONCAT('gazetteerid_',gaz.GazetteerID), CURDATE(), gaz.ParentID INTO onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated,oparentid FROM Gazetteer as gaz JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID WHERE gaz.GazetteerID=temppar;
		SELECT COUNT(*) INTO val FROM templocalitypre WHERE nomeid=onomeid;
		IF (val=0) THEN
		INSERT INTO templocalitypre (`nome`, `searchname`,`lat`,`logitude`,`alt`,`nomeid`,`dataupdated`) VALUES (onome, osearchname,olat,ologitude,oalt,onomeid,odataupdated);
		SET temppar = oparentid;
		ELSE 
		SET temppar = 0;
		END IF;
	END WHILE;
	IF done=1 THEN
		IF (faztudo>0) THEN
			CLOSE cur1;
		ELSE
			CLOSE cur2;
		END IF;
	LEAVE loop1;
	END IF;
END LOOP loop1;
	IF (faztudo=TRUE) THEN
	SET @drp= "DROP TABLE LocalitySimple";
	PREPARE drptb FROM @drp;
	EXECUTE drptb;
	DEALLOCATE PREPARE drptb;
	SET @drp= "CREATE TABLE LocalitySimple SELECT * FROM templocalitypre ORDER BY nome";
	PREPARE drptb FROM @drp;
	EXECUTE drptb;
	DEALLOCATE PREPARE drptb;
	SET @drp= "ALTER TABLE LocalitySimple ADD INDEX (searchname)";
	PREPARE drptb FROM @drp;
	EXECUTE drptb;
	DEALLOCATE PREPARE drptb;
	ELSE
	SET @drp= "DROP TABLE LocalitySimpleSearch";
	PREPARE drptb FROM @drp;
	EXECUTE drptb;
	DEALLOCATE PREPARE drptb;
	SET @drp= "CREATE TABLE LocalitySimpleSearch SELECT * FROM templocalitypre ORDER BY nome";
	PREPARE drptb FROM @drp;
	EXECUTE drptb;
	DEALLOCATE PREPARE drptb;
	SET @drp= "ALTER TABLE LocalitySimpleSearch ADD INDEX (searchname)";
	PREPARE drptb FROM @drp;
	EXECUTE drptb;
	DEALLOCATE PREPARE drptb;
	END IF;
END
