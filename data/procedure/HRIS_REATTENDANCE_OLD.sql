CREATE OR REPLACE PROCEDURE HRIS_REATTENDANCE(
    P_FROM_ATTENDANCE_DT HRIS_ATTENDANCE.ATTENDANCE_DT%TYPE,
    P_EMPLOYEE_ID HRIS_ATTENDANCE.EMPLOYEE_ID%TYPE:=NULL )
AS
  V_DATE_DIFF NUMBER:= TRUNC(SYSDATE)- TRUNC(P_FROM_ATTENDANCE_DT);
  --
  V_EMPLOYEE_ID HRIS_EMPLOYEES.EMPLOYEE_ID%TYPE;
  V_IN_TIME HRIS_ATTENDANCE_DETAIL.IN_TIME%TYPE;
  V_OUT_TIME HRIS_ATTENDANCE_DETAIL.OUT_TIME%TYPE;
  V_DIFF_IN_MIN NUMBER;
  --
  V_OVERALL_STATUS HRIS_ATTENDANCE_DETAIL.OVERALL_STATUS%TYPE;
  V_LATE_STATUS HRIS_ATTENDANCE_DETAIL.LATE_STATUS%TYPE:='N';
  --
  V_FROM_DATE DATE;
  V_TO_DATE   DATE;
  --
  V_START_TIME HRIS_SHIFTS.START_TIME%TYPE ;
  V_END_TIME HRIS_SHIFTS.END_TIME%TYPE ;
  V_GRACE_START_TIME HRIS_SHIFTS.GRACE_START_TIME%TYPE;
  V_GRACE_END_TIME HRIS_SHIFTS.GRACE_END_TIME%TYPE ;
  V_LATE_IN HRIS_SHIFTS.LATE_IN%TYPE;
  V_EARLY_OUT HRIS_SHIFTS.EARLY_OUT%TYPE;
  V_LATE_START_TIME TIMESTAMP;
  V_EARLY_END_TIME  TIMESTAMP;
  V_ADJUSTED_START_TIME HRIS_SHIFT_ADJUSTMENT.START_TIME%TYPE:=NULL;
  V_ADJUSTED_END_TIME HRIS_SHIFT_ADJUSTMENT.END_TIME%TYPE    :=NULL;
  --
  V_LATE_COUNT NUMBER;
BEGIN
  FOR i IN 0..V_DATE_DIFF
  LOOP
    BEGIN
      SELECT FROM_DATE,
        TO_DATE
      INTO V_FROM_DATE,
        V_TO_DATE
      FROM HRIS_MONTH_CODE
      WHERE TRUNC(P_FROM_ATTENDANCE_DT+i) BETWEEN TRUNC(FROM_DATE) AND TRUNC(TO_DATE);
    EXCEPTION
    WHEN NO_DATA_FOUND THEN
      RAISE_APPLICATION_ERROR(-20344, 'NO MONTH_CODE FOUND FOR THE DATE');
    END;
    DELETE
    FROM HRIS_ATTENDANCE_DETAIL
    WHERE ATTENDANCE_DT= TRUNC(P_FROM_ATTENDANCE_DT+i)
    AND (EMPLOYEE_ID   =
      CASE
        WHEN P_EMPLOYEE_ID IS NOT NULL
        THEN P_EMPLOYEE_ID
      END
    OR P_EMPLOYEE_ID IS NULL) ;
    --
    HRIS_PRELOAD_ATTENDANCE(P_FROM_ATTENDANCE_DT+i,P_EMPLOYEE_ID,HRIS_BEST_CASE_SHIFT(P_EMPLOYEE_ID,P_FROM_ATTENDANCE_DT+i));
    --
    FOR employee IN
    (SELECT       *
    FROM HRIS_ATTENDANCE_DETAIL
    WHERE ATTENDANCE_DT = P_FROM_ATTENDANCE_DT+i
    AND (EMPLOYEE_ID    =
      CASE
        WHEN P_EMPLOYEE_ID IS NOT NULL
        THEN P_EMPLOYEE_ID
      END
    OR P_EMPLOYEE_ID IS NULL)
    )
    LOOP
      V_DIFF_IN_MIN    :=NULL;
      V_OVERALL_STATUS :=employee.OVERALL_STATUS;
      V_LATE_STATUS    :=employee.LATE_STATUS;
      --
      SELECT MIN(TO_DATE(TO_CHAR(ATTENDANCE_TIME,'HH:MI AM'),'HH:MI AM')) AS IN_TIME,
        MAX(TO_DATE(TO_CHAR(ATTENDANCE_TIME,'HH:MI AM'),'HH:MI AM')) OUT_TIME
      INTO V_IN_TIME,
        V_OUT_TIME
      FROM HRIS_ATTENDANCE
      WHERE ATTENDANCE_DT =TRUNC(employee.ATTENDANCE_DT)
      AND EMPLOYEE_ID     = employee.EMPLOYEE_ID ;
      --
      IF V_IN_TIME IS NULL THEN
        CONTINUE;
      END IF ;
      --
      IF V_IN_TIME  = V_OUT_TIME THEN
        V_OUT_TIME := NULL;
      END IF;
      --
      IF V_OUT_TIME IS NOT NULL THEN
        SELECT SUM(ABS(EXTRACT( HOUR FROM DIFF ))*60 + ABS(EXTRACT( MINUTE FROM DIFF )))
        INTO V_DIFF_IN_MIN
        FROM
          (SELECT V_OUT_TIME -V_IN_TIME AS DIFF FROM DUAL
          ) ;
      END IF;
      --
      BEGIN
        SELECT S.START_TIME ,
          S.END_TIME ,
          S.GRACE_START_TIME,
          S.GRACE_END_TIME ,
          S.LATE_IN,
          S.EARLY_OUT,
          S.START_TIME+(.000694*NVL(S.LATE_IN,0)),
          S.END_TIME  -(.000694*NVL(S.EARLY_OUT,0))
        INTO V_START_TIME,
          V_END_TIME,
          V_GRACE_START_TIME,
          V_GRACE_END_TIME,
          V_LATE_IN,
          V_EARLY_OUT,
          V_LATE_START_TIME,
          V_EARLY_END_TIME
        FROM HRIS_SHIFTS S
        WHERE S.SHIFT_ID=employee.SHIFT_ID ;
      EXCEPTION
      WHEN NO_DATA_FOUND THEN
        RAISE_APPLICATION_ERROR(-20344, 'SHIFT WITH SHIFT_ID => '|| employee.SHIFT_ID ||' NOT FOUND.');
      END;
      --   CHECK FOR ADJUSTED SHIFT
      BEGIN
        SELECT SA.START_TIME,
          SA.END_TIME
        INTO V_ADJUSTED_START_TIME,
          V_ADJUSTED_END_TIME
        FROM HRIS_SHIFT_ADJUSTMENT SA
        JOIN HRIS_EMPLOYEE_SHIFT_ADJUSTMENT ESA
        ON (SA.ADJUSTMENT_ID=ESA.ADJUSTMENT_ID)
        WHERE (TRUNC(employee.ATTENDANCE_DT) BETWEEN TRUNC(SA.ADJUSTMENT_START_DATE) AND TRUNC(SA.ADJUSTMENT_END_DATE) )
        AND ESA.EMPLOYEE_ID       =employee.EMPLOYEE_ID;
        IF(V_ADJUSTED_START_TIME IS NOT NULL) THEN
          V_START_TIME           :=V_ADJUSTED_START_TIME;
          V_LATE_START_TIME      := V_START_TIME+(.000694*NVL(V_LATE_IN,0));
        END IF;
        IF(V_ADJUSTED_END_TIME IS NOT NULL) THEN
          V_END_TIME           :=V_ADJUSTED_END_TIME;
          V_EARLY_END_TIME     := V_END_TIME -(.000694*NVL(V_EARLY_OUT,0));
        END IF;
      EXCEPTION
      WHEN NO_DATA_FOUND THEN
        DBMS_OUTPUT.PUT_LINE('NO ADJUSTMENT FOUND FOR EMPLOYEE =>'|| employee.EMPLOYEE_ID || 'ON THE DATE'||employee.ATTENDANCE_DT);
      END;
      --      END FOR CHECK FOR ADJUSTED_SHIFT
      IF(V_OVERALL_STATUS     ='DO') THEN
        V_OVERALL_STATUS     :='WD';
      ELSIF (V_OVERALL_STATUS ='HD') THEN
        V_OVERALL_STATUS     :='WH';
      ELSIF (V_OVERALL_STATUS ='LV') THEN
        V_OVERALL_STATUS     :='LP';
      ELSIF (V_OVERALL_STATUS ='TV') THEN
        V_OVERALL_STATUS     :='VP';
      ELSIF (V_OVERALL_STATUS ='TN') THEN
        V_OVERALL_STATUS     :='TP';
      ELSE
        V_OVERALL_STATUS :='PR';
      END IF;
      --
      IF (V_LATE_START_TIME-TRUNC(V_LATE_START_TIME))<(V_IN_TIME-TRUNC(V_IN_TIME)) THEN
        V_LATE_STATUS                               :='L';
      END IF;
      --
      IF (V_EARLY_END_TIME-TRUNC(V_EARLY_END_TIME))>(V_OUT_TIME-TRUNC(V_OUT_TIME)) THEN
        IF (V_LATE_STATUS                          = 'L') THEN
          V_LATE_STATUS                           :='B';
        ELSE
          V_LATE_STATUS :='E';
        END IF;
      END IF;
      --
      IF i                 <V_DATE_DIFF THEN
        IF V_IN_TIME      IS NOT NULL AND V_OUT_TIME IS NULL THEN
          IF V_LATE_STATUS ='L' THEN
            V_LATE_STATUS := 'Y';
          ELSE
            V_LATE_STATUS := 'X';
          END IF;
        END IF;
        --
        SELECT COUNT(*)
        INTO V_LATE_COUNT
        FROM HRIS_ATTENDANCE_DETAIL
        WHERE EMPLOYEE_ID = employee.EMPLOYEE_ID
        AND (ATTENDANCE_DT BETWEEN V_FROM_DATE AND employee.ATTENDANCE_DT )
        AND OVERALL_STATUS           IN ('PR','LA')
        AND LATE_STATUS              IN ('E','L','Y') ;
        IF V_LATE_STATUS             IN ('E','L','Y') THEN
          V_LATE_COUNT       := V_LATE_COUNT+1;
          IF V_LATE_COUNT    != 0 AND MOD(V_LATE_COUNT,4)=0 THEN
            V_OVERALL_STATUS := 'LA';
          END IF;
        END IF;
        --
        IF V_LATE_STATUS   ='B' AND V_OVERALL_STATUS='PR' THEN
          V_OVERALL_STATUS:='BA';
        END IF;
      END IF;
      --
      UPDATE HRIS_ATTENDANCE_DETAIL
      SET IN_TIME         = V_IN_TIME,
        OUT_TIME          =V_OUT_TIME,
        OVERALL_STATUS    = V_OVERALL_STATUS,
        LATE_STATUS       = V_LATE_STATUS
      WHERE ATTENDANCE_DT = TO_DATE (employee.ATTENDANCE_DT, 'DD-MON-YY')
      AND EMPLOYEE_ID     = employee.EMPLOYEE_ID;
      --
    END LOOP;
  END LOOP;
END;