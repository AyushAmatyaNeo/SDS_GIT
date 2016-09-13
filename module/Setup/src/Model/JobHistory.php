<?php
/**
 * Created by PhpStorm.
 * User: ukesh
 * Date: 8/23/16
 * Time: 12:19 PM
 */

namespace Setup\Model;

use Application\Model\Model;

class JobHistory extends Model
{
    const TABLE_NAME="HR_JOB_HISTORY";

    const JOB_HISTORY_ID="JOB_HISTORY_ID";
    const EMPLOYEE_ID="EMPLOYEE_ID";
    const START_DATE="START_DATE";
    const END_DATE="END_DATE";
    const SERVICE_TYPE_ID="SERVICE_TYPE_ID";
    const FROM_BRANCH_ID="FROM_BRANCH_ID";
    const TO_BRANCH_ID="TO_BRANCH_ID";
    const FROM_DEPARTMENT_ID="FROM_DEPARTMENT_ID";
    const TO_DEPARTMENT_ID="TO_DEPARTMENT_ID";
    const FROM_DESIGNATION_ID="FROM_DESIGNATION_ID";
    const TO_DESIGNATION_ID="TO_DESIGNATION_ID";
    const FROM_POSITION_ID="FROM_POSITION_ID";
    const TO_POSITION_ID="TO_POSITION_ID";


    public $jobHistoryId;
    public $employeeId;
    public $startDate;
    public $endDate;
    public $serviceTypeId;
    public $fromBranchId;
    public $toBranchId;
    public $fromDepartmentId;
    public $toDepartmentId;
    public $fromDesignationId;
    public $toDesignationId;
    public $fromPositionId;
    public $toPositionId;

    public $mappings = [
        'jobHistoryId' => self::JOB_HISTORY_ID,
        'employeeId' => self::EMPLOYEE_ID,
        'startDate' => self::START_DATE,
        'endDate' => self::END_DATE,
        'serviceTypeId' => self::SERVICE_TYPE_ID,
        'fromBranchId' => self::FROM_BRANCH_ID,
        'toBranchId' => self::TO_BRANCH_ID,
        'fromDepartmentId' => self::FROM_DEPARTMENT_ID,
        'toDepartmentId' => self::TO_DEPARTMENT_ID,
        'fromDesignationId' => self::FROM_DESIGNATION_ID,
        'toDesignationId' => self::TO_DESIGNATION_ID,
        'fromPositionId' => self::FROM_POSITION_ID,
        'toPositionId' => self::TO_POSITION_ID
    ];
}