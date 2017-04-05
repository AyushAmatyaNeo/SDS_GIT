(function ($, app) {
    'use strict';
    $(document).ready(function () {
        // Init Data Tables
        var table = $('#sample_1');

        /* Table tools samples: https://www.datatables.net/release-datatables/extras/TableTools/ */

        /* Set tabletools buttons and button container */

        $.extend(true, $.fn.DataTable.TableTools.classes, {
            "container": "btn-group tabletools-dropdown-on-portlet",
            "buttons": {
                "normal": "btn btn-sm default",
                "disabled": "btn btn-sm default disabled"
            },
            "collection": {
                "container": "DTTT_dropdown dropdown-menu tabletools-dropdown-menu"
            }
        });

        var oTable = table.dataTable({

            // Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "No data available in table",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered1 from _MAX_ total entries)",
                "lengthMenu": "Show _MENU_ entries",
                "search": "Search:",
                "zeroRecords": "No matching records found"
            },

            // Or you can use remote translation file
            //"language": {
            //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
            //},

            "order": [
                [1, 'asc']
            ],

            "lengthMenu": [
                [5, 15, 20, -1],
                [5, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "pageLength": 10,

            "dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

            // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
            // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
            // So when dropdowns used the scrollable div should be removed.
            //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",

            "tableTools": {
                "sSwfPath": "../assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
                "aButtons": [{
                    "sExtends": "pdf",
                    "sButtonText": "PDF"
                }, {
                    "sExtends": "csv",
                    "sButtonText": "CSV"
                }, {
                    "sExtends": "xls",
                    "sButtonText": "Excel"
                }, {
                    "sExtends": "print",
                    "sButtonText": "Print",
                    "sInfo": 'Please press "CTR+P" to print or "ESC" to quit',
                    "sMessage": "Generated by DataTables"
                }]
            }
        });

        var tableWrapper = $('#sample_1_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper

        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown
    });

    /***** Charts *****/
    var genderHeadCountData = [];
    for(var x in document.xndr) {
        genderHeadCountData.push([document.xndr[x], document.xndrhc[x]]);
    }
    Highcharts.chart('chart-gender-headcount', {
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45
            }
        },
        title: {
            text: 'Employees By Gender'
        },
        subtitle: {
            text: 'Gender Wise Employees Head Count'
        },
        plotOptions: {
            pie: {
                innerSize: 100,
                depth: 45
            }
        },
        legend: {
            layout: 'vertical',
            floating: true,
            align: 'right',
            verticalAlign: 'top',
            symbolPadding: 20,
            symbolWidth: 10
        },
        series: [{
            name: 'Head Count',
            data: genderHeadCountData,
            showInLegend: true
        }]
    });

    var locationHeadCountData = [];
    for(var x in document.brln) {
        locationHeadCountData.push([document.brln[x], document.brlnhc[x]]);
    }
    Highcharts.chart('chart-location-headcount', {
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45
            }
        },
        title: {
            text: 'Employees By Branch'
        },
        subtitle: {
            text: 'Branch Wise Employees Head Count'
        },
        plotOptions: {
            pie: {
                innerSize: 100,
                depth: 45
            }
        },
        legend: {
            layout: 'vertical',
            floating: true,
            align: 'left',
            verticalAlign: 'top',
            symbolPadding: 20,
            symbolWidth: 10
        },
        series: [{
            name: 'Head Count',
            data: locationHeadCountData,
            showInLegend: true
        }]
    });


    var departmentHeadCountData = [];
    for(var x in document.odept) {
        departmentHeadCountData.push([document.odept[x], document.odepthc[x]]);
    }
    Highcharts.chart('chart-department-headcount', {
        chart: {
            type: 'column',
            options3d: {
                enabled: true,
                alpha: 0,
                beta: -1,
                depth: 50,
                viewDistance: 25
            }
        },
        title: {
            text: 'Employees By Department'
        },
        subtitle: {
            text: 'Department Wise Employee Head Count'
        },
        xAxis: {
            type: 'category',
            labels: {
                rotation: -50,
                style: {
                    fontSize: '12px'
                }
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'No. Of Employees'
            }
        },
        legend: {
            enabled: false
        },
        tooltip: {
            pointFormat: 'Employees: <b>{point.y}</b>'
        },
        series: [{
            name: 'Head Count',
            data: departmentHeadCountData,
            dataLabels: {
                enabled: true,
                rotation: 0,
                color: '#FFFFFF',
                align: 'right',
                format: '{point.y}', // one decimal
                y: -5, // 10 pixels down from the top
                style: {
                    fontSize: '12px',
                    // fontFamily: 'Verdana, sans-serif'
                }
            }
        }]
    });

    ComponentsPickers.init();

})(window.jQuery, window.app);