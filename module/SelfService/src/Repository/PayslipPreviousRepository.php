<?php

namespace SelfService\Repository;

use Application\Repository\HrisRepository;
use Zend\Db\Adapter\AdapterInterface;

class PayslipPreviousRepository extends HrisRepository {

    public function __construct(AdapterInterface $adapter, $tableName = null) {
        parent::__construct($adapter, $tableName);
    }

    public function getPeriodList($companyCode) {
        $sql = "SELECT PERIOD_DT_CODE AS mcode,
                  DT_EDESC            AS mname
                FROM HR_PERIOD_DETAIL
                WHERE COMPANY_CODE='{$companyCode}'
                AND BRANCH_CODE='{$companyCode}.01'
                ORDER BY to_number(PERIOD_DT_CODE)";
        return $this->rawQuery($sql);
    }

    public function getPayslipDetail($companyCode, $employeeCode, $periodDtCode) {
        $sql = "SELECT 'R000' PAY_CODE,
                  'Calc Basic' PAY_EDESC,
                  'Calc Basic' PAY_NDESC,
                  CALC_BASIC AMOUNT,
                  'A' PAY_TYPE_FLAG,
                  0 PRIORITY_INDEX
                FROM HR_SALARY_SHEET_DETAIL
                WHERE 1          = 1
                AND SHEET_NO     =''
                AND EMPLOYEE_CODE=''
                AND COMPANY_CODE ='01'
                AND BRANCH_CODE  ='01.01'
                AND DELETED_FLAG ='N'
                UNION ALL
                SELECT A.PAY_CODE,
                  B.PAY_EDESC,
                  B.PAY_NDESC,
                  A.AMOUNT,
                  A.PAY_TYPE_FLAG,
                  B.PRIORITY_INDEX
                FROM HR_SALARY_PAY_DETAIL A,
                  HR_PAY_SETUP B
                WHERE 1                     = 1
                AND SHEET_NO                =(SELECT HSS.SHEET_NO
                    FROM HR_SALARY_SHEET HSS
                    JOIN HR_EMPLOYEE_SETUP HES
                    ON (HSS.SAL_SHEET_CODE   =HES.SAL_SHEET_CODE)
                    WHERE HSS.PERIOD_DT_CODE ='{$periodDtCode}'
                    AND HSS.COMPANY_CODE     ='{$companyCode}'
                    AND HSS.BRANCH_CODE      ='{$companyCode}.01'
                    AND HES.EMPLOYEE_CODE    ='{$employeeCode}')
                AND A.EMPLOYEE_CODE         ='{$employeeCode}'
                AND A.COMPANY_CODE          ='{$companyCode}'
                AND A.BRANCH_CODE           ='{$companyCode}.01'
                AND A.DELETED_FLAG          ='N'
                AND A.PAY_CODE              =B.PAY_CODE
                AND A.COMPANY_CODE          =B.COMPANY_CODE
                AND A.BRANCH_CODE           = B.BRANCH_CODE
                AND A.PAY_TYPE_FLAG        IN ('A','D')
                AND B.INVISIBLE_ON_PAY_SLIP = 'N'
                ORDER BY PRIORITY_INDEX";
        return $this->rawQuery($sql);
    }

}
