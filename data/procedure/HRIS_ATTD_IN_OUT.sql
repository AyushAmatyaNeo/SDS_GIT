create or replace PROCEDURE HRIS_ATTD_IN_OUT(
    P_EMPLOYEE_ID          IN HRIS_ATTENDANCE_DETAIL.EMPLOYEE_ID%TYPE,
    P_FROM_ATTENDANCE_TIME IN TIMESTAMP,
    P_TO_ATTENDANCE_TIME   IN TIMESTAMP,
    P_IN_TIME OUT HRIS_ATTENDANCE_DETAIL.IN_TIME%TYPE,
    P_OUT_TIME OUT HRIS_ATTENDANCE_DETAIL.OUT_TIME%TYPE)
AS
  V_IN_TIME  TIMESTAMP;
  V_OUT_TIME TIMESTAMP;
BEGIN
  SELECT MIN(A.ATTENDANCE_TIME) AS IN_TIME,
    MAX(A.ATTENDANCE_TIME)      AS OUT_TIME
  INTO P_IN_TIME,
    P_OUT_TIME
  FROM HRIS_ATTENDANCE A
  WHERE (A.ATTENDANCE_TIME >= P_FROM_ATTENDANCE_TIME
  AND A.ATTENDANCE_TIME    <= P_TO_ATTENDANCE_TIME)
  AND A.EMPLOYEE_ID         = P_EMPLOYEE_ID;
  --
  SELECT MIN(TO_DATE(TO_CHAR(A.ATTENDANCE_TIME,'HH:MI AM'),'HH:MI AM')) AS IN_TIME
  INTO V_IN_TIME
  FROM HRIS_ATTENDANCE A
  LEFT JOIN HRIS_ATTD_DEVICE_MASTER ADM
  ON (A.IP_ADDRESS=ADM.DEVICE_IP)
  WHERE (A.ATTENDANCE_TIME >= P_FROM_ATTENDANCE_TIME
  AND A.ATTENDANCE_TIME    <= P_TO_ATTENDANCE_TIME)
  AND A.EMPLOYEE_ID = P_EMPLOYEE_ID
  AND ADM.PURPOSE   ='IN';
  --
  SELECT MAX(TO_DATE(TO_CHAR(A.ATTENDANCE_TIME,'HH:MI AM'),'HH:MI AM')) AS IN_TIME
  INTO V_OUT_TIME
  FROM HRIS_ATTENDANCE A
  LEFT JOIN HRIS_ATTD_DEVICE_MASTER ADM
  ON (A.IP_ADDRESS=ADM.DEVICE_IP)
  WHERE (A.ATTENDANCE_TIME >= P_FROM_ATTENDANCE_TIME
  AND A.ATTENDANCE_TIME    <= P_TO_ATTENDANCE_TIME)
  AND A.EMPLOYEE_ID = P_EMPLOYEE_ID
  AND ADM.PURPOSE   ='OUT';
  --
  IF V_IN_TIME IS NOT NULL THEN
    P_IN_TIME  :=V_IN_TIME;
  END IF;
  --
  IF V_OUT_TIME IS NOT NULL THEN
    P_OUT_TIME  :=V_OUT_TIME;
  END IF;
END;