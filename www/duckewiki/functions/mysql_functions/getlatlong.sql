CREATE FUNCTION getlatlong(latt DOUBLE, longg DOUBLE, gpspointid INT(10), gazid INT(10), muniid INT(10), provid INT(10), countryidd INT(10), lat BOOLEAN) RETURNS DOUBLE
BEGIN
DECLARE parid  INT(10) DEFAULT 0;
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
				SELECT muni.Latitude+0,muni.Longitude+0  INTO latt,longg FROM Municipio as muni WHERE MunicipioID=muniid;
			END IF;
		END IF;
	END IF;
END IF;
IF ((ABS(latt)+ABS(longg))=0 OR (latt IS NULL) OR (longg IS NULL)) THEN
	SET latt = NULL;
	SET longg = NULL;
END IF;
IF (lat=1) THEN
	return latt;
ELSE 
	return longg;
END IF; 
END
