<?php
/**
 * Created by PhpStorm.
 * User: ukesh
 * Date: 9/9/16
 * Time: 10:53 AM
 */

namespace LeaveManagement\Repository;


use Application\Model\Model;
use Application\Repository\RepositoryInterface;
use LeaveManagement\Model\LeaveMaster;
use Zend\Console\Prompt\Select;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
use LeaveManagement\Model\LeaveAssign;

class LeaveAssignRepository implements RepositoryInterface
{
    private $tableGateway;
    private $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->tableGateway = new TableGateway(LeaveAssign::TABLE_NAME, $adapter);
        $this->adapter = $adapter;
    }

    public function add(Model $model)
    {
        $this->tableGateway->insert($model->getArrayCopyForDB());
    }


    public function edit(Model $model, $id)
    {
        $array = $model->getArrayCopyForDB();
        $this->tableGateway->update($array, [LeaveAssign::EMPLOYEE_LEAVE_ASSIGN_ID => $id]);
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function fetchByEmployeeId($id)
    {
//        return $this->tableGateway->select(['EMPLOYEE_ID' => $id]);

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['A' => LeaveAssign::TABLE_NAME])
            ->join(['S' => 'HR_LEAVE_MASTER_SETUP'], 'A.LEAVE_ID=S.LEAVE_ID');
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;

    }

    public function filter($leaveId, $branchId, $departmentId, $genderId, $designationId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();

        $select->columns(["EMPLOYEE_ID", "FIRST_NAME", "MIDDLE_NAME", "LAST_NAME"], true);
        $select->from(['E' => "HR_EMPLOYEES"])
            ->join(['L' => LeaveAssign::TABLE_NAME], 'E.EMPLOYEE_ID=L.EMPLOYEE_ID', [LeaveAssign::LEAVE_ID, LeaveAssign::BALANCE], \Zend\Db\Sql\Select::JOIN_LEFT)
            ->join(['DE'=>'HR_DESIGNATIONS'],'DE.DESIGNATION_ID=E.DESIGNATION_ID',["DESIGNATION_ID","DESIGNATION_TITLE"])
            ->join(['B'=>'HR_BRANCHES'],'B.BRANCH_ID=E.BRANCH_ID',["BRANCH_ID","BRANCH_NAME"])
        ;
        if ($branchId != -1) {
            $select->where(["E.BRANCH_ID=$branchId"]);
        }
        if ($departmentId != -1) {
            $select->where(["DEPARTMENT_ID=$departmentId"]);
        }
        if ($genderId != -1) {
            $select->where(["E.GENDER_ID= $genderId"]);
        }
        if ($designationId != -1) {
            $select->where(["E.DESIGNATION_ID=$designationId"]);
        }
        $select->where(["(L.LEAVE_ID=$leaveId OR L.LEAVE_ID IS NULL)"]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;

    }

    public function fetchById($id)
    {
        $rowset = $this->tableGateway->select([LeaveAssign::EMPLOYEE_LEAVE_ASSIGN_ID => $id]);
        return $rowset->current();
    }

    public function delete($id)
    {
        $this->tableGateway->delete([LeaveAssign::EMPLOYEE_LEAVE_ASSIGN_ID => $id]);

    }
}