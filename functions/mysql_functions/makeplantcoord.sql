CREATE FUNCTION makeplantcoord(gazid INT(10), dmx FLOAT,dmy FLOAT, xyref CHAR(100), qual CHAR(10), parid INT(10)) RETURNS FLOAT
BEGIN
DECLARE plotx FLOAT DEFAULT 0;
DECLARE ploty FLOAT DEFAULT 0;
DECLARE nchild FLOAT DEFAULT 0;
DECLARE strtx FLOAT DEFAULT 0;
DECLARE strty FLOAT DEFAULT 0; 
SELECT DimX,DimY,StartX,StartY INTO plotx,ploty,strtx,strty FROM Gazetteer WHERE GazetteerID=gazid;
IF (dimx=0 OR dimx IS NULL OR dmy=0 OR dmy IS NULL) THEN
	SELECT ROUND(0 + (RAND() * plotx),2) INTO dmx;
	SELECT ROUND(0 + (RAND() * ploty),2) INTO dmy;
END IF;
IF (gazid<>parid) THEN 
	SET dmx = strtx+dmx;  
	SET dmy = strty+dmy; 
ELSE
	#SELECT COUNT(*) INTO nchild FROM Gazetteer WHERE ParentID=gazid;
	#IF nchild>0 THEN
	#	SET dmx = strtx+dmx;  
	#	SET dmy = strty+dmy; 
	#END IF;
	IF UPPER(xyref)='E' THEN
		SET dmy = (ploty/2)+dmy;
	END IF;
	IF UPPER(xyref)='D' THEN
		SET dmy = (ploty/2)-dmy;
	END IF;
	IF dmy<0 THEN
		SET dmy=0;
	END IF;
	IF (dmx>plotx AND dmx>0) THEN
		SET dmx = plotx;
	END IF;
	IF (dmy>ploty AND dmy>0) THEN
		SET dmy = ploty;
	END IF;
END IF;
IF UPPER(qual)='Y' THEN
RETURN dmy;
ELSE 
RETURN dmx;
END IF;
END
