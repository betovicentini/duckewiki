CREATE FUNCTION istreeinplot(gazid INT(10), gpspointid INT(10), theid INT(10)) RETURNS INT
BEGIN
DECLARE evalido INT DEFAULT 0;
DECLARE nchilds INT DEFAULT 0;
IF evalido=0 AND gazid=theid THEN
	SET evalido=1;
ELSE
	IF (gpspointid>0) THEN
		SELECT COUNT(*) into evalido FROM GPS_DATA as gpss WHERE gpss.PointID=gpspointid AND (gpss.GazetteerID=theid OR checkissub(gpss.GazetteerID,theid)>0);
	ELSE
		IF (gazid>0) THEN
			SELECT checkissub(gazid,theid) INTO evalido;
		END IF;
	END IF;
END IF;
RETURN evalido;
END
