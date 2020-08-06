create or replace PROCEDURE HRIS_RECALC_MONTHLY_LEAVES(
    P_EMPLOYEE_ID HRIS_ATTENDANCE.EMPLOYEE_ID%TYPE  :=NULL,
    P_LEAVE_ID HRIS_LEAVE_MASTER_SETUP.LEAVE_ID%TYPE:=NULL)
AS
V_BALANCE                     NUMBER;
BEGIN
  UPDATE HRIS_EMPLOYEE_LEAVE_ASSIGN
  SET BALANCE     =TOTAL_DAYS
  WHERE LEAVE_ID IN
    (SELECT LEAVE_ID FROM HRIS_LEAVE_MASTER_SETUP WHERE IS_MONTHLY='Y'
    )AND (EMPLOYEE_ID =
      CASE
        WHEN P_EMPLOYEE_ID IS NOT NULL
        THEN P_EMPLOYEE_ID
      END
    OR P_EMPLOYEE_ID IS NULL)
    AND (LEAVE_ID =
      CASE
        WHEN P_LEAVE_ID IS NOT NULL
        THEN P_LEAVE_ID
      END
    OR P_LEAVE_ID IS NULL);

    -- TO UPDATE MONTHYLY_LEAVE WHERE   CARYY FORWARD IS NO
  FOR leave IN
  (SELECT AA.EMPLOYEE_ID,
    AA.LEAVE_ID,
    AA.LEAVE_YEAR_MONTH_NO,
    SUM(AA.TOTAL_NO_OF_DAYS) AS  TOTAL_NO_OF_DAYS
    FROM(SELECT R.EMPLOYEE_ID,
    R.LEAVE_ID,
    M.LEAVE_YEAR_MONTH_NO,
   CASE WHEN 
   R.HALF_DAY IN ('F','S')
   THEN R.NO_OF_DAYS/2 
   ELSE R.NO_OF_DAYS 
   END  AS TOTAL_NO_OF_DAYS
  FROM HRIS_EMPLOYEE_LEAVE_REQUEST R
   LEFT JOIN (SELECT * FROM HRIS_LEAVE_YEARS  WHERE TRUNC(SYSDATE) BETWEEN START_DATE AND END_DATE ) LY ON (1=1)
  JOIN HRIS_LEAVE_MASTER_SETUP L
  ON (R.LEAVE_ID = L.LEAVE_ID),
    HRIS_LEAVE_MONTH_CODE M
  WHERE R.STATUS   = 'AP'
  AND L.IS_MONTHLY = 'Y'
  AND L.CARRY_FORWARD='N'
  AND R.START_DATE BETWEEN M.FROM_DATE AND M.TO_DATE
  AND R.START_DATE BETWEEN LY.START_DATE AND LY.END_DATE
  AND (R.EMPLOYEE_ID =
      CASE
        WHEN P_EMPLOYEE_ID IS NOT NULL
        THEN P_EMPLOYEE_ID
      END
    OR P_EMPLOYEE_ID IS NULL)
    AND (R.LEAVE_ID =
      CASE
        WHEN P_LEAVE_ID IS NOT NULL
        THEN P_LEAVE_ID
      END
    OR P_LEAVE_ID IS NULL)) AA
  GROUP BY AA.EMPLOYEE_ID,
    AA.LEAVE_ID,
    AA.LEAVE_YEAR_MONTH_NO
  )
  LOOP
    UPDATE HRIS_EMPLOYEE_LEAVE_ASSIGN
    SET BALANCE             = TOTAL_DAYS - leave.TOTAL_NO_OF_DAYS
    WHERE EMPLOYEE_ID       = leave.EMPLOYEE_ID
    AND LEAVE_ID            = leave.LEAVE_ID
    AND FISCAL_YEAR_MONTH_NO=leave.LEAVE_YEAR_MONTH_NO;
  END LOOP;


  -- TO UPDATE MONTHYLY_LEAVE WHERE   CARYY FORWARD IS YES

  FOR leave IN
  (SELECT EMPLOYEE_ID,LEAVE_ID,
SUM(TOTAL_NO_OF_DAYS ) AS TOTAL_NO_OF_DAYS FROM (
SELECT R.EMPLOYEE_ID,
    R.LEAVE_ID,
    SUM(R.NO_OF_DAYS) AS TOTAL_NO_OF_DAYS
  FROM HRIS_EMPLOYEE_LEAVE_REQUEST R
  JOIN HRIS_LEAVE_MASTER_SETUP L
  ON (R.LEAVE_ID = L.LEAVE_ID)
    LEFT JOIN (SELECT * FROM  HRIS_LEAVE_YEARS WHERE TRUNC(SYSDATE) BETWEEN START_DATE AND END_DATE )LY ON (1=1) 
  WHERE R.STATUS   = 'AP'
  AND L.IS_MONTHLY = 'Y'
  AND L.CARRY_FORWARD='Y' 
  AND R.HALF_DAY NOT IN ('F','S')
  AND R.START_DATE BETWEEN LY.START_DATE AND LY.END_DATE
  AND (R.EMPLOYEE_ID =
      CASE
        WHEN P_EMPLOYEE_ID IS NOT NULL
        THEN P_EMPLOYEE_ID
      END
    OR P_EMPLOYEE_ID IS NULL)
    AND (R.LEAVE_ID =
      CASE
        WHEN P_LEAVE_ID IS NOT NULL
        THEN P_LEAVE_ID
      END
    OR P_LEAVE_ID IS NULL)
  GROUP BY R.EMPLOYEE_ID,
    R.LEAVE_ID
    UNION ALL
    SELECT R.EMPLOYEE_ID,
    R.LEAVE_ID,
    SUM(R.NO_OF_DAYS)/2 AS TOTAL_NO_OF_DAYS
  FROM HRIS_EMPLOYEE_LEAVE_REQUEST R
  JOIN HRIS_LEAVE_MASTER_SETUP L
  ON (R.LEAVE_ID = L.LEAVE_ID)
    LEFT JOIN (SELECT * FROM  HRIS_LEAVE_YEARS WHERE TRUNC(SYSDATE) BETWEEN START_DATE AND END_DATE )LY ON (1=1) 
  WHERE R.STATUS   = 'AP'
  AND L.IS_MONTHLY = 'Y'
  AND L.CARRY_FORWARD='Y' 
  AND R.HALF_DAY IN ('F','S')
  AND R.START_DATE BETWEEN LY.START_DATE AND LY.END_DATE
  AND (R.EMPLOYEE_ID =
      CASE
        WHEN P_EMPLOYEE_ID IS NOT NULL
        THEN P_EMPLOYEE_ID
      END
    OR P_EMPLOYEE_ID IS NULL)
    AND (R.LEAVE_ID =
      CASE
        WHEN P_LEAVE_ID IS NOT NULL
        THEN P_LEAVE_ID
      END
    OR P_LEAVE_ID IS NULL)
  GROUP BY R.EMPLOYEE_ID,
    R.LEAVE_ID) GROUP BY EMPLOYEE_ID,LEAVE_ID)
  LOOP

   FOR LEAVE_ASSIGN_DTL IN (
          SELECT
            *
          FROM
            HRIS_EMPLOYEE_LEAVE_ASSIGN
          WHERE
              EMPLOYEE_ID =leave.EMPLOYEE_ID
            AND
              LEAVE_ID =leave.LEAVE_ID
          ORDER BY FISCAL_YEAR_MONTH_NO
        ) LOOP
            V_BALANCE := LEAVE_ASSIGN_DTL.TOTAL_DAYS + (case when LEAVE_ASSIGN_DTL.previous_year_bal is null then 0 else LEAVE_ASSIGN_DTL.previous_year_bal end) -leave.TOTAL_NO_OF_DAYS;
          UPDATE HRIS_EMPLOYEE_LEAVE_ASSIGN
            SET
              BALANCE = V_BALANCE
          WHERE
              EMPLOYEE_ID = LEAVE_ASSIGN_DTL.EMPLOYEE_ID
            AND
              LEAVE_ID = LEAVE_ASSIGN_DTL.LEAVE_ID
            AND
              FISCAL_YEAR_MONTH_NO = LEAVE_ASSIGN_DTL.FISCAL_YEAR_MONTH_NO;

        END LOOP;


  END LOOP;





END;