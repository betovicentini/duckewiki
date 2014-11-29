CREATE FUNCTION isvalidlocalandsub(gazid INT(10), gpspointid INT(10), theid INT(10), theref CHAR(100)) RETURNS INT
BEGIN
DECLARE nspecs INT DEFAULT 0;
DECLARE nchilds INT DEFAULT 0;
IF (theref='Country') THEN
IF (gpspointid>0) THEN
	SELECT COUNT(*) into nspecs FROM GPS_DATA as gpss JOIN Gazetteer as gaz ON gaz.GazetteerID=gpss.GazetteerID JOIN Municipio as muni ON muni.MunicipioID=gaz.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID WHERE prov.CountryID=theid AND gpss.PointID=gpspointid;
ELSE
	IF (gazid>0) THEN
	SELECT COUNT(*) into nspecs FROM Gazetteer as gaz JOIN Municipio as muni ON muni.MunicipioID=gaz.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID WHERE prov.CountryID=theid AND gaz.GazetteerID=gazid;
	END IF;
END IF;
END IF;
IF (theref='Province') THEN
IF (gpspointid>0) THEN
	SELECT COUNT(*) into nspecs FROM GPS_DATA as gpss JOIN Gazetteer as gaz ON gaz.GazetteerID=gpss.GazetteerID JOIN Municipio as muni ON muni.MunicipioID=gaz.MunicipioID WHERE muni.ProvinceID=theid AND gpss.PointID=gpspointid;
ELSE
	IF (gazid>0) THEN
	SELECT COUNT(*) into nspecs FROM Gazetteer as gaz JOIN Municipio as muni ON muni.MunicipioID=gaz.MunicipioID WHERE muni.ProvinceID=theid AND gaz.GazetteerID=gazid;
	END IF;
END IF;
END IF;
IF (theref='Municipio') THEN
IF (gpspointid>0) THEN
	SELECT COUNT(*) into nspecs FROM GPS_DATA as gpss JOIN Gazetteer as gaz ON gaz.GazetteerID=gpss.GazetteerID WHERE gaz.MunicipioID=theid AND gpss.PointID=gpspointid;
ELSE
	IF (gazid>0) THEN
	SELECT COUNT(*) into nspecs FROM Gazetteer as gaz WHERE gaz.MunicipioID=theid AND gaz.GazetteerID=gazid;
	END IF;
END IF;
END IF;
IF (theref='Gazetteer') THEN
IF (gpspointid>0) THEN
	SELECT COUNT(*) into nspecs FROM GPS_DATA as gpss WHERE gpss.PointID=gpspointid AND (gpss.GazetteerID=theid OR checkissub(gpss.GazetteerID,theid)>0);
ELSE
	IF (gazid>0) THEN
	SELECT checkissub(gazid,theid) INTO nspecs;
	END IF;
END IF;
IF nspecs=0 AND gazid=theid THEN
	SET nspecs=1;
END IF;
END IF;
RETURN nspecs;
END
