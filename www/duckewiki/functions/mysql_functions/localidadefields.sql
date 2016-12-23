CREATE FUNCTION localidadefields(gazid INT(10), gpsptid INT(10), muniid INT(10), provid INT(10), paisid INT(10), qual CHAR(50)) RETURNS CHAR(255) CHARSET utf8
BEGIN
DECLARE pais CHAR(100) DEFAULT '';
DECLARE provincia CHAR(100) DEFAULT '';
DECLARE munip CHAR(100) DEFAULT '';
DECLARE gaztipo CHAR(100) DEFAULT '';
DECLARE gaztxt CHAR(100) DEFAULT '';
DECLARE partp CHAR(100) DEFAULT '';
DECLARE partxt CHAR(100) DEFAULT '';
DECLARE coordprec CHAR(100) DEFAULT '';
DECLARE resultado CHAR(255) DEFAULT '';
DECLARE gazpath CHAR(255) DEFAULT '';
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
DECLARE parname1 CHAR(100) DEFAULT '';
DECLARE parname2 CHAR(100) DEFAULT '';
DECLARE parname3 CHAR(150) DEFAULT '';
DECLARE parname4 CHAR(150) DEFAULT '';
DECLARE parname5 CHAR(150) DEFAULT '';
DECLARE parname6 CHAR(150) DEFAULT '';
DECLARE parname7 CHAR(150) DEFAULT '';
DECLARE resultado2 CHAR(255) DEFAULT '';
DECLARE temponto CHAR(2) DEFAULT '';


IF (gpsptid>0) THEN
SELECT Country,Province,Municipio,gaz1.Gazetteer,gaz1.PathName,gaz2.Gazetteer,gaz3.Gazetteer,gaz4.Gazetteer,gaz5.Gazetteer,gaz6.Gazetteer,gaz7.Gazetteer INTO pais,provincia,munip,gaztxt,gazpath,parname1,parname2,parname3,parname4,parname5,parname6 FROM GPS_DATA as gps JOIN Gazetteer as gaz1 ON gps.GazetteerID=gaz1.GazetteerID LEFT JOIN Gazetteer as gaz2 ON gaz1.ParentID=gaz2.GazetteerID LEFT JOIN Gazetteer as gaz3 ON gaz2.ParentID=gaz3.GazetteerID  LEFT JOIN Gazetteer as gaz4 ON gaz3.ParentID=gaz4.GazetteerID LEFT JOIN Gazetteer as gaz5 ON gaz4.ParentID=gaz5.GazetteerID LEFT JOIN Gazetteer as gaz6 ON gaz5.ParentID=gaz6.GazetteerID LEFT JOIN Gazetteer as gaz7 ON gaz6.ParentID=gaz7.GazetteerID JOIN Municipio ON gaz1.MunicipioID=Municipio.MunicipioID JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE PointID=gpsptid;
ELSE 
IF (gazid>0)  THEN
SELECT Country,Province,Municipio,gaz1.Gazetteer,gaz1.PathName,gaz2.Gazetteer,gaz3.Gazetteer,gaz4.Gazetteer,gaz5.Gazetteer,gaz6.Gazetteer,gaz7.Gazetteer INTO pais,provincia,munip,gaztxt,gazpath,parname1,parname2,parname3,parname4,parname5,parname6 FROM Gazetteer as gaz1 LEFT JOIN Gazetteer as gaz2 ON gaz1.ParentID=gaz2.GazetteerID LEFT JOIN Gazetteer as gaz3 ON gaz2.ParentID=gaz3.GazetteerID  LEFT JOIN Gazetteer as gaz4 ON gaz3.ParentID=gaz4.GazetteerID LEFT JOIN Gazetteer as gaz5 ON gaz4.ParentID=gaz5.GazetteerID LEFT JOIN Gazetteer as gaz6 ON gaz5.ParentID=gaz6.GazetteerID LEFT JOIN Gazetteer as gaz7 ON gaz6.ParentID=gaz7.GazetteerID JOIN Municipio ON gaz1.MunicipioID=Municipio.MunicipioID JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE gaz1.GazetteerID=gazid;
ELSE 
IF (muniid>0)  THEN
SELECT Country,Province,Municipio INTO pais,provincia,munip FROM Municipio JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE Municipio.MunicipioID=muniid;
ELSE 
IF (provid>0)  THEN
SELECT Country,Province INTO pais,provincia FROM Province JOIN Country USING(CountryID) WHERE Province.ProvinceID=provid;
ELSE 
SELECT Country INTO pais FROM Country WHERE CountryID=paisid;
END IF;
END IF;
END IF;
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
/* SET resultado= CONCAT(gaztipo,' ',gaztxt); */
	SET resultado= gaztxt;
END IF;
IF qual='GAZ_PAR1' THEN
	IF parname1<>''  THEN
		SET resultado= CONCAT(gaztxt,' - ' ,parname1);
	ELSE 
		SET resultado= gaztxt;
	END IF;
END IF;
IF qual='GAZ_PAR2' THEN
	IF parname1<>''  THEN
		SET resultado= parname1;
	ELSE 
		SET resultado= gaztxt;
	END IF;
END IF;
IF (qual='GAZfirstPARENT' or qual='GAZminusParent') THEN
	IF parname6<>''  THEN
		SET resultado= parname6;
		SET resultado2 = CONCAT(parname5,". ",parname4,". ",parname3,". ",parname2,". ",parname1,". ",gaztxt);
	ELSE 
		IF parname5<>''  THEN
			SET resultado= parname5;
			SET resultado2 = CONCAT(parname4,". ",parname3,". ",parname2,". ",parname1,". ",gaztxt);
		ELSE 
			IF parname4<>''  THEN
				SET resultado= parname4;
				SET resultado2 = CONCAT(parname3,". ",parname2,". ",parname1,". ",gaztxt);
			ELSE 
				IF parname3<>''  THEN
					SET resultado = parname3;
					SET resultado2 = CONCAT(parname2,". ",parname1,". ",gaztxt);
				ELSE 
					IF parname2<>''  THEN
						SET resultado = parname2;
						SET resultado2 = CONCAT(parname1,". ",gaztxt);
					ELSE 
						IF parname1<>''  THEN
							SET resultado= parname1;
							SET resultado2 = gaztxt;
						ELSE 
							SET resultado= gaztxt;
						END IF;
					END IF;
				END IF;
			END IF;
		END IF;
	END IF;
	IF qual='GAZminusParent' THEN
		SET resultado = REPLACE(resultado2,". .","");
		SET resultado = REPLACE(resultado,"..","");
	END IF;
END IF;
SET resultado = TRIM(resultado);
SET temponto = RIGHT(resultado,1);
IF temponto="." THEN
	SET resultado = SUBSTRING(resultado, 1, CHAR_LENGTH(resultado)-1);
END IF;
RETURN resultado;
END
