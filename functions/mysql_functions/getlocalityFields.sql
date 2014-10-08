CREATE FUNCTION getlocalityFields(gazid INT(10), qual CHAR(100)) RETURNS VARCHAR(300)  CHARSET utf8
BEGIN
DECLARE pais CHAR(100) DEFAULT '';
DECLARE provincia CHAR(100) DEFAULT '';
DECLARE munip CHAR(100) DEFAULT '';
DECLARE gaztipo CHAR(100) DEFAULT '';
DECLARE gaztxt CHAR(100) DEFAULT '';
DECLARE partp CHAR(100) DEFAULT '';
DECLARE partxt CHAR(100) DEFAULT '';
DECLARE coordprec CHAR(100) DEFAULT '';
DECLARE resultado VARCHAR(300)  DEFAULT '';
DECLARE gazpath VARCHAR(300)  DEFAULT '';
DECLARE ns CHAR(100) DEFAULT '';
DECLARE we CHAR(100) DEFAULT '';
DECLARE munilat DOUBLE DEFAULT 0;
DECLARE munilong DOUBLE DEFAULT 0;
DECLARE gazlat DOUBLE DEFAULT 0;
DECLARE gazlong DOUBLE DEFAULT 0;
DECLARE gazalt DOUBLE DEFAULT 0;
DECLARE gazparid INT(10) DEFAULT 0;
DECLARE temppar INT(10) DEFAULT 0;
DECLARE parparid INT(10) DEFAULT 0;
DECLARE parlat DOUBLE DEFAULT 0;
DECLARE parlong DOUBLE DEFAULT 0;
DECLARE paralt DOUBLE DEFAULT 0;
/*GazetteerTIPOtxt, */
SELECT Country,Province,Municipio,Municipio.Latitude+0,Municipio.Longitude+0,Gazetteer.Latitude+0,Gazetteer.Longitude+0,Gazetteer.Altitude+0,Gazetteer.ParentID,Gazetteer,Gazetteer.PathName INTO pais,provincia,munip,munilat,munilong,gazlat,gazlong,gazalt,gazparid,gaztxt,gazpath FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GazetteerID=gazid;
IF (ABS(gazlat)+ABS(gazlong))=0 THEN
	SELECT Gazetteer.Latitude+0,Gazetteer.Longitude+0,Gazetteer.Altitude+0 INTO parlat,parlong,paralt FROM Gazetteer WHERE GazetteerID=gazparid;
	IF (ABS(parlat)+ABS(parlong))>0 THEN
		SET gazlat =parlat;
		SET gazlong =parlong;
		SET gazalt =paralt;
		SET coordprec = "Gazetteer";
	END IF;
END IF;
IF (ABS(gazlat)+ABS(gazlong))=0 THEN
	IF (ABS(munilat)+ABS(munilong))>0 THEN
		SET gazlat =munilat;
		SET gazlong =munilong;
		SET coordprec = "Municipio";
	END IF;
END IF;
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
IF qual='NS' THEN
	SET resultado=ns;
END IF;
IF qual='EW' THEN
	SET resultado=we;
END IF;
RETURN resultado;
END
