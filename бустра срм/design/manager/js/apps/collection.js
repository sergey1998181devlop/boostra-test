$( document ).ready(function() {
    $(document).on('click', '.js-blacklist-call-user', function(e){
        let users = $("[name='sms_check[]']:checked");
        let days = $(this).parent().find('.days-count').val()
        let manager = $(this).data('manager')
        let yourArray = [];
        if (users.length < 1 || days == 0 || days == "" || days<0){
            Swal.fire({
                timer: 5000,
                title: 'Ошибка блокировки',
                text: 'Ошибка в данных',
                type: 'error',
            });

            return;
        }

        $('.preloader').show();

        $("[name='sms_check[]']:checked").each(function(){
            yourArray.push($(this).val());
        });
        ajaxRequest(yourArray,days,manager)
        $('.preloader').hide();
    });

    $(document).on('click','.js-client-blacklist-call-user',function() {
        let user = $(this).data('user');
        let days = $(this).parent().find('.days-count').val()
        let manager = $(this).data('manager')
        let block = $(this).val()
        $('.preloader').show();

        ajaxRequest(user,days,manager,block)
        $('.preloader').hide();
    })

    function ajaxRequest(users,days,manager, block = null){
        $.ajax({
            url: 'ajax/add-call-blacklist.php',
            type: 'POST',
            data: {
                'users_ids' : users,
                'days' : days,
                'manager' : manager,
                'block' : block
            },
            success: function (resp) {
                if (resp.error) {
                    Swal.fire({
                        timer: 5000,
                        title: 'Ошибка!',
                        text: resp.error,
                        type: 'error',
                    });
                } else {
                    Swal.fire({
                        timer: 5000,
                        title: '',
                        text: resp.success,
                        type: 'success',
                    });
                }
                location.reload()
            }
        });
    }
})
