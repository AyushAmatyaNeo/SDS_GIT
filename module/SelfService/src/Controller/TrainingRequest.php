<?php
namespace SelfService\Controller;

use Application\Helper\Helper;
use DateTime;
use LeaveManagement\Repository\LeaveAssignRepository;
use LeaveManagement\Repository\LeaveMasterRepository;
use SelfService\Form\TrainingRequestForm;
use SelfService\Model\TrainingRequest as TrainingRequestModel;
use Setup\Model\Training;
use Setup\Repository\TrainingRepository;
use SelfService\Repository\TrainingRequestRepository;
use Setup\Repository\EmployeeRepository;
use Setup\Repository\RecommendApproveRepository;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Mvc\Controller\AbstractActionController;

class TrainingRequest extends AbstractActionController {

    private $form;
    private $adapter;
    private $repository;
    private $employeeId;
    private $recommender;
    private $approver;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
        $this->repository = new TrainingRequestRepository($adapter);
        $auth = new AuthenticationService();
        $this->employeeId = $auth->getStorage()->read()['employee_id'];
    }

    public function initializeForm() {
        $builder = new AnnotationBuilder();
        $form = new TrainingRequestForm();
        $this->form = $builder->createForm($form);
    }

    public function getRecommendApprover() {
        $recommendApproveRepository = new RecommendApproveRepository($this->adapter);
        $empRecommendApprove = $recommendApproveRepository->fetchById($this->employeeId);

        if ($empRecommendApprove != null) {
            $this->recommender = $empRecommendApprove['RECOMMEND_BY'];
            $this->approver = $empRecommendApprove['APPROVED_BY'];
        } else {
            $result = $this->recommendApproveList();
            if (count($result['recommender']) > 0) {
                $this->recommender = $result['recommender'][0]['id'];
            } else {
                $this->recommender = null;
            }
            if (count($result['approver']) > 0) {
                $this->approver = $result['approver'][0]['id'];
            } else {
                $this->approver = null;
            }
        }
    }

    public function indexAction() {
        $this->getRecommendApprover();
        $result = $this->repository->getAllByEmployeeId($this->employeeId);
        $fullName = function($id) {
            $empRepository = new EmployeeRepository($this->adapter);
            $empDtl = $empRepository->fetchById($id);
            $empMiddleName = ($empDtl['MIDDLE_NAME'] != null) ? " " . $empDtl['MIDDLE_NAME'] . " " : " ";
            return $empDtl['FIRST_NAME'] . $empMiddleName . $empDtl['LAST_NAME'];
        };

        $recommenderName = $fullName($this->recommender);
        $approverName = $fullName($this->approver);

        $list = [];
        $getValue = function($status) {
            if ($status == "RQ") {
                return "Pending";
            } else if ($status == 'RC') {
                return "Recommended";
            } else if ($status == "R") {
                return "Rejected";
            } else if ($status == "AP") {
                return "Approved";
            } else if ($status == "C") {
                return "Cancelled";
            }
        };
        $getAction = function($status) {
            if ($status == "RQ") {
                return ["delete" => 'Cancel Request'];
            } else {
                return ["view" => 'View'];
            }
        };
        $getValueComType = function($trainingTypeId){
            if($trainingTypeId=='CC'){
                return 'Company Contribution';
            }else if($trainingTypeId=='CP'){
                return 'Company Personal';
            }
        };
        foreach ($result as $row) {
            $status = $getValue($row['STATUS']);
            $action = $getAction($row['STATUS']);
            $statusID = $row['STATUS'];
            $approvedDT = $row['APPROVED_DATE'];
            $MN1 = ($row['MN1'] != null) ? " " . $row['MN1'] . " " : " ";
            $recommended_by = $row['FN1'] . $MN1 . $row['LN1'];
            $MN2 = ($row['MN2'] != null) ? " " . $row['MN2'] . " " : " ";
            $approved_by = $row['FN2'] . $MN2 . $row['LN2'];
            $authRecommender = ($statusID == 'RQ' || $statusID == 'C') ? $recommenderName : $recommended_by;
            $authApprover = ($statusID == 'RC' || $statusID == 'RQ' || $statusID == 'C' || ($statusID == 'R' && $approvedDT == null)) ? $approverName : $approved_by;

            $new_row = array_merge($row, [
                'RECOMMENDER_NAME' => $authRecommender,
                'APPROVER_NAME' => $authApprover,
                'STATUS' => $status,
                'ACTION' => key($action),
                'TRAINING_TYPE'=> $getValueComType($row['TRAINING_TYPE']),
                'ACTION_TEXT' => $action[key($action)]
            ]);
            $startDate = DateTime::createFromFormat(Helper::PHP_DATE_FORMAT, $row['FROM_DATE']);
            $toDayDate = new DateTime();
            if (($toDayDate < $startDate) && ($statusID == 'RQ' || $statusID == 'RC' || $statusID == 'AP')) {
                $new_row['ALLOW_TO_EDIT'] = 1;
            } else if (($toDayDate >= $startDate) && $statusID == 'RQ') {
                $new_row['ALLOW_TO_EDIT'] = 1;
            } else if ($toDayDate >= $startDate) {
                $new_row['ALLOW_TO_EDIT'] = 0;
            } else {
                $new_row['ALLOW_TO_EDIT'] = 0;
            }
            array_push($list, $new_row);
        }
        return Helper::addFlashMessagesToArray($this, ['list' => $list]);
    }

    public function addAction() {
        $this->initializeForm();
        $request = $this->getRequest();

        $model = new TrainingRequestModel();
        if ($request->isPost()) {
            $this->form->setData($request->getPost());
            if ($this->form->isValid()) {
                $model->exchangeArrayFromForm($this->form->getData());
                $model->requestId = ((int) Helper::getMaxId($this->adapter, TrainingRequestModel::TABLE_NAME, TrainingRequestModel::REQUEST_ID)) + 1;
                $model->employeeId = $this->employeeId;
                $model->requestedDate = Helper::getcurrentExpressionDate();
                $model->status = 'RQ';
                // print_r($model); die();
                $this->repository->add($model);
                $this->flashmessenger()->addMessage("Training Request Successfully added!!!");
                return $this->redirect()->toRoute("trainingRequest");
            }
        }

        $trainings = $this->getTrainingList($this->employeeId);
        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'trainings' => $trainings["trainingKVList"],
        ]);
    }

    public function deleteAction() {
        $id = (int) $this->params()->fromRoute("id");
        if (!$id) {
            return $this->redirect()->toRoute('trainingRequest');
        }
        $detail = $this->repository->fetchById($id);
        if ($detail['STATUS'] == 'AP') {
            //to get the previous balance of selected leave from assigned leave detail
            $leaveAssignRepo = new LeaveAssignRepository($this->adapter);
            $leaveMasterRepo = new LeaveMasterRepository($this->adapter);
            $substituteLeave = $leaveMasterRepo->getSubstituteLeave()->getArrayCopy();
            $substituteLeaveId = $substituteLeave['LEAVE_ID'];
            $empSubLeaveDtl = $leaveAssignRepo->filterByLeaveEmployeeId($substituteLeaveId, $detail['EMPLOYEE_ID']);
            $preBalance = $empSubLeaveDtl['BALANCE'];
            $total = $empSubLeaveDtl['TOTAL_DAYS'] - $detail['DURATION'];
            $balance = $preBalance - $detail['DURATION'];
            $leaveAssignRepo->updatePreYrBalance($detail['EMPLOYEE_ID'], $substituteLeaveId, 0, $total, $balance);
        }
        $this->repository->delete($id);
        $this->flashmessenger()->addMessage("Training Request Successfully Cancelled!!!");
        return $this->redirect()->toRoute('trainingRequest');
    }

    public function viewAction() {
        $this->initializeForm();
        $this->getRecommendApprover();
        $id = (int) $this->params()->fromRoute('id');

        if ($id === 0) {
            return $this->redirect()->toRoute("workOnHoliday");
        }
        $fullName = function($id) {
            $empRepository = new EmployeeRepository($this->adapter);
            $empDtl = $empRepository->fetchById($id);
            $empMiddleName = ($empDtl['MIDDLE_NAME'] != null) ? " " . $empDtl['MIDDLE_NAME'] . " " : " ";
            return $empDtl['FIRST_NAME'] . $empMiddleName . $empDtl['LAST_NAME'];
        };

        $recommenderName = $fullName($this->recommender);
        $approverName = $fullName($this->approver);

        $model = new WorkOnHolidayModel();
        $detail = $this->repository->fetchById($id);
        $status = $detail['STATUS'];
        $approvedDT = $detail['APPROVED_DATE'];
        $recommended_by = $fullName($detail['RECOMMENDED_BY']);
        $approved_by = $fullName($detail['APPROVED_BY']);
        $authRecommender = ($status == 'RQ' || $status == 'C') ? $recommenderName : $recommended_by;
        $authApprover = ($status == 'RC' || $status == 'RQ' || $status == 'C' || ($status == 'R' && $approvedDT == null)) ? $approverName : $approved_by;

        $model->exchangeArrayFromDB($detail);
        $this->form->bind($model);

        $employeeName = $fullName($detail['EMPLOYEE_ID']);
        $holidays = $this->getHolidayList($this->employeeId);
        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'employeeName' => $employeeName,
                    'status' => $detail['STATUS'],
                    'requestedDate' => $detail['REQUESTED_DATE'],
                    'recommender' => $authRecommender,
                    'approver' => $authApprover,
                    'holidays' => $holidays["holidayKVList"],
                    'holidayObjList' => $holidays["holidayList"]
        ]);
    }

    public function recommendApproveList() {
        $employeeRepository = new EmployeeRepository($this->adapter);
        $recommendApproveRepository = new RecommendApproveRepository($this->adapter);
        $employeeId = $this->employeeId;
        $employeeDetail = $employeeRepository->fetchById($employeeId);
        $branchId = $employeeDetail['BRANCH_ID'];
        $departmentId = $employeeDetail['DEPARTMENT_ID'];
        $designations = $recommendApproveRepository->getDesignationList($employeeId);

        $recommender = array();
        $approver = array();
        foreach ($designations as $key => $designationList) {
            $withinBranch = $designationList['WITHIN_BRANCH'];
            $withinDepartment = $designationList['WITHIN_DEPARTMENT'];
            $designationId = $designationList['DESIGNATION_ID'];
            $employees = $recommendApproveRepository->getEmployeeList($withinBranch, $withinDepartment, $designationId, $branchId, $departmentId);

            if ($key == 1) {
                $i = 0;
                foreach ($employees as $employeeList) {
                    // array_push($recommender,$employeeList);
                    $recommender [$i]["id"] = $employeeList['EMPLOYEE_ID'];
                    $recommender [$i]["name"] = $employeeList['FIRST_NAME'] . " " . $employeeList['MIDDLE_NAME'] . " " . $employeeList['LAST_NAME'];
                    $i++;
                }
            } else if ($key == 2) {
                $i = 0;
                foreach ($employees as $employeeList) {
                    //array_push($approver,$employeeList);
                    $approver [$i]["id"] = $employeeList['EMPLOYEE_ID'];
                    $approver [$i]["name"] = $employeeList['FIRST_NAME'] . " " . $employeeList['MIDDLE_NAME'] . " " . $employeeList['LAST_NAME'];
                    $i++;
                }
            }
        }
        $responseData = [
            "recommender" => $recommender,
            "approver" => $approver
        ];
        return $responseData;
    }

    public function getTrainingList($employeeId) {
        $trainingRepo = new TrainingRepository($this->adapter);
        $trainingResult = $trainingRepo->selectAll($employeeId);
        $trainingList = [];
        foreach ($trainingResult as $trainingRow) {
            $trainingList[$trainingRow['TRAINING_ID']] = $trainingRow['TRAINING_NAME'] . " (" . $trainingRow['START_DATE'] . " to " . $trainingRow['END_DATE'] . ")";
        }
        return ['trainingKVList' => $trainingList];
    }
}