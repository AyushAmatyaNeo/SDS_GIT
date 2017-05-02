<?php
namespace SelfService\Repository;

use Application\Model\Model;
use Application\Repository\RepositoryInterface;
use Zend\Db\Adapter\AdapterInterface;
use SelfService\Model\Overtime;
use SelfService\Model\OvertimeDetail;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Setup\Model\HrEmployees;
use Application\Helper\Helper;
use Application\Helper\EntityHelper;

class OvertimeRepository implements RepositoryInterface{
    private $tableGateway;
    private $adapter;
    
    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
        $this->tableGateway = new TableGateway(Overtime::TABLE_NAME,$adapter);
    }

    public function add(Model $model) {
        $this->tableGateway->insert($model->getArrayCopyForDB());
    }

    public function delete($id) {
        $currentDate = Helper::getcurrentExpressionDate();
        $this->tableGateway->update([Overtime::STATUS => 'C', Overtime::MODIFIED_DATE=>$currentDate], [Overtime::OVERTIME_ID => $id]);
    }

    public function edit(Model $model, $id) {
        
    }

    public function fetchAll() {
        
    }

    public function fetchById($id) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(EntityHelper::getColumnNameArrayWithOracleFns(Overtime::class, null, [Overtime::OVERTIME_DATE,Overtime::REQUESTED_DATE, Overtime::RECOMMENDED_DATE, Overtime::APPROVED_DATE, Overtime::MODIFIED_DATE], NULL, NULL, NULL, "OT"), false);

        $select->from(['OT' => Overtime::TABLE_NAME])
                ->join(['E' => HrEmployees::TABLE_NAME], "E.".HrEmployees::EMPLOYEE_ID."=OT.". Overtime::EMPLOYEE_ID, ["FIRST_NAME" => new Expression("INITCAP(E.FIRST_NAME)"),"MIDDLE_NAME" => new Expression("INITCAP(E.MIDDLE_NAME)"),"LAST_NAME" => new Expression("INITCAP(E.LAST_NAME)")])
                ->join(['E1'=>"HRIS_EMPLOYEES"],"E1.EMPLOYEE_ID=OT.RECOMMENDED_BY",['FN1' =>  new Expression("INITCAP(E1.FIRST_NAME)"), 'MN1' => new Expression("INITCAP(E1.MIDDLE_NAME)"), 'LN1' => new Expression("INITCAP(E1.LAST_NAME)")],"left")
                ->join(['E2'=>"HRIS_EMPLOYEES"],"E2.EMPLOYEE_ID=OT.APPROVED_BY",['FN2' =>  new Expression("INITCAP(E2.FIRST_NAME)"), 'MN2' => new Expression("INITCAP(E2.MIDDLE_NAME)"), 'LN2' => new Expression("INITCAP(E2.LAST_NAME)")],"left");

        $select->where([
            "OT.OVERTIME_ID=" . $id
        ]);
        $select->order("OT.REQUESTED_DATE DESC");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }
    public function getAllByEmployeeId($employeeId){
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(EntityHelper::getColumnNameArrayWithOracleFns(Overtime::class, null, [Overtime::OVERTIME_DATE,Overtime::REQUESTED_DATE, Overtime::RECOMMENDED_DATE, Overtime::APPROVED_DATE, Overtime::MODIFIED_DATE], NULL, NULL, NULL, "OT"), false);

        $select->from(['OT' => Overtime::TABLE_NAME])
                ->join(['E' => HrEmployees::TABLE_NAME], "E.".HrEmployees::EMPLOYEE_ID."=OT.". Overtime::EMPLOYEE_ID, ["FIRST_NAME" => new Expression("INITCAP(E.FIRST_NAME)"),"MIDDLE_NAME" => new Expression("INITCAP(E.MIDDLE_NAME)"),"LAST_NAME" => new Expression("INITCAP(E.LAST_NAME)")])
                ->join(['E1'=>"HRIS_EMPLOYEES"],"E1.EMPLOYEE_ID=OT.RECOMMENDED_BY",['FN1' =>  new Expression("INITCAP(E1.FIRST_NAME)"), 'MN1' => new Expression("INITCAP(E1.MIDDLE_NAME)"), 'LN1' => new Expression("INITCAP(E1.LAST_NAME)")],"left")
                ->join(['E2'=>"HRIS_EMPLOYEES"],"E2.EMPLOYEE_ID=OT.APPROVED_BY",['FN2' =>  new Expression("INITCAP(E2.FIRST_NAME)"), 'MN2' => new Expression("INITCAP(E2.MIDDLE_NAME)"), 'LN2' => new Expression("INITCAP(E2.LAST_NAME)")],"left");

        $select->where([
            "E.EMPLOYEE_ID=" . $employeeId
        ]);
        $select->order("OT.REQUESTED_DATE DESC");
        $statement = $sql->prepareStatementForSqlObject($select);
//        print_r($statement->getSql()); die();
        $result = $statement->execute();
        return $result;
    }
}