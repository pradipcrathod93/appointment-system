$(document).ready(function() {
    // Initialize Date Picker
    $("#appointment_date").datepicker({
        dateFormat: 'yy-mm-dd',
        beforeShowDay: function(date) {
            var day = date.getDay(); 

            // Disable Sundays
            if (day === 0) {
                return [false, '', ''];
            }

            // Disable past dates
            var currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0);
            if (date < currentDate) {
                return [false, '', ''];
            }

            var dateStr = $.datepicker.formatDate('yy-mm-dd', date);
            var result = [true, '', ''];
            $.ajax({
                url: '/check-date-availability',
                method: 'GET',
                data: { date: dateStr },
                async: false,
                success: function(response) {
                    if (!response.isAvailable) {
                        result = [false, 'unavailable', ''];
                    }
                }
            });

            return result;
        },
        onSelect: function(dateText) {
            $.ajax({
                url: '/get-available-slots',
                method: 'GET',
                data: { date: dateText },
                success: function(response) {
                    var slots = response.slots.map(function(slot) {
                        return { 
                            start: slot, 
                            end: addOneHour(slot) 
                        };
                    });
                    var disabledRanges = slots.map(function(slot) {
                        return [formatTime(slot.start), formatTime(slot.end)];
                    });
                    $('#appointment_time').timepicker('option', 'disableTimeRanges', disabledRanges);
                }
            });
        }
    });

    // Initialize Time Picker
    $('#appointment_time').timepicker({
        timeFormat: 'h:i A',
        step: 60,
        minTime: '10:00am',
        maxTime: '6:00pm', // Last start time should be 6:00pm to allow a 1-hour slot until 7:00pm
        defaultTime: '10:00am',
        startTime: '10:00am',
        dynamic: false,
        dropdown: true,
        scrollbar: true
    });

    $('#appointment_time').on('changeTime', function() {
        var startTime = $(this).timepicker('getTime');
        var endTime = new Date(startTime.getTime() + 60 * 60 * 1000); // Add 1 hour
    
        var startFormatted = formatTime(startTime.getHours() + ':' + ('0' + startTime.getMinutes()).slice(-2));
        var endFormatted = formatTime(endTime.getHours() + ':' + ('0' + endTime.getMinutes()).slice(-2));
    
        $('#appointment_state_time_end_time').text('Start Time: ' + startFormatted + ' | End Time: ' + endFormatted);
    });

    function formatTime(time) {
        var parts = time.split(':');
        var hour = parseInt(parts[0]);
        var minutes = parts[1];
        var ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12;
        hour = hour ? hour : 12;
        return hour + ':' + minutes + ' ' + ampm;
    }

    function addOneHour(time) {
        var parts = time.split(':');
        var hour = parseInt(parts[0]) + 1;
        return hour + ':' + parts[1] + ':' + parts[2];
    }

    $('#appointmentForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'), // Get the action attribute from the form
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#formResponse').html('<div class="alert alert-success">' + response.message + '</div>');
                $('#appointmentForm')[0].reset();
                $('#appointmentForm').hide();
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessages = '<div class="alert alert-danger">';
                $.each(errors, function(key, value) {
                    errorMessages += '<p>' + value[0] + '</p>';
                });
                errorMessages += '</div>';
                $('#formResponse').html(errorMessages);
            }
        });
    });
});