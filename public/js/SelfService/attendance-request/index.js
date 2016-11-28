(function ($) {
    'use strict';
    $(document).ready(function () {    
       
        $("#attendanceRequestTable").kendoGrid({
            dataSource: {
                data: document.attendanceRequest,
                pageSize: 20
            },
            height: 450,
            scrollable: true,
            sortable: true,
            filterable: true,
            pageable: {
                input: true,
                numeric: false
            },
            dataBound:gridDataBound,
            rowTemplate: kendo.template($("#rowTemplate").html()),
            columns: [
                {field: "ATTENDANCE_DT", title: "Attendance Date"},
                {field: "IN_TIME", title: "Check In"},
                {field: "OUT_TIME", title: "Check Out"},
                {field: "IN_REMARKS", title: "Late In Reason"},
                {field: "OUT_REMARKS", title: "Early Out Reason"},
                {field: "A_STATUS", title: "Status"},
                {title: "Action"}
            ]
        });    
        function gridDataBound(e) {
            var grid = e.sender;
            if (grid.dataSource.total() == 0) {
                var colCount = grid.columns.length;
                $(e.sender.wrapper)
                        .find('tbody')
                        .append('<tr class="kendo-data-row"><td colspan="' + colCount + '" class="no-data">There is no data to show in the grid.</td></tr>');
            }
        };
    });   
})(window.jQuery, window.app);