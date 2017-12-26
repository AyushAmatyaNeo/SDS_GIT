


-- ADVANCE MODULE start
CREATE TABLE HRIS_ADVANCE_MASTER_SETUP
  (
    ADVANCE_ID                NUMBER(7,0) PRIMARY KEY,
    ADVANCE_CODE              VARCHAR2(15 BYTE),
    ADVANCE_ENAME             VARCHAR2(255 BYTE) NOT NULL,
    ADVANCE_LNAME             VARCHAR2(255 BYTE),
    ALLOWED_TO                CHAR(3 BYTE) NOT NULL CHECK(ALLOWED_TO IN('ALL','PER','PRO','CON')),
    ALLOWED_MONTH_GAP         NUMBER(2,0) ,
    ALLOW_UNCLEARED_ADVANCE   CHAR(1 BYTE) DEFAULT 'N' NOT NULL CHECK(ALLOW_UNCLEARED_ADVANCE IN ('Y','N')),
    MAX_SALARY_RATE           NUMBER(5,2),
    MAX_ADVANCE_MONTH         NUMBER(2,0),
    DEDUCTION_TYPE            CHAR(1 BYTE) NOT NULL CHECK(DEDUCTION_TYPE IN ('S','M')),
    DEDUCTION_RATE            NUMBER(5,2) NOT NULL ,
    DEDUCTION_IN              NUMBER(2,0) NOT NULL,
    ALLOW_OVERRIDE_RATE       CHAR(1 BYTE) NOT NULL CHECK(ALLOW_OVERRIDE_RATE IN('Y','N')),
    MIN_OVERRIDE_RATE         NUMBER(5,2),
    ALLOW_OVERRIDE_MONTH      CHAR(1 BYTE) NOT NULL CHECK(ALLOW_OVERRIDE_MONTH IN ('Y','N')),
    MAX_OVERRIDE_MONTH        NUMBER(2,0),
    OVERRIDE_RECOMMENDER_FLAG CHAR(1 BYTE) DEFAULT 'N' NOT NULL CHECK(OVERRIDE_RECOMMENDER_FLAG IN ('Y','N')),
    OVERRIDE_APPROVER_FLAG    CHAR(1 BYTE) DEFAULT 'N' NOT NULL CHECK(OVERRIDE_APPROVER_FLAG    IN ('Y','N')),
    CREATED_BY                NUMBER(7,0),
    CREATED_DATE              DATE DEFAULT TRUNC(SYSDATE),
    MODIFIED_BY               NUMBER(7,0),
    MODIFIED_DATE             DATE,
    STATUS                    CHAR(1 BYTE) DEFAULT 'E' NOT NULL CHECK(STATUS IN ('E','D'))
  );





CREATE TABLE HRIS_EMPLOYEE_ADVANCE_REQUEST(
    ADVANCE_REQUEST_ID NUMBER(7,0) NOT NULL,
    EMPLOYEE_ID NUMBER(7,0) NOT NULL,
    ADVANCE_ID NUMBER(7,0) NOT NULL,
    REQUESTED_AMOUNT FLOAT(126) NOT NULL,
    REQUESTED_DATE DATE DEFAULT TRUNC(SYSDATE) ,
    DATE_OF_ADVANCE DATE NOT NULL, 
    REASON VARCHAR2(255),
    RECOMMENDED_BY NUMBER(7,0),
    RECOMMENDED_DATE DATE,
    RECOMMENDED_REMARKS VARCHAR2(255),
    APPROVED_BY NUMBER(7,0),
    APPROVED_DATE DATE,
    APPROVED_REMARKS VARCHAR2(255),
    STATUS CHAR (2 BYTE) NOT NULL CHECK(STATUS IN ('RQ','RC','AP','C','R')),
    DEDUCTION_TYPE CHAR(1 BYTE) NOT NULL CHECK(DEDUCTION_TYPE IN ('S','M')),
    DEDUCTION_RATE NUMBER(5,2),
    DEDUCTION_IN NUMBER(2,0),
    OVERRIDE_RECOMMENDER_ID NUMBER(7,0),
    OVERRIDE_APPROVER_ID NUMBER(7,0)
);




CREATE TABLE HRIS_EMPLOYEE_ADVANCE_PAYMENT
  (
    ADVANCE_REQUEST_ID NUMBER(7,0) NOT NULL ,
    AMOUNT FLOAT(126) NOT NULL,
    STATUS        CHAR (2 BYTE) NOT NULL CHECK (STATUS        IN ('PE','PA','SK')),
    PAYMENT_MODE  CHAR(1 BYTE) DEFAULT 'S' CHECK(PAYMENT_MODE IN ('S','H')),
    PAYAMENT_DATE DATE,
    NEP_YEAR      NUMBER(4,0) NOT NULL,
    NEP_MONTH     NUMBER(2,0) NOT NULL,
    REF_NEP_YEAR  NUMBER(4,0),
    REF_NEP_MONTH NUMBER(2,0),
    CREATED_BY    NUMBER(7,0),
    CREATED_DATE  DATE DEFAULT TRUNC(SYSDATE),
    MODIFIED_BY   NUMBER(7,0),
    MODIFIED_DATE DATE
  );


--advance module end
    