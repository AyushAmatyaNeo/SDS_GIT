<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 10/25/16
 * Time: 12:08 PM
 */

namespace LeaveManagement\Repository;

use Application\Model\Model;
use Application\Repository\RepositoryInterface;
use LeaveManagement\Model\LeaveApply;
use Setup\Model\HrEmployees;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class LeaveStatusRepository implements RepositoryInterface {

    private $adapter;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
    }

    public function add(Model $model) {
        // TODO: Implement add() method.
    }

    public function edit(Model $model, $id) {
        // TODO: Implement edit() method.
    }

//    public function getAllRequest($status = null, $date = null, $branchId = NULL, $employeeId = NULL) {
//
//        $sql = "SELECT L.LEAVE_ENAME,LA.NO_OF_DAYS,
//                TO_CHAR(LA.START_DATE, 'DD-MON-YYYY') AS START_DATE,
//                TO_CHAR(LA.END_DATE, 'DD-MON-YYYY') AS END_DATE,
//                TO_CHAR(LA.REQUESTED_DT, 'DD-MON-YYYY') AS APPLIED_DATE,
//                LA.STATUS AS STATUS,
//                LA.ID AS ID,
//                TO_CHAR(LA.RECOMMENDED_DT, 'DD-MON-YYYY') AS RECOMMENDED_DT,
//                TO_CHAR(LA.APPROVED_DT, 'DD-MON-YYYY') AS APPROVED_DT,
//                E.FIRST_NAME,E.MIDDLE_NAME,E.LAST_NAME,
//                E1.FIRST_NAME AS FN1,E1.MIDDLE_NAME AS MN1,E1.LAST_NAME AS LN1,
//                E2.FIRST_NAME AS FN2,E2.MIDDLE_NAME AS MN2,E2.LAST_NAME AS LN2,
//                LA.RECOMMENDED_BY AS RECOMMENDER,
//                LA.APPROVED_BY AS APPROVER
//                FROM HR_EMPLOYEE_LEAVE_REQUEST LA, 
//                HR_LEAVE_MASTER_SETUP L,
//                HR_EMPLOYEES E,
//                HR_EMPLOYEES E1,
//                HR_EMPLOYEES E2
//                WHERE 
//                L.STATUS='E' AND
//                E.STATUS='E' AND
//                E1.STATUS='E' AND
//                E2.STATUS='E' AND
//                L.LEAVE_ID=LA.LEAVE_ID AND
//                E.EMPLOYEE_ID=LA.EMPLOYEE_ID AND
//                E1.EMPLOYEE_ID=LA.RECOMMENDED_BY AND
//                E2.EMPLOYEE_ID=LA.APPROVED_BY";
//        if ($status != null) {
//            $sql .= " AND LA.STATUS ='" . $status . "'";
//        }
//        if ($date != null) {
//            $sql .= "AND (" . $date->getExpression() . " between LA.START_DATE AND LA.END_DATE)";
//        }
//
//        if ($branchId != null) {
//            $sql .= " AND E." . HrEmployees::EMPLOYEE_ID . " IN (SELECT " . HrEmployees::EMPLOYEE_ID . " FROM " . HrEmployees::TABLE_NAME . " WHERE " . HrEmployees::BRANCH_ID . "= $branchId)";
//        }
//
//        if ($employeeId != null) {
//            $sql .= "AND E." . HrEmployees::EMPLOYEE_ID . " = $employeeId";
//        }
//        $statement = $this->adapter->query($sql);
//
//        $result = $statement->execute();
//        return $result;
//    }
    public function getAllRequest($status = null, $date = null, $branchId = NULL, $employeeId = NULL) {

        $sql = "SELECT L.LEAVE_ENAME,
                LA.NO_OF_DAYS,
                TO_CHAR(LA.START_DATE, 'DD-MON-YYYY')     AS START_DATE,
                TO_CHAR(LA.END_DATE, 'DD-MON-YYYY')       AS END_DATE,
                TO_CHAR(LA.REQUESTED_DT, 'DD-MON-YYYY')   AS APPLIED_DATE,
                LA.STATUS                                 AS STATUS,
                LA.ID                                     AS ID,
                TO_CHAR(LA.RECOMMENDED_DT, 'DD-MON-YYYY') AS RECOMMENDED_DT,
                TO_CHAR(LA.APPROVED_DT, 'DD-MON-YYYY')    AS APPROVED_DT,
                E.FIRST_NAME,
                E.MIDDLE_NAME,
                E.LAST_NAME
                FROM HR_EMPLOYEE_LEAVE_REQUEST LA,
                  HR_LEAVE_MASTER_SETUP L,
                  HR_EMPLOYEES E
                WHERE L.STATUS   ='E'
                AND E.STATUS     ='E'
                AND L.LEAVE_ID   =LA.LEAVE_ID
                AND E.EMPLOYEE_ID=LA.EMPLOYEE_ID ";
        if ($status != null) {
            $sql .= " AND LA.STATUS ='" . $status . "'";
        }
        if ($date != null) {
            $sql .= "AND (" . $date->getExpression() . " between LA.START_DATE AND LA.END_DATE)";
        }

        if ($branchId != null) {
            $sql .= " AND E." . HrEmployees::EMPLOYEE_ID . " IN (SELECT " . HrEmployees::EMPLOYEE_ID . " FROM " . HrEmployees::TABLE_NAME . " WHERE " . HrEmployees::BRANCH_ID . "= $branchId)";
        }

        if ($employeeId != null) {
            $sql .= "AND E." . HrEmployees::EMPLOYEE_ID . " = $employeeId";
        }
        $statement = $this->adapter->query($sql);

        $result = $statement->execute();
        return $result;
    }

    public function fetchAll() {
        // TODO: Implement fetchAll() method.
    }

    public function fetchById($id) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            new Expression("TO_CHAR(LA.START_DATE, 'DD-MON-YYYY') AS START_DATE"),
            new Expression("TO_CHAR(LA.REQUESTED_DT, 'DD-MON-YYYY') AS REQUESTED_DT"),
            new Expression("LA.STATUS AS STATUS"),
            new Expression("LA.ID AS ID"),
            new Expression("TO_CHAR(LA.END_DATE, 'DD-MON-YYYY') AS END_DATE"),
            new Expression("TO_CHAR(LA.APPROVED_DT, 'DD-MON-YYYY') AS APPROVED_DT"),
            new Expression("LA.NO_OF_DAYS AS NO_OF_DAYS"),
            new Expression("LA.HALF_DAY AS HALF_DAY"),
            new Expression("LA.EMPLOYEE_ID AS EMPLOYEE_ID"),
            new Expression("LA.LEAVE_ID AS LEAVE_ID"),
            new Expression("LA.REMARKS AS REMARKS"),
            new Expression("LA.RECOMMENDED_REMARKS AS RECOMMENDED_REMARKS"),
            new Expression("LA.APPROVED_REMARKS AS APPROVED_REMARKS"),
                ], true);

        $select->from(['LA' => LeaveApply::TABLE_NAME])
                ->join(['E' => "HR_EMPLOYEES"], "E.EMPLOYEE_ID=LA.EMPLOYEE_ID", ['FIRST_NAME', 'MIDDLE_NAME', 'LAST_NAME'], "left")
                ->join(['E1' => "HR_EMPLOYEES"], "E1.EMPLOYEE_ID=LA.RECOMMENDED_BY", ['FN1' => 'FIRST_NAME', 'MN1' => 'MIDDLE_NAME', 'LN1' => 'LAST_NAME'], "left")
                ->join(['E2' => "HR_EMPLOYEES"], "E2.EMPLOYEE_ID=LA.APPROVED_BY", ['FN2' => 'FIRST_NAME', 'MN2' => 'MIDDLE_NAME', 'LN2' => 'LAST_NAME'], "left");

        $select->where([
            "LA.ID=" . $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);
        //print_r($statement->getSql()); DIE();
        $result = $statement->execute();
        return $result->current();
    }

    public function delete($id) {
        // TODO: Implement delete() method.
    }

    public function getFilteredRecord($data, $recomApproveId = null) {
        $fromDate = $data['fromDate'];
        $toDate = $data['toDate'];
        $employeeId = $data['employeeId'];
        $branchId = $data['branchId'];
        $departmentId = $data['departmentId'];
        $designationId = $data['designationId'];
        $positionId = $data['positionId'];
        $serviceTypeId = $data['serviceTypeId'];
        $serviceEventTypeId = $data['serviceEventTypeId'];
        $leaveId = $data['leaveId'];
        $leaveRequestStatusId = $data['leaveRequestStatusId'];

        if ($serviceEventTypeId == 5 || $serviceEventTypeId == 8 || $serviceEventTypeId == 14) {
            $retiredFlag = " AND E.RETIRED_FLAG='Y' ";
        } else {
            $retiredFlag = " AND E.RETIRED_FLAG='N' ";
        }

        $sql = "SELECT L.LEAVE_ENAME,L.LEAVE_CODE,LA.NO_OF_DAYS,
                TO_CHAR(LA.START_DATE, 'DD-MON-YYYY') AS START_DATE,
                TO_CHAR(LA.END_DATE, 'DD-MON-YYYY') AS END_DATE,
                TO_CHAR(LA.REQUESTED_DT, 'DD-MON-YYYY') AS APPLIED_DATE,
                LA.STATUS AS STATUS,
                LA.ID AS ID,
                LA.EMPLOYEE_ID AS EMPLOYEE_ID,
                TO_CHAR(LA.RECOMMENDED_DT, 'DD-MON-YYYY') AS RECOMMENDED_DT,
                TO_CHAR(LA.APPROVED_DT, 'DD-MON-YYYY') AS APPROVED_DT,
                E.FIRST_NAME,E.MIDDLE_NAME,E.LAST_NAME,
                E1.FIRST_NAME AS FN1,E1.MIDDLE_NAME AS MN1,E1.LAST_NAME AS LN1,
                E2.FIRST_NAME AS FN2,E2.MIDDLE_NAME AS MN2,E2.LAST_NAME AS LN2,
                RA.RECOMMEND_BY AS RECOMMENDER,
                RA.APPROVED_BY AS APPROVER,
                RECM.FIRST_NAME AS RECM_FN,RECM.MIDDLE_NAME AS RECM_MN,RECM.LAST_NAME AS RECM_LN,
                APRV.FIRST_NAME AS APRV_FN,APRV.MIDDLE_NAME AS APRV_MN,APRV.LAST_NAME AS APRV_LN,
                LA.RECOMMENDED_BY AS RECOMMENDED_BY,
                LA.APPROVED_BY AS APPROVED_BY,
                LA.RECOMMENDED_REMARKS AS RECOMMENDED_REMARKS,
                LA.APPROVED_REMARKS AS APPROVED_REMARKS
                FROM HR_EMPLOYEE_LEAVE_REQUEST LA
                LEFT OUTER JOIN HR_LEAVE_MASTER_SETUP L ON
                L.LEAVE_ID=LA.LEAVE_ID 
                LEFT OUTER JOIN HR_EMPLOYEES E ON
                E.EMPLOYEE_ID=LA.EMPLOYEE_ID
                LEFT OUTER JOIN HR_EMPLOYEES E1 ON
                E1.EMPLOYEE_ID=LA.RECOMMENDED_BY
                LEFT OUTER JOIN HR_EMPLOYEES E2 ON
                E2.EMPLOYEE_ID=LA.APPROVED_BY
                LEFT OUTER JOIN HR_RECOMMENDER_APPROVER RA ON
                LA.EMPLOYEE_ID = RA.EMPLOYEE_ID
                LEFT OUTER JOIN HR_EMPLOYEES RECM ON
                RECM.EMPLOYEE_ID = RA.RECOMMEND_BY
                LEFT OUTER JOIN HR_EMPLOYEES APRV ON
                APRV.EMPLOYEE_ID = RA.APPROVED_BY
                WHERE 
                L.STATUS='E' AND
                E.STATUS='E'" . $retiredFlag . "              
                AND
                (E1.STATUS = CASE WHEN E1.STATUS IS NOT NULL
                         THEN ('E')     
                    END OR  E1.STATUS is null) AND
                (E2.STATUS = CASE WHEN E2.STATUS IS NOT NULL
                         THEN ('E')       
                    END OR  E2.STATUS is null) AND
                (RECM.STATUS = CASE WHEN RECM.STATUS IS NOT NULL
                         THEN ('E')       
                    END OR  RECM.STATUS is null) AND
                (APRV.STATUS = CASE WHEN APRV.STATUS IS NOT NULL
                         THEN ('E')       
                    END OR  APRV.STATUS is null)";
        if ($recomApproveId == null) {
            if ($leaveRequestStatusId != -1) {
                $sql .= " AND LA.STATUS ='" . $leaveRequestStatusId . "'";
            }
        }
        if ($recomApproveId != null) {
            if ($leaveRequestStatusId == -1) {
                $sql .= " AND ((RA.RECOMMEND_BY=" . $recomApproveId . " AND  LA.STATUS='RQ') "
                        . "OR (LA.RECOMMENDED_BY=" . $recomApproveId . " AND (LA.STATUS='RC' OR LA.STATUS='R' OR LA.STATUS='AP')) "
                        . "OR (RA.APPROVED_BY=" . $recomApproveId . " AND  LA.STATUS='RC' ) "
                        . "OR (LA.APPROVED_BY=" . $recomApproveId . " AND (LA.STATUS='AP' OR (LA.STATUS='R' AND LA.APPROVED_DT IS NOT NULL))) )";
            } else if ($leaveRequestStatusId == 'RQ') {
                $sql .= " AND (RA.RECOMMEND_BY=" . $recomApproveId . " AND LA.STATUS='RQ')";
            } else if ($leaveRequestStatusId == 'RC') {
                $sql .= " AND LA.STATUS='RC' AND
                    (LA.RECOMMENDED_BY=" . $recomApproveId . " OR RA.APPROVED_BY=" . $recomApproveId . ")";
            } else if ($leaveRequestStatusId == 'AP') {
                $sql .= " AND LA.STATUS='AP' AND
                    (LA.RECOMMENDED_BY=" . $recomApproveId . " OR LA.APPROVED_BY=" . $recomApproveId . ")";
            } else if ($leaveRequestStatusId == 'R') {
                $sql .= " AND LA.STATUS='" . $leaveRequestStatusId . "' AND
                    ((LA.RECOMMENDED_BY=" . $recomApproveId . ") OR (LA.APPROVED_BY=" . $recomApproveId . " AND LA.APPROVED_DT IS NOT NULL) )";
            }
        }

        if ($leaveId != -1) {
            $sql .= " AND LA.LEAVE_ID ='" . $leaveId . "'";
        }

        if ($fromDate != null) {
            $sql .= " AND LA.START_DATE>=TO_DATE('" . $fromDate . "','DD-MM-YYYY')";
        }

        if ($toDate != null) {
            $sql .= "AND LA.END_DATE<=TO_DATE('" . $toDate . "','DD-MM-YYYY')";
        }

        if ($employeeId != -1) {
            $sql .= "AND E." . HrEmployees::EMPLOYEE_ID . " = $employeeId";
        }

        if ($branchId != -1) {
            $sql .= " AND E." . HrEmployees::EMPLOYEE_ID . " IN (SELECT " . HrEmployees::EMPLOYEE_ID . " FROM " . HrEmployees::TABLE_NAME . " WHERE " . HrEmployees::BRANCH_ID . "= $branchId)";
        }
        if ($departmentId != -1) {
            $sql .= " AND E." . HrEmployees::EMPLOYEE_ID . " IN (SELECT " . HrEmployees::EMPLOYEE_ID . " FROM " . HrEmployees::TABLE_NAME . " WHERE " . HrEmployees::DEPARTMENT_ID . "= $departmentId)";
        }
        if ($designationId != -1) {
            $sql .= " AND E." . HrEmployees::EMPLOYEE_ID . " IN (SELECT " . HrEmployees::EMPLOYEE_ID . " FROM " . HrEmployees::TABLE_NAME . " WHERE " . HrEmployees::DESIGNATION_ID . "= $designationId)";
        }
        if ($positionId != -1) {
            $sql .= " AND E." . HrEmployees::EMPLOYEE_ID . " IN (SELECT " . HrEmployees::EMPLOYEE_ID . " FROM " . HrEmployees::TABLE_NAME . " WHERE " . HrEmployees::POSITION_ID . "= $positionId)";
        }
        if ($serviceTypeId != -1) {
            $sql .= " AND E." . HrEmployees::EMPLOYEE_ID . " IN (SELECT " . HrEmployees::EMPLOYEE_ID . " FROM " . HrEmployees::TABLE_NAME . " WHERE " . HrEmployees::SERVICE_TYPE_ID . "= $serviceTypeId)";
        }
        if ($serviceEventTypeId != -1) {
            $sql .= " AND E." . HrEmployees::EMPLOYEE_ID . " IN (SELECT " . HrEmployees::EMPLOYEE_ID . " FROM " . HrEmployees::TABLE_NAME . " WHERE " . HrEmployees::SERVICE_EVENT_TYPE_ID . "= $serviceEventTypeId)";
        }

        $sql .= " ORDER BY LA.REQUESTED_DT DESC";

        $statement = $this->adapter->query($sql);
        //print_r($statement->getSql());  die();
        $result = $statement->execute();
        return $result;
    }

}
