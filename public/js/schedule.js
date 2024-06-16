$(function() {
    var rounded = (num, decimals) => Number(num.toFixed(decimals));

    $(".drag").draggable({
        revert : "invalid",
        zIndex: 10000,
        start: function (e, ui) {
            // Date from where task was dragged from
            $(this).data("oldDate", $(this).parent().data("date"));
            $(this).data("oldDoctor", $(this).parent().data("doctor-id"));

            var top = ui.position.top;
            var left = ui.position.left;
            $(this).attr({"data-top": top, "data-left": left});
        }
    });

    $("td[data-date]").droppable({
        drop: function (e, ui) {
            var drag = ui.draggable,
                drop = $(this),
                oldDate = drag.data("oldDate"),
                newDate = drop.data("date"),
                oldDoctor = drag.data("oldDoctor"),
                newDoctor = drop.data("doctor-id");

            if (oldDate == newDate || oldDoctor != newDoctor) {
                return $(drag).css({ top: 0, left: 0 }); // Return task to old position
            }

            $.ajax({
                url: '/schedule/'+ drag.data('taskid')+'/edit',
                type: 'PUT',
                processing: true,
                data: { date: newDate },
                beforeSend: () => {$("#loading-wrapper").fadeIn(500);},
                success: function(data){
                    var date1 = parseInt(oldDate.substr(8,2));
                    var date2 = parseInt(newDate.substr(8,2));
                    var $tdResultStart, $tdResultEnd;
                    $tdResultStart = $(drag).closest('tr').find('.cal-part-start');
                    $tdResultEnd = $(drag).closest('tr').find('.cal-part-end');

                    if (date1 <= 15 && date2 > 15) {
                        changeResult($tdResultStart, (-1) * $(drag).data('hour'));
                        changeResult($tdResultEnd, $(drag).data('hour'));
                    }

                    if (date2 <= 15 && date1 > 15) {
                        changeResult($tdResultStart, $(drag).data('hour'));
                        changeResult($tdResultEnd, (-1) * $(drag).data('hour'));
                    }

                    $(drag).detach().css({ top: 0, left: 0 }).appendTo(drop);
                },
                error: function(result) {
                    alert('Ошибка удаления! Обновите страницу и попробуйте еще раз!')
                    $(drag).css({ top: 0, left: 0 }); // Return task to old position
                },
                complete: () => {$("#loading-wrapper").fadeOut(500);},
            });
        }
    });

    // show EDIT and TRASH tools
    $(".drag").hover(
        function () {
            $(this)
                .css("z-index", "500")
                .prepend(
                    '<div class="opt-tools"><div class="opt-edit"><i class="fas fa-pen"></i></div><div class="opt-trash"><i class="fas fa-trash"></i></div></div>'
                );
        },
        function () {
            //When mouse hovers out DIV remove tools
            $(this).css("z-index", "0").find(".opt-tools").remove();
        }
    );

    function changeResult($instance, $value) {
        var value = parseFloat($instance.html()) + parseFloat($value);
        if (value < 0) {
            $instance.html(0);
        } else {
            $instance.html(rounded(value,2));
        }
    }

    // Modal remove task ?
    $(document).on("click", ".opt-trash", function () {
        var $task = $(this).parent().parent();

        $.showConfirm({
            title:"Подтверждение",
            body:"Вы точно хотите удалить эту запись?",
            textTrue:'Да',
            textFalse:'Нет',
            onSubmit: function (result, modal) {
                if (!result) return;

                var date = parseInt($task.parent().data('date').substr(8,2));

                var $tdResult;
                var $tdResultMain = $task.closest('tr').find('.cal-part-main');

                if (date <= 15) {
                    $tdResult =$task.closest('tr').find('.cal-part-start');
                } else {

                    $tdResult =$task.closest('tr').find('.cal-part-end');
                }

                $.ajax({
                    url: '/schedule/'+$task.data('taskid')+'/delete',
                    type: 'DELETE',
                    success: function () {
                        changeResult($tdResult, (-1) * $task.data('hour'));
                        changeResult($tdResultMain, (-1) * $task.data('hour'));

                        $task.remove();
                    },
                    error: function(result) {
                        alert('Ошибка удаления! Обновите страницу и попробуйте еще раз!')
                    },
                });

            }
        }).show();
    });

    // Modal remove task ?
    $(document).on("click", ".opt-edit", function () {
        var $task = $(this).parent().parent();

        $.showConfirm({
            title:"Изменить",
            body: '<div class="form-group"><label>Начало смены<input type="time" name="timeStart" class="form-control" value="'+$task.data('time-start')+'" /></label></div>'+
                '<div class="form-group"><label>Конец смены<input type="time" name="timeEnd" class="form-control" value="'+$task.data('time-end')+'" /></label></div>'+
                '<div class="form-group"><label>Обед (минут)<input type="number" name="offMinutes" class="form-control" value="'+$task.data('off-minutes')+'" /></label></div>',
            textTrue:'Сохранить',
            textFalse:'Отменить',
            onSubmit: function (result, modal) {
                if (!result) return;

                var timeStart = $(modal.bodyElement).find('input[name=timeStart]').val();
                var timeEnd = $(modal.bodyElement).find('input[name=timeEnd]').val();
                var offMinutes = $(modal.bodyElement).find('input[name=offMinutes]').val();

                $.ajax({
                    url: '/schedule/'+ $task.data('taskid')+'/edit',
                    type: 'PUT',
                    processing: true,
                    data: { timeStart: timeStart, timeEnd: timeEnd, offMinutes: offMinutes },
                    beforeSend: () => {$("#loading-wrapper").fadeIn(500);},
                    success: function(data){
                        var date = parseInt($task.parent().data('date').substr(8,2));
                        var month = parseInt($task.parent().data('date').substr(5,2));
                        var years = parseInt($task.parent().data('date').substr(0,4));

                        var $tdResult;
                        var $tdResultMain = $task.closest('tr').find('.cal-part-main');

                        if (date <= 15) {
                            $tdResult =$task.closest('tr').find('.cal-part-start');
                        } else {

                            $tdResult =$task.closest('tr').find('.cal-part-end');
                        }

                        var dateStart = new Date(
                            years,
                            month -1,
                            date,
                            timeStart.substr(0,2),
                            timeStart.substr(3,2),
                        );

                        var dateEnd = new Date(
                            years,
                            month -1,
                            date,
                            timeEnd.substr(0,2),
                            timeEnd.substr(3,2),
                        );

                        if (dateEnd < dateStart) {
                            dateEnd.setDate(dateEnd.getDate()+1);
                        }

                        var hour = (dateEnd.getTime() - dateStart.getTime());
                        hour /= 1000 * 60;
                        hour -= offMinutes;
                        hour /= 60;


                        changeResult($tdResult, (-1) * $task.data('hour') + rounded(hour,2));
                        changeResult($tdResultMain, (-1) * $task.data('hour') + rounded(hour,2));

                        $task.data('hour', rounded(hour,2));
                        $task.data('time-start', timeStart);
                        $task.data('time-end', timeEnd);
                        $task.data('off-minutes', offMinutes);

                        $task.find('.details-time > span').html(timeStart + ' - ' + timeEnd);
                        $task.find('.details-hour > span').html(rounded(hour,2));
                        $task.find('.details-off-minutes > span').html(offMinutes);
                    },
                    error: function(result) {
                        alert('Ошибка удаления! Обновите страницу и попробуйте еще раз!')
                    },
                    complete: () => {$("#loading-wrapper").fadeOut(500);},
                });
            }
        }).show();
    });
});