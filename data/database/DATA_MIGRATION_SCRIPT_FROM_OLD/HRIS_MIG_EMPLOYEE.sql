create or replace PROCEDURE HRIS_MIG_EMPLOYEE
AS
V_EMPLOYEE_ID NUMBER;
V_FIRST_NAME VARCHAR2(255);
V_BIRTH_DATE DATE;
V_GENDER_ID NUMBER;
V_MOBILE VARCHAR(255);
V_COUNTRY_ID NUMBER;
V_RETIRED_FLAG CHAR(1 BYTE);
V_STATUS CHAR(1 BYTE);
V_BRANCH_ID NUMBER;
V_MARITAL_STATUS CHAR(1 BYTE);
V_RESIGNED_FLAG CHAR(1 BYTE);
V_EMPLOYEE_FILE_ID NUMBER;
BEGIN

FOR V_EMPLOYEE_LIST IN (SELECT * from V_HR_EMPLOYEE_SETUP WHERE LOWER(EMPLOYEE_STATUS)='working' AND GROUP_SKU_FLAG='I' ORDER BY EMPLOYEE_CODE)
LOOP

IF(V_EMPLOYEE_LIST.PHOTO_FILE_NAME IS NOT NULL)THEN
SELECT NVL(MAX(FILE_CODE),0)+1 INTO V_EMPLOYEE_FILE_ID FROM HRIS_EMPLOYEE_FILE;

INSERT INTO HRIS_EMPLOYEE_FILE (FILE_CODE,FILETYPE_CODE,FILE_PATH,STATUS,CREATED_DT,FILE_NAME)
VALUES(V_EMPLOYEE_FILE_ID,'001',V_EMPLOYEE_LIST.PHOTO_FILE_NAME,'E',TRUNC(SYSDATE),V_EMPLOYEE_LIST.PHOTO_FILE_NAME);
ELSE
V_EMPLOYEE_FILE_ID:=NULL;
END IF;


SELECT REGEXP_SUBSTR (V_EMPLOYEE_LIST.BRANCH_CODE, '[^.]+', 1, 2) INTO V_BRANCH_ID FROM DUAL;

DBMS_OUTPUT.PUT_LINE('BRANCHiD'||V_BRANCH_ID);

BEGIN
SELECT EMPLOYEE_ID INTO V_EMPLOYEE_ID FROM HRIS_EMPLOYEES WHERE EMPLOYEE_ID=V_EMPLOYEE_LIST.EMPLOYEE_CODE;
EXCEPTION
  WHEN NO_DATA_FOUND THEN
BEGIN

-- TO CHECK EMPLOYEE STATUS
IF(V_EMPLOYEE_LIST.DELETED_FLAG='N') THEN
V_STATUS:='E';
ELSE
V_STATUS:='D';
END IF;

-- TO CHECK EMPLOYEE GENDER
IF(V_EMPLOYEE_LIST.SEX='M') THEN
V_GENDER_ID:=1;
ELSIF  (V_EMPLOYEE_LIST.SEX='F') THEN
V_GENDER_ID:=2;
ELSE 
V_GENDER_ID:=3;
END IF;

-- TO CHECK PERMANENT COUNRTY
BEGIN
SELECT COUNTRY_ID INTO V_COUNTRY_ID FROM  HRIS_COUNTRIES WHERE LOWER(COUNTRY_NAME)= LOWER(V_EMPLOYEE_LIST.EPERMANENT_COUNTRY);
EXCEPTION
  WHEN NO_DATA_FOUND THEN
  V_COUNTRY_ID:=168;
END;

-- TO CHECK IF EMPLOYEE IS RESIGNED 
IF(V_EMPLOYEE_LIST.EMPLOYEE_STATUS='Resigned') THEN
V_RETIRED_FLAG:='Y';
ELSE 
V_RETIRED_FLAG:='N';
END IF;

-- TO CHECK IF EMPLOYEE MOBILE IS NULL
IF(V_EMPLOYEE_LIST.MOBILE IS NULL) THEN
V_MOBILE:='9999999999';
ELSE 
V_MOBILE:=V_EMPLOYEE_LIST.MOBILE;
END IF;

-- TO CHECK IF EMPLOYEE FIRST_NAME IS NULL
IF(V_EMPLOYEE_LIST.FIRST_NAME IS NULL) THEN
V_FIRST_NAME:=V_EMPLOYEE_LIST.LAST_NAME;
ELSE 
V_FIRST_NAME:=V_EMPLOYEE_LIST.FIRST_NAME;
END IF;

-- TO CHECK IF EMPLOYEE BRITH_DATE IS NULL
IF(V_EMPLOYEE_LIST.BIRTH_DATE IS NULL) THEN
V_BIRTH_DATE:=TRUNC(SYSDATE);
ELSE 
V_BIRTH_DATE:=V_EMPLOYEE_LIST.BIRTH_DATE;
END IF;


-- TO CHECK MARITIAL STATUS
IF(V_EMPLOYEE_LIST.MARITAL_STATUS='Married') THEN
V_MARITAL_STATUS:='M';
ELSE 
V_MARITAL_STATUS:='U';
END IF;

if(V_EMPLOYEE_LIST.RESIGNED_DATE IS NULL)THEN
V_RESIGNED_FLAG:='N';
ELSE
V_RESIGNED_FLAG:='Y';
END IF;


DBMS_OUTPUT.PUT_LINE(V_EMPLOYEE_LIST.EMPLOYEE_CODE);
DBMS_OUTPUT.PUT_LINE(V_GENDER_ID);
DBMS_OUTPUT.PUT_LINE(V_COUNTRY_ID);
DBMS_OUTPUT.PUT_LINE(V_RETIRED_FLAG);
DBMS_OUTPUT.PUT_LINE(V_MOBILE);

INSERT INTO HRIS_EMPLOYEES 
(
EMPLOYEE_ID,
EMPLOYEE_CODE,
ID_THUMB_ID,
COMPANY_ID,
BRANCH_ID,
DEPARTMENT_ID,
DESIGNATION_ID,
POSITION_ID,
SERVICE_TYPE_ID,
FIRST_NAME,
MIDDLE_NAME,
LAST_NAME,
GENDER_ID,
BIRTH_DATE,
BLOOD_GROUP_ID,
RELIGION_ID,
JOIN_DATE,
TELEPHONE_NO,
MOBILE_NO,
EMAIL_OFFICIAL,
EMAIL_PERSONAL,
ADDR_PERM_COUNTRY_ID,
ADDR_TEMP_COUNTRY_ID,
STATUS,
CREATED_DT,
RETIRED_FLAG,
ID_CITIZENSHIP_NO,
ID_CITIZENSHIP_ISSUE_DATE,
ID_DRIVING_LICENCE_NO,
ID_DRIVING_LICENCE_TYPE,
OVERTIME_FLAG,
FAM_FATHER_NAME,
MARITAL_STATUS,
SALARY,
ADDR_PERM_STREET_ADDRESS,
ADDR_TEMP_STREET_ADDRESS,
ID_PASSPORT_NO,
ID_PASSPORT_EXPIRY,
PERMANENT_DATE,
COUNTRY_ID,
ID_ACCOUNT_NO,
ID_RETIREMENT_NO,
ID_BAR_CODE,
RESIGNED_DATE,
RESIGNED_FLAG,
ID_PAN_NO,
GROUP_ID,
ID_ACC_CODE,
REMARKS,
PROFILE_PICTURE_ID,
ID_PROVIDENT_FUND_NO,
ID_LBRF
)
VALUES
(
V_EMPLOYEE_LIST.EMPLOYEE_CODE,
V_EMPLOYEE_LIST.EMPLOYEE_MANUAL_CODE,
V_EMPLOYEE_LIST.THUMB_ID,
V_EMPLOYEE_LIST.COMPANY_CODE,
V_BRANCH_ID,
V_EMPLOYEE_LIST.CUR_DEPARTMENT_CODE,
V_EMPLOYEE_LIST.CUR_DESIGNATION_CODE,
V_EMPLOYEE_LIST.CUR_GRADE_CODE,
V_EMPLOYEE_LIST.EMPLOYEE_TYPE_CODE,
V_FIRST_NAME,
V_EMPLOYEE_LIST.MIDDLE_NAME,
V_EMPLOYEE_LIST.LAST_NAME,
V_GENDER_ID,
V_BIRTH_DATE,
9,
5,
V_EMPLOYEE_LIST.JOIN_DATE,
V_EMPLOYEE_LIST.PHONE,
V_MOBILE,
V_EMPLOYEE_LIST.EMAIL,
V_EMPLOYEE_LIST.PERSONAL_EMAIL,
V_COUNTRY_ID,
V_COUNTRY_ID,
V_STATUS,
TRUNC(SYSDATE),
V_RETIRED_FLAG,
V_EMPLOYEE_LIST.CITIZENSHIP_NO,
V_EMPLOYEE_LIST.CTZ_ISSUED_DATE,
V_EMPLOYEE_LIST.LICENSE_NO,
V_EMPLOYEE_LIST.LICENSE_TYPE,
V_EMPLOYEE_LIST.OVERTIME_APPLICABLE,
V_EMPLOYEE_LIST.EFATHER_NAME,
V_MARITAL_STATUS,
V_EMPLOYEE_LIST.CUR_BASIC_SALARY,
V_EMPLOYEE_LIST.EPERMANENT_ADDRESS1,
V_EMPLOYEE_LIST.ETEMPORARY_ADDRESS1,
V_EMPLOYEE_LIST.PASSPORT_NO,
V_EMPLOYEE_LIST.PASS_EXPIRY_DATE,
V_EMPLOYEE_LIST.PERMANENT_DATE,
V_COUNTRY_ID,
V_EMPLOYEE_LIST.ACCOUNT_NO,
V_EMPLOYEE_LIST.CIT_NUMBER,
V_EMPLOYEE_LIST.BAR_CODE,
V_EMPLOYEE_LIST.RESIGNED_DATE,
V_RESIGNED_FLAG,
V_EMPLOYEE_LIST.PAN_NO,
V_EMPLOYEE_LIST.SAL_SHEET_CODE,
V_EMPLOYEE_LIST.DEPOSIT_ACCOUNT,
V_EMPLOYEE_LIST.REASON,
V_EMPLOYEE_FILE_ID,
V_EMPLOYEE_LIST.PF_NUMBER,
V_EMPLOYEE_LIST.RF_NUMBER
);




END;
END;

END LOOP;
END;