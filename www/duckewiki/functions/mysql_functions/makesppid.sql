CREATE FUNCTION makesppid(famid INT(10), genusid INT(10), spid INT(10), infspid INT(10)) RETURNS CHAR(60)
BEGIN
DECLARE famtxt  CHAR(15) DEFAULT '';
DECLARE gentxt  CHAR(15) DEFAULT '';
DECLARE sptxt  CHAR(15) DEFAULT '';
DECLARE infsptxt  CHAR(15) DEFAULT '';
DECLARE spppid  CHAR(60) DEFAULT '';
IF famid>0 THEN 
SET famtxt = famid; END IF;
IF genusid>0 THEN SET gentxt = genusid; END IF;
IF spid>0 THEN SET  sptxt = spid; END IF;
IF infspid>0 THEN SET infsptxt = infspid; END IF;
SET spppid = CONCAT(famtxt,'_',gentxt,'_',sptxt,'_',infsptxt);
RETURN spppid;
END
