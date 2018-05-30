(function ($, app) {
    'use strict';
    $(document).ready(function () {
        var $table = $('#report');
        var months = {
            'ONE': 'Shrawan',
            'TWO': 'Bhadra',
            'THREE': 'Ashwin',
            'FOUR': 'Kartik',
            'FIVE': 'Mangsir',
            'SIX': 'Paush',
            'SEVEN': 'Magh',
            'EIGHT': 'Falgun',
            'NINE': 'Chaitra',
            'TEN': 'Baishakh',
            'ELEVEN': 'Jestha',
            'TWELVE': 'Ashadh',
        };
        var reportHeadList = {
            'PR': {field: 'PRESENT', class: 'blue'},
            'AB': {field: 'ABSENT', class: 'red'},
            'LV': {field: 'LEAVE', class: 'green'},
            'DO': {field: 'DAYOFF', class: 'yellow'},
            'HD': {field: 'HOLIDAY', class: 'purple'},
            'WH': {field: 'WOH', class: 'yellow-soft'},
            'WD': {field: 'WOD', class: 'purple-soft'}
        };
        var exportMap = {"FULL_NAME": "Employee"};
        var columns = [
            {field: "FULL_NAME", title: "Employee", width: 150, locked: true}
        ];
        for (var i in months) {
            var column = {title: months[i]};
            var innerColumns = [];
            for (var j in reportHeadList) {
                var field = i + "_" + reportHeadList[j]['field'];
                var exportColumnName = months[i] + "_" + reportHeadList[j]['field'];
                innerColumns.push({
                    field: field,
                    title: j,
                    width: 70,
                    template: `<button type="button" class="btn btn-block ${reportHeadList[j]['class']}"> #: ${field} # </button>`
                });
                exportMap[field] = exportColumnName;
            }
            column['columns'] = innerColumns;
            columns.push(column);
        }
        app.initializeKendoGrid($table, columns);
        app.serverRequest('', {}).then(function (response) {
            app.renderKendoGrid($table, response.data);
        }, function (error) {

        });

        app.searchTable($table, ['FULL_NAME']);
        $('#excelExport').on('click', function () {
            app.excelExport($table, exportMap, 'Employee_Wise_Attendance_Report');
        });
        $('#pdfExport').on('click', function () {
            app.exportToPDF($table, exportMap, 'Employee_Wise_Attendance_Report');
        });

    });
})(window.jQuery, window.app);