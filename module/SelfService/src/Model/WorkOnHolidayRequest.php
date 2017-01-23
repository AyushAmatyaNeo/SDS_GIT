<?php
namespace SelfService\Model;

use Application\Model\Model;

class WorkOnHolidayRequest extends Model{
    const TABLE_NAME = "HR_EMPLOYEE_WORK_HOLIDAY";
    const ID = "ID";
    const EMPLOYEE_ID = "EMPLOYEE_ID";
    const HOLIDAY_ID = "HOLIDAY_ID";
    const FROM_DATE = "FROM_DATE";
    const TO_DATE = "TO_DATE";
    const DURATION = "DURATION";
    const REMARKS = "REMARKS";
    const STATUS = "STATUS";
    const REQUESTED_DATE = "REQUESTED_DATE";
    const RECOMMENDED_BY = "RECOMMENDED_BY";
    const RECOMMENDED_DATE = "RECOMMENDED_DATE";
    const RECOMMENDED_REMARKS = "RECOMMENDED_REMARKS";
    const APPROVED_BY = "APPROVED_BY";
    const APPROVED_DATE = "APPROVED_DATE";
    const APPROVED_REMARKS = "APPROVED_REMARKS";
    const MODIFIED_DATE = "MODIFIED_DATE";
}