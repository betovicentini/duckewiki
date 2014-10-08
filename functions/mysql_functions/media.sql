CREATE FUNCTION media(nome VARCHAR(10)) RETURNS float
DETERMINISTIC
BEGIN
DECLARE n1,n2,n3,n4 INT;
DECLARE med FLOAT;
SELECT nota1,nota2,nota3,nota4 INTO n1,n2,n3,n4 FROM notas WHERE aluno = nome;
SET med = (n1+n2+n3+n4)/4;
RETURN med;
END
