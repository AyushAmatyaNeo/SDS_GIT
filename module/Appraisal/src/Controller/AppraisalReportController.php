<?php
namespace Appraisal\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Appraisal\Repository\AppraisalReportRepository;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Authentication\AuthenticationService;
use Application\Helper\Helper;
use Appraisal\Model\Setup;
use Appraisal\Model\Stage;
use Zend\Form\Element\Select;
use Application\Helper\EntityHelper;
use Appraisal\Repository\AppraisalAssignRepository;
use Setup\Repository\EmployeeRepository;
use Appraisal\Repository\HeadingRepository;
use Appraisal\Repository\QuestionRepository;
use Appraisal\Repository\QuestionOptionRepository;
use Appraisal\Repository\StageQuestionRepository;
use Appraisal\Repository\AppraisalAnswerRepository;
use Application\Helper\CustomFormElement;
use Appraisal\Model\AppraisalAnswer;
use Appraisal\Repository\StageRepository;
use Notification\Controller\HeadNotification;
use Notification\Model\NotificationEvents;
use Appraisal\Model\Question;
use Application\Helper\AppraisalHelper;
use SelfService\Repository\AppraisalKPIRepository;
use SelfService\Repository\AppraisalCompetenciesRepo;
use Appraisal\Repository\AppraisalStatusRepository;
use Appraisal\Model\AppraisalStatus;

class AppraisalReportController extends AbstractActionController{
    private $repository;
    private $adapter;
    private $employeeId;
    
    public function __construct(AdapterInterface $adapter){
        $this->adapter = $adapter;
        $this->repository = new AppraisalReportRepository($this->adapter);
        $auth = new AuthenticationService();
        $this->employeeId = $auth->getStorage()->read()['employee_id'];
    }
    public function indexAction() {
        $appraisalFormElement = new Select();
        $appraisalFormElement->setName("Appraisal");
        $appraisals = EntityHelper::getTableKVListWithSortOption($this->adapter, Setup::TABLE_NAME, Setup::APPRAISAL_ID, [Setup::APPRAISAL_EDESC], [Setup::STATUS => 'E'], Setup::APPRAISAL_EDESC, "ASC",NULL,FALSE,TRUE);
        $appraisals1 = [-1 => "All Type"] + $appraisals;
        $appraisalFormElement->setValueOptions($appraisals1);
        $appraisalFormElement->setAttributes(["id" => "appraisalId", "class" => "form-control"]);
        $appraisalFormElement->setLabel("Appraisal");
        
        $appraisalStageFormElement = new Select();
        $appraisalStageFormElement->setName("Appraisal");
        $appraisalStages = EntityHelper::getTableKVListWithSortOption($this->adapter, Stage::TABLE_NAME, Stage::STAGE_ID, [Stage::STAGE_EDESC], [Stage::STATUS => 'E'], Stage::STAGE_EDESC, "ASC",NULL,FALSE,TRUE);
        $appraisalStages1 = [-1 => "All Stage"] + $appraisalStages;
        $appraisalStageFormElement->setValueOptions($appraisalStages1);
        $appraisalStageFormElement->setAttributes(["id" => "appraisalStageId", "class" => "form-control"]);
        $appraisalStageFormElement->setLabel("Appraisal Stage");

        return Helper::addFlashMessagesToArray($this,[
            'appraisals'=>$appraisalFormElement,
            'appraisalStages'=>$appraisalStageFormElement,
            'userId'=>$this->employeeId,
            'searchValues' => EntityHelper::getSearchData($this->adapter)
                ]);
    }
    public function viewAction(){
        $request = $this->getRequest();
        $appraisalId = $this->params()->fromRoute('appraisalId');
        $employeeId = $this->params()->fromRoute('employeeId');
        $tab = $this->params()->fromRoute('tab');
        $appraisalAssignRepo = new AppraisalAssignRepository($this->adapter);
        $employeeRepo = new EmployeeRepository($this->adapter);
        $headingRepo = new HeadingRepository($this->adapter);
        $employeeDetail = $employeeRepo->fetchForProfileById($employeeId);
        $assignedAppraisalDetail = $appraisalAssignRepo->getEmployeeAppraisalDetail($employeeId,$appraisalId);
        $appraisalTypeId = $assignedAppraisalDetail['APPRAISAL_TYPE_ID'];
        $currentStageId = $assignedAppraisalDetail['STAGE_ID'];
        $headingList = $headingRepo->fetchByAppraisalTypeId($appraisalTypeId);
        $questionTemplate = [];
        
        $appraiseeFlag = ["(Q.".Question::APPRAISEE_FLAG."='Y' OR Q.".Question::APPRAISEE_RATING."='Y')"];
        $appraiserFlag = ["(Q.".Question::APPRAISER_FLAG."='Y' OR Q.".Question::APPRAISER_RATING."='Y') AND (Q.".Question::APPRAISEE_FLAG."='N' AND Q.".Question::APPRAISEE_RATING."='N')"];
        $reviewerFlag = ["(Q.".Question::REVIEWER_FLAG."='Y' OR Q.".Question::REVIEWER_RATING."='Y') AND (Q.".Question::APPRAISEE_FLAG."='N' AND Q.".Question::APPRAISEE_RATING."='N') AND (Q.".Question::APPRAISER_FLAG."='N' AND Q.".Question::APPRAISER_RATING."='N')"];
        
        $appraiserQuestionTemplate = [];
        $appraiseeQuestionTemplate = [];
        $reviewerQuestionTemplate = [];
        $questionForCurStage = 0;
        $appraiseeAvailableAnswer = false;
        $appraiserAvailableAnswer = false;
        $reviewerAvailableAnswer = false;
        foreach($headingList as $headingRow){
            //get question list for appraisee with current stage id
            $questionList = AppraisalHelper::getAllQuestionWidOptions($this->adapter,$headingRow['HEADING_ID'],$currentStageId,$appraiseeFlag,$appraisalId,$employeeId,$employeeId,"=1",[$assignedAppraisalDetail['APPRAISER_ID'],$assignedAppraisalDetail['ALT_APPRAISER_ID']],[$assignedAppraisalDetail['REVIEWER_ID'],$assignedAppraisalDetail['ALT_REVIEWER_ID']]);
            $appraiserQuestionList =AppraisalHelper::getAllQuestionWidOptions($this->adapter,$headingRow['HEADING_ID'],$currentStageId,$appraiserFlag,$appraisalId,$employeeId,[$assignedAppraisalDetail['APPRAISER_ID'],$assignedAppraisalDetail['ALT_APPRAISER_ID']],null,null,[$assignedAppraisalDetail['REVIEWER_ID'],$assignedAppraisalDetail['ALT_REVIEWER_ID']]);
            $appraiseeQuestionList = AppraisalHelper::getAllQuestionWidOptions($this->adapter,$headingRow['HEADING_ID'], $currentStageId, $appraiseeFlag, $appraisalId,$employeeId,$employeeId,"!=1");
            $reviewerQuestionList = AppraisalHelper::getAllQuestionWidOptions($this->adapter,$headingRow['HEADING_ID'], $currentStageId, $reviewerFlag, $appraisalId,$employeeId,[$assignedAppraisalDetail['REVIEWER_ID'],$assignedAppraisalDetail['ALT_REVIEWER_ID']]);
            
            if($reviewerQuestionList['questionForCurStage']){
                $questionForCurStage+=1;
            }
            if($appraiserQuestionList['availableAnswer']){
                $appraiserAvailableAnswer=true;
            }
            if($appraiseeQuestionList['availableAnswer']){
                $appraiseeAvailableAnswer=true;
            }
            if($reviewerQuestionList['availableAnswer']){
                $reviewerAvailableAnswer=true;
            }
            if(count($questionList['questionList'])>0){
                array_push($questionTemplate, [
                    'HEADING_ID'=>$headingRow['HEADING_ID'],
                    'HEADING_EDESC'=>$headingRow['HEADING_EDESC'],
                    'QUESTIONS'=>$questionList['questionList']]);
            }
            if(count($appraiserQuestionList['questionList'])>0){
                array_push($appraiserQuestionTemplate, [
                    'HEADING_ID'=>$headingRow['HEADING_ID'],
                    'HEADING_EDESC'=>$headingRow['HEADING_EDESC'],
                    'QUESTIONS'=>$appraiserQuestionList['questionList']]);
            }
            if(count($appraiseeQuestionList['questionList'])>0){
                array_push($appraiseeQuestionTemplate, [
                    'HEADING_ID'=>$headingRow['HEADING_ID'],
                    'HEADING_EDESC'=>$headingRow['HEADING_EDESC'],
                    'QUESTIONS'=>$appraiseeQuestionList['questionList']]);
            }
            if(count($reviewerQuestionList['questionList'])>0){
                array_push($reviewerQuestionTemplate, [
                    'HEADING_ID'=>$headingRow['HEADING_ID'],
                    'HEADING_EDESC'=>$headingRow['HEADING_EDESC'],
                    'QUESTIONS'=>$reviewerQuestionList['questionList']   ]);
            }
        }
        $returnData = [
            'assignedAppraisalDetail'=> $assignedAppraisalDetail,
            'employeeDetail'=>$employeeDetail,
            'questionTemplate'=>$questionTemplate,
            'appraiserQuestionTemplate'=>$appraiserQuestionTemplate,
            'appraiseeQuestionTemplate'=>$appraiseeQuestionTemplate,
            'reviewerQuestionTemplate'=>$reviewerQuestionTemplate,
            'questionForCurStage'=>$questionForCurStage,
            'performanceAppraisalObj'=>CustomFormElement::formElement(),
            'customRenderer' => Helper::renderCustomView(),
            'customRendererForCheckbox' => Helper::renderCustomViewForCheckbox(),
            'appraisalId'=>$appraisalId,
            'employeeId'=>$employeeId,
            'appraiseeAvailableAnswer'=>$appraiseeAvailableAnswer,
            'appraiserAvailableAnswer'=>$appraiserAvailableAnswer,
            'reviewerAvailableAnswer'=>$reviewerAvailableAnswer
        ];
        if($request->isPost()){
            try{
                $appraisalStatus = new AppraisalStatus();
                $appraisalStatusRepo = new AppraisalStatusRepository($this->adapter);
                $appraisalStatus->exchangeArrayFromDB($appraisalStatusRepo->fetchByEmpAppId($employeeId,$appraisalId)->getArrayCopy());
                $postData = $request->getPost()->getArrayCopy();
                $appraisalStatusRepo->updateColumnByEmpAppId([AppraisalStatus::HR_FEEDBACK=>$postData['hrComment']], $appraisalId, $employeeId);
                $nextStageId = 2; // completed stage
                $appraisalAssignRepo->updateCurrentStageByAppId($nextStageId, $appraisalId, $employeeId);
                HeadNotification::pushNotification(NotificationEvents::HR_FEEDBACK, $appraisalStatus, $this->adapter, $this,['ID'=>$this->employeeId],['ID'=>$employeeId,'USER_TYPE'=>"APPRAISEE"]);
                HeadNotification::pushNotification(NotificationEvents::HR_FEEDBACK, $appraisalStatus, $this->adapter, $this,['ID'=>$this->employeeId],['ID'=>$assignedAppraisalDetail['REVIEWER_ID'],'USER_TYPE'=>"REVIEWER"]);
                HeadNotification::pushNotification(NotificationEvents::HR_FEEDBACK, $appraisalStatus, $this->adapter, $this,['ID'=>$this->employeeId],['ID'=>$assignedAppraisalDetail['APPRAISER_ID'],'USER_TYPE'=>"APPRAISER"]);
                
                $this->flashmessenger()->addMessage("Appraisal Successfully Submitted!!");
                $this->redirect()->toRoute("appraisalReport");
            }catch(Exception $e){
                $this->flashmessenger()->addMessage("Appraisal Submit Failed!!");
                $this->flashmessenger()->addMessage($e->getMessage());
            }
        }
        $defaultRatingDtl = AppraisalHelper::checkDefaultRatingForEmp($this->adapter, $employeeId, $appraisalTypeId);
        $appraisalKPI = new AppraisalKPIRepository($this->adapter);
        $appraisalCompetencies = new AppraisalCompetenciesRepo($this->adapter);
        $keyAchievementDtlNum = $appraisalKPI->countKeyAchievementDtl($employeeId, $appraisalId)['NUM'];
        $appraiserRatingDtlNum = $appraisalKPI->countAppraiserRatingDtl($employeeId, $appraisalId)['NUM'];
        $appCompetenciesRatingDtlNum = $appraisalCompetencies->countCompetenciesRatingDtl($employeeId,$appraisalId)['NUM'];
        $returnData['tab']=$tab;
        $returnData['keyAchievementDtlNum']=$keyAchievementDtlNum;
        $returnData['appraiserRatingDtlNum']=$appraiserRatingDtlNum;
        $returnData['appCompetenciesRatingDtlNum']=$appCompetenciesRatingDtlNum;
        $returnData['defaultRatingDtl']=$defaultRatingDtl;
        return Helper::addFlashMessagesToArray($this,$returnData);
    }
    public function reportAction(){
        $request = $this->getRequest();
        $appraisalId = $this->params()->fromRoute('appraisalId');
        $employeeId = $this->params()->fromRoute('employeeId');
        $tab = $this->params()->fromRoute('tab');
        $appraisalAssignRepo = new AppraisalAssignRepository($this->adapter);
        $employeeRepo = new EmployeeRepository($this->adapter);
        $headingRepo = new HeadingRepository($this->adapter);
        $employeeDetail = $employeeRepo->fetchForProfileById($employeeId);
        $assignedAppraisalDetail = $appraisalAssignRepo->getEmployeeAppraisalDetail($employeeId,$appraisalId);
        $appraisalTypeId = $assignedAppraisalDetail['APPRAISAL_TYPE_ID'];
        $currentStageId = $assignedAppraisalDetail['STAGE_ID'];
        $headingList = $headingRepo->fetchByAppraisalTypeId($appraisalTypeId);
        $questionTemplate = [];
        
        $appraiseeFlag = ["(Q.".Question::APPRAISEE_FLAG."='Y' OR Q.".Question::APPRAISEE_RATING."='Y')"];
        $appraiserFlag = ["(Q.".Question::APPRAISER_FLAG."='Y' OR Q.".Question::APPRAISER_RATING."='Y') AND (Q.".Question::APPRAISEE_FLAG."='N' AND Q.".Question::APPRAISEE_RATING."='N')"];
        $reviewerFlag = ["(Q.".Question::REVIEWER_FLAG."='Y' OR Q.".Question::REVIEWER_RATING."='Y') AND (Q.".Question::APPRAISEE_FLAG."='N' AND Q.".Question::APPRAISEE_RATING."='N') AND (Q.".Question::APPRAISER_FLAG."='N' AND Q.".Question::APPRAISER_RATING."='N')"];
        
        $appraiserQuestionTemplate = [];
        $appraiseeQuestionTemplate = [];
        $reviewerQuestionTemplate = [];
        $questionForCurStage = 0;
        $appraiseeAvailableAnswer = false;
        $appraiserAvailableAnswer = false;
        $reviewerAvailableAnswer = false;
        foreach($headingList as $headingRow){
            //get question list for appraisee with current stage id
            $questionList = AppraisalHelper::getAllQuestionWidOptions($this->adapter,$headingRow['HEADING_ID'],$currentStageId,$appraiseeFlag,$appraisalId,$employeeId,$employeeId,"=1",$assignedAppraisalDetail['APPRAISER_ID'],$assignedAppraisalDetail['REVIEWER_ID']);
            $appraiserQuestionList =AppraisalHelper::getAllQuestionWidOptions($this->adapter,$headingRow['HEADING_ID'],$currentStageId,$appraiserFlag,$appraisalId,$employeeId,$assignedAppraisalDetail['APPRAISER_ID'],null,null,$assignedAppraisalDetail['REVIEWER_ID']);
            $appraiseeQuestionList = AppraisalHelper::getAllQuestionWidOptions($this->adapter,$headingRow['HEADING_ID'], $currentStageId, $appraiseeFlag, $appraisalId,$employeeId,$employeeId,"!=1");
            $reviewerQuestionList = AppraisalHelper::getAllQuestionWidOptions($this->adapter,$headingRow['HEADING_ID'], $currentStageId, $reviewerFlag, $appraisalId,$employeeId,$assignedAppraisalDetail['REVIEWER_ID']);
            
            if($reviewerQuestionList['questionForCurStage']){
                $questionForCurStage+=1;
            }
            if($appraiserQuestionList['availableAnswer']){
                $appraiserAvailableAnswer=true;
            }
            if($appraiseeQuestionList['availableAnswer']){
                $appraiseeAvailableAnswer=true;
            }
            if($reviewerQuestionList['availableAnswer']){
                $reviewerAvailableAnswer=true;
            }
            if(count($questionList['questionList'])>0){
                array_push($questionTemplate, [
                    'HEADING_ID'=>$headingRow['HEADING_ID'],
                    'HEADING_EDESC'=>$headingRow['HEADING_EDESC'],
                    'QUESTIONS'=>$questionList['questionList']]);
            }
            if(count($appraiserQuestionList['questionList'])>0){
                array_push($appraiserQuestionTemplate, [
                    'HEADING_ID'=>$headingRow['HEADING_ID'],
                    'HEADING_EDESC'=>$headingRow['HEADING_EDESC'],
                    'QUESTIONS'=>$appraiserQuestionList['questionList']]);
            }
            if(count($appraiseeQuestionList['questionList'])>0){
                array_push($appraiseeQuestionTemplate, [
                    'HEADING_ID'=>$headingRow['HEADING_ID'],
                    'HEADING_EDESC'=>$headingRow['HEADING_EDESC'],
                    'QUESTIONS'=>$appraiseeQuestionList['questionList']]);
            }
            if(count($reviewerQuestionList['questionList'])>0){
                array_push($reviewerQuestionTemplate, [
                    'HEADING_ID'=>$headingRow['HEADING_ID'],
                    'HEADING_EDESC'=>$headingRow['HEADING_EDESC'],
                    'QUESTIONS'=>$reviewerQuestionList['questionList']   ]);
            }
        }
        $returnData = [
            'assignedAppraisalDetail'=> $assignedAppraisalDetail,
            'employeeDetail'=>$employeeDetail,
            'questionTemplate'=>$questionTemplate,
            'appraiserQuestionTemplate'=>$appraiserQuestionTemplate,
            'appraiseeQuestionTemplate'=>$appraiseeQuestionTemplate,
            'reviewerQuestionTemplate'=>$reviewerQuestionTemplate,
            'questionForCurStage'=>$questionForCurStage,
            'performanceAppraisalObj'=>CustomFormElement::formElement(),
            'customRenderer' => Helper::renderCustomView(),
            'customRendererForCheckbox' => Helper::renderCustomViewForCheckbox(),
            'appraisalId'=>$appraisalId,
            'employeeId'=>$employeeId,
            'appraiseeAvailableAnswer'=>$appraiseeAvailableAnswer,
            'appraiserAvailableAnswer'=>$appraiserAvailableAnswer,
            'reviewerAvailableAnswer'=>$reviewerAvailableAnswer
        ];
        $defaultRatingDtl = AppraisalHelper::checkDefaultRatingForEmp($this->adapter, $employeeId, $appraisalTypeId);
        $appraisalKPI = new AppraisalKPIRepository($this->adapter);
        $appraisalCompetencies = new AppraisalCompetenciesRepo($this->adapter);
        $keyAchievementDtlNum = $appraisalKPI->countKeyAchievementDtl($employeeId, $appraisalId)['NUM'];
        $appraiserRatingDtlNum = $appraisalKPI->countAppraiserRatingDtl($employeeId, $appraisalId)['NUM'];
        $appCompetenciesRatingDtlNum = $appraisalCompetencies->countCompetenciesRatingDtl($employeeId,$appraisalId)['NUM'];
        $returnData['tab']=$tab;
        $returnData['keyAchievementDtlNum']=$keyAchievementDtlNum;
        $returnData['appraiserRatingDtlNum']=$appraiserRatingDtlNum;
        $returnData['appCompetenciesRatingDtlNum']=$appCompetenciesRatingDtlNum;
        $returnData['defaultRatingDtl']=$defaultRatingDtl;
        return Helper::addFlashMessagesToArray($this,$returnData);
    }
}