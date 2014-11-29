CREATE FUNCTION getlatlongdms(latt DOUBLE, longg DOUBLE, gpspointid INT(10), gazid INT(10), muniid INT(10), provid INT(10), countryidd INT(10), lat BOOLEAN, what INT(1)) RETURNS CHAR(20)
BEGIN
DECLARE parid  INT(10) DEFAULT 0;
DECLARE llaa DOUBLE DEFAULT 0;
DECLARE lonoo DOUBLE DEFAULT 0;
DECLARE lattDG INT(10) DEFAULT 0;
DECLARE longgDG INT(10) DEFAULT 0;
DECLARE llaamm DOUBLE DEFAULT 0;
DECLARE lonoomm DOUBLE DEFAULT 0;
DECLARE lattMM INT(10) DEFAULT 0;
DECLARE longgMM INT(10) DEFAULT 0;
DECLARE lattSS DOUBLE DEFAULT 0;
DECLARE longgSS DOUBLE DEFAULT 0;
DECLARE ns CHAR(10) DEFAULT '';
DECLARE ew CHAR(10) DEFAULT '';
IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
	IF (gpspointid>0) THEN
		SELECT Latitude+0,Longitude+0  INTO latt,longg FROM GPS_DATA WHERE PointID=gpspointid;
	END IF;
	IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
		IF (gazid>0) THEN
			SELECT Latitude+0,Longitude+0,ParentID+0 INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=gazid;
			IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
				IF (parid>0) THEN
					SELECT Latitude+0,Longitude+0,ParentID+0 INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
					IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
						IF (parid>0) THEN
							SELECT Latitude+0,Longitude+0,ParentID+0 INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
							IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
								IF (parid>0) THEN
									SELECT Latitude+0,Longitude+0,ParentID+0 INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
									IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
										IF (parid>0) THEN
											SELECT Latitude+0,Longitude+0,ParentID+0 INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
										END IF;
									END IF;
								END IF;
							END IF;
						END IF;
					END IF;
				END IF;
			END IF;
			IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
				SELECT muni.Latitude+0,muni.Longitude+0  INTO latt,longg FROM Gazetteer JOIN Municipio as muni USING(MunicipioID) WHERE GazetteerID=gazid;
			END IF;
		ELSE 
			IF (muniid>0) THEN
				SELECT muni.Latitude+0, muni.Longitude+0  INTO latt,longg FROM Municipio as muni WHERE MunicipioID=muniid;
			END IF;
		END IF;
	END IF;
END IF;
IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
	SET latt = NULL;
	SET longg = NULL;
	IF (lat=1) THEN
		RETURN latt;
	ELSE 
		RETURN longg;
	END IF;
ELSE
	SET llaa = abs(latt);
	SET lonoo = abs(longg);
	SET lattDG= FLOOR(llaa);
	SET longgDG= FLOOR(lonoo);
	SET llaamm = (llaa-lattDG)*60;
	SET lonoomm = (lonoo-longgDG)*60;
	SET lattMM = FLOOR(llaamm);
	SET longgMM = FLOOR(lonoomm);
	SET lattSS = TRUNCATE(((llaamm-lattMM)*60),4);
	SET longgSS = TRUNCATE(((lonoomm-longgMM)*60),4);
	IF (latt<0) THEN
		SET ns = 'S';
	ELSE
		SET ns = 'N';
	END IF;
	IF (longg<0) THEN
		SET ew = 'W';
	ELSE
		SET ew = 'E';
	END IF;
	IF (what=1 AND lat=1) THEN
		RETURN lattDG;
	END IF;
	IF (what=1 AND lat=0) THEN
		RETURN longgDG;
	END IF;
	IF (what=2 AND lat=1) THEN
		RETURN lattMM;
	END IF;
	IF (what=2 AND lat=0) THEN
		RETURN longgMM;
	END IF;
	IF (what=3 AND lat=1) THEN
		RETURN lattSS;
	END IF;
	IF (what=3 AND lat=0) THEN
		RETURN longgSS;
	END IF;
	IF (what=4 AND lat=1) THEN
		RETURN ns;
	END IF;
	IF (what=4 AND lat=0) THEN
		RETURN ew;
	END IF;
	IF (what=5 AND lat=1) THEN
		RETURN latt;
	END IF;
	IF (what=5 AND lat=0) THEN
		RETURN longg;
	END IF;
END IF;
END