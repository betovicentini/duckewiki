CREATE FUNCTION getplantcoord(gazid INT(10), dmx FLOAT,dmy FLOAT, xyref CHAR(100), qual CHAR(10), parid INT(10)) RETURNS FLOAT
BEGIN
DECLARE plotx FLOAT DEFAULT 0;
DECLARE ploty FLOAT DEFAULT 0;
DECLARE nchild FLOAT DEFAULT 0;
DECLARE strtx FLOAT DEFAULT 0;
DECLARE strty FLOAT DEFAULT 0; 
SELECT DimX,DimY,StartX,StartY INTO plotx,ploty,strtx,strty FROM Gazetteer WHERE GazetteerID=gazid;
/*SE NAO TEM COORDENADA MAS ESTA NUMA PARCELA CALCULA UMA POSICAO ALEATORIA PARA PLOTAR*/
IF (dmx=0 AND plotx>0) THEN
	SET dmx = 0+RAND()*plotx;
END IF;
IF (dmy=0 AND ploty>0) THEN
	SET dmy = 0+RAND()*ploty;
END IF;
IF (gazid<>parid) THEN 
	SET dmx = strtx+dmx;  
	SET dmy = strty+dmy;  
ELSE
	/*SELECT COUNT(*) INTO nchild FROM Gazetteer WHERE ParentID=gazid;
	IF nchild>0 THEN
		SET dmx = strtx+dmx;  
		SET dmy = strty+dmy;  	
	END IF;
	*/
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
