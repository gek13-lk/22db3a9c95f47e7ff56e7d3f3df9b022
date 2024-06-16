$(function() {
    var rounded = (num, decimals) => Number(num.toFixed(decimals));

    $(".drag").draggable({
        revert: true,
        start: function (e, ui) {
            // Date from where task was dragged from
            $(this).data("oldDate", $(this).parent().data("date"));
            $(this).data("oldDoctor", $(this).parent().data("doctor-id"));

            console.log($(this).parent().data("date"));
            console.log($(this).parent().data("doctor-id"));
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
            console.log(oldDate);
            console.log(newDate);
            console.log(oldDoctor);
            console.log(newDoctor);

            if (oldDate == newDate || oldDoctor != newDoctor) {
                return $(drag).css({ top: 0, left: 0 }); // Return task to old position
            }

            $(drag).detach().css({ top: 0, left: 0 }).appendTo(drop);
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
                    error: function(result) {
                        alert('Ошибка удаления! Обновите страницу и попробуйте еще раз!')
                    },
                });

                changeResult($tdResult, (-1) * $task.data('hour'));
                changeResult($tdResultMain, (-1) * $task.data('hour'));

                $task.remove();
            }
        }).show();
    });
});