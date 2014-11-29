CREATE FUNCTION localidadestring(gazid INT(10),gpsptid INT(10),munid INT(10),provid INT(10),crtid INT(10),latt DOUBLE, longg DOUBLE, altt DOUBLE) RETURNS CHAR(255) CHARSET utf8
BEGIN
DECLARE resultado CHAR(255) DEFAULT '';
DECLARE countr CHAR(100) DEFAULT '';
DECLARE munici CHAR(100) DEFAULT '';
DECLARE coordref CHAR(100) DEFAULT '';
DECLARE provincia CHAR(100) DEFAULT '';
DECLARE gazz CHAR(255) DEFAULT '';
DECLARE gazparid INT(10) DEFAULT 0;
DECLARE gazrunid INT(10) DEFAULT 0;
IF (gpsptid>0) THEN
SELECT CONCAT(UPPER(Country),'. ',UPPER(Province),'. ' ,UPPER(Municipio),'. ', Gazetteer.PathName ,'. ')  INTO resultado FROM GPS_DATA as gps JOIN Gazetteer USING(GazetteerID) JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE PointID=gpsptid;
ELSE
IF (gazid>0) THEN
SELECT CONCAT(UPPER(Country),'. ',UPPER(Province),'. ' ,UPPER(Municipio),'. ', Gazetteer.PathName ,'. ') INTO resultado FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GazetteerID=gazid;
ELSE
IF (munid>0) THEN
SELECT CONCAT(UPPER(Country),'. ',UPPER(Province),'. ' ,UPPER(Municipio),'. ')  INTO resultado FROM Municipio JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE MunicipioID=munid;
ELSE
IF (provid>0) THEN
SELECT CONCAT(UPPER(Country),'. ',UPPER(Province),'. ') INTO resultado FROM Province JOIN Country USING(CountryID) WHERE ProvinceID=provid;
ELSE
IF (crtid>0) THEN
SELECT UPPER(Country) INTO resultado FROM Country WHERE CountryID=crtid;
END IF;
END IF;
END IF;
END IF;
END IF;
IF (latt='' OR longg='' OR latt IS NULL OR longg IS NULL OR (latt=0 AND longg=0)) THEN
	SET latt=9999;
	SET longg=9999;
END IF;
IF (gpsptid>0 AND latt=9999 AND longg=9999) THEN
	SELECT gps.Latitude,gps.Longitude, ROUND(gps.Altitude,0) INTO latt,longg,altt FROM GPS_DATA as gps WHERE PointID=gpsptid;
	SET coordref = 'GPS  ';
END IF;
IF (gazid>0 AND latt=9999 AND longg=9999) THEN
			SET gazrunid = gazid;
			myloop: WHILE (gazrunid>0) 
			DO
				SELECT (gs.Latitude+0),(gs.Longitude+0), ROUND(gs.Altitude,0), ParentID INTO latt,longg,altt,gazparid FROM Gazetteer as gs WHERE gs.GazetteerID=gazrunid;
				IF ((ABS(latt)+ABS(longg))>0) THEN
					LEAVE myloop;
				END IF;
				SET gazrunid = gazparid;
			END WHILE;
			IF ((ABS(latt)+ABS(longg))=0) THEN
				SELECT (gs.Latitude+0),(gs.Longitude+0) INTO latt,longg FROM Gazetteer JOIN Municipio as gs USING(MunicipioID) WHERE GazetteerID=gazid;
				IF ((ABS(latt)+ABS(longg))=0) THEN
					SET latt=9999;
					SET longg=9999;
				ELSE 
					SET coordref = 'Municipio  ';
				END IF;
			ELSE 
				SET coordref = 'Gazetteer  ';
			END IF;
END IF;
IF (munid>0 AND latt=9999 AND longg=9999) THEN
		SELECT gps.Latitude,gps.Longitude INTO latt,longg FROM Municipio as gps WHERE MunicipioID=munid;
		IF ((ABS(latt)+ABS(longg))>0) THEN
			SET coordref = 'Municipio  ';
		END IF;
END IF;
IF ((latt<>9999 AND longg<>9999) OR (ABS(latt)+ABS(longg))>0) THEN
	SET resultado = CONCAT(resultado,' \(', coordref,' Lat: ',latt,' Long: ',longg,IF(altt>0,CONCAT(' Alt: ',altt),''),'\).');
END IF;
RETURN resultado;
END
