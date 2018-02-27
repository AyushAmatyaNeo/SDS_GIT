(function ($) {
    'use strict';
    $(document).ready(function () {
        var $table = $('#table');
        var actiontemplateConfig = {
            update: {
                'ALLOW_UPDATE': document.acl.ALLOW_UPDATE,
                'params': ["CONTRACT_ID"],
                'url': document.editLink
            },
            delete: {
                'ALLOW_DELETE': document.acl.ALLOW_DELETE,
                'params': ["CONTRACT_ID"],
                'url': document.deleteLink
            }
        };
        
        var actionTemplate=app.genKendoActionTemplate(actiontemplateConfig);
        var locationLinkTemplate=`
                <a class="btn-edit" title="View" href="${document.contractDetailLink}/#:CONTRACT_ID #" style="height:17px;">
                    <i class="fa fa-search-plus"></i>
                </a>`;
        var allTemplate=actionTemplate+locationLinkTemplate;
        
        var columns = [
            {field: "CONTRACT_NAME", title: "Contract" ,width:"100px"},
            {field: "CUSTOMER_ENAME", title: "Customer" ,width:"120px"},
            {title: "From Date", columns: [
                    {field: "START_DATE_AD", title: "AD" ,width:"80px"},
                    {field: "START_DATE_BS", title: "BS" ,width:"80px"},
                ]},
            {title: "To Date", columns: [
                    {field: "END_DATE_AD", title: "AD" ,width:"80px"},
                    {field: "END_DATE_BS", title: "BS" ,width:"80px"},
                ]},
            {field: "WORKING_CYCLE", title: "Cycle" ,width:"90px"},
            {field: "CHARGE_TYPE", title: "Charge Type" ,width:"120px"},
            {field: "CHARGE_RATE", title: "Charge Rate" ,width:"120px"},
            {field: "REMARKS", title: "Remarks"},
            {field: ["CONTRACT_ID"], width:"90px" ,title: "Action", template: allTemplate}
        ];
        var map = {
            'CONTRACT_NAME': 'Contract',
            'CUSTOMER_ENAME': 'Customer Name',
            'START_DATE_AD': 'From (AD)',
            'START_DATE_BS': 'From (BS)',
            'END_DATE_AD': 'To (AD)',
            'END_DATE_BS': 'To (BS)',
            'WORKING_CYCLES': 'Working Cycles',
            'CHARGE_TYPE': 'Charge Type',
            'CHARGE_RATE': 'Charge Rate',
            'REMARKS': 'Remarks',
        }
        app.initializeKendoGrid($table, columns);

        app.searchTable($table, ['CONTRACT_NAME','CUSTOMER_ENAME']);

        $('#excelExport').on('click', function () {
            app.excelExport($table, map, 'Customer Contract List.xlsx');
        });
        $('#pdfExport').on('click', function () {
            app.exportToPDF($table, map, 'Customer Contract List.pdf');
        });

        app.pullDataById("", {}).then(function (response) {
            console.log(response);
            app.renderKendoGrid($table, response.data);
        }, function (error) {

        });
    });
})(window.jQuery);