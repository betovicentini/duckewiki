CREATE FUNCTION getaltitude(altt DOUBLE, gpspointid INT(10), gazid INT(10)) RETURNS DOUBLE
BEGIN
IF (altt)=0 THEN
	IF (gpspointid>0) THEN
		SELECT Altitude+0 INTO altt FROM GPS_DATA WHERE PointID=gpspointid;
	ELSE
		IF (gazid>0) THEN
			SELECT Altitude+0  INTO altt FROM Gazetteer WHERE GazetteerID=gazid;
		END IF;
	END IF;
END IF;
IF (altt)=0 THEN
	SET altt = NULL;
END IF;
return altt;
END
