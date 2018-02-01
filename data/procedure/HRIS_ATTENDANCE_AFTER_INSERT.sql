create or replace PROCEDURE HRIS_ATTENDANCE_AFTER_INSERT(
    P_EMPLOYEE_ID HRIS_ATTENDANCE.EMPLOYEE_ID%TYPE,
    P_ATTENDANCE_DT HRIS_ATTENDANCE.ATTENDANCE_DT%TYPE,
    P_ATTENDANCE_TIME HRIS_ATTENDANCE.ATTENDANCE_TIME%TYPE,
    P_REMARKS HRIS_ATTENDANCE.REMARKS%TYPE)
AS
  V_IN_TIME HRIS_ATTENDANCE_DETAIL.IN_TIME%TYPE;
  V_SHIFT_ID HRIS_SHIFTS.SHIFT_ID%TYPE;
  V_OVERALL_STATUS HRIS_ATTENDANCE_DETAIL.OVERALL_STATUS%TYPE;
  V_LATE_STATUS HRIS_ATTENDANCE_DETAIL.LATE_STATUS%TYPE:='N';
  V_HALFDAY_FLAG HRIS_ATTENDANCE_DETAIL.HALFDAY_FLAG%TYPE;
  V_HALFDAY_PERIOD HRIS_ATTENDANCE_DETAIL.HALFDAY_PERIOD%TYPE;
  V_GRACE_PERIOD HRIS_ATTENDANCE_DETAIL.GRACE_PERIOD%TYPE;
  V_LATE_IN HRIS_SHIFTS.LATE_IN%TYPE;
  V_EARLY_OUT HRIS_SHIFTS.EARLY_OUT%TYPE;
  V_LATE_START_TIME TIMESTAMP;
  V_EARLY_END_TIME  TIMESTAMP;
  V_ADJUSTED_START_TIME HRIS_SHIFT_ADJUSTMENT.START_TIME%TYPE:=NULL;
  V_ADJUSTED_END_TIME HRIS_SHIFT_ADJUSTMENT.END_TIME%TYPE    :=NULL;
  V_LATE_COUNT NUMBER                                        :=0;
  V_TOTAL_HOUR NUMBER                                        :=0;
  V_TWO_DAY_SHIFT HRIS_ATTENDANCE_DETAIL.TWO_DAY_SHIFT%TYPE;
  V_HALF_INTERVAL DATE;
BEGIN
  --
  IF TRUNC(P_ATTENDANCE_DT) < TRUNC(SYSDATE) THEN
    HRIS_REATTENDANCE(TRUNC(P_ATTENDANCE_DT),P_EMPLOYEE_ID,TRUNC(SYSDATE-1));
    RETURN;
  END IF;
  --
  BEGIN
    SELECT SHIFT_ID,
      OVERALL_STATUS,
      LATE_STATUS,
      HALFDAY_FLAG,
      HALFDAY_PERIOD,
      GRACE_PERIOD,
      IN_TIME,
      HALFDAY_PERIOD,
      TWO_DAY_SHIFT
    INTO V_SHIFT_ID,
      V_OVERALL_STATUS,
      V_LATE_STATUS,
      V_HALFDAY_FLAG,
      V_HALFDAY_PERIOD,
      V_GRACE_PERIOD,
      V_IN_TIME,
      V_HALFDAY_PERIOD,
      V_TWO_DAY_SHIFT
    FROM HRIS_ATTENDANCE_DETAIL
    WHERE ATTENDANCE_DT = TRUNC(P_ATTENDANCE_DT)
    AND EMPLOYEE_ID     = P_EMPLOYEE_ID;
    IF V_TWO_DAY_SHIFT  ='E' THEN
      --
      SELECT S.LATE_IN,
        S.EARLY_OUT,
        S.START_TIME+((1/1440)*NVL(S.LATE_IN,0)),
        S.END_TIME  -((1/1440)*NVL(S.EARLY_OUT,0))
      INTO V_LATE_IN,
        V_EARLY_OUT,
        V_LATE_START_TIME,
        V_EARLY_END_TIME
      FROM HRIS_SHIFTS S
      WHERE S.SHIFT_ID=V_SHIFT_ID ;
      --
      SELECT V_EARLY_END_TIME + (V_LATE_START_TIME -V_EARLY_END_TIME)/2
      INTO V_HALF_INTERVAL
      FROM DUAL;
      IF (TO_DATE(TO_CHAR(P_ATTENDANCE_TIME,'HH:MI AM'),'HH:MI AM')) < (TO_DATE(TO_CHAR(V_HALF_INTERVAL,'HH:MI AM'),'HH:MI AM')) THEN
        SELECT OVERALL_STATUS,
          LATE_STATUS,
          HALFDAY_FLAG,
          HALFDAY_PERIOD,
          GRACE_PERIOD,
          IN_TIME,
          HALFDAY_PERIOD,
          GRACE_PERIOD
        INTO V_OVERALL_STATUS,
          V_LATE_STATUS,
          V_HALFDAY_FLAG,
          V_HALFDAY_PERIOD,
          V_GRACE_PERIOD,
          V_IN_TIME,
          V_HALFDAY_PERIOD,
          V_GRACE_PERIOD
        FROM HRIS_ATTENDANCE_DETAIL
        WHERE ATTENDANCE_DT = TRUNC(P_ATTENDANCE_DT-1)
        AND EMPLOYEE_ID     = P_EMPLOYEE_ID;
      END IF;
      --
    END IF;
  EXCEPTION
  WHEN NO_DATA_FOUND THEN
    DBMS_OUTPUT.PUT_LINE ('Attendance Job for '||P_ATTENDANCE_DT||' not excecuted');
    RETURN;
  END;
  --
  BEGIN
    IF V_HALFDAY_PERIOD IS NOT NULL THEN
      SELECT S.LATE_IN,
        S.EARLY_OUT,
        (
        CASE
          WHEN V_HALFDAY_PERIOD ='F'
          THEN S.HALF_DAY_IN_TIME
          ELSE S.START_TIME
        END ) +((1/1440)*NVL(S.LATE_IN,0)),
        (
        CASE
          WHEN V_HALFDAY_PERIOD ='F'
          THEN S.END_TIME
          ELSE S.HALF_DAY_OUT_TIME
        END ) -((1/1440)*NVL(S.EARLY_OUT,0))
      INTO V_LATE_IN,
        V_EARLY_OUT,
        V_LATE_START_TIME,
        V_EARLY_END_TIME
      FROM HRIS_SHIFTS S
      WHERE S.SHIFT_ID    =V_SHIFT_ID ;
    ELSIF V_GRACE_PERIOD IS NOT NULL THEN
      SELECT S.LATE_IN,
        S.EARLY_OUT,
        (
        CASE
          WHEN V_GRACE_PERIOD ='E'
          THEN S.GRACE_START_TIME
          ELSE S.START_TIME
        END) +((1/1440)*NVL(S.LATE_IN,0)),
        (
        CASE
          WHEN V_GRACE_PERIOD ='E'
          THEN S.END_TIME
          ELSE S.GRACE_END_TIME
        END) -((1/1440)*NVL(S.EARLY_OUT,0))
      INTO V_LATE_IN,
        V_EARLY_OUT,
        V_LATE_START_TIME,
        V_EARLY_END_TIME
      FROM HRIS_SHIFTS S
      WHERE S.SHIFT_ID=V_SHIFT_ID ;
    ELSE
      SELECT S.LATE_IN,
        S.EARLY_OUT,
        S.START_TIME+((1/1440)*NVL(S.LATE_IN,0)),
        S.END_TIME  -((1/1440)*NVL(S.EARLY_OUT,0))
      INTO V_LATE_IN,
        V_EARLY_OUT,
        V_LATE_START_TIME,
        V_EARLY_END_TIME
      FROM HRIS_SHIFTS S
      WHERE S.SHIFT_ID=V_SHIFT_ID ;
    END IF;
  EXCEPTION
  WHEN NO_DATA_FOUND THEN
    RAISE_APPLICATION_ERROR(-20344, 'SHIFT WITH SHIFT_ID => '|| V_SHIFT_ID ||' NOT FOUND.');
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
    WHERE (TRUNC(P_ATTENDANCE_DT) BETWEEN TRUNC(SA.ADJUSTMENT_START_DATE) AND TRUNC(SA.ADJUSTMENT_END_DATE) )
    AND ESA.EMPLOYEE_ID =P_EMPLOYEE_ID;
  EXCEPTION
  WHEN NO_DATA_FOUND THEN
    DBMS_OUTPUT.PUT_LINE('NO ADJUSTMENT FOUND FOR EMPLOYEE =>'|| P_EMPLOYEE_ID || 'ON THE DATE'||P_ATTENDANCE_DT);
  END;
  IF(V_ADJUSTED_START_TIME IS NOT NULL) THEN
    V_LATE_START_TIME      :=V_ADJUSTED_START_TIME;
    V_LATE_START_TIME      := V_LATE_START_TIME+((1/1440)*NVL(V_LATE_IN,0));
  END IF;
  IF(V_ADJUSTED_END_TIME IS NOT NULL) THEN
    V_EARLY_END_TIME     :=V_ADJUSTED_END_TIME;
    V_EARLY_END_TIME     := V_EARLY_END_TIME -((1/1440)*NVL(V_EARLY_OUT,0));
  END IF;
  --      END FOR CHECK FOR ADJUSTED_SHIFT
  IF(V_TWO_DAY_SHIFT = 'E') THEN
    --    two day shift
    IF (TO_DATE(TO_CHAR(P_ATTENDANCE_TIME,'HH:MI AM'),'HH:MI AM')) < (TO_DATE(TO_CHAR(V_HALF_INTERVAL,'HH:MI AM'),'HH:MI AM')) THEN
      IF V_OVERALL_STATUS                                          ='PR' AND (V_EARLY_END_TIME-TRUNC(V_EARLY_END_TIME))>(P_ATTENDANCE_TIME-TRUNC(P_ATTENDANCE_TIME)) THEN
        IF (V_LATE_STATUS                                          = 'L') THEN
          V_LATE_STATUS                                           :='B';
        ELSE
          V_LATE_STATUS :='E';
        END IF;
      ELSE
        IF (V_LATE_STATUS     ='B') THEN
          V_LATE_STATUS      :='L';
        ELSIF ( V_LATE_STATUS ='E') THEN
          V_LATE_STATUS      := 'N';
        END IF;
      END IF;
      --
      SELECT SUM(ABS(EXTRACT( HOUR FROM DIFF ))*60 + ABS(EXTRACT( MINUTE FROM DIFF )))
      INTO V_TOTAL_HOUR
      FROM
        (SELECT (P_ATTENDANCE_TIME-TRUNC(P_ATTENDANCE_TIME))-(V_IN_TIME-TRUNC(V_IN_TIME)) AS DIFF
        FROM DUAL
        ) ;
      UPDATE HRIS_ATTENDANCE_DETAIL
      SET OUT_TIME        = TO_DATE ( TO_CHAR (P_ATTENDANCE_TIME, 'DD-MON-YY HH:MI AM'), 'DD-MON-YY HH:MI AM'),
        LATE_STATUS       =V_LATE_STATUS,
        OUT_REMARKS       = P_REMARKS,
        TOTAL_HOUR        =V_TOTAL_HOUR
      WHERE ATTENDANCE_DT = TRUNC(P_ATTENDANCE_DT-1)
      AND EMPLOYEE_ID     = P_EMPLOYEE_ID;
    ELSE
      IF (V_IN_TIME            IS NULL) THEN
        IF(V_OVERALL_STATUS     ='DO') THEN
          V_OVERALL_STATUS     :='WD';
        ELSIF (V_OVERALL_STATUS ='HD') THEN
          V_OVERALL_STATUS     :='WH';
        ELSIF (V_OVERALL_STATUS ='LV') THEN
          NULL;
        ELSIF (V_OVERALL_STATUS ='TV') THEN
          NULL;
        ELSIF (V_OVERALL_STATUS ='TN') THEN
          NULL;
        ELSIF(V_HALFDAY_FLAG   !='Y' AND V_HALFDAY_PERIOD IS NOT NULL) OR V_GRACE_PERIOD IS NOT NULL THEN
          V_OVERALL_STATUS     :='LP';
        ELSIF (V_OVERALL_STATUS ='AB') THEN
          V_OVERALL_STATUS     :='PR';
        END IF;
        IF V_OVERALL_STATUS = 'PR' AND ( V_LATE_START_TIME-TRUNC(V_LATE_START_TIME))<(P_ATTENDANCE_TIME-TRUNC(P_ATTENDANCE_TIME)) THEN
          V_LATE_STATUS    :='L';
        END IF;
        --
        UPDATE HRIS_ATTENDANCE_DETAIL
        SET IN_TIME         = TO_DATE ( TO_CHAR (P_ATTENDANCE_TIME, 'DD-MON-YY HH:MI AM'), 'DD-MON-YY HH:MI AM'),
          OVERALL_STATUS    = V_OVERALL_STATUS,
          LATE_STATUS       = V_LATE_STATUS,
          IN_REMARKS        = P_REMARKS
        WHERE ATTENDANCE_DT = TRUNC (P_ATTENDANCE_DT)
        AND EMPLOYEE_ID     = P_EMPLOYEE_ID;
        RETURN;
      END IF;
      --
    END IF;
    --      end for two day shift
  ELSE
    --    nornal shift
    IF (V_IN_TIME            IS NULL) THEN
      IF(V_OVERALL_STATUS     ='DO') THEN
        V_OVERALL_STATUS     :='WD';
      ELSIF (V_OVERALL_STATUS ='HD') THEN
        V_OVERALL_STATUS     :='WH';
      ELSIF (V_OVERALL_STATUS ='LV') THEN
        NULL;
      ELSIF (V_OVERALL_STATUS ='TV') THEN
        NULL;
      ELSIF (V_OVERALL_STATUS ='TN') THEN
        NULL;
      ELSIF(V_HALFDAY_FLAG !='Y' AND V_HALFDAY_PERIOD IS NOT NULL) OR V_GRACE_PERIOD IS NOT NULL THEN
        V_OVERALL_STATUS   :='LP';
      ELSE
        V_OVERALL_STATUS :='PR';
      END IF;
      IF V_OVERALL_STATUS = 'PR' AND ( V_LATE_START_TIME-TRUNC(V_LATE_START_TIME))<(P_ATTENDANCE_TIME-TRUNC(P_ATTENDANCE_TIME)) THEN
        V_LATE_STATUS    :='L';
      END IF;
      --
      UPDATE HRIS_ATTENDANCE_DETAIL
      SET IN_TIME         = TO_DATE ( TO_CHAR (P_ATTENDANCE_TIME, 'DD-MON-YY HH:MI AM'), 'DD-MON-YY HH:MI AM'),
        OVERALL_STATUS    = V_OVERALL_STATUS,
        LATE_STATUS       = V_LATE_STATUS,
        IN_REMARKS        = P_REMARKS
      WHERE ATTENDANCE_DT = TO_DATE (P_ATTENDANCE_DT, 'DD-MON-YY')
      AND EMPLOYEE_ID     = P_EMPLOYEE_ID;
      RETURN;
    END IF;
    --
    IF V_OVERALL_STATUS ='PR' AND (V_EARLY_END_TIME-TRUNC(V_EARLY_END_TIME))>(P_ATTENDANCE_TIME-TRUNC(P_ATTENDANCE_TIME)) THEN
      IF (V_LATE_STATUS = 'L') THEN
        V_LATE_STATUS  :='B';
      ELSE
        V_LATE_STATUS :='E';
      END IF;
    ELSE
      IF (V_LATE_STATUS     ='B') THEN
        V_LATE_STATUS      :='L';
      ELSIF ( V_LATE_STATUS ='E') THEN
        V_LATE_STATUS      := 'N';
      END IF;
    END IF;
    --
    SELECT SUM(ABS(EXTRACT( HOUR FROM DIFF ))*60 + ABS(EXTRACT( MINUTE FROM DIFF )))
    INTO V_TOTAL_HOUR
    FROM
      (SELECT (P_ATTENDANCE_TIME-TRUNC(P_ATTENDANCE_TIME))-(V_IN_TIME-TRUNC(V_IN_TIME)) AS DIFF
      FROM DUAL
      ) ;
    UPDATE HRIS_ATTENDANCE_DETAIL
    SET OUT_TIME        = TO_DATE ( TO_CHAR (P_ATTENDANCE_TIME, 'DD-MON-YY HH:MI AM'), 'DD-MON-YY HH:MI AM'),
      LATE_STATUS       =V_LATE_STATUS,
      OUT_REMARKS       = P_REMARKS,
      TOTAL_HOUR        =V_TOTAL_HOUR
    WHERE ATTENDANCE_DT = TO_DATE (P_ATTENDANCE_DT, 'DD-MON-YY')
    AND EMPLOYEE_ID     = P_EMPLOYEE_ID;
    --      end of nornal shift
  END IF;
END;