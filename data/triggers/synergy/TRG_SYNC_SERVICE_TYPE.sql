create or replace TRIGGER "TRG_SYNC_SERVICE_TYPE" 
   AFTER INSERT OR UPDATE OR DELETE ON HRIS_SERVICE_TYPES
   REFERENCING NEW AS NEW OLD AS OLD
   FOR EACH ROW
DECLARE
    V_DELETED_FLAG CHAR(1 BYTE);
    V_COUNT NUMBER;
BEGIN

	BEGIN
		SELECT COUNT(*) INTO V_COUNT FROM HR_EMPLOYEE_TYPE_CODE
		WHERE EMPLOYEE_TYPE_CODE = TO_CHAR(:OLD.SERVICE_TYPE_ID,'FM00');
	EXCEPTION
		WHEN OTHERS THEN
			V_COUNT := 0;
	END;

	IF :NEW.STATUS = 'E' THEN
		V_DELETED_FLAG := 'N';
	ELSE
		V_DELETED_FLAG := 'Y';
	END IF;

     IF V_COUNT = 0 THEN
		FOR I IN (SELECT COMPANY_CODE FROM COMPANY_SETUP) LOOP
			INSERT INTO HR_EMPLOYEE_TYPE_CODE
			(EMPLOYEE_TYPE_CODE,
			EMPLOYEE_TYPE_EDESC,
			EMPLOYEE_TYPE_NDESC,
			EREMARKS,
			NREMARKS,
			COMPANY_CODE,
			BRANCH_CODE,
			CREATED_BY,
			CREATED_DATE,
			DELETED_FLAG,
			MODIFY_DATE,
			MODIFY_BY)
			VALUES
			(TO_CHAR(:NEW.SERVICE_TYPE_ID,'FM00')
			, :NEW.SERVICE_TYPE_NAME
			, TO_NUMBER(:NEW.SERVICE_TYPE_ID)
			, :NEW.REMARKS
			, :NEW.REMARKS
			, I.COMPANY_CODE, I.COMPANY_CODE||'.01'
			,'ADMIN',TRUNC(SYSDATE),V_DELETED_FLAG,NULL,NULL);
		END LOOP;

	ELSIF V_COUNT >= 1 THEN
		UPDATE HR_EMPLOYEE_TYPE_CODE SET
		EMPLOYEE_TYPE_EDESC = :NEW.SERVICE_TYPE_NAME,
		EREMARKS = :NEW.REMARKS,
		DELETED_FLAG = V_DELETED_FLAG
		WHERE EMPLOYEE_TYPE_CODE = TO_CHAR(:OLD.SERVICE_TYPE_ID,'FM00');

    END IF;

END;