CREATE OR REPLACE FUNCTION BOOLEAN_DESC(
    P_FLAG CHAR)
  RETURN VARCHAR2
IS
  V_FLAG_DESC VARCHAR2(50 BYTE);
BEGIN
  V_FLAG_DESC:=
  (
    CASE P_FLAG
    WHEN 'Y' THEN
      'Yes'
    WHEN 'N'THEN
      'No'
    END);
  RETURN V_FLAG_DESC;
END; 