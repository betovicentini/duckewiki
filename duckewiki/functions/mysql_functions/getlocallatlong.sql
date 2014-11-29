CREATE FUNCTION getlocallatlong(gpspointid INT(10), gazid INT(10), muniid INT(10), lat BOOLEAN) RETURNS DOUBLE
BEGIN
DECLARE gazrun DOUBLE DEFAULT 0;
DECLARE parid INT(10) DEFAULT 0;
DECLARE latt DOUBLE DEFAULT 0;
DECLARE longg DOUBLE DEFAULT 0;
	IF (gpspointid>0) THEN
		SELECT Latitude+0,Longitude+0  INTO latt,longg FROM GPS_DATA WHERE PointID=gpspointid;
	ELSE
		IF (gazid>0) THEN
			SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=gazid;
			IF (ABS(latt)+ABS(longg))=0 THEN
				IF (parid>0) THEN
					SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
					IF (ABS(latt)+ABS(longg))=0 THEN
						IF (parid>0) THEN
							SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
							IF (ABS(latt)+ABS(longg))=0 THEN
								IF (parid>0) THEN
									SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
									IF (ABS(latt)+ABS(longg))=0 THEN
										IF (parid>0) THEN
											SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
											IF (ABS(latt)+ABS(longg))=0 THEN
												IF (parid>0) THEN
													SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
												END IF;
											END IF;
										END IF;
									END IF;
								END IF;
							END IF;
						END IF;
					END IF;
				END IF;
			END IF;
/* SET gazrun = (ABS(latt)+ABS(longg))+0;
			WHILE (gazrun=0 AND parid>0) DO
				SELECT Latitude+0,Longitude+0,ParentID  INTO latt,longg,parid FROM Gazetteer WHERE GazetteerID=parid;
				SET gazrun = (ABS(latt)+ABS(longg))+0;
			END WHILE;
*/
			IF (ABS(latt)+ABS(longg))=0 THEN
				SELECT muni.Latitude+0,muni.Longitude+0  INTO latt,longg FROM Gazetteer JOIN Municipio as muni USING(MunicipioID) WHERE GazetteerID=gazid;
			END IF;
		ELSE 
			IF (muniid>0) THEN
				SELECT muni.Latitude+0,muni.Longitude+0  INTO latt,longg FROM Municipio as muni WHERE MunicipioID=muniid;
			END IF;
		END IF;
	END IF;
IF (ABS(latt)+ABS(longg))=0 THEN
	SET latt = NULL;
	SET longg = NULL;
END IF;
IF (lat=1) THEN
	return latt;
ELSE 
	return longg;
END IF; 
END
