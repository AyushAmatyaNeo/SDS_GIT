(function ($, app) {
    'use strict';
    $(document).ready(function () {
        $("select").select2();

        var $penalty = $('#penalty');
        var $fiscalYearId = $('#fiscalYearId');
        var $monthId = $('#monthId');
        var $search = $('#search');

        app.populateSelect($fiscalYearId, document.fiscalYears, 'FISCAL_YEAR_ID', 'FISCAL_YEAR_NAME', 'Select Fiscal Year');
        $fiscalYearId.on('change', function () {
            var $this = $(this);
            var value = $this.val();
            var months = document.months.filter(function (item) {
                return item['FISCAL_YEAR_ID'] == value;
            });
            app.populateSelect($monthId, months, 'MONTH_ID', 'MONTH_EDESC', "Select Month");
        });

        app.initializeKendoGrid($penalty, [
            {field: "COMPANY_NAME", title: "Company", width: 150},
            {field: "DEPARTMENT_NAME", title: "Department", width: 150},
            {field: "FULL_NAME", title: "Name", width: 150},
            {title: "Date", columns: [
                    {field: "ATTENDANCE_DT", title: "AD", width: 75},
                    {field: "ATTENDANCE_DT_N", title: "BS", width: 75},
                ]},
            {field: "TYPE", title: "Type", width: 150},
        ], function (e) {
            app.pullDataById(document.penaltyDetailWS, {employeeId: e.data.EMPLOYEE_ID, attendanceDt: e.data.ATTENDANCE_DT, type: e.data.TYPE_CODE}).then(function (response) {
                if (!response.success) {
                    app.showMessage(response.error, 'error');
                    return;
                }
                $("<div/>").appendTo(e.detailCell).kendoGrid({
                    dataSource: {
                        data: response.data,
                        pageSize: 20
                    },
                    scrollable: false,
                    sortable: false,
                    pageable: false,
                    columns: [
                        {title: "Date", columns: [
                                {field: "ATTENDANCE_DT", title: "AD"},
                                {field: "ATTENDANCE_DT_N", title: "BS"},
                            ]},
                        {field: "IN_TIME", title: "In Time", width: 150},
                        {field: "OUT_TIME", title: "Out Time", width: 150},
                        {field: "START_TIME", title: "Start Time", width: 150},
                        {field: "END_TIME", title: "End Time", width: 150},
                        {field: "TYPE", title: "Type", width: 150},
                    ]
                });
            }, function (error) {
            });
        });
        app.searchTable('withOTReport', ['COMPANY_NAME', 'DEPARTMENT_NAME', 'FULL_NAME', 'ATTENDANCE_DT', 'ATTENDANCE_DT_N', 'TYPE'], true);
        app.pdfExport(
                'withOTReport',
                {
                    'COMPANY_NAME': 'Company',
                    'DEPARTMENT_NAME': 'Department',
                    'FULL_NAME': 'Name',
                    'ATTENDANCE_DT': 'Date(AD)',
                    'ATTENDANCE_DT_N': 'Date(BS)',
                    'TYPE': 'Type',
                }
        );



        $search.on('click', function () {
            var monthValue = $monthId.val();
            if (monthValue === null) {
                app.showMessage("Please select month first.");
                $monthId.focus();
                return;
            }
            var data = document.searchManager.getSearchValues();
            data['monthId'] = monthValue;
            app.pullDataById(document.penalty, data).then(function (response) {
                if (response.success) {
                    app.renderKendoGrid($penalty, response.data);
                    actionBtnStatus(response.alreadyPenalized);
                } else {
                    app.showMessage(response.error, 'error');
                }
            }, function (error) {
                app.showMessage(error, 'error');
            });
        });
        var $actionBtn = $('#actionBtn');
        var $noOfDays = $('#noOfDays');

        var actionBtnStatus = function (alreadyPenalized) {
            $('#actionDiv').show();
            if (alreadyPenalized['IS_DEDUCTED'] == 'Y') {
                $actionBtn.html('Rededuct');
            } else {
                $actionBtn.html('Deduct');
            }
        };

        $actionBtn.on('click', function () {
            if ($noOfDays.val() == '') {
                app.showMessage('No of Days is required.', 'error');
                $noOfDays.focus();
            }
            var data = {'monthId': $monthId.val(), 'noOfDays': $noOfDays.val()};
            app.pullDataById(document.deductLink, data).then(function (response) {
                app.showMessage("The leave is successfully deducted.");
                $search.trigger('click');
            }, function (error) {});

        });

    });
})(window.jQuery, window.app);