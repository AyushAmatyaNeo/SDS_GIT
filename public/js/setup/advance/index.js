(function ($, app) {
    'use strict';
    $(document).ready(function () {
        $("#advanceTable").kendoGrid({
            dataSource: {
                data: document.advances,
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
            rowTemplate: kendo.template($("#rowTemplate").html()),
            columns: [
                {field: "ADVANCE_CODE", title: "Advance Code",width:80},
                {field: "ADVANCE_NAME", title: "Advance Name",width:130},
                {field: "MIN_SALARY_AMT", title: "Min. Salary Amount",width:90},
                {field: "MAX_SALARY_AMT", title: "Max Salary Amount",width:90},
                {field: "AMOUNT_TO_ALLOW", title: "Amount To Allow",width:120},
                {field: "MONTH_TO_ALLOW", title: "Month To Allow",width:150},
                {title: "Action",width:100}
            ]
        });
        
        $("#export").click(function (e) {
            var rows = [{
                    cells: [
                        {value: "Advance Code"},
                        {value: "Advance Name"},
                        {value: "Min. Salary Amount"},
                        {value: "Max. Salary Amount"},
                        {value: "Amount To Allow"},
                        {value: "Month To Allow"},
                        {value: "Remarks"}
                    ]
                }];
            var dataSource = $("#advanceTable").data("kendoGrid").dataSource;
            var filteredDataSource = new kendo.data.DataSource({
                data: dataSource.data(),
                filter: dataSource.filter()
            });

            filteredDataSource.read();
            var data = filteredDataSource.view();
            
            for (var i = 0; i < data.length; i++) {
                var dataItem = data[i];
                rows.push({
                    cells: [
                        {value: dataItem.ADVANCE_CODE},
                        {value: dataItem.ADVANCE_NAME},
                        {value: dataItem.MIN_SALARY_AMT+"-"+dataItem.MAX_SALARY_AMT},
                        {value: dataItem.AMOUNT_TO_ALLOW+"%"},
                        {value: dataItem.MONTH_TO_ALLOW},
                        {value: dataItem.REMARKS}
                    ]
                });
            }
            excelExport(rows);
            e.preventDefault();
        });
        
        function excelExport(rows) {
            var workbook = new kendo.ooxml.Workbook({
                sheets: [
                    {
                        columns: [
                            {autoWidth: true},
                            {autoWidth: true},
                            {autoWidth: true},
                            {autoWidth: true},
                            {autoWidth: true},
                            {autoWidth: true},
                            {autoWidth: true}
                        ],
                        title: "Advance",
                        rows: rows
                    }
                ]
            });
            kendo.saveAs({dataURI: workbook.toDataURL(), fileName: "AdvanceList.xlsx"});
        }       
        window.app.UIConfirmations();
    });
})(window.jQuery, window.app);