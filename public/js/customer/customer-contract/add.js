(function ($, app) {
    'use strict';
    $(document).ready(function () {
        $('select').select2();
        app.startEndDatePickerWithNepali('nepaliStartDate', 'startDate', 'nepaliEndDate', 'endDate');
        var $form = $('#customerContract')
        var $inTime = $('#inTime');
        var $outTime = $('#outTime');
        var $workingHours = $('#workingHours');
        app.addComboTimePicker($inTime, $outTime, $workingHours);
        app.addDatePicker($('.contractDates'));
        app.addDatePicker($('.contractEmpStartDate'));
        app.addDatePicker($('.contractEmpEndDate'));

        app.populateSelect($('.employees'), document.employeeList, 'EMPLOYEE_ID', 'FULL_NAME', 'Select An Employee', '');

        $form.on('submit', function () {
            if ($inTime.val() == '') {
                app.showMessage('In Time is required.', 'error');
                $inTime.focus();
                return false;
            }
            if ($outTime.val() == '') {
                app.showMessage('Out Time is required.', 'error');
                $outTime.focus();
                return false;
            }
            if ($workingHours.val() == '') {
                app.showMessage('Working Hours is required.', 'error');
                $workingHours.focus();
                return false;
            }
        });

        $('#addContractdate').on('click', function () {
            var appendValues = "<tr>"
                    + "<td><input type='text' class='contractDates' name='contractDates[]'></td>"
                    + "<td>"
                    + "<div class='th-inner '>"
                    + "<label class='mt-checkbox mt-checkbox-single mt-checkbox-outline'>"
                    + "<input class='chkBoxContractDates' type='checkbox'/>"
                    + "<span></span>"
                    + "</label>"
                    + "</div>"
                    + "</td>"
                    + "</tr>";

            $('#tblContractDates tbody').append(appendValues);
            $('#tblContractDates tbody').find('.contractDates:last').datepicker({
                format: 'dd-M-yyyy',
                todayHighlight: true,
                autoclose: true,
                setDate: new Date()
            });
        });


        $('#delContractdate').on('click', function () {
            $('#tblContractDates .chkBoxContractDates:checked').each(function () {
                $(this).parents("tr").remove()
            });
        });



        var checkCycleType = function () {
            var selectedVal = $('input[name=workingCycle]:checked').val();
            console.log(selectedVal);
            (selectedVal == 'W') ? $('#monthWise').show() : $('#monthWise').hide();
            (selectedVal == 'R') ? $('#dateWise').show() : $('#dateWise').hide();
        }

        $("input[name=workingCycle]").on("change", function () {
            checkCycleType();
        });

        checkCycleType();



        $('#addContractEmp').on('click', function () {

            var appendValues = "<tr>"
                    + "<td><select name='employee[]' class='employees'></select></td>"
                    + "<td><input type='text' class='contractEmpStartDate' name='contractEmpStartDate[]'></td>"
                    + "<td><input type='text' class='contractEmpEndDate' name='contractEmpEndDate[]'></td>"
                    + "<td>"
                    + "<div class='th-inner '>"
                    + "<label class='mt-checkbox mt-checkbox-single mt-checkbox-outline'>"
                    + "<input class='chkBoxContractEmp' type='checkbox'/>"
                    + "<span></span>"
                    + "</label>"
                    + "</div>"
                    + "</td>"
                    + "</tr>";

            $('#tblContractEmp tbody').append(appendValues);

            $('#tblContractEmp tbody').find('.contractEmpStartDate:last').datepicker({
                format: 'dd-M-yyyy',
                todayHighlight: true,
                autoclose: true,
                setDate: new Date()
            });

            $('#tblContractEmp tbody').find('.contractEmpEndDate:last').datepicker({
                format: 'dd-M-yyyy',
                todayHighlight: true,
                autoclose: true,
                setDate: new Date()
            });

            app.populateSelect($('#tblContractEmp tbody').find('.employees:last'), document.employeeList, 'EMPLOYEE_ID', 'FULL_NAME', 'Select An Employee', '');
            $('#tblContractEmp tbody').find('.employees:last').select2();


        });


        $('#delContractEmp').on('click', function () {
            $('#tblContractEmp .chkBoxContractEmp:checked').each(function () {
                $(this).parents("tr").remove()
            });
        });


    });
})(window.jQuery, window.app);