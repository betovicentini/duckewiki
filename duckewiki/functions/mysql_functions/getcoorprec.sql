CREATE FUNCTION getcoorprec(latt DOUBLE, longg DOUBLE, gpspointid INT(10), gazid INT(10), muniid INT(10)) RETURNS CHAR(100) CHARSET utf8
BEGIN
DECLARE coorprec CHAR(100) DEFAULT '';
DECLARE gazrun DOUBLE DEFAULT 0;
DECLARE parid INT(10) DEFAULT 0;
IF (ABS(latt)+ABS(longg))>0 THEN
	SET coorprec ='GPS-Planta';
ELSE
	IF (gpspointid>0) THEN
		SET coorprec ='GPS-Planta';
	ELSE
		IF (gazid>0) THEN
			SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=gazid;
			SET gazrun = (ABS(latt)+ABS(longg))+0;
			WHILE (gazrun=0 AND parid>0) DO
				SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
				SET gazrun = (ABS(latt)+ABS(longg))+0;
			END WHILE;
			IF (ABS(latt)+ABS(longg))=0 THEN
				SELECT muni.Latitude+0,muni.Longitude+0  INTO latt,longg FROM Gazetteer JOIN Municipio as muni USING(MunicipioID) WHERE GazetteerID=gazid;
				IF (ABS(latt)+ABS(longg))=0 THEN
						SET coorprec ='Municipio';
				END IF;
			ELSE 
						SET coorprec ='Localidade';
			END IF;
		ELSE 
			IF (muniid>0) THEN
				SET coorprec ='Municipio';
			END IF;
		END IF;
	END IF;
END IF;
return coorprec;
END
