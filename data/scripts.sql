
-- FIRST LEVEL AUDIT TRIAL

ALTER TABLE HR_BRANCHES ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_BRANCHES ADD CONSTRAINT BRA_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_BRANCHES ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_BRANCHES ADD CONSTRAINT BRA_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 



ALTER TABLE HR_COMPANY ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_COMPANY ADD CONSTRAINT COM_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_COMPANY ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_COMPANY ADD CONSTRAINT COM_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_DEPARTMENTS ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_DEPARTMENTS ADD CONSTRAINT DEP_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_DEPARTMENTS ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_DEPARTMENTS ADD CONSTRAINT DEP_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 



ALTER TABLE HR_DESIGNATIONS ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_DESIGNATIONS ADD CONSTRAINT DES_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_DESIGNATIONS ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_DESIGNATIONS ADD CONSTRAINT DES_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_POSITIONS ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_POSITIONS ADD CONSTRAINT POS_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_POSITIONS ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_POSITIONS ADD CONSTRAINT POS_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_SERVICE_TYPES ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_SERVICE_TYPES ADD CONSTRAINT SER_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_SERVICE_TYPES ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_SERVICE_TYPES ADD CONSTRAINT SER_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_RECOMMENDER_APPROVER ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_RECOMMENDER_APPROVER ADD CONSTRAINT RECOMMAPPR_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_RECOMMENDER_APPROVER ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_RECOMMENDER_APPROVER ADD CONSTRAINT RECOMMAPPR_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_ACADEMIC_UNIVESITY ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_ACADEMIC_UNIVESITY ADD CONSTRAINT AUNI_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_ACADEMIC_UNIVESITY ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_ACADEMIC_UNIVESITY ADD CONSTRAINT AUNI_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_ACADEMIC_PROGRAMS ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_ACADEMIC_PROGRAMS ADD CONSTRAINT APRO_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_ACADEMIC_PROGRAMS ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_ACADEMIC_PROGRAMS ADD CONSTRAINT APRO_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_ACADEMIC_COURSES ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_ACADEMIC_COURSES ADD CONSTRAINT ACOUR_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_ACADEMIC_COURSES ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_ACADEMIC_COURSES ADD CONSTRAINT ACOUR_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_ACADEMIC_DEGREES ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_ACADEMIC_DEGREES ADD CONSTRAINT ADEGRE_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_ACADEMIC_DEGREES ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_ACADEMIC_DEGREES ADD CONSTRAINT ADEGRE_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_LEAVE_MASTER_SETUP ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_LEAVE_MASTER_SETUP ADD CONSTRAINT LEAVE_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_LEAVE_MASTER_SETUP ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_LEAVE_MASTER_SETUP ADD CONSTRAINT ALEAVE_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_EMPLOYEE_LEAVE_ASSIGN ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_EMPLOYEE_LEAVE_ASSIGN ADD CONSTRAINT EMPLEAVE_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_EMPLOYEE_LEAVE_ASSIGN ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_EMPLOYEE_LEAVE_ASSIGN ADD CONSTRAINT EMPLEAVE_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_HOLIDAY_MASTER_SETUP ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_HOLIDAY_MASTER_SETUP ADD CONSTRAINT HOLI_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_HOLIDAY_MASTER_SETUP ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_HOLIDAY_MASTER_SETUP ADD CONSTRAINT HOLI_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_SHIFTS ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_SHIFTS ADD CONSTRAINT SHIFTS_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_SHIFTS ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_SHIFTS ADD CONSTRAINT SHIFTS_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_EMPLOYEE_SHIFT_ASSIGN ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_EMPLOYEE_SHIFT_ASSIGN ADD CONSTRAINT EMPSHIFT_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_EMPLOYEE_SHIFT_ASSIGN ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_EMPLOYEE_SHIFT_ASSIGN ADD CONSTRAINT EMPSHIFT_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_JOB_HISTORY ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_JOB_HISTORY ADD CONSTRAINT JOBHIS_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_JOB_HISTORY ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_JOB_HISTORY ADD CONSTRAINT JOBHIS_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_JOB_HISTORY ADD CREATED_DT DATE

ALTER TABLE HR_JOB_HISTORY ADD MODIFIED_DT DATE


ALTER TABLE HR_ROLES ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_ROLES ADD CONSTRAINT ROLES_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_ROLES ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_ROLES ADD CONSTRAINT ROLES_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_USERS ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_USERS ADD CONSTRAINT USERS_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_USERS ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_USERS ADD CONSTRAINT USERS_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


ALTER TABLE HR_MENUS ADD CREATED_BY NUMBER(6,0)

ALTER TABLE HR_MENUS ADD CONSTRAINT MENUS_EMP_ID_FK FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 

ALTER TABLE HR_MENUS ADD MODIFIED_BY NUMBER(6,0)

ALTER TABLE HR_MENUS ADD CONSTRAINT MENUS_EMP_ID_FK2 FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID) 


-- END OF FIRST LEVEL AUDIT TRIAL

-- PROFILE PICTURE FK SET

ALTER TABLE HR_EMPLOYEES 
ADD CONSTRAINT FK_EMP_FILE_EMP_PRO_PIC_ID
FOREIGN KEY(PROFILE_PICTURE_ID) REFERENCES HR_EMPLOYEE_FILE(FILE_CODE);

UPDATE HR_EMPLOYEES SET PROFILE_PICTURE_ID=3;

-- END OF PICTURE FK SET

-- 
SELECT E.* FROM HR_EMPLOYEES E
        JOIN HR_EMPLOYEE_SHIFT_ASSIGN ESA ON (E.EMPLOYEE_ID=ESA.EMPLOYEE_ID) JOIN HR_SHIFTS S ON (ESA.SHIFT_ID=S.SHIFT_ID) 
        WHERE  
        (CASE
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'SUN' THEN ( CASE WHEN (S.WEEKDAY1 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'MON' THEN ( CASE WHEN (S.WEEKDAY2 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'TUE' THEN ( CASE WHEN (S.WEEKDAY3 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'WED' THEN ( CASE WHEN (S.WEEKDAY4 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'THU' THEN ( CASE WHEN (S.WEEKDAY5 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'FRI' THEN ( CASE WHEN (S.WEEKDAY6 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'SAT' THEN ( CASE WHEN (S.WEEKDAY7 = 'DAY_OFF') THEN 0 ELSE 1 END )
        END)=1 AND E.STATUS='E' AND E.RETIRED_FLAG='N' AND E.JOIN_DATE <= SYSDATE; 
-- 
--  SALARY DETAIL TABLE ADDED
CREATE TABLE HR_SALARY_DETAIL
(
  EMPLOYEE_ID NUMBER(6,0) NOT NULL,  
  OLD_AMOUNT NUMBER(9,0) NOT NULL,
  NEW_AMOUNT NUMBER(9,0) NOT NULL,
  EFFECTIVE_DATE DATE,
  SERVICE_EVENT_TYPE_ID NUMBER(6,0),
  CREATED_BY NUMBER(6,0) NOT NULL,
  CREATED_DT DATE NOT NULL,
  MODIFIED_BY NUMBER(6,0),
  MODIFIED_DT DATE,
  STATUS CHAR(1 BYTE) NOT NULL CHECK(STATUS IN ('E','D'))
)


ALTER TABLE HR_SALARY_DETAIL 
ADD CONSTRAINT FK_SAL_DETL_EMP_EMPLOYEE_ID 
FOREIGN KEY(EMPLOYEE_ID) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID);


ALTER TABLE HR_SALARY_DETAIL
ADD CONSTRAINT FK_SAL_DET_EMP_CREATED_BY
FOREIGN KEY(CREATED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID);

ALTER TABLE HR_SALARY_DETAIL
ADD CONSTRAINT FK_SAL_DET_EMP_MODIFIED_BY
FOREIGN KEY(MODIFIED_BY) REFERENCES HR_EMPLOYEES(EMPLOYEE_ID);


-- 