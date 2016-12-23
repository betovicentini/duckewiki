CREATE FUNCTION isvalidlocalteste(gazid INT(10), gpspointid INT(10), theid INT(10), theref CHAR(100)) RETURNS INT(10)
BEGIN
DECLARE nspecs INT(10) DEFAULT 0;
IF (theref='Country') THEN
IF (gpspointid>0) THEN
	SELECT COUNT(*) into nspecs FROM GPS_DATA as gpss JOIN Gazetteer as gaz ON gaz.GazetteerID=gpss.GazetteerID LEFT JOIN Municipio as muni ON muni.MunicipioID=gaz.MunicipioID LEFT JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID WHERE prov.CountryID=theid AND gpss.PointID=gpspointid;
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
	SELECT COUNT(*) into nspecs FROM GPS_DATA as gpss JOIN Gazetteer as gaz ON gaz.GazetteerID=gpss.GazetteerID LEFT JOIN Gazetteer AS gaz2 ON gaz.ParentID=gaz2.GazetteerID LEFT JOIN Gazetteer AS gaz3 on gaz2.ParentID=gaz3.GazetteerID LEFT JOIN Gazetteer AS gaz4 on gaz3.ParentID=gaz4.GazetteerID LEFT JOIN Gazetteer AS gaz5 on gaz4.ParentID=gaz5.GazetteerID LEFT JOIN Gazetteer AS gaz6 on gaz5.ParentID=gaz6.GazetteerID WHERE (gpss.GazetteerID=theid OR gaz.ParentID=theid OR gaz2.ParentID=theid OR gaz3.ParentID=theid OR gaz4.ParentID=theid OR gaz5.ParentID=theid OR gaz6.ParentID=theid) AND gpss.PointID=gpspointid;
ELSE
	IF (gazid>0 AND gazid<>theid) THEN
	SELECT COUNT(*) into nspecs FROM Gazetteer as gaz LEFT JOIN Gazetteer AS gaz2 ON gaz.ParentID=gaz2.GazetteerID LEFT JOIN Gazetteer AS gaz3 on gaz2.ParentID=gaz3.GazetteerID  LEFT JOIN Gazetteer AS gaz4 on gaz3.ParentID=gaz4.GazetteerID LEFT JOIN Gazetteer AS gaz5 on gaz4.ParentID=gaz5.GazetteerID LEFT JOIN Gazetteer AS gaz6 on gaz5.ParentID=gaz6.GazetteerID WHERE gaz.GazetteerID=gazid AND (gaz.ParentID=theid  OR gaz2.ParentID=theid OR gaz3.ParentID=theid OR gaz4.ParentID=theid OR gaz5.ParentID=theid OR gaz6.ParentID=theid);
	END IF;
	IF (gazid=theid) THEN
		SET nspecs=1;
	END IF;
END IF;
END IF;
RETURN nspecs;
END
