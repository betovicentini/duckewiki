CREATE FUNCTION getGPSlocalityFields(gpsptid INT(10), qual CHAR(100)) RETURNS VARCHAR(300) CHARSET utf8
BEGIN
DECLARE pais CHAR(100) DEFAULT '';
DECLARE provincia CHAR(100) DEFAULT '';
DECLARE munip CHAR(100) DEFAULT '';
DECLARE gaztipo CHAR(100) DEFAULT '';
DECLARE gaztxt CHAR(100) DEFAULT '';
DECLARE gazpath VARCHAR(300) DEFAULT '';
DECLARE partp CHAR(100) DEFAULT '';
DECLARE partxt CHAR(100) DEFAULT '';
DECLARE coordprec CHAR(100) DEFAULT '';
DECLARE resultado VARCHAR(300) DEFAULT '';
DECLARE ns CHAR(100) DEFAULT '';
DECLARE we CHAR(100) DEFAULT '';
DECLARE gazlat DOUBLE DEFAULT 0;
DECLARE gazlong DOUBLE DEFAULT 0;
DECLARE gazalt DOUBLE DEFAULT 0;
DECLARE gazparid INT(10) DEFAULT 0;
DECLARE temppar INT(10) DEFAULT 0;
DECLARE parparid INT(10) DEFAULT 0;
SET coordprec = "GPS";
/*Gazetteer.GazetteerTIPOtxt, */
SELECT Country,Province,Municipio,Gazetteer.ParentID,Gazetteer.Gazetteer,GPS_DATA.Latitude,GPS_DATA.Longitude,GPS_DATA.Altitude,Gazetteer.PathName INTO pais,provincia,munip,gazparid,gaztxt,gazlat,gazlong,gazalt,gazpath FROM GPS_DATA JOIN Gazetteer USING(GazetteerID) JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GPS_DATA.PointID=gpsptid;
IF gazlat<0 THEN
	SET ns = 'S';
ELSE 
	SET ns = 'N';
END IF;
IF gazlong<0 THEN
	SET we = 'W';
ELSE 
	SET we = 'E';
END IF;
IF qual='COUNTRY' THEN
	SET resultado=pais;
END IF;
IF qual='MAJORAREA' THEN
	SET resultado=provincia;
END IF;
IF qual='MINORAREA' THEN
	SET resultado=munip;
END IF;
IF qual='GAZETTEER' THEN
	SET resultado=gazpath;
END IF;
IF qual='GAZETTEER_SPEC' THEN
	SET resultado=gaztxt;
END IF;
IF qual='LATITUDE' THEN
	SET resultado=gazlat;
END IF;
IF qual='LONGITUDE' THEN
	SET resultado=gazlong;
END IF;
IF qual='ALTITUDE' THEN
	SET resultado=gazalt;
END IF;
IF qual='COORDENADAS' THEN
	SET resultado=coordprec;
END IF;
IF qual='COUNTRY' THEN
	SET resultado=pais;
END IF;
IF qual='NS' THEN
	SET resultado=ns;
END IF;
IF qual='EW' THEN
	SET resultado=we;
END IF;
RETURN resultado;
END
