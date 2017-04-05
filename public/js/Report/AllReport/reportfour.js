(function ($, app) {
    'use strict';
    $(document).ready(function () {
        var $tableContainer = $("#reportTable");
        var extractDetailData = function (rawData, employeeId) {
            var data = {};
            var column = {};

            for (var i in rawData) {
                if (typeof data[rawData[i].MONTH_ID] !== 'undefined') {
                    data[rawData[i].MONTH_ID].MONTHS['c' + rawData[i].DAY_COUNT] =
                            JSON.stringify({
                                IS_ABSENT: rawData[i].IS_ABSENT,
                                IS_PRESENT: rawData[i].IS_PRESENT,
                                ON_LEAVE: rawData[i].ON_LEAVE
                            });
                    data[rawData[i].MONTH_ID].TOTAL.IS_ABSENT = data[rawData[i].MONTH_ID].TOTAL.IS_ABSENT + parseFloat(rawData[i].IS_ABSENT);
                    data[rawData[i].MONTH_ID].TOTAL.IS_PRESENT = data[rawData[i].MONTH_ID].TOTAL.IS_PRESENT + parseFloat(rawData[i].IS_PRESENT);
                    data[rawData[i].MONTH_ID].TOTAL.ON_LEAVE = data[rawData[i].MONTH_ID].TOTAL.ON_LEAVE + parseFloat(rawData[i].ON_LEAVE);
                    data[rawData[i].MONTH_ID].TOTAL.IS_DAYOFF = data[rawData[i].MONTH_ID].TOTAL.IS_DAYOFF + parseFloat(rawData[i].IS_DAYOFF);

                } else {
                    data[rawData[i].MONTH_ID] = {
                        MONTH_ID: rawData[i].MONTH_ID,
                        MONTH_EDESC: rawData[i].MONTH_EDESC,
                        MONTHS: {},
                        TOTAL: {
                            IS_ABSENT: parseFloat(rawData[i].IS_ABSENT),
                            IS_PRESENT: parseFloat(rawData[i].IS_PRESENT),
                            ON_LEAVE: parseFloat(rawData[i].ON_LEAVE),
                            IS_DAYOFF: parseFloat(rawData[i].IS_DAYOFF)
                        }
                    };
                    data[rawData[i].MONTH_ID].MONTHS['c' + rawData[i].DAY_COUNT] =
                            JSON.stringify({
                                IS_ABSENT: rawData[i].IS_ABSENT,
                                IS_PRESENT: rawData[i].IS_PRESENT,
                                ON_LEAVE: rawData[i].ON_LEAVE
                            });

                }
                if (typeof column[rawData[i].DAY_COUNT] === 'undefined') {
                    var temp = 'c' + rawData[i].DAY_COUNT;
                    column[rawData[i].DAY_COUNT] = {
                        field: temp,
                        title: rawData[i].DAY_COUNT,
                        template: '<span data="#: ' + temp + ' #" class="custom-btn-group"></span>'
                    }
                }
            }


            var returnData = {rows: [], cols: []};

            returnData.cols.push({field: 'month', title: 'Month'});
            for (var k in column) {
                returnData.cols.push(column[k]);
            }
            returnData.cols.push({
                field: 'total',
                title: 'Total',
                template: '<div data="#: total #" class="btn-group widget-btn-list total-attendance">' +
                        '<a class="btn btn-default widget-btn custom-btn-present"></a>' +
                        '<a class="btn btn-danger widget-btn custom-btn-absent"></a>' +
                        '<a class="btn btn-info widget-btn custom-btn-leave"></a>' +
                        '</div>'});


            for (var k in data) {
                var row = data[k].MONTHS;
                for (var i in column) {
                    if (typeof row[column[i]['field']] === 'undefined') {
                        row[column[i]['field']] = null;
                    }
                }

                row['month'] = data[k].MONTH_EDESC;
                returnData.rows.push(row);
                row['total'] = JSON.stringify(data[k].TOTAL);
            }

            return returnData;
        };
        var displayDataInBtnGroup = function (selector) {
            $(selector).each(function (k, group) {
                var $group = $(group);

                var data = $group.attr('data');
                if (data == "null") {

                } else {
                    data = JSON.parse(data);
                    if (data['IS_PRESENT'] == 1) {
                        $group.html('P');
                        $group.parent().addClass('bg-green');
                    } else {
                        if (data['IS_ABSENT'] == 1) {
                            $group.html('A');
                            $group.parent().addClass('bg-red');

                        } else {
                            if (data['ON_LEAVE'] == 1) {
                                $group.html('L');
                                $group.parent().addClass('bg-blue');

                            } else {
                                $group.html('H');
                                $group.parent().addClass('bg-white');
                            }

                        }

                    }
                }
            });

        };

        var initializeReport = function (employeeId) {
            $tableContainer.block();
            app.pullDataById(document.wsEmployeeWiseDailyReport, {employeeId: employeeId}).then(function (response) {
                $tableContainer.unblock();
                console.log('departmentWiseEmployeeMonthlyR', response);
                var extractedDetailData = extractDetailData(response.data, employeeId);
                console.log('extractedDetailData', extractedDetailData);
                $tableContainer.kendoGrid({
                    dataSource: {
                        data: extractedDetailData.rows,
                        pageSize: 20
                    },
                    scrollable: false,
                    sortable: false,
                    pageable: false,
                    columns: extractedDetailData.cols
                });
                displayDataInBtnGroup('.custom-btn-group');
                displayTotalInGrid('.total-attendance');

            }, function (error) {
                $tableContainer.unblock();
                console.log('departmentWiseEmployeeMonthlyE', error);
            });
        };
        var displayTotalInGrid = function (selector) {
            $(selector).each(function (k, group) {
                var $group = $(group);
                var data = JSON.parse($group.attr('data'));
                var $childrens = $group.children();
                var $present = $($childrens[0]);
                var $absent = $($childrens[1]);
                var $leave = $($childrens[2]);

                var presentDays = parseFloat(data['IS_PRESENT']);
                var absentDays = parseFloat(data['IS_ABSENT']);
                var leaveDays = parseFloat(data['ON_LEAVE']);

                $present.attr('title', data['IS_PRESENT']);
                $absent.attr('title', data['IS_ABSENT']);
                $leave.attr('title', data['ON_LEAVE']);

                var total = presentDays + absentDays + leaveDays;

                $present.html(Number((presentDays * 100 / total).toFixed(1)));
                $absent.html(Number((absentDays * 100 / total).toFixed(1)));
                $leave.html(Number((leaveDays * 100 / total).toFixed(1)));
            });
        };
        $('select').select2();
        var $companylist = $('#companyList');
        var $employeeList = $('#employeeList');
        var $branchList = $('#branchList');
        var $departmentList = $('#departmentList');
        var $generateReport = $('#generateReport');

        var populateList = function ($element, list, id, value, defaultMessage) {
            $element.html('');
            $element.append($("<option></option>").val(-1).text(defaultMessage));
            for (var i in list) {
                $element.append($("<option></option>").val(list[i][id]).text(list[i][value]));
            }
        }

        var comBraDepList = document.comBraDepList;
        var employeeList = document.employeeList;
        populateList($employeeList, employeeList, 'EMPLOYEE_ID', 'FULL_NAME', "Select Employee");
        populateList($companylist, comBraDepList, 'COMPANY_ID', 'COMPANY_NAME', "All Company");
        populateList($branchList, [], 'BRANCH_ID', 'BRANCH_NAME', "All Branch");
        populateList($departmentList, [], 'DEPARTMENT_ID', 'DEPARTMENT_NAME', "All Department");

        var employeeFilter = function (empList, companyId, branchId, departmentId) {
            return empList.filter(function (item) {
                if (companyId != -1) {
                    if (branchId != -1) {
                        if (departmentId != -1) {
//                            return item['COMPANY_ID'] == companyId && item['BRANCH_ID'] == branchId && item['DEPARTMENT_ID'] == departmentId;
                            return item['DEPARTMENT_ID'] == departmentId;
                        } else {
//                            return item['COMPANY_ID'] == companyId && item['BRANCH_ID'] == branchId;
                            return item['BRANCH_ID'] == branchId;
                        }
                    } else {

                        return item['COMPANY_ID'] == companyId;
                    }
                } else {
                    return true;
                }
            });
        }
        $companylist.on('change', function () {
            var $this = $(this);
            if ($this.val() != -1) {
                populateList($branchList, comBraDepList[$this.val()]['BRANCH_LIST'], 'BRANCH_ID', 'BRANCH_NAME', "SELECT BRANCH");
                populateList($departmentList, [], 'DEPARTMENT_ID', 'DEPARTMENT_NAME', "SELECT DEPARTMENT");
                populateList($employeeList, employeeFilter(employeeList, $this.val(), -1, -1), 'EMPLOYEE_ID', 'FULL_NAME', "Select Employee");
            }
        });
        $branchList.on('change', function () {
            var $this = $(this);
            if ($this.val() != -1) {
                populateList($departmentList, comBraDepList[$companylist.val()]['BRANCH_LIST'][$this.val()]['DEPARTMENT_LIST'], 'DEPARTMENT_ID', 'DEPARTMENT_NAME', "SELECT DEPARTMENT");
                populateList($employeeList, employeeFilter(employeeList, $companylist.val(), $this.val(), -1), 'EMPLOYEE_ID', 'FULL_NAME', "Select Employee");
            }
        });
        $departmentList.on('change', function () {
            var $this = $(this);
            if ($this.val() != -1) {
                populateList($employeeList, employeeFilter(employeeList, $companylist.val(), $branchList.val(), $this.val()), 'EMPLOYEE_ID', 'FULL_NAME', "Select Employee");
            }
        });

        $generateReport.on('click', function () {
            var employeeId = $employeeList.val();
            if (employeeId == -1) {
                app.errorMessage("No Department Selected", "Notification");
            } else {
                initializeReport(employeeId);
            }
        });

        var employeeId = document.employeeId;
        if (employeeId != 0) {
            initializeReport(employeeId);
            $employeeList.val(employeeId);
        }


    });
})(window.jQuery, window.app);