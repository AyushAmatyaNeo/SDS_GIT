<?php

namespace Medical\Repository;

use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Application\Model\Model;
use Application\Repository\RepositoryInterface;
use Medical\Model\Medical;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGateway;

class MedicalRepo implements RepositoryInterface {

    private $adapter;
    private $gateway;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
        $this->gateway = new TableGateway(Medical::TABLE_NAME, $adapter);
    }

    public function add(Model $model) {
        $this->gateway->insert($model->getArrayCopyForDB());
    }

    public function delete($id) {
        
    }

    public function edit(Model $model, $id) {
        $this->gateway->update($model->getArrayCopyForDB(), [Medical::MEDICAL_ID => $id]);
    }

    public function fetchAll() {
        $sql = "SELECT M.*
                    ,CASE M.BILL_STATUS 
                    WHEN 'RQ' THEN 'REQUESTED'
                    WHEN 'AP' THEN 'APPROVED'
                    WHEN 'PD' THEN 'PAID'
                    WHEN 'C' THEN 'CANCELLED'
                    END AS BILL_STATUS_NAME
                    ,CASE M.CLAIM_OF 
                    WHEN 'S' THEN 'SELF'
                    WHEN 'D' THEN 'DEPENDENT'
                    END AS CLAIM_OF_NAME
                    ,E.EMPLOYEE_CODE
                    ,E.FULL_NAME
                    ,D.DEPARTMENT_NAME
                    ,FUNT.FUNCTIONAL_TYPE_EDESC
                    FROM Hris_Medical M
                    LEFT JOIN HRIS_EMPLOYEES E ON (E.EMPLOYEE_ID=M.EMPLOYEE_ID)
                    LEFT JOIN HRIS_DEPARTMENTS D  ON (D.DEPARTMENT_ID=E.DEPARTMENT_ID)
                    LEFT JOIN HRIS_FUNCTIONAL_TYPES FUNT ON (E.FUNCTIONAL_TYPE_ID=FUNT.FUNCTIONAL_TYPE_ID)
                    WHERE M.STATUS='E'";
        $rawResult = EntityHelper::rawQueryResult($this->adapter, $sql);
        return $rawResult;
    }

    public function fetchById($id) {
        $sql = "SELECT M.*
                    ,CASE M.BILL_STATUS 
                    WHEN 'RQ' THEN 'REQUESTED'
                    WHEN 'AP' THEN 'APPROVED'
                    WHEN 'PD' THEN 'PAID'
                    WHEN 'C' THEN 'CANCELLED'
                    END AS BILL_STATUS_NAME
                    ,CASE M.CLAIM_OF 
                    WHEN 'S' THEN 'SELF'
                    WHEN 'D' THEN 'DEPENDENT'
                    END AS CLAIM_OF_NAME
                    ,CASE M.OPERATION_FLAG 
                    WHEN 'Y' THEN 'Yes'
                    WHEN 'N' THEN 'No'
                    END AS OPERATION_FLAG_NAME
                    ,CASE WHEN
                    M.APPROVED_AMT IS  NULL then M.REQUESTED_AMT 
                    else M.APPROVED_AMT
                    END AS APPROVED_AMT_DISPALY
                    ,E.EMPLOYEE_CODE
                    ,E.FULL_NAME
                    ,D.DEPARTMENT_NAME
                    ,FUNT.FUNCTIONAL_TYPE_EDESC
                    ,ER.PERSON_NAME
                    ,R.RELATION_NAME
                    FROM Hris_Medical M
                    LEFT JOIN HRIS_EMPLOYEES E ON (E.EMPLOYEE_ID=M.EMPLOYEE_ID)
                    LEFT JOIN HRIS_EMPLOYEE_RELATION ER ON (ER.E_R_Id=M.E_R_Id)
                    LEFT JOIN HRIS_RELATIONS R ON (R.RELATION_ID=ER.RELATION_ID)
                    LEFT JOIN HRIS_DEPARTMENTS D  ON (D.DEPARTMENT_ID=E.DEPARTMENT_ID)
                    LEFT JOIN HRIS_FUNCTIONAL_TYPES FUNT ON (E.FUNCTIONAL_TYPE_ID=FUNT.FUNCTIONAL_TYPE_ID)
                    WHERE M.MEDICAL_ID={$id}";
        $rawResult = EntityHelper::rawQueryResult($this->adapter, $sql);
        return $rawResult->current();
    }

    public function filterRecord($companyId, $branchId, $departmentId, $designationId, $positionId, $serviceTypeId, $serviceEventTypeId, $employeeTypeId, $genderId, $functionalTypeId, $employeeId, $fromDate = null, $toDate = null, $status = null) {
        $searchConditon = EntityHelper::getSearchConditon($companyId, $branchId, $departmentId, $positionId, $designationId, $serviceTypeId, $serviceEventTypeId, $employeeTypeId, $employeeId, $genderId, null, $functionalTypeId);
        $fromDateCondition = "";
        $toDateCondition = "";
        $statusCondition = '';
        $rowNums = '';
        if ($fromDate != null) {
            $fromDateCondition = " AND M.TRANSACTION_DT>=TO_DATE('" . $fromDate . "','DD-MM-YYYY') ";
        }
        if ($toDate != null) {
            $toDateCondition = " AND M.TRANSACTION_DT<=TO_DATE('" . $toDate . "','DD-MM-YYYY') ";
        }

        if ($status != -1) {
            $statusCondition = " AND M.BILL_STATUS='{$status}'";
        }
//        $presentStatusMap = [
//            "LI" => "'L','B','Y'",
//            "EO" => "'E','B'",
//            "MP" => "'X','Y'",
//        ];
//        if ($presentStatus != null) {
//            if (gettype($presentStatus) === 'array') {
//                $q = "";
//                for ($i = 0; $i < sizeof($presentStatus); $i++) {
//                    if ($i == 0) {
//                        $q = $presentStatusMap[$presentStatus[$i]];
//                    } else {
//                        $q .= "," . $presentStatusMap[$presentStatus[$i]];
//                    }
//                }
//                $presentStatusCondition = "AND A.LATE_STATUS IN ({$q})";
//            } else {
//                $presentStatusCondition = "AND A.LATE_STATUS IN ({$presentStatusMap[$presentStatus]})";
//            }
//        }
//          $orderByString=EntityHelper::getOrderBy('A.ATTENDANCE_DT DESC ,A.IN_TIME ASC','A.ATTENDANCE_DT DESC ,A.IN_TIME ASC','E.SENIORITY_LEVEL','P.LEVEL_NO','E.JOIN_DATE','DES.ORDER_NO','E.FULL_NAME');

        $sql = "SELECT M.*
                    ,CASE M.BILL_STATUS 
                    WHEN 'RQ' THEN 'REQUESTED'
                    WHEN 'AP' THEN 'APPROVED'
                    WHEN 'PD' THEN 'PAID'
                    WHEN 'C' THEN 'CANCELLED'
                    END AS BILL_STATUS_NAME
                    ,CASE M.CLAIM_OF 
                    WHEN 'S' THEN 'SELF'
                    WHEN 'D' THEN 'DEPENDENT'
                    END AS CLAIM_OF_NAME
                    ,E.EMPLOYEE_CODE
                    ,E.FULL_NAME
                    ,D.DEPARTMENT_NAME
                    ,FUNT.FUNCTIONAL_TYPE_EDESC
                    FROM Hris_Medical M
                    LEFT JOIN HRIS_EMPLOYEES E ON (E.EMPLOYEE_ID=M.EMPLOYEE_ID)
                    LEFT JOIN HRIS_DEPARTMENTS D  ON (D.DEPARTMENT_ID=E.DEPARTMENT_ID)
                    LEFT JOIN HRIS_FUNCTIONAL_TYPES FUNT ON (E.FUNCTIONAL_TYPE_ID=FUNT.FUNCTIONAL_TYPE_ID)
                    WHERE M.STATUS='E'
                {$searchConditon}
                {$fromDateCondition}
                {$toDateCondition}
                {$statusCondition}
                ";
        return EntityHelper::rawQueryResult($this->adapter, $sql);
    }

    public function fetchEmpMedicalDetail($employeeId) {
        $sql = "SELECT 
ST.SELF_TAKEN,DT.DEPENDENT_TAKEN,DTWO.DEPENDENT_TAKEN_WO
,SM.VALUE-ST.SELF_TAKEN AS SELF_REAMINING
,DM.VALUE-DT.DEPENDENT_TAKEN AS DEP_REAMINING
,DM.VALUE+DMO.VALUE-DTWO.DEPENDENT_TAKEN_WO AS DEP_REAMINING_WITH_OPER
FROM 
(SELECT NVL(SUM(CASE 
WHEN APPROVED_AMT IS NOT NULL 
THEN APPROVED_AMT
ELSE
REQUESTED_AMT
END),0) AS SELF_TAKEN
FROM HRIS_MEDICAL
WHERE 
STATUS='E' 
AND BILL_STATUS!='C' 
AND CLAIM_OF='S' 
AND EMPLOYEE_ID={$employeeId}) ST
LEFT JOIN (SELECT NVL(SUM(CASE 
WHEN M.APPROVED_AMT IS NOT NULL 
THEN M.APPROVED_AMT
ELSE
M.REQUESTED_AMT
END),0) AS DEPENDENT_TAKEN
FROM HRIS_MEDICAL M
WHERE 
M.STATUS='E' 
AND M.BILL_STATUS!='C' 
AND M.CLAIM_OF='D'
AND M.OPERATION_FLAG='N'
AND M.EMPLOYEE_ID={$employeeId}) DT ON (1=1)
LEFT JOIN (SELECT NVL(SUM(CASE 
WHEN APPROVED_AMT IS NOT NULL 
THEN APPROVED_AMT
ELSE
REQUESTED_AMT
END),0) AS DEPENDENT_TAKEN_WO
FROM HRIS_MEDICAL
WHERE 
STATUS='E' 
AND BILL_STATUS!='C' 
AND CLAIM_OF='D'
AND EMPLOYEE_ID={$employeeId}) DTWO ON (1=1)    
LEFT JOIN (select *  from HRIS_PREFERENCES WHERE KEY='STAFF_MEDICAL') SM ON (1=1)
LEFT JOIN (select *  from HRIS_PREFERENCES WHERE KEY='STAFF_DEP_MEDICAL') DM ON (1=1)
LEFT JOIN (select *  from HRIS_PREFERENCES WHERE KEY='STAFF_DEP_OPERATION') DMO ON (1=1)";
//echo $sql;
//die();
        $rawResult = EntityHelper::rawQueryResult($this->adapter, $sql);
        return $rawResult->current();
//        return Helper::extractDbData($result);
    }

}
