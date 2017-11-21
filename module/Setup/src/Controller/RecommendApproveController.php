<?php

namespace Setup\Controller;

use Application\Controller\HrisController;
use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Exception;
use Setup\Form\RecommendApproveForm;
use Setup\Model\Branch;
use Setup\Model\Department;
use Setup\Model\Designation;
use Setup\Model\HrEmployees;
use Setup\Model\RecommendApprove;
use Setup\Repository\EmployeeRepository;
use Setup\Repository\RecommendApproveRepository;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Form\Element\Select;
use Zend\View\Model\JsonModel;

class RecommendApproveController extends HrisController {

    public function __construct(AdapterInterface $adapter, StorageInterface $storage) {
        parent::__construct($adapter, $storage);
        $this->initializeRepository(RecommendApproveRepository::class);
        $this->initializeForm(RecommendApproveForm::class);
    }

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $search = $request->getPost();
                $result = $this->repository->getFilteredList((array) $search);
                $list = Helper::extractDbData($result);
                return new JsonModel(['success' => true, 'data' => $list, 'error' => '']);
            } catch (Exception $e) {
                return new JsonModel(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
            }
        }

        $recommeders = EntityHelper::getTableKVListWithSortOption($this->adapter, "HRIS_EMPLOYEES", "EMPLOYEE_ID", ["FULL_NAME"], ["STATUS" => "E"], "FULL_NAME", "ASC", null, [-1 => "All Recommender"], true);
        $recommenderSE = $this->getSelectElement(['name' => 'recommender', "id" => "recommenderId", "class" => "form-control", "label" => "Recommender"], $recommeders);

        $approvers = EntityHelper::getTableKVListWithSortOption($this->adapter, "HRIS_EMPLOYEES", "EMPLOYEE_ID", ["FULL_NAME"], ["STATUS" => "E"], "FULL_NAME", "ASC", null, [-1 => "All Approver"], true);
        $approverSE = $this->getSelectElement(['name' => 'approver', "id" => "approverId", "class" => "form-control", "label" => "Approver"], $approvers);

        return $this->stickFlashMessagesTo([
                    'approverFormElement' => $recommenderSE,
                    'recommenderFormElement' => $approverSE,
                    'searchValues' => EntityHelper::getSearchData($this->adapter),
                    'acl' => $this->acl,
                    'employeeDetail' => $this->storageData['employee_detail'],
        ]);
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->form->setData($request->getPost());

            if ($this->form->isValid()) {
                $recommendApprove = new RecommendApprove();
                $recommendApprove->exchangeArrayFromForm($this->form->getData());
                $recommendApprove->createdDt = Helper::getcurrentExpressionDate();
                $recommendApprove->createdBy = $this->employeeId;
                $recommendApprove->status = 'E';
                $this->repository->add($recommendApprove);

                $this->flashmessenger()->addMessage("Recommender And Approver Successfully Assigned!!!");
                return $this->redirect()->toRoute("recommendapprove");
            }
        }

        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'employees' => $this->repository->getEmployees()
                        ]
        );
    }

    public function editAction() {
        $id = (int) $this->params()->fromRoute("id");
        $request = $this->getRequest();

        $recommendApprove = new RecommendApprove();
        if (!$request->isPost()) {
            $recommendApprove->exchangeArrayFromDB($this->repository->fetchById($id)->getArrayCopy());
            $this->form->bind($recommendApprove);
        } else {
            $modifiedDt = date('d-M-y');
            $this->form->setData($request->getPost());
            if ($this->form->isValid()) {
                $recommendApprove->exchangeArrayFromForm($this->form->getData());
                $recommendApprove->modifiedDt = Helper::getcurrentExpressionDate();
                $recommendApprove->modifiedBy = $this->employeeId;
                $this->repository->edit($recommendApprove, $id);

                $this->flashmessenger()->addMessage("Recommender And Approver Successfully Assigned!!!");
                return $this->redirect()->toRoute("recommendapprove");
            }
        }
        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'id' => $id,
                    'employeeList' => EntityHelper::getTableKVList($this->adapter, "HRIS_EMPLOYEES", "EMPLOYEE_ID", ["FIRST_NAME", "MIDDLE_NAME", "LAST_NAME"], ["STATUS" => "E"]),
                    'employees' => $this->repository->getEmployees($id)
        ]);
    }

    public function groupAssignAction() {
        $branchFormElement = new Select();
        $branchFormElement->setName("branch");
        $branches = EntityHelper::getTableKVListWithSortOption($this->adapter, Branch::TABLE_NAME, Branch::BRANCH_ID, [Branch::BRANCH_NAME], [Branch::STATUS => 'E'], "BRANCH_NAME", "ASC", null, false, true);
        $branches1 = [-1 => "All"] + $branches;
        $branchFormElement->setValueOptions($branches1);
        $branchFormElement->setAttributes(["id" => "branchId", "class" => "form-control"]);
        $branchFormElement->setLabel("Branch");
        $branchFormElement->setAttribute("ng-click", "view()");

        $departmentFormElement = new Select();
        $departmentFormElement->setName("department");
        $departments = EntityHelper::getTableKVListWithSortOption($this->adapter, Department::TABLE_NAME, Department::DEPARTMENT_ID, [Department::DEPARTMENT_NAME], [Department::STATUS => 'E'], "DEPARTMENT_NAME", "ASC", null, false, true);
        $departments1 = [-1 => "All"] + $departments;
        $departmentFormElement->setValueOptions($departments1);
        $departmentFormElement->setAttributes(["id" => "departmentId", "class" => "form-control"]);
        $departmentFormElement->setLabel("Department");

        $designationFormElement = new Select();
        $designationFormElement->setName("designation");
        $designations = EntityHelper::getTableKVListWithSortOption($this->adapter, Designation::TABLE_NAME, Designation::DESIGNATION_ID, [Designation::DESIGNATION_TITLE], [Designation::STATUS => 'E'], "DESIGNATION_TITLE", "ASC", null, false, true);
        $designations1 = [-1 => "All"] + $designations;
        $designationFormElement->setValueOptions($designations1);
        $designationFormElement->setAttributes(["id" => "designationId", "class" => "form-control"]);
        $designationFormElement->setLabel("Designation");

        $employeeResult = EntityHelper::getTableKVListWithSortOption($this->adapter, HrEmployees::TABLE_NAME, HrEmployees::EMPLOYEE_ID, [HrEmployees::FIRST_NAME, HrEmployees::MIDDLE_NAME, HrEmployees::LAST_NAME], [HrEmployees::STATUS => 'E', HrEmployees::RETIRED_FLAG => 'N'], "FIRST_NAME", "ASC", " ", false, true);
        $employeeList = [];
        foreach ($employeeResult as $key => $value) {
            array_push($employeeList, ['id' => $key, 'name' => $value]);
        }
        return Helper::addFlashMessagesToArray($this, [
                    "branches" => $branchFormElement,
                    "departments" => $departmentFormElement,
                    'designations' => $designationFormElement,
                    'searchValues' => EntityHelper::getSearchData($this->adapter),
                    'employeeList' => $employeeList
        ]);
    }

    public function pullEmployeeForRecAppAssignAction() {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            $companyId = $data['companyId'];
            $positionId = $data['positionId'];
            $serviceTypeId = $data['serviceTypeId'];
            $branchId = $data['branchId'];
            $departmentId = $data['departmentId'];
            $designationId = $data['designationId'];
            $employeeId = $data['employeeId'];
            $serviceEventTypeId = (!isset($data['serviceEventTypeId']) || $data['serviceEventTypeId'] == null) ? -1 : $data['serviceEventTypeId'];
            $recommenderId = (!isset($data['recommenderId']) || $data['recommenderId'] == null) ? -1 : $data['recommenderId'];
            $approverId = (!isset($data['approverId']) || $data['approverId'] == null) ? -1 : $data['approverId'];
            $employeeTypeId = $data['employeeTypeId'];

            $recommApproverRepo = new RecommendApproveRepository($this->adapter);

            $employeeRepo = new EmployeeRepository($this->adapter);
            $employeeResult = $employeeRepo->filterRecords($employeeId, $branchId, $departmentId, $designationId, $positionId, $serviceTypeId, $serviceEventTypeId, 1, $companyId, $employeeTypeId);

            $employeeList = [];
            foreach ($employeeResult as $employeeRow) {
                $employeeId = $employeeRow['EMPLOYEE_ID'];
                $recommedApproverList = $recommApproverRepo->getDetailByEmployeeID($employeeId, $recommenderId, $approverId);
                if ($recommedApproverList != null) {
                    $middleNameR = ($recommedApproverList['MIDDLE_NAME_R'] != null) ? " " . $recommedApproverList['MIDDLE_NAME_R'] . " " : " ";
                    $middleNameA = ($recommedApproverList['MIDDLE_NAME_A'] != null) ? " " . $recommedApproverList['MIDDLE_NAME_A'] . " " : " ";

                    if ($recommedApproverList['RETIRED_R'] != 'Y' && $recommedApproverList['STATUS_R'] != 'D') {
                        $employeeRow['RECOMMENDER_NAME'] = $recommedApproverList['FIRST_NAME_R'] . $middleNameR . $recommedApproverList['LAST_NAME_R'];
                        $employeeRow['RETIRED_R'] = $recommedApproverList['RETIRED_R'];
                        $employeeRow['STATUS_R'] = $recommedApproverList['STATUS_R'];
                        $employeeRow['RECOMMENDER_ID'] = $recommedApproverList['RECOMMEND_BY'];
                    } else {
                        $employeeRow['RECOMMENDER_NAME'] = "";
                        $employeeRow['RETIRED_R'] = "";
                        $employeeRow['STATUS_R'] = "";
                        $employeeRow['RECOMMENDER_ID'] = null;
                    }
                    if ($recommedApproverList['RETIRED_A'] != 'Y' && $recommedApproverList['STATUS_A'] != 'D') {
                        $employeeRow['APPROVER_NAME'] = $recommedApproverList['FIRST_NAME_A'] . $middleNameA . $recommedApproverList['LAST_NAME_A'];
                        $employeeRow['RETIRED_A'] = $recommedApproverList['RETIRED_A'];
                        $employeeRow['STATUS_A'] = $recommedApproverList['STATUS_A'];
                        $employeeRow['APPROVER_ID'] = $recommedApproverList['APPROVED_BY'];
                    } else {
                        $employeeRow['APPROVER_NAME'] = "";
                        $employeeRow['RETIRED_A'] = "";
                        $employeeRow['STATUS_A'] = "";
                        $employeeRow['APPROVER_ID'] = null;
                    }
                } else {
                    $employeeRow['RECOMMENDER_NAME'] = "";
                    $employeeRow['RETIRED_R'] = "";
                    $employeeRow['STATUS_R'] = "";
                    $employeeRow['RECOMMENDER_ID'] = null;

                    $employeeRow['APPROVER_NAME'] = "";
                    $employeeRow['RETIRED_A'] = "";
                    $employeeRow['STATUS_A'] = "";
                    $employeeRow['APPROVER_ID'] = null;
                }
                array_push($employeeList, $employeeRow);
            }


            return new JsonModel(['success' => true, 'data' => $employeeList, 'message' => null]);
        } catch (Exception $e) {
            return new JsonModel(['success' => false, 'data' => null, 'message' => $e->getMessage()]);
        }
    }

    public function assignEmployeeReportingHierarchyAction() {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            $employeeId = $data['employeeId'];
            $recommenderId = $data['recommenderId'];
            $approverId = $data['approverId'];

            if ($recommenderId == "" || $recommenderId == null) {
                $recommenderIdNew = null;
            } else if ($employeeId == $recommenderId) {
                $recommenderIdNew = "";
            } else {
                $recommenderIdNew = $recommenderId;
            }

            if ($approverId == "" || $approverId == null) {
                $approverIdNew = null;
            } else if ($employeeId == $approverId) {
                $approverIdNew = "";
            } else {
                $approverIdNew = $approverId;
            }



            $recommApproverRepo = new RecommendApproveRepository($this->adapter);
            $recommendApprove = new RecommendApprove();
            $employeePreDtl = $recommApproverRepo->fetchById($employeeId);
            if ($employeePreDtl == null) {
                $recommendApprove->employeeId = $employeeId;
                $recommendApprove->recommendBy = $recommenderIdNew;
                $recommendApprove->approvedBy = $approverIdNew;
                $recommendApprove->createdDt = Helper::getcurrentExpressionDate();
                $recommendApprove->status = 'E';
                $recommApproverRepo->add($recommendApprove);
            } else if ($employeePreDtl != null) {
                $id = $employeePreDtl['EMPLOYEE_ID'];
                $recommendApprove->employeeId = $employeeId;
                $recommendApprove->recommendBy = $recommenderIdNew;
                $recommendApprove->approvedBy = $approverIdNew;
                $recommendApprove->modifiedDt = Helper::getcurrentExpressionDate();
                $recommendApprove->status = 'E';
                $recommApproverRepo->edit($recommendApprove, $id);
            }

            return new JsonModel(['success' => true, 'data' => $data, 'message' => null]);
        } catch (Exception $e) {
            return new JsonModel(['success' => false, 'data' => null, 'message' => $e->getMessage()]);
        }
    }

}
