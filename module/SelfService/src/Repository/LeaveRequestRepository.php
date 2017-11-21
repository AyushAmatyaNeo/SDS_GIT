<?php

namespace SelfService\Repository;

use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Application\Model\Model;
use Application\Repository\RepositoryInterface;
use LeaveManagement\Model\LeaveApply;
use LeaveManagement\Model\LeaveAssign;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;

class LeaveRequestRepository implements RepositoryInterface {

    private $tableGateway;
    private $adapter;

    public function __construct(AdapterInterface $adapter) {
        $this->tableGateway = new TableGateway(LeaveApply::TABLE_NAME, $adapter);
        $this->tableGatewayLeaveAssign = new TableGateway(LeaveAssign::TABLE_NAME, $adapter);
        $this->adapter = $adapter;
    }

    public function add(Model $model) {
        $this->tableGateway->insert($model->getArrayCopyForDB());
    }

    public function edit(Model $model, $id) {
        // TODO: Implement edit() method.
    }

    public function fetchAll() {
        // TODO: Implement fetchAll() method.
    }

    //to get the all applied leave request list
    public function selectAll($employeeId) {

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            new Expression("INITCAP(TO_CHAR(LA.START_DATE, 'DD-MON-YYYY')) AS FROM_DATE"),
            new Expression("INITCAP(TO_CHAR(LA.END_DATE, 'DD-MON-YYYY')) AS TO_DATE"),
            new Expression("LA.STATUS AS STATUS"),
            new Expression("INITCAP(TO_CHAR(LA.REQUESTED_DT, 'DD-MON-YYYY')) AS REQUESTED_DT"),
            new Expression("LA.NO_OF_DAYS AS NO_OF_DAYS"),
            new Expression("LA.ID AS ID"),
                ], true);

        $select->from(['LA' => LeaveApply::TABLE_NAME])
                ->join(['E' => "HRIS_EMPLOYEES"], "E.EMPLOYEE_ID=LA.EMPLOYEE_ID", ["FIRST_NAME" => new Expression("INITCAP(E.FIRST_NAME)"), "MIDDLE_NAME" => new Expression("INITCAP(E.MIDDLE_NAME)"), "LAST_NAME" => new Expression("INITCAP(E.LAST_NAME)")])
                ->join(['L' => 'HRIS_LEAVE_MASTER_SETUP'], "L.LEAVE_ID=LA.LEAVE_ID", ['LEAVE_CODE', 'LEAVE_ENAME' => new Expression("INITCAP(L.LEAVE_ENAME)")]);

        $select->where([
            "L.STATUS='E'",
            "E.EMPLOYEE_ID=" . $employeeId
        ]);
        $select->order("LA.REQUESTED_DT DESC");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }

    //to get the leave detail based on assigned employee id
    public function getLeaveDetail($employeeId, $leaveId) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([new Expression("LA.BALANCE AS BALANCE")], true);

        $select->from(['LA' => LeaveAssign::TABLE_NAME])
                ->join(['E' => "HRIS_EMPLOYEES"], "E.EMPLOYEE_ID=LA.EMPLOYEE_ID", ["FIRST_NAME" => new Expression("INITCAP(E.FIRST_NAME)"), "MIDDLE_NAME" => new Expression("INITCAP(E.MIDDLE_NAME)"), "LAST_NAME" => new Expression("INITCAP(E.LAST_NAME)")])
                ->join(['L' => 'HRIS_LEAVE_MASTER_SETUP'], "L.LEAVE_ID=LA.LEAVE_ID", ['LEAVE_CODE', 'LEAVE_ENAME' => new Expression("INITCAP(L.LEAVE_ENAME)"), 'ALLOW_HALFDAY', 'ALLOW_GRACE_LEAVE', 'IS_SUBSTITUTE_MANDATORY', 'ENABLE_SUBSTITUTE']);

        $select->where([
            "L.STATUS='E'",
            "E.EMPLOYEE_ID=" . $employeeId,
            "L.LEAVE_ID=" . $leaveId
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }

    //to get the leave list based on assigned employee id for select option
    public function getLeaveList($employeeId) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['LA' => LeaveAssign::TABLE_NAME])
                ->join(['L' => 'HRIS_LEAVE_MASTER_SETUP'], "L.LEAVE_ID=LA.LEAVE_ID", ['LEAVE_CODE', 'LEAVE_ENAME' => new Expression("INITCAP(L.LEAVE_ENAME)")]);
        $select->where([
            "L.STATUS='E'",
            "LA.EMPLOYEE_ID=" . $employeeId
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);

        $resultset = $statement->execute();

        $entitiesArray = array();
        foreach ($resultset as $result) {
            $entitiesArray[$result['LEAVE_ID']] = $result['LEAVE_ENAME'];
        }
        return $entitiesArray;
    }

    public function fetchById($id) {
        // TODO: Implement fetchById() method.
    }

    public function delete($id) {
        $currentDate = Helper::getcurrentExpressionDate();
        $this->tableGateway->update([LeaveApply::STATUS => 'C', LeaveApply::MODIFIED_DT => $currentDate], [LeaveApply::ID => $id]);
        EntityHelper::rawQueryResult($this->adapter, "
                   DECLARE
                      V_ID HRIS_EMPLOYEE_LEAVE_REQUEST.ID%TYPE;
                      V_STATUS HRIS_EMPLOYEE_LEAVE_REQUEST.STATUS%TYPE;
                      V_START_DATE HRIS_EMPLOYEE_LEAVE_REQUEST.START_DATE%TYPE;
                      V_EMPLOYEE_ID HRIS_EMPLOYEE_LEAVE_REQUEST.EMPLOYEE_ID%TYPE;
                    BEGIN
                      SELECT ID,
                        STATUS,
                        START_DATE,
                        EMPLOYEE_ID
                      INTO V_ID,
                        V_STATUS,
                        V_START_DATE,
                        V_EMPLOYEE_ID
                      FROM HRIS_EMPLOYEE_LEAVE_REQUEST
                      WHERE ID                                    = {$id};
                      IF(V_STATUS IN ('AP','C') AND V_START_DATE <=TRUNC(SYSDATE)) THEN
                        HRIS_REATTENDANCE(V_START_DATE,V_EMPLOYEE_ID);
                      END IF;
                    END;
    ");
    }

    public function checkEmployeeLeave($employeeId, $date) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['L' => LeaveApply::TABLE_NAME]);
        $select->where(["L." . LeaveApply::EMPLOYEE_ID . "=$employeeId"]);
        $select->where([$date->getExpression() . " BETWEEN " . "L." . LeaveApply::START_DATE . " AND L." . LeaveApply::END_DATE]);
        $select->where(['L.' . LeaveApply::STATUS . " = " . "'AP'"]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }

    public function getfilterRecords($data) {
        $employeeId = $data['employeeId'];
        $leaveId = $data['leaveId'];
        $leaveRequestStatusId = $data['leaveRequestStatusId'];
        $fromDate = $data['fromDate'];
        $toDate = $data['toDate'];

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            new Expression("LA.ID AS ID"),
            new Expression("LA.EMPLOYEE_ID AS EMPLOYEE_ID"),
            new Expression("LA.LEAVE_ID AS LEAVE_ID"),
            new Expression("INITCAP(TO_CHAR(LA.START_DATE, 'DD-MON-YYYY')) AS FROM_DATE_AD"),
            new Expression("BS_DATE(TO_CHAR(LA.START_DATE, 'DD-MON-YYYY')) AS FROM_DATE_BS"),
            new Expression("INITCAP(TO_CHAR(LA.END_DATE, 'DD-MON-YYYY')) AS TO_DATE_AD"),
            new Expression("BS_DATE(TO_CHAR(LA.END_DATE, 'DD-MON-YYYY')) AS TO_DATE_BS"),
            new Expression("LA.HALF_DAY AS HALF_DAY"),
            new Expression("(CASE WHEN (LA.HALF_DAY IS NULL OR LA.HALF_DAY = 'N') THEN 'Full Day' WHEN (LA.HALF_DAY = 'F') THEN 'First Half' ELSE 'Second Half' END) AS HALF_DAY_DETAIL"),
            new Expression("LA.GRACE_PERIOD AS GRACE_PERIOD"),
            new Expression("(CASE WHEN LA.GRACE_PERIOD = 'E' THEN 'Early' WHEN LA.GRACE_PERIOD = 'L' THEN 'Late' ELSE '-' END) AS GRACE_PERIOD_DETAIL"),
            new Expression("LA.NO_OF_DAYS AS NO_OF_DAYS"),
            new Expression("INITCAP(TO_CHAR(LA.REQUESTED_DT, 'DD-MON-YYYY')) AS REQUESTED_DT_AD"),
            new Expression("BS_DATE(TO_CHAR(LA.REQUESTED_DT, 'DD-MON-YYYY')) AS REQUESTED_DT_BS"),
            new Expression("LA.REMARKS AS REMARKS"),
            new Expression("LA.STATUS AS STATUS"),
            new Expression("LEAVE_STATUS_DESC(LA.STATUS) AS STATUS_DETAIL"),
            new Expression("LA.RECOMMENDED_BY AS RECOMMENDED_BY"),
            new Expression("INITCAP(TO_CHAR(LA.RECOMMENDED_DT, 'DD-MON-YYYY')) AS RECOMMENDED_DT"),
            new Expression("LA.RECOMMENDED_REMARKS AS RECOMMENDED_REMARKS"),
            new Expression("LA.APPROVED_BY AS APPROVED_BY"),
            new Expression("INITCAP(TO_CHAR(LA.APPROVED_DT, 'DD-MON-YYYY')) AS APPROVED_DT"),
            new Expression("LA.APPROVED_REMARKS AS APPROVED_REMARKS"),
            new Expression("(CASE WHEN LA.STATUS = 'XX' THEN 'Y' ELSE 'N' END) AS ALLOW_EDIT"),
            new Expression("(CASE WHEN LA.STATUS IN ('RQ','RC','AP') THEN 'Y' ELSE 'N' END) AS ALLOW_DELETE"),
                ], true);

        $select->from(['LA' => LeaveApply::TABLE_NAME])
                ->join(['L' => 'HRIS_LEAVE_MASTER_SETUP'], "L.LEAVE_ID=LA.LEAVE_ID", ['LEAVE_CODE', 'LEAVE_ENAME' => new Expression("INITCAP(L.LEAVE_ENAME)")])
                ->join(['E' => 'HRIS_EMPLOYEES'], 'LA.EMPLOYEE_ID=E.EMPLOYEE_ID', ["FULL_NAME" => new Expression("INITCAP(E.FULL_NAME)")], "left")
                ->join(['E2' => "HRIS_EMPLOYEES"], "E2.EMPLOYEE_ID=LA.RECOMMENDED_BY", ['RECOMMENDED_BY_NAME' => new Expression("INITCAP(E2.FULL_NAME)")], "left")
                ->join(['E3' => "HRIS_EMPLOYEES"], "E3.EMPLOYEE_ID=LA.APPROVED_BY", ['APPROVED_BY_NAME' => new Expression("INITCAP(E3.FULL_NAME)")], "left")
                ->join(['RA' => "HRIS_RECOMMENDER_APPROVER"], "RA.EMPLOYEE_ID=LA.EMPLOYEE_ID", ['RECOMMENDER_ID' => 'RECOMMEND_BY', 'APPROVER_ID' => 'APPROVED_BY'], "left")
                ->join(['RECM' => "HRIS_EMPLOYEES"], "RECM.EMPLOYEE_ID=RA.RECOMMEND_BY", ['RECOMMENDER_NAME' => new Expression("INITCAP(RECM.FULL_NAME)")], "left")
                ->join(['APRV' => "HRIS_EMPLOYEES"], "APRV.EMPLOYEE_ID=RA.APPROVED_BY", ['APPROVER_NAME' => new Expression("INITCAP(APRV.FULL_NAME)")], "left");
        $select->where([
            "L.STATUS='E'",
            "E.EMPLOYEE_ID=" . $employeeId
        ]);

        if ($leaveId != null && $leaveId != -1) {
            $select->where(["LA.LEAVE_ID" => $leaveId]);
        }
        if ($leaveRequestStatusId != -1) {
            $select->where(["LA.STATUS" => $leaveRequestStatusId]);
        }
        if ($leaveRequestStatusId != 'C') {
            $select->where([
                "(TRUNC(SYSDATE)- LA.REQUESTED_DT) < (
                      CASE
                        WHEN LA.STATUS = 'C'
                        THEN 20
                        ELSE 365
                      END)"
            ]);
        }

        if ($fromDate != null) {
            $select->where("LA.START_DATE>=TO_DATE('" . $fromDate . "','DD-MM-YYYY')");
        }
        if ($toDate != null) {
            $select->where(["LA.END_DATE<=TO_DATE('" . $toDate . "','DD-MM-YYYY')"]);
        }
        $select->order("LA.REQUESTED_DT DESC");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }

    public function fetchAvailableDays($fromDate, $toDate, $employeeId) {
        $rawResult = EntityHelper::rawQueryResult($this->adapter, "SELECT HRIS_AVAILABLE_LEAVE_DAYS({$fromDate},{$toDate},{$employeeId}) AS AVAILABLE_DAYS FROM DUAL");
        return $rawResult->current();
    }

    public function validateLeaveRequest($fromDate, $toDate, $employeeId) {
        $rawResult = EntityHelper::rawQueryResult($this->adapter, "SELECT HRIS_VALIDATE_LEAVE_REQUEST({$fromDate},{$toDate},{$employeeId}) AS ERROR FROM DUAL");
        return $rawResult->current();
    }

}
