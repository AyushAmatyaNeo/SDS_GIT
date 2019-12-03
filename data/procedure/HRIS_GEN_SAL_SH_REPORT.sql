CREATE OR REPLACE PROCEDURE HRIS_GEN_SAL_SH_REPORT(
P_SHEET_NO HRIS_SALARY_SHEET.SHEET_NO%TYPE )
AS
V_GENERATED_COUNT NUMBER;
BEGIN
SELECT COUNT(*)
INTO V_GENERATED_COUNT
FROM HRIS_SALARY_SHEET_EMP_DETAIL
WHERE SHEET_NO = P_SHEET_NO;
--
IF V_GENERATED_COUNT >0 THEN
DELETE FROM HRIS_SALARY_SHEET_EMP_DETAIL WHERE SHEET_NO=P_SHEET_NO;
END IF;
INSERT INTO HRIS_SALARY_SHEET_EMP_DETAIL
SELECT SS.SHEET_NO,
SS.MONTH_ID,
SS.YEAR,
SS.MONTH_NO,
SS.START_DATE,
SS.END_DATE,
(TRUNC(SS.END_DATE )-TRUNC(SS.START_DATE))+1 AS TOTAL_DAYS,
A.EMPLOYEE_ID,
E.FULL_NAME,
A.DAYOFF,
A.PRESENT,
A.HOLIDAY,
A.LEAVE,
A.PAID_LEAVE,
A.UNPAID_LEAVE,
A.ABSENT,
NVL(ROUND(OT.TOTAL_MIN/60,2),0) AS OVERTIME_HOUR,
A.TRAVEL,
A.TRAINING,
A.WORK_ON_HOLIDAY,
A.WORK_ON_DAYOFF,
(
CASE
WHEN EHM.SALARY IS NULL
THEN E.SALARY
ELSE EHM.SALARY
END),
(
CASE
WHEN EHM.MARITAL_STATUS IS NULL
THEN E.MARITAL_STATUS
ELSE EHM.MARITAL_STATUS
END) ,
(
CASE
WHEN (
CASE
WHEN EHM.MARITAL_STATUS IS NULL
THEN E.MARITAL_STATUS
ELSE EHM.MARITAL_STATUS
END) ='M'
THEN 'MARRIED'
ELSE 'UNMARRIED'
END) AS MARITAL_STATUS_DESC,
E.GENDER_ID,
G.GENDER_CODE,
G.GENDER_NAME,
E.JOIN_DATE,
C.COMPANY_ID,
C.COMPANY_NAME,
B.BRANCH_ID,
B.BRANCH_NAME,
DEP.DEPARTMENT_ID,
DEP.DEPARTMENT_NAME,
DES.DESIGNATION_ID,
DES.DESIGNATION_TITLE,
P.POSITION_ID,
P.POSITION_NAME,
P.LEVEL_NO,
ST.SERVICE_TYPE_ID,
ST.SERVICE_TYPE_NAME,
ST.TYPE AS SERVICE_TYPE,
(
CASE
WHEN ST.SERVICE_TYPE_NAME='Permanent'
THEN 'Y'
ELSE 'N'
END),
E.PERMANENT_DATE,
E.ADDR_PERM_STREET_ADDRESS,
E.ID_ACCOUNT_NO,
FT.FUNCTIONAL_TYPE_ID,
FT.FUNCTIONAL_TYPE_EDESC 
FROM
(SELECT SS.EMPLOYEE_ID,
SUM(
CASE
WHEN A.OVERALL_STATUS IN( 'DO','WD')
THEN 1
ELSE 0
END) AS DAYOFF,
SUM(
CASE
WHEN A.OVERALL_STATUS IN ('PR','BA','LA','TV','VP','TN','TP')
THEN 1
ELSE 0
END) AS PRESENT,
SUM(
CASE
WHEN A.OVERALL_STATUS IN ('HD','WH')
THEN 1
ELSE 0
END) AS HOLIDAY,
SUM(
CASE
WHEN A.OVERALL_STATUS IN ('LV','LP')
THEN 1
ELSE 0
END) AS LEAVE,
SUM(
CASE
WHEN L.PAID = 'Y'
THEN 1
ELSE 0
END) AS PAID_LEAVE,
SUM(
CASE
WHEN L.PAID = 'N'
THEN 1
ELSE 0
END) AS UNPAID_LEAVE,
SUM(
CASE
WHEN A.OVERALL_STATUS = 'AB'
THEN 1
ELSE 0
END) AS ABSENT,
SUM(
CASE
WHEN A.OVERALL_STATUS= 'TV'
THEN 1
ELSE 0
END) AS TRAVEL,
SUM(
CASE
WHEN A.OVERALL_STATUS ='TN'
THEN 1
ELSE 0
END) AS TRAINING,
SUM(
CASE
WHEN A.OVERALL_STATUS = 'WH'
THEN 1
ELSE 0
END) WORK_ON_HOLIDAY,
SUM(
CASE
WHEN A.OVERALL_STATUS ='WD'
THEN 1
ELSE 0
END) WORK_ON_DAYOFF
FROM
(SELECT SS.*,
E.EMPLOYEE_ID
FROM HRIS_SALARY_SHEET SS
JOIN HRIS_EMPLOYEES E
ON (SS.COMPANY_ID=E.COMPANY_ID
AND SS.GROUP_ID = E.GROUP_ID)
WHERE SS.SHEET_NO=P_SHEET_NO
) SS
LEFT JOIN HRIS_ATTENDANCE_DETAIL A
ON (( A.ATTENDANCE_DT BETWEEN SS.START_DATE AND SS.END_DATE)
AND (SS.EMPLOYEE_ID = A.EMPLOYEE_ID))
LEFT JOIN HRIS_LEAVE_MASTER_SETUP L
ON (A.LEAVE_ID = L.LEAVE_ID)
GROUP BY SS.EMPLOYEE_ID
) A
LEFT JOIN
(SELECT OT.EMPLOYEE_ID,
SUM(OT.TOTAL_HOUR) AS TOTAL_MIN
FROM
(SELECT SS.*,
E.EMPLOYEE_ID
FROM HRIS_SALARY_SHEET SS
JOIN HRIS_EMPLOYEES E
ON (SS.COMPANY_ID=E.COMPANY_ID
AND SS.GROUP_ID = E.GROUP_ID)
WHERE SS.SHEET_NO=P_SHEET_NO
) SS
LEFT JOIN HRIS_OVERTIME OT
ON ( OT.OVERTIME_DATE BETWEEN SS.START_DATE AND SS.END_DATE)
WHERE 1 =1
AND SS.SHEET_NO=P_SHEET_NO
AND OT.STATUS = 'AP'
GROUP BY OT.EMPLOYEE_ID
) OT ON (A.EMPLOYEE_ID = OT.EMPLOYEE_ID)
LEFT JOIN HRIS_EMPLOYEES E
ON(A.EMPLOYEE_ID = E.EMPLOYEE_ID)
LEFT JOIN HRIS_GENDERS G
ON(E.GENDER_ID=G.GENDER_ID)
LEFT JOIN HRIS_COMPANY C
ON(E.COMPANY_ID= C.COMPANY_ID)
LEFT JOIN HRIS_BRANCHES B
ON (E.BRANCH_ID=B.BRANCH_ID)
LEFT JOIN HRIS_DEPARTMENTS DEP
ON (E.DEPARTMENT_ID= DEP.DEPARTMENT_ID)
LEFT JOIN HRIS_DESIGNATIONS DES
ON (E.DESIGNATION_ID=DES.DESIGNATION_ID)
LEFT JOIN HRIS_POSITIONS P
ON (E.POSITION_ID=P.POSITION_ID)
LEFT JOIN HRIS_SERVICE_TYPES ST
ON (E.SERVICE_TYPE_ID=ST.SERVICE_TYPE_ID)
LEFT JOIN HRIS_SALARY_SHEET SS
ON (SS.SHEET_NO=P_SHEET_NO)
LEFT JOIN HRIS_EMPLOYEE_HISTORY_MIG EHM
ON (EHM.EMPLOYEE_ID=A.EMPLOYEE_ID
AND EHM.MONTH_ID =SS.MONTH_ID)
LEFT JOIN HRIS_FUNCTIONAL_TYPES FT
ON (E.FUNCTIONAL_TYPE_ID=FT.FUNCTIONAL_TYPE_ID)
WHERE 1 =1
AND A.EMPLOYEE_ID IN (select EMPLOYEE_ID from HRIS_PAYROLL_EMP_LIST)
ORDER BY C.COMPANY_NAME,
DEP.DEPARTMENT_NAME,
E.FULL_NAME ;
END;