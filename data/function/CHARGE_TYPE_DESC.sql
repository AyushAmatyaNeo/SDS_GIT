create or replace FUNCTION CHARGE_TYPE_DESC(
    P_CHARGE_TYPE CHAR)
  RETURN VARCHAR2
IS
  V_CHARGE_TYPE_DESC VARCHAR2(50 BYTE);
BEGIN
  V_CHARGE_TYPE_DESC:=
  (
    CASE P_CHARGE_TYPE
    WHEN 'H' THEN
      'Hourly'
    WHEN 'D'THEN
      'Day wise'
    WHEN 'W' THEN
      'Weekly'
    WHEN 'M' THEN
      'Monthly'
    END);
  RETURN V_CHARGE_TYPE_DESC;
END;