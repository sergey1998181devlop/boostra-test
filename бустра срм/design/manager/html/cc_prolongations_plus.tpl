
{$meta_title='Мои задачи' scope=parent}

{capture name='page_scripts'}
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/prolongations.js?v=1.035"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/moment/moment.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.js"></script>

    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/prtasks.app.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/movements.app.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        async function sendSmsToUsers() {
            let users = $("[name='sms_check[]']:checked");
            let template_id = 8

            let yourArray = [];

            // if not selected items
            if (users.length < 1){
                Swal.fire({
                    timer: 5000,
                    title: 'Ошибка отправки СМС',
                    text: 'Не выбраны отправители!!!',
                    type: 'error',
                });

                return;
            }

            $('.preloader').show();

            $("[name='sms_check[]']:checked").each(function(){
                yourArray.push($(this).val());
            });

            var items_data = {
                action: 'sms_arr',
                type: 'sms',
                template_id: template_id,
                users_ids: yourArray,
                manager: {$manager->id}
            };

            console.log(items_data);

            $.ajax({
                url: '/ajax/array_sms_send.php',
                type: 'POST',
                data: items_data,
                success: function(resp){
                    if (resp.success)
                    {
                        Swal.fire({
                            timer: 18000,
                            title: 'Отправка СМС',
                            html: resp.sms,
                            type: 'success',
                        });
                        location.reload()
                    }
                    else {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка отправки СМС',
                            text: resp.sms,
                            type: 'error',
                        });
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            });
            $('.preloader').hide();


            return;

            /*            if (users.length) {
                            $('.preloader').show();
                            $("#send_message_to_users").button('loading');

                            let success_users = [],
                                errors_users = [],
                                promises = [];

                            $(users).each(function(){
                                let _user_id = $(this).val(),
                                    data = {
                                       user_id: _user_id,
                                       action: 'send_sms',
                                       type: 'sms',
                                       template_id: template_id,
                                    };

                                promises.push(
                                    $.ajax({
                                        url: 'client/'+_user_id,
                                        type: 'POST',
                                        data: data,
                                        success: function(resp){

                                            if (resp.success)
                                            {
                                                success_users.push(_user_id);
                                            }
                                            else
                                            {
                                                console.log(resp);
                                                errors_users.push(_user_id);
                                            }
                                        },
                                        error: function(xhr, ajaxOptions, thrownError) {
                                            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                                            alert(error);
                                            console.log(error);
                                        },
                                    })
                                );
                            });

                            $.when.apply($, promises).then(function() {
                                $('.preloader').hide();
                                $("#send_message_to_users").button('reset');

                                if (success_users.length) {
                                    Swal.fire({
                                        timer: 10000,
                                        title: 'Сообщение отправлено ' + success_users.length + ' из ' + users.length,
                                        text: 'Сообщение получили сл. пользователи: ' + success_users.join(','),
                                        type: 'success',
                                    });
                                }

                                if (errors_users.length) {
                                    Swal.fire({
                                        timer: 15000,
                                        title: 'Сообщение не получили ' + errors_users.length,
                                        text: 'Сбой у сл. пользователей: ' + errors_users.join(','),
                                        type: 'error',
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                timer: 5000,
                                title: 'Ошибка отправки СМС',
                                text: 'Не выбраны отправители!!!',
                                type: 'error',
                            });
                        }*/
        }

        $(function(){
            $('.js-filter-manager').select2({
                placeholder: "Менеджеры"
            });
        });

        $(function(){
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'YYYY.MM.DD'
                },
                default:''
            });

            $(document).on('click', '.runFilter', function(e){
                $(this).closest('form').submit();
            });

            let buffManagers = []

            {*$(document).on('click', '.js-distribute-open', function (e) {*}
            {*    e.preventDefault();*}

            {*    var managers = {$managers|json_encode};*}
            {*    let arr = []*}

            {*    $('.js-distribute-contract').remove();*}
            {*    $('.js-contract-row').each(function () {*}
            {*        $('#form_distribute').append('<input type="hidden" name="contracts[]" class="js-distribute-contract" value="' + $(this).data('contract') + '" />');*}
            {*    });*}

            {*    $('.js-select-type').val('all');*}
            {*    $('#modal_distribute').modal();*}
            {*    $.ajax({*}
            {*        url: '/ajax/getManagers.php',*}
            {*        type: 'GET',*}

            {*        success: function (resp) {*}
            {*            buffManagers = [...resp]*}
            {*            for (i in managers) {*}
            {*                resp.map((e) => {*}
            {*                    if (e.manager_id == managers[i].id) {*}
            {*                        managers[i].checked = 'checked = "true"'*}
            {*                    }*}
            {*                })*}
            {*                if ((managers[i].role == 'contact_center') && (managers[i].blocked === '0')) {*}
            {*                    arr.push(managers[i]);*}
            {*                }*}
            {*            }*}
            {*            $('.list-unstyled').empty()*}
            {*            arr.forEach(function (m) {*}
            {*                $('.list-unstyled').append(`*}
            {*                <li>*}
            {*                    <div class="">*}
            {*                        <input class="" name="managers[]" id="distribute_` + m.id + `" value="` + m.id + `" type="checkbox" `+m.checked +`/>*}
            {*                        <label for='distribute_` + m.id + `' class=''>` + m.name + ` </label>*}
            {*                    </div>*}
            {*                </li>*}
            {*                `)*}

            {*            })*}
            {*        }*}
            {*    })*}
            {*});*}

            // нулёвки за 3/5 дней до
            $(document).on('submit', '#form_zero_days_payment', function (e) {
                e.preventDefault();
                var $form = $(this);

                $.ajax({
                    url: $form.attr('action'),
                    data: $form.serialize(),
                    type: 'POST',
                    beforeSend: function () {
                        $form.addClass('loading');
                    },
                    success: function (resp) {
                        // $(".loader-icon").css("display","none")
                        // $(".list-unstyled").css("opacity","1")
                        if (resp.success) {

                            console.log(resp)

                            Swal.fire({
                                timer: 18000,
                                title: 'Отправка в VOX',
                                html: resp.text,
                                type: 'success',
                            });


                        } else {
                            Swal.fire({
                                text: resp.error,
                                type: 'error',
                            });

                        }
                        $form.removeClass('loading');
                    }
                })


            });

            {*$(document).on('submit', '#form_distribute', function (e) {*}

            {*    e.preventDefault();*}
            {*    var $form = $(this);*}
            {*    const serializeForm = $form.serializeArray()*}
            {*    const json = {};*}
            {*    $.each(serializeForm, function () {*}
            {*        if(json[this.name]) {*}
            {*            json[this.name].push(this.value)*}
            {*        } else {*}
            {*            json[this.name] = [this.value] || "";*}
            {*        }*}
            {*    });*}
            {*    const oldManagers = []*}
            {*    buffManagers.forEach(item => {*}
            {*        oldManagers.push(item.manager_id)*}
            {*    })*}


            {*    const deleted = oldManagers.filter(value => !json['managers[]'].includes(value));*}
            {*    let txt = ''*}
            {*    if(deleted.length) {*}
            {*        deleted.forEach(item=> {*}
            {*            txt += '&deleted[]='+ item*}
            {*        })*}
            {*    }*}

            {*    if ($form.hasClass('loading'))*}
            {*        return false;*}

            {*    var _hash = location.hash.replace('#', '?');*}
            {*    if(json['managers[]'].length){*}
            {*        $(".loader-icon").css("display","block")*}
            {*        $(".list-unstyled").css("opacity","0.5")*}
            {*        $.ajax({*}
            {*            url: '/ccprolongations' + _hash,*}
            {*            data: $form.serialize()+txt,*}
            {*            type: 'POST',*}
            {*            beforeSend: function () {*}
            {*                $form.addClass('loading');*}
            {*            },*}
            {*            success: function (resp) {*}
            {*                $(".loader-icon").css("display","none")*}
            {*                $(".list-unstyled").css("opacity","1")*}
            {*                if (resp.success) {*}
            {*                    $('#modal_distribute').modal('hide');*}

            {*                    Swal.fire({*}
            {*                        timer: 5000,*}
            {*                        title: 'Договора распределены.',*}
            {*                        type: 'success',*}
            {*                    });*}
            {*                    location.reload();*}
            {*                } else {*}
            {*                    Swal.fire({*}
            {*                        text: resp.error,*}
            {*                        type: 'error',*}
            {*                    });*}

            {*                }*}
            {*                $form.removeClass('loading');*}
            {*            }*}
            {*        })*}

            {*    }*}
            {*})*}



            $(document).on('click', '#distribute_me', function(e){
                e.preventDefault();

                var $form = $(this);

                if ($form.hasClass('loading'))
                    return false;

    console.log(location.hash)
                var _hash = location.hash.replace('#', '?');
                $.ajax({
                    data: {
                        action: 'distribute_me'
                    },
                    type: 'POST',
                    beforeSend: function(){
                        $form.addClass('loading');
                    },
                    success: function(resp){
                        if (resp.success)
                        {
                            $('#modal_distribute').modal('hide');

                            Swal.fire({
                                timer: 5000,
                                title: 'Договора распределены.',
                                type: 'success',
                            });
                            location.reload();
                        }
                        else
                        {
                            Swal.fire({
                                text: resp.error,
                                type: 'error',
                            });

                        }
                        $form.removeClass('loading');
                    }
                })
            })

            $(document).on('click', '.update-user-balance', function () {
               let task_id = $(this).data('task'),
                   user_id = $(this).data('user'),
                   $_td = $(this).closest('td');

                $.ajax({
                    url: '/order/0000',
                    type: 'POST',
                    data: {
                        action: 'get_balance',
                        user_id: user_id,
                        update: 1
                    },
                    beforeSend: function(){
                        $_td.addClass('data-loading');
                    },
                    success: function(resp){
                        if (!resp.error) {
                            let $_ostatok = $('#ostatok_' + task_id).closest('td.zaim-ostatok-odd'),
                                $_prolongation = $('#prolongation_' + task_id).closest('td.zaim-prolongation'),
                                amount_full = parseInt(resp.balance.ostatok_od) + parseInt(resp.balance.ostatok_percents) + parseInt(resp.balance.ostatok_peni),
                                amount_prolongation = parseInt(resp.balance.prolongation_amount) + {$tv_medical_price};

                            if (!$_prolongation.is('a') && amount_prolongation > 0) {
                                let html = '<a class="" href="#" data-toggle="collapse" data-target="#prolongation_' + task_id + '"></a>' +
                                    '<div id="prolongation_' + task_id + '" class="collapse">' +
                                    '<div class="prolongation_percent">Проценты: <strong></strong></div> ' +
                                    '<div class="prolongation_insurer">Страховка: <strong></strong></div> ' +
                                    '<div class="prolongation_prolongation">Пролонгация: <strong></strong></div> ' +
                                    '<div class="prolongation_sms">СМС-информ: <strong></strong></div> ' +
                                    '<div>Телемедицина (Лайт) <strong>{$tv_medical_price}</strong></div> ' +
                                    '</div>';

                                $_prolongation.html(html);
                            }

                            $_ostatok.find('a').text(amount_full);
                            $_ostatok.find('div > div').eq(0).find('strong').text(resp.balance.ostatok_od);
                            $_ostatok.find('div > div').eq(1).find('strong').text(resp.balance.ostatok_percents);
                            $_ostatok.find('div > div').eq(2).find('strong').text(resp.balance.ostatok_peni);

                            $_prolongation.find('a').text(amount_prolongation);
                            $_prolongation.find('div .prolongation_percent').find('strong').text(resp.balance.prolongation_summ_percents);
                            $_prolongation.find('div .prolongation_insurer').find('strong').text(resp.balance.prolongation_summ_insurance);
                            $_prolongation.find('div .prolongation_prolongation').find('strong').text(resp.balance.prolongation_summ_cost);
                            $_prolongation.find('div .prolongation_sms').find('strong').text(resp.balance.prolongation_summ_sms);

                            Swal.fire({
                                timer: 5000,
                                title: 'Действие выполнено',
                                text: 'Баланс пользователя успешно обновлен',
                                type: 'success',
                            });
                        } else {
                            Swal.fire({
                                timer: 5000,
                                title: 'Произошла ошибка',
                                text: 'Ошибка: ' + resp.error,
                                type: 'error',
                            });
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                        alert(error);
                        console.log(error);
                    },
                }).done(function () {
                    $_td.removeClass('data-loading');
                });
            });
        })

        $(".js-schedule-open").click(function (){

            $('.schedule-calendar-div').toggleClass('showDiv')

        })

        $('.employs-button').click(function (){
            var content = $('.employs-div');
            if (content.find('.employs-list').length > 0) {
                $('.employs-div').empty()
            }else{
                $('.employs-div').empty()
                $.ajax({
                    type: 'GET',
                    url:'ajax/get_company_managers.php?plus=true',
                    success: function(resp){
                        $('.employs-list').empty()
                        $(".employs-div-buttons").remove()
                        $('.employs-div').append(`<ul class="employs-list"> </ul>`)
                        resp.managers.forEach(function (m) {
                            $('.employs-list').append(`
                              <li>
                                <div class="main-input-div" >
                                <div class="input-div">
                                    <input class="" name="managers[]" id="distribute_` + m.id + `" value="` + m.id + `" type="checkbox" />
                                 </div>
                                    <label for='distribute_` + m.id + `' class=''>` + m.name + ` </label>
                                </div>
                            </li>
                        `)
                        })

                        $('.employs-div').append(
                            `<div class = "employs-div-buttons">
                            <button class="replace-operator-button">Заменить оператора</button>
                            <button class="apply-button">Применить</button>
                        </div>`

                        )
                    }
                })

            }
        })
        let date = null
        let buffManagers = []
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                height: 350,
                plugins: [ 'dayGrid', 'interaction' ],

                dateClick: function(info) {
                    $('input[name="managers[]"]').prop('checked', false);
                    // console.log(info)
                    var selectedDate = document.querySelector('.selected-date');
                    if (selectedDate) {
                        selectedDate.classList.remove('selected-date');
                    }
                    info.dayEl.classList.add('selected-date');
                    date = info.dateStr
                    $.ajax({
                        type: 'Get',
                        url:'ajax/get_company_managers.php',
                        data: {
                            date: date,
                            plus:true
                        },
                        success: function(resp){
                            buffManagers = []
                            resp.managers.forEach(function (m) {
                                buffManagers.push(m.id)
                                $("#distribute_" + m.id).prop('checked', true);
                            })
                        }
                    })

                },
            });
            calendar.render();
        });


        let managers = null
        let boolean = false
        $(document).on('click', '.apply-button', function() {
            var checkedValues = $('input[name="managers[]"]:checked').map(function() {
                return $(this).val();
            }).get();
            managers = checkedValues
            let currentDate = new Date();
            let year = currentDate.getFullYear();
            let month = String(currentDate.getMonth() + 1).padStart(2, '0');
            let day = String(currentDate.getDate()).padStart(2, '0');

            let formattedDate = year + '-' + month + '-' + day;
            let deleted = buffManagers.filter(value => !managers.includes(value));
            let added = managers.filter(value => !buffManagers.includes(value));
            if(date == formattedDate && checkedValues.length && boolean){
                $(".modal-wrapper").css('display', 'flex')
                let txt = ""
                //let deleted = buffManagers.filter(value => !managers.includes(value));
                // let added = managers.filter(value => !buffManagers.includes(value));
                deleted.forEach(function (el) {
                    let value = $("label[for='distribute_" + el + "']").text()
                    txt += value + " "
                })
                txt += 'будут распределены на '
                added.forEach(function (el) {
                    let value = $("label[for='distribute_" + el + "']").text()
                    txt += value + " "
                })

                $('.employs-names').html(txt)
                $('.third-p').html("")
                $('body').css('overflow', 'hidden');
            }
            else if(date == formattedDate && !(deleted.length)){
                $(".modal-wrapper").css('display', 'flex')
                $('.third-p').html("будут распределены")
            }
            else if(date == formattedDate && checkedValues.length){
                $(".modal-wrapper").css('display', 'flex')
                let txt = ""
                //let deleted = buffManagers.filter(value => !managers.includes(value));
                deleted.forEach(function (el) {
                    let value = $("label[for='distribute_" + el + "']").text()
                    txt += value + ", "
                })
                $('.employs-names').html(txt)
                $('body').css('overflow', 'hidden');
            }
            else if(date && checkedValues.length){
                $.ajax({
                    type: 'POST',
                    url:'ajax/insert-schedule.php',
                    data: { date: date,
                            managers: checkedValues,
                            plus:true
                    },
                    success: function(resp){
                        Swal.fire({
                            timer: 5000,
                            title: 'Сотрудники назначены.',
                            type: 'success',
                        });
                    }
                })
            }

        });

        $('.employs-names-button-no').click(function () {
            $(".modal-wrapper").css('display', 'none')
            $('body').css('overflow', 'auto');
        })

        $('.employs-names-button-yes').click(function () {

            const oldManagers = []
            buffManagers.forEach(item => {
                oldManagers.push(item)
            })
            $(this).prop('disabled', true);
            $('.employs-names-button-no').prop('disabled', true);
            let deleted = oldManagers.filter(value => !managers.includes(value));
            let added = managers.filter(value => !oldManagers.includes(value));
            var _hash = location.hash.replace('#', '?');
            let txt = 'action=distribute&period=plus_one_two'
            managers.forEach(function (el) {
                txt += '&managers[]='+ el
            })

            deleted.forEach(item=> {
                txt += '&deleted[]='+ item
            })

            added.forEach(item=> {
                txt += '&added[]='+ item
            })
            txt += '&boolean='+boolean
            let newData = false

            let diffAdded = managers.filter(value => added.includes(value));
            if (diffAdded.length > 0){
                newData = true
                let diffAddedMan = managers.filter(value => !added.includes(value));
                diffAddedMan.forEach(item=> {
                    txt += '&diffAddedMan[]='+ item
                })
            }
            txt += '&newData='+ newData

            if(!$(this).hasClass('loading')){
                $(".loader-icon").css("display","block")
                $(".modal-wrapper").css('opacity','0.5')
                $.ajax({
                    type: 'POST',
                    url:'ajax/insert-schedule.php',
                    data: { date: date, managers: managers,plus:true},
                })

                    .then(function () {
                        $.ajax({
                            url: '/ccprolongations_plus' + _hash,
                            data: txt,
                            type: 'POST',
                            beforeSend: function () {
                                $(this).addClass('loading');
                            },
                            success: (resp) => {
                                $(this).prop('disabled', false);
                                $('.employs-names-button-no').prop('disabled', false);
                                $(".modal-wrapper").css('display', 'none')
                                $('body').css('overflow', 'auto');
                                $(".loader-icon").css("display", "none")
                                if (resp.success) {
                                    $('#modal_distribute').modal('hide');

                                    Swal.fire({
                                        timer: 5000,
                                        title: 'Договора распределены.',
                                        type: 'success',
                                    });
                                    location.reload();
                                } else {
                                    Swal.fire({
                                        text: resp.error,
                                        type: 'error',
                                    });

                                }
                                $(this).removeClass('loading');
                            }
                        })
                    })

            }
        })


        $(document).on('click', '.replace-operator-button', function() {
            boolean = true
        })
        $(document).on('change', 'input[name="managers[]"]', function () {
            if(boolean) {
                var checkedValues = $('input[name="managers[]"]:checked').map(function() {
                    return $(this).val();
                }).get();
                console.log(buffManagers)
                console.log(managers)
                let deleted = buffManagers.filter(value => !checkedValues.includes(value));
                let added = checkedValues.filter(value => !buffManagers.includes(value));
                console.log($('#distribute_' + deleted).parent())
                $(this).parent().css("background-color", "#fff")
                $('#distribute_' + deleted).parent().css("background-color", "red");


                $('#distribute_' + added).css('accent-color', 'green')
            }
        })

        $('.employs-edit').click(function () {
            let content = $('.employs-div');
            if (content.find('.vox-inputs').length > 0) {
                $('.employs-div').empty()
            } else {
                $('.employs-div').empty()
                $.ajax({
                    type: 'GET',
                    url: 'ajax/get_company_managers.php?bool=true&plus=true',
                    success: function (resp) {
                        $('.employs-div').append(`<div class="vox-div">
                <select multiple  class="form-control  js-vox-manager" id="js-vox-manager" name="manager_id" placeholder = "Менеджеры">
                </select>
                </div>`)
                        resp.managers.forEach(function (m) {
                            if (m.company) {
                                $('.js-vox-manager').append(`
                        <option value="` + m.id + `">` + m.name + `  pds:` + m.company + `  dnc:` + m.dnc_number + `</option>
                    `)
                            } else {
                                $('.js-vox-manager').append(`
                        <option value="` + m.id + `">` + m.name + `</option>
                    `)
                            }

                        })
                        $('.vox-div').append(`<div class = "vox-inputs">
                    <div>
                        <label for="">ID PDS</label>
                        <input class="pds" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                    </div>
                    <div>
                        <label for="">ID DNC</label>
                      <input class = "dnc" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                    </div>
            </div>
            <button class = "add-manager-to-vox">Применить </button>`)
                        $('.js-vox-manager').select2({
                            width: '100%',
                            placeholder: "Менеджеры",
                            allowClear: true,
                            maximumSelectionLength: 1,
                        });
                    }
                })
            }
        })
            $(document).on('click', '.add-manager-to-vox', function () {
                let manager_id = $('.js-vox-manager').val().join('')
                console.log(manager_id)
                let pds = $('.pds').val()
                let dnc = $('.dnc').val()
                if (manager_id && pds && dnc) {
                    $.ajax({
                        type: 'POST',
                        url: 'ajax/add_vox_manager.php',
                        data: {
                            manager_id: manager_id,
                            pds: pds,
                            dnc: dnc,
                            plus:true
                        },
                        success: function (resp) {
                            $('.modal-wrapper-vox>.div-modal').append(
                                `<p style="width: 50%;">
                                   Настройки сотрудника ` + resp.name + ` c ID PPS `+ pds +` и ID DNC `+dnc+` успешно изменены
                                </p>
                                <div class="employs-vox-buttons">
                                <button class="employs-vox-names-button-yes btn btn-success">Да</button>
                        </div>`
                            )
                            $('.js-vox-manager').val(null).trigger('change');
                            $('.pds').val("")
                            $('.dnc').val("")

                            $('.modal-wrapper-vox').css('display','flex')
                        }
                    })
                }
            })
        $(document).on('click','.employs-vox-names-button-yes', function () {
            $('.modal-wrapper-vox').css('display','none')
            location.reload()
        })

        $(document).on('click', '.js-voximplant-call', function () {
            let phone =  $(this).data('phone')
            setUnmark(phone)
        })

        $(document).on('click', (".call-btn.call"), function () {
            let phone = $('.numpad-view__input').val()
            setUnmark(phone)
        })

        function setUnmark(phone) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax/update_task_mark.php',
                    data: {
                        phone: phone,
                    },
                    success: function (resp) {
                        console.log(resp.id, 'task_id')
                        console.log($('td[data-id="' + resp.id + '"]'))
                        $('td[data-id="' + resp.id + '"]').css('border', 'none')
                    }
                })
        }
        $(document).on('click', '.js-open-sms-modal', function(e){
            e.preventDefault();

            let task = $(this).data('task');
            let zaim_number = $(this).data('zaim');
            $('#modal_send_sms [name=task_id]').val(task)
            $('#modal_send_sms [name=zaim_number]').val(zaim_number)
        });

        $(document).on('click', '.js-send-sms',function(){
            let user_id = $(".js-sms-form input[name=user_id]").val()
            let task_id = $(".js-sms-form input[name=task_id]").val()
            let zaim_number = $(".js-sms-form input[name=zaim_number]").val()
            sendSms(user_id,task_id,zaim_number)
        })

        $(document).on('click', '.js-send-viber',function(){
            let user_id = $(".js-sms-form input[name=user_id]").val()
            let task_id = $(".js-sms-form input[name=task_id]").val()
            let zaim_number = $(".js-sms-form input[name=zaim_number]").val()
            sendSms(user_id,task_id,zaim_number)
        })

        function sendSms(user_id,task_id,zaim_number) {
            $.ajax({
                type: 'POST',
                url: 'ajax/send_sms.php',
                data: {
                    user_id: user_id,
                    zaim_number: zaim_number,
                },
                success: function (resp) {
                    console.log(resp)
                    if(resp.success == "updated")
                    {
                        $('#main_' + task_id + ' input[name="sms_check[]"]').prop('disabled', true)
                        $('#main_' + task_id + " .js-open-sms-modal").prop('disabled', true)
                    }
                }
            })
        }

        $(document).on('click','.downloadCallList',function () {
            $('.preloader').show();
            let dataRange = $(".daterange").val()
            let manager = $('#js-filter-manager').val()
            $.ajax({
                type: 'GET',
                url:'ajax/download-call-list.php',
                data: { dataRange: dataRange, manager: manager, plus: true},
                success:function (resp){
                    $('.preloader').hide();
                    console.log(resp)
                    resp = JSON.parse(resp)
                    if(!resp.success){
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка валидации',
                            text: resp.message,
                            type: 'error',
                        });
                        return;
                    }
                    window.open(
                        resp.message
                    );
                }
            })
        })

        function markSelectAllCheckbox() {
            console.log("111");
            let numberOfChecked = $('.user_checkbox input:checkbox:checked').length;
            let totalCheckboxes = $('.user_checkbox input:checkbox:not(:disabled)').length;
            if  (numberOfChecked == 0) {
                $('.check_all_checkbox').removeClass('all_checkboxes');
                $('.check_all_checkbox').removeClass('some_checkboxes');
            } else if (totalCheckboxes != numberOfChecked) {
                $('.check_all_checkbox').removeClass('all_checkboxes');
                $('.check_all_checkbox').addClass('some_checkboxes');
            } else {
                $('.check_all_checkbox').removeClass('some_checkboxes');
                $('.check_all_checkbox').addClass('all_checkboxes');
            }
        }

        $(document).on('click','.user_checkbox input',function () {
            markSelectAllCheckbox();
        });

        $(document).on('click','.check_all_checkbox input',function () {
            if ($('.check_all_checkbox').hasClass('all_checkboxes')) {
                $('.user_checkbox input:checkbox:checked').each(function() {
                    $( this ).prop("checked", false)
                });
            } else {
                $('.user_checkbox input:checkbox:not(:disabled)').each(function() {
                    $( this ).prop("checked",true)
                });
            }
            markSelectAllCheckbox();
        })
    </script>

{/capture}

{capture name='page_styles'}
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.2.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.2.0/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.2.0/main.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.2.0/main.min.css" rel="stylesheet"/>

    <!-- Date picker plugins css -->
    <link href="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/css-chart/css-chart.css" />
    <style>
        .input-div{
            display: flex;
            justify-content: center;
            align-items: center;
            width: 15px;
            height: 15px;
        }
        .main-input-div{
            display: flex;
        }
        .employs-div-buttons {
            width: 100%;
            display: flex;
            gap: 7px;
        }

        .div-modal{
            width: 600px;
            height: 300px;
            position: absolute;
            justify-content: center;
            flex-direction: column;
            background: #868e96;
            z-index: 1000;
            align-items: center;
            top: 200px;
            color: white;
            font-size: 18px;
            border-radius: 30px;
            display: flex;
        }
        .modal-wrapper,.modal-wrapper-vox{
            width: 100%;
            height: 100%;
            display: none;
            justify-content: center;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 5;

        }
        .modal-background{
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            position: fixed;
            left: 0;
            top: 0;
        }
        #calendar{
            width: 50%;
            height: 500px;
        }
        .fc-day.fc-other-month {
            background-color: grey !important;
            border: 1px solid !important;
        }
        .fc-day-number{
            color: black !important;
        }
        #calendar-parent .fc-day{
            background: white;
        }
        .fc-day-header{
            background: #7460ee !important;
            color: white !important;
        }
        #calendar-parent .fc-day{
            background: white;
        }

        .schedule-calendar-div{
            display: none !important;
            margin-top: 50px;
            width: 80%;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            min-width: 300px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .replace-operator-button{
            width: 200px;
            height: 50px;
            border-radius: 10px;
            background: #7460ee;
            color: white;
            border: none;
        }

        .showDiv{
            display: flex !important;
        }
        .schedule-calendar-div>.left-side{
            width: 50%;
            min-width: 300px;
        }
        .left-side>.left-side-buttons{
            width: 100%;
        }
        .left-side-buttons>button{
            width: 200px;
            height: 50px;
            background: grey;
            border: 1px solid black;
            border-radius: 10px;
        }
        .employs-div{
            margin-top: 10px;
        }
        .employs-list{
            list-style: none;
        }

        #calendar-parent .selected-date{
            background: #7460ee !important;
            color: white !important;
        }

        .apply-button, .vox-div>button{
            width: 200px;
            height: 50px;
            border-radius: 10px;
            background: #28a745;
            color: white;
            border: none #28a745;

        }
        .jsgrid-table { margin-bottom:0}
        .jsgrid-grid-body{
            min-height: 180px;
        }
        .loader-icon {
            border: 5px solid #f3f3f3; /* Light grey */
            border-top: 5px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            display: none;
        }
        #form_distribute{
            position: relative;
        }

        label.labels-missing {
            padding: 0;
            line-height: unset;
            padding: 0px 6px;
            border-radius: 5px;
            border: 1px solid #797979;
            color: white;
            margin: auto;
            margin-right: 1rem;
        }

        label.labels-missing.color1:hover {
            border: 1px solid #4dcb5d;
            color: #55ce63;
        }

        .checkbox-mis.color1:checked ~ label.labels-missing.color1 {
            border: 1px solid #4dcb5d;
            color: #55ce63;
        }

        .checkbox-mis.color1:checked ~ .input_block input {
            border: 1px solid #4dcb5d;
        }


        label.labels-missing.color2:hover {
            border: 1px solid #009efb;
            color: #009efb;
        }

        .checkbox-mis.color2:checked ~ label.labels-missing.color2 {
            border: 1px solid #009efb;
            color: #009efb;
        }

        .checkbox-mis.color2:checked ~ .input_block input {
            border: 1px solid #009efb;
        }

        label.labels-missing.color3:hover {
            border: 1px solid #ffbc34;
            color: #ffbc34;
        }

        .checkbox-mis.color3:checked ~ label.labels-missing.color3 {
            border: 1px solid #ffbc34;
            color: #ffbc34;
        }

        .checkbox-mis.color3:checked ~ .input_block input {
            border: 1px solid #ffbc34;
        }

        .checkbox-mis:checked ~ .input_block input {
            opacity: 1;
            padding-right: 1rem;
        }

        .checkbox-mis:checked ~ label.labels-missing {
            cursor: pointer;
            margin-right: 0rem;
        }

        label.labels-missing:hover {
            cursor: pointer;
        }

        .form_zero_payment.loading {
            opacity: .5;
            pointer-events: none;
        }


        /* выбор компании */
        .check_box_block {
            display: flex;
            align-items: baseline;
            justify-content: flex-start;
            position: relative;
        }

        .input_block input {
            height: fit-content;
            padding: 0;
            padding-left: 6px;
        }

        .input_block {
            max-width: 60px;
            width: 0px;
            transition: width ease-out .5s;
        }

        .check_box_block input:checked ~ .input_block {
            width: 60px !important;
            display: block;
            margin-right: 1rem;
        }

        .input_block input {
            opacity: 0;
        }
        .vox-div{
            width: 60%;
            display: flex;
            justify-content: flex-start;
            gap: 15px;
            flex-direction: column;
        }
        .vox-div input{
            background: #272C33;
            border: none;
            border-radius: 5px;
            padding: 12px;
        }
        .vox-inputs{
            width: 100%;
            display: flex;
            gap: 15px;
        }
        .pds, .dnc{
            color: white;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
{/capture}


<style>
    @media screen and (min-width: 580px){
        body {
            padding-right: 0px !important;
        }
    }
</style>

<div class="modal-wrapper">
    <div class="modal-background"></div>
    <div class="div-modal">
        <div class="loader-icon"></div>
        <p>Заявки</p>
        <p class="employs-names"></p>
        <p class="third-p">будут распределены на оставшихся в смене</p>

        <div class="employs-names-buttons">
            <button class="employs-names-button-no btn btn-warning mr-3">Нет</button>
            <button class="employs-names-button-yes btn btn-success">Да</button>
        </div>
    </div>
</div>



<div class="modal-wrapper-vox">
    <div class="modal-background"></div>
    <div class="div-modal">

    </div>
</div>


<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-2 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-closed-caption"></i>
                    <span> Продление 1-2 день</span>
                    <br />
                    {if $request_date_from}
                        <small class="text-white">С {$request_date_from}</small>
                    {/if}
                    {if $request_date_to}
                        <small class="text-white"> по {$request_date_to}</small>
                    {/if}
                    {if $filter_manager}
                    <small class="text-white">{$managers[$filter_manager]->name|escape}</small>
                    {/if}
                    </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Продление 1-2 день</li>
                </ol>
            </div>
            <div class="col-md-1 pt-4">
                {if 0 && $manager->role == 'contact_center_new'}{*убрал кнопку*}
                <button type="button" class="btn btn-primary " id="distribute_me">Распределить мне</button>
                {elseif $manager->id == 77 || $manager->id == 89 || $manager->id == 144 || in_array($manager->role, ['developer', 'admin', 'opr', 'ts_operator'])}
{*                <button type="button" class="btn btn-primary js-distribute-open">Распределить</button>*}
                    <button type="button" class="btn btn-primary js-schedule-open">График </button>
                {/if}
            </div>
            <div class="col-md-3">
                <div class="p-2">
                    <form autocomplete="off" action="{url}">

                        <input type="hidden" name="period" value="plus_new" />

                        <div class="row">
                            <div class="col-md-9">
                                <div class="input-group mb-3">
                                    <input type="text" name="date_range" class="form-control daterange" value="{$request_date_from} - {$request_date_to}">
                                    <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                    </div>
                                </div>
                                {if $manager->role != 'contact_center_new'}
                                    <select multiple class="form-control js-filter-manager" id="js-filter-manager" name="manager_id[]" placeholder = "Менеджеры">
                                        {*<option value="" {if !$filter_manager}selected=""{/if}>Все менеджеры</option>*}
                                        {foreach $managers as $m}
                                            {if $m->role == 'contact_center_new' || $m->role == 'contact_center_new_robo'}
                                                <option value="{$m->id}" {if in_array($m->id, $filter_manager)}selected{/if}>{$m->name|escape}</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                {/if}
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-success runFilter">
                                    <span>Выбрать</span>
                                </button>
                                <button type="button" class="btn btn-success downloadCallList mt-3" style="width: 170px">
                                    <span>Выгрузить колл лист</span>
                                </button>
                            </div>
                        </div>

                    </form>


                </div>
            </div>
            <div class="col-md-5 offset-md-1 col-4 align-self-center">

                {if $statistic->total_amount > 0}
                <div class="row bg-grey">
                    <div class="col-md-6 text-center">
                        <h3 class="pt-1">
                            <i class="fas fa-id-card-alt"></i>
                            <span>Портфель: {$statistic->total_amount|round} P</span>
                        </h3>
                    </div>
                    <div class="col-md-6 text-center">
                        <h3 class="pt-1">
                            <i class=" far fa-money-bill-alt"></i>
                            <span>Собрано: {$statistic->total_paid|round} P</span>
                            <span class="label label-info">
                                <h4 class="mb-0">
                                    {if $statistic->total_amount > 0}
                                        {($statistic->total_paid / $statistic->total_amount * 100)|round}%
                                    {else}
                                        0%
                                    {/if}
                                </h4>
                            </span>
                        </h3>
                    </div>
                    <div class="col-md-12">
                        <hr class="m-0" />
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0  bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div data-label="{($statistic->inwork/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{($statistic->inwork/$statistic->total*10)|round*10}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Обработано</h5>
                                        <h6 class="text-white">
                                            {$statistic->inwork} / {$statistic->total}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0 bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    {if $manager->role == 'contact_center_new'}
                                        {$real_percents = ($statistic->prolongation/$settings->cc_pr_prolongation_plan*100)|round}
                                        {$round_percents = ($statistic->prolongation/$settings->cc_pr_prolongation_plan*10)|round*10}
                                        {$cc_pr_prolongation_plan = $settings->cc_pr_prolongation_plan}
                                    {else}
                                        {$real_percents = ($statistic->prolongation/$statistic->total*100)|round}
                                        {$round_percents = ($statistic->prolongation/$statistic->total*10)|round*10}
                                        {$cc_pr_prolongation_plan = $statistic->total}
                                    {/if}
                                    <div class="col-4">
                                        <div data-label="{$real_percents}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{$round_percents}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Пролонгации</h5>
                                        <h6 class="text-white">
                                            {$statistic->prolongation} / {$cc_pr_prolongation_plan}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0 bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    {if $manager->role == 'contact_center_new'}
                                        {$real_percents = ($statistic->closed/$settings->cc_pr_close_plan*100)|round}
                                        {if ($statistic->closed/$settings->cc_pr_close_plan*10)|round*10 > 100}
                                            {$round_percents = 100}
                                        {else}
                                            {$round_percents = ($statistic->closed/$settings->cc_pr_close_plan*10)|round*10}
                                        {/if}
                                        {$cc_pr_close_plan = $settings->cc_pr_close_plan}
                                    {else}
                                        {$real_percents = ($statistic->closed/$statistic->total*100)|round}
                                        {$round_percents = ($statistic->closed/$statistic->total*10)|round*10}
                                        {$cc_pr_close_plan = $statistic->total}
                                    {/if}
                                    <div class="col-4">
                                        <div data-label="{$real_percents}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{$round_percents}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Закрытия</h5>
                                        <h6 class="text-white">
                                            {$statistic->closed} / {$cc_pr_close_plan}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

{*                    <div class="col-md-4">*}
{*                        <div class="card m-0  bg-grey">*}
{*                            <div class="card-body p-0 pt-2">*}
{*                                <div class="row">*}
{*                                    <div class="col-4">*}
{*                                        <div data-label="{($statistic->perezvon/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-primary css-bar-{($statistic->perezvon/$statistic->total*10)|round*10}"></div>*}
{*                                    </div>*}
{*                                    <div class="col-8">*}
{*                                        <h5 class="card-title mb-1">Перезвон</h5>*}
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->perezvon} / {$statistic->total}*}
{*                                        </h6>*}
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->perezvonPaid} / {$statistic->totalPaid} Р*}
{*                                        </h6>*}
{*                                    </div>*}
{*                                </div>*}
{*                            </div>*}
{*                        </div>*}
{*                    </div>*}

                    <div class="col-md-4">
                        <div class="card m-0  bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div data-label="{($statistic->nedozvon/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-warning css-bar-{($statistic->nedozvon/$statistic->total*10)|round*10}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Недозвон</h5>
                                        <h6 class="text-white">
                                            {$statistic->nedozvon} / {$statistic->total}
                                        </h6>
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->nedozvonPaid} / {$statistic->totalPaid} Р*}
{*                                        </h6>*}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card m-0  bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div data-label="{($statistic->perspective/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{($statistic->perspective/$statistic->total*10)|round*10}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1" style="width: 105px">Перспектива</h5>
                                        <h6 class="text-white">
                                            {$statistic->perspective} / {$statistic->total}
                                        </h6>
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->perspectivePaid} / {$statistic->totalPaid} Р*}
{*                                        </h6>*}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card m-0  bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div data-label="{($statistic->decline/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->decline/$statistic->total*10)|round*10}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Отказ</h5>
                                        <h6 class="text-white">
                                            {$statistic->decline} / {$statistic->total}
                                        </h6>
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
{*                                        </h6>*}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
{*                    <div class="col-md-3">*}
{*                        <div class="card m-0  bg-grey">*}
{*                            <div class="card-body p-0 pt-2">*}
{*                                <div class="row">*}
{*                                    <div class="col-4">*}
{*                                        <div data-label="{($statistic->alreadyPaid/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->alreadyPaid/$statistic->total*10)|round*10}"></div>*}
{*                                    </div>*}
{*                                    <div class="col-8">*}
{*                                        <h5 class="card-title mb-1">Обещал оплатить</h5>*}
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->alreadyPaid} / {$statistic->total}*}
{*                                        </h6>*}
{*                                                                                <h6 class="text-white">*}
{*                                                                                    {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
{*                                                                                </h6>*}
{*                                    </div>*}
{*                                </div>*}
{*                            </div>*}
{*                        </div>*}
{*                    </div>*}
                    <div class="col-md-4">
                        <div class="card m-0  bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div data-label="{($statistic->receivedInformation/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->receivedInformation/$statistic->total*10)|round*10}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Получил информацию по долгу</h5>
                                        <h6 class="text-white">
                                            {$statistic->receivedInformation} / {$statistic->total}
                                        </h6>
                                        {*                                        <h6 class="text-white">*}
                                        {*                                            {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
                                        {*                                        </h6>*}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0  bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div data-label="{($statistic->identityConfirmed/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->identityConfirmed/$statistic->total*10)|round*10}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Подтвердил Личность</h5>
                                        <h6 class="text-white">
                                            {$statistic->identityConfirmed} / {$statistic->total}
                                        </h6>
                                        {*                                        <h6 class="text-white">*}
                                        {*                                            {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
                                        {*                                        </h6>*}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0  bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div data-label="{($statistic->diedPrisonBankrupt/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->diedPrisonBankrupt/$statistic->total*10)|round*10}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Умер, тюрьма, банкрот</h5>
                                        <h6 class="text-white">
                                            {$statistic->diedPrisonBankrupt} / {$statistic->total}
                                        </h6>
                                        {*                                        <h6 class="text-white">*}
                                        {*                                            {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
                                        {*                                        </h6>*}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
{*                    <div class="col-md-3">*}
{*                        <div class="card m-0  bg-grey">*}
{*                            <div class="card-body p-0 pt-2">*}
{*                                <div class="row">*}
{*                                    <div class="col-4">*}
{*                                        <div data-label="{($statistic->stopListNumber/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->stopListNumber/$statistic->total*10)|round*10}"></div>*}
{*                                    </div>*}
{*                                    <div class="col-8">*}
{*                                        <h5 class="card-title mb-1">Номер в стоп листе</h5>*}
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->stopListNumber} / {$statistic->total}*}
{*                                        </h6>*}
{*                                                                                <h6 class="text-white">*}
{*                                                                                    {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
{*                                                                                </h6>*}
{*                                    </div>*}
{*                                </div>*}
{*                            </div>*}
{*                        </div>*}
{*                    </div>*}
{*                    <div class="col-md-3">*}
{*                        <div class="card m-0  bg-grey">*}
{*                            <div class="card-body p-0 pt-2">*}
{*                                <div class="row">*}
{*                                    <div class="col-4">*}
{*                                        <div data-label="{($statistic->negative/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->negative/$statistic->total*10)|round*10}"></div>*}
{*                                    </div>*}
{*                                    <div class="col-8">*}
{*                                        <h5 class="card-title mb-1">Негатив от абонента</h5>*}
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->negative} / {$statistic->total}*}
{*                                        </h6>*}
{*                                                                                <h6 class="text-white">*}
{*                                                                                    {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
{*                                                                                </h6>*}
{*                                    </div>*}
{*                                </div>*}
{*                            </div>*}
{*                        </div>*}
{*                    </div>*}
{*                    <div class="col-md-3">*}
{*                        <div class="card m-0  bg-grey">*}
{*                            <div class="card-body p-0 pt-2">*}
{*                                <div class="row">*}
{*                                    <div class="col-4">*}
{*                                        <div data-label="{($statistic->thirdPerson/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->thirdPerson/$statistic->total*10)|round*10}"></div>*}
{*                                    </div>*}
{*                                    <div class="col-8">*}
{*                                        <h5 class="card-title mb-1">Контакт с третьим лицом</h5>*}
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->thirdPerson} / {$statistic->total}*}
{*                                        </h6>*}
{*                                                                                <h6 class="text-white">*}
{*                                                                                    {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
{*                                                                                </h6>*}
{*                                    </div>*}
{*                                </div>*}
{*                            </div>*}
{*                        </div>*}
{*                    </div>*}
{*                    <div class="col-md-3">*}
{*                        <div class="card m-0  bg-grey">*}
{*                            <div class="card-body p-0 pt-2">*}
{*                                <div class="row">*}
{*                                    <div class="col-4">*}
{*                                        <div data-label="{($statistic->refinancing/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->refinancing/$statistic->total*10)|round*10}"></div>*}
{*                                    </div>*}
{*                                    <div class="col-8">*}
{*                                        <h5 class="card-title mb-1">Заявка на рефинансирование</h5>*}
{*                                        <h6 class="text-white">*}
{*                                            {$statistic->refinancing} / {$statistic->total}*}
{*                                        </h6>*}
{*                                                                                <h6 class="text-white">*}
{*                                                                                    {$statistic->declinePaid} / {$statistic->totalPaid} Р*}
{*                                                                                </h6>*}
{*                                    </div>*}
{*                                </div>*}
{*                            </div>*}
{*                        </div>*}
{*                    </div>*}
                </div>
                {/if}
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="schedule-calendar-div" id="calendar-parent">
            <div class="left-side">
                <div class="left-side-buttons">
                    <button class="employs-button">Сотрудники</button>
                    <button> Дни просрочки</button>
                    <button class="employs-edit">Редактировать</button>
                </div>
                <div class="employs-div">

                </div>
            </div>
            <div id='calendar' style="width: 600px;"></div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix js-filter-status">
                            <h4 class="card-title float-left">
                                Продление 1-2 день
                            </h4>
                            <div class="float-right">

                                <button type="button" class="btn btn-sm btn-primary" id="send_message_to_users" onclick="sendSmsToUsers();" title="Отправить смс">
                                    Массовая отправка СМС <i class=" far fa-share-square"></i>
                                </button>
                                <input type="hidden" value="{$filter_period}" id="period" />
                            </div>
                        </div>

                        {if !$tasks|count}
                        <div class="alert alert-danger">
                            <h3 class="text-danger">Нет распределенных договоров</h3>
                        </div>
                        {/if}

                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 45px;" class="jsgrid-header-cell jsgrid-header-sortable">Send SMS</th>
                                        <th style="width: 90px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'count_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'count_asc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'count_asc'}<a href="{url page=null sort='count_desc'}">Количество взаимодействий</a>
                                            {else}<a href="{url page=null sort='count_asc'}">Количество взаимодействий</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'login_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'login_asc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'login_asc'}<a href="{url page=null sort='login_desc'}">Вход в ЛК</a>
                                            {else}<a href="{url page=null sort='login_asc'}">Вход в ЛК</a>{/if}
                                        </th>
                                        <th style="width: 40px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'timezone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'timezone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'timezone_asc'}<a href="{url page=null sort='timezone_desc'}">Время</a>
                                            {else}<a href="{url page=null sort='timezone_asc'}">Время</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'number_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'number_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'number_asc'}<a href="{url page=null sort='number_desc'}">Займ</a>
                                            {else}<a href="{url page=null sort='number_asc'}">Займ</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата выдачи</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата выдачи</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'summ_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'summ_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'summ_asc'}<a href="{url page=null sort='summ_desc'}">Сумма</a>
                                            {else}<a href="{url page=null sort='summ_asc'}">Сумма</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'payment_date_asc'}<a href="{url page=null sort='payment_date_desc'}">Дата возврата</a>
                                            {else}<a href="{url page=null sort='payment_date_asc'}">Дата возврата</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'payment_asc'}<a href="{url page=null sort='payment_desc'}">Сумма возврата</a>
                                            {else}<a href="{url page=null sort='payment_asc'}">Сумма возврата</a>{/if}
                                        </th>
                                        <th style="width: 72px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'prolongation_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'prolongation_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'prolongation_asc'}<a href="{url page=null sort='prolongation_desc'}">Пролонгация</a>
                                            {else}<a href="{url page=null sort='prolongation_asc'}">Пролонгация</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Ответственный</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Ответственный</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width:75px" class="text-right jsgrid-header-cell" ></th>
                                    </tr>

                                    <tr class="jsgrid-filter-row" id="search_form">
                                        <td style="width: 45px;" class="jsgrid-cell jsgrid-align-center">
                                            <label for="sms_check_all" class="check_all_checkbox">
                                                <input  type="checkbox" name="sms_check_all" id="sms_check_all" value="0" />
                                                <div></div>
                                            </label>
                                        </td>
                                        <td style="width: 90px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 50px;" class="jsgrid-cell jsgrid-align-right">
                                                                                   </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="login" value="{$search['login']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 40px;" class="jsgrid-cell jsgrid-align-right">

                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="number" value="{$search['number']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell"></td>
                                        <td style="width: 60px;" class="jsgrid-cell">
                                            <select name="manager" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $managers as $m}
                                                    {if $m->role == 'contact_center_new' || $m->role == 'contact_center_new_robo'}
                                                <option value="{$m->id}" {if $m->id == $search['manager']}selected="true"{/if}>{$m->name|escape}</option>
                                                {/if}
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 50px;" class="jsgrid-cell">
                                            <select name="status" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $pr_statuses as $ts_id => $ts}
                                                <option value="{$ts_id}" {if $ts_id === $search['status']}selected="true"{/if}>{$ts|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 75px;" class="jsgrid-cell">
                                        </td>
                                    </tr>

                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover ">
                                    <tbody>
                                    {foreach $tasks as $task}
                                        <tr class="jsgrid-row" id="main_{$task->id}">
                                            <td style="width: 45px;" class="jsgrid-cell jsgrid-align-center">
                                                <label for="sms_check_{$task->user->id}" class="user_checkbox">
                                                    <input  {if $task->sms_send}disabled{/if} name="sms_check[]" type="checkbox" id="sms_check_{$task->user->id}" value="{$task->user->id}" />
                                                    <div></div>
                                                </label>
                                            </td>
                                            <td style="width: 90px;{if $task->marked } border: 2px solid red;{elseif $task->close == 0 && $task->prolongation == 0 && $task->paid > 0}border: 2px solid green;{/if }" class="jsgrid-cell jsgrid-align-right" data-id="{$task->user->id}">
                                                <div class="button-toggle-wrapper">
                                                    <button class="js-open-order button-toggle" data-id="{$task->id}" data-uid="{$task->user->UID}" data-site-id="{$task->user->site_id}" data-number="{$task->balance->zaim_number}" type="button" title="Подробнее"></button>
                                                </div>
                                                <a href="client/{$task->user->id}" target="_blank">
                                                    {$task->user->lastname|escape} {$task->user->firstname|escape} {$task->user->patronymic|escape}
                                                </a>
                                                {$task->id}
                                                {if $task->looker_link}
                                                    <a href="{$task->looker_link}" class="float-left btn-info btn waves-effect waves-light btn-xs" target="_blank">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                {/if}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <strong>{$task->user->phone_mobile|escape}</strong><br>
                                                <button class="btn waves-effect waves-light btn-xs btn-info  js-mango-call" data-phone="{$task->user->phone_mobile}" data-user="{$task->user->id}" title="Выполнить звонок">
                                                    <i class="fas fa-phone-square"></i>
                                                </button>
                                                {if !$task->vox_call}
                                                <button
                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                        data-phone="{$task->user->phone_mobile|escape}">
                                                    <i class="fas fa-phone-square"></i>
                                                </button>
                                                {/if}
                                                <br><br>
                                                {if !empty($task->user->work_phone)}
                                                    <span style="color:#009efb;">Рабочий</span>
                                                <strong>{$task->user->work_phone|escape}</strong>
                                                <button class="btn waves-effect waves-light btn-xs btn-info  js-mango-call" data-phone="{$task->user->work_phone}" data-user="{$task->user->id}" title="Выполнить звонок">
                                                    <i class="fas fa-phone-square"></i>
                                                </button>
                                                    {if !$task->vox_call}
                                                <button
                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                        data-phone="{$task->user->work_phone|escape}">
                                                    <i class="fas fa-phone-square"></i>
                                                </button>
                                                        {/if}
                                                {/if}
                                            </td>
                                            <td style="width: 50px;" class="jsgrid-cell">
                                               <span class="label label-primary">
                                                   <h6 class="m-0">{$task->status_count|escape}</h6>
                                               </span>
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <strong>{$task->user->last_lk_visit_time}</strong>
                                            </td>
                                            <td style="width: 40px;" class="jsgrid-cell">
                                                <div>{if $task->timezone >= 0} + {/if}{$task->timezone}:00 </div>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {if $task->order}
                                                <a href="order/{$task->order->order_id}">{$task->zaim_number}</a>
                                                {else}
                                                <div>{$task->balance->zaim_number}</div>
                                                {/if}

                                                {if $task->close}
                                                    <span class="label label-primary">Закрыт</span>
                                                {/if}
                                                {if $task->prolongation}
                                                    <span class="label label-success">Продлен</span>
                                                {/if}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$task->balance->zaim_date|date}
                                                <br />
                                                <button class="js-get-movements btn btn-link btn-xs js-no-peni" data-number="{$task->balance->zaim_number}">Операции</button>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell zaim-sum">
                                                {$task->balance->zaim_summ}
                                                {if $task->balance->percent}<span class="label label-danger">{$task->balance->percent}%</span>
                                                {else}<span class="label label-success">{$task->balance->percent}%</span>{/if}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell zaim-payment-date">
                                                {$task->balance->payment_date|date}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell zaim-ostatok-odd">
                                                <a class="" href="#" data-toggle="collapse" data-target="#ostatok_{$task->id}">
                                                    {if $task->balance->loan_type == 'IL' && $task->balance->overdue_debt_od_IL+ $task->balance->next_payment_od > 0}
                                                        {$task->balance->overdue_debt_od_IL + $task->balance->overdue_debt_percent_IL + $task->balance->next_payment_od+$task->balance->next_payment_percent}
                                                    {elseif $task->balance->ostatok_od > 0}
                                                        {$task->balance->ostatok_od + $task->balance->ostatok_percents + $task->balance->ostatok_peni}
                                                    {/if}
                                                </a>
                                                <div id="ostatok_{$task->id}" class="collapse">
                                                    <div>Основной долг:
                                                        <strong>
                                                            {if $task->balance->loan_type == 'IL'}
                                                                {$task->balance->overdue_debt_od_IL+ $task->balance->next_payment_percent}
                                                            {else}
                                                                {$task->balance->ostatok_od}
                                                            {/if}
                                                        </strong>
                                                    </div>
                                                    <div>Проценты:
                                                        <strong>
                                                            {if $task->balance->loan_type == 'IL'}
                                                                {$task->balance->overdue_debt_percent_IL + $task->balance->next_payment_od}
                                                            {else}
                                                                {$task->balance->ostatok_percents}
                                                            {/if}
                                                        </strong>
                                                    </div>
                                                    <div>Пени: <strong>{$task->balance->ostatok_peni}</strong></div>
                                                </div>
                                            </td>
                                            <td style="width: 72px;" class="jsgrid-cell zaim-prolongation">
                                                {if $task->balance->prolongation_count == 5 || $task->balance->loan_type == 'IL'}
                                                    <span data-toggle="collapse">Пролонгация недоступна</span>
                                                {elseif $task->balance->prolongation_amount > 0}
                                                <a class="" href="#" data-toggle="collapse" data-target="#prolongation_{$task->id}">{$task->balance->prolongation_amount + $tv_medical_price}</a>
                                                {/if}
                                                {if $task->balance->last_prolongation == 2}
                                                <span class="label label-danger float-right" title="Количество пролонгаций займа">
                                                {elseif $task->balance->last_prolongation == 1}
                                                <span class="label label-warning float-right" title="Количество пролонгаций займа">
                                                {else}
                                                <span class="label label-primary float-right" title="Количество пролонгаций займа">
                                                {/if}
                                                    <h6 class="m-0">{$task->balance->prolongation_count}</h6>
                                                </span>
                                                <div id="prolongation_{$task->id}" class="collapse">
                                                    {if $task->balance->prolongation_summ_percents > 0}
                                                    <div class="prolongation_percent">Проценты: <strong>{1 * $task->balance->prolongation_summ_percents}</strong></div>
                                                    {/if}
                                                    {if $task->balance->prolongation_summ_insurance > 0}
                                                    <div class="prolongation_insurer">Страховка: <strong>{1 * $task->balance->prolongation_summ_insurance}</strong></div>
                                                    {/if}
                                                    {if $task->balance->prolongation_summ_cost > 0}
                                                    <div class="prolongation_prolongation">Пролонгация: <strong>{1 * $task->balance->prolongation_summ_cost}</strong></div>
                                                    {/if}
                                                    {if $task->balance->prolongation_summ_sms > 0}
                                                    <div class="prolongation_sms">СМС-информ: <strong>{1 * $task->balance->prolongation_summ_sms}</strong></div>
                                                    {/if}
                                                    <div>Телемедицина (Лайт): <strong>{1 * $tv_medical_price}</strong></div>
                                                </div>
                                                {if $is_developer}
                                                <br />
                                                <small title="Дата обновления">{$task->balance->last_update|date}</small>
                                                {/if}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$managers[$task->manager_id]->name|escape}
                                            </td>
                                            <td style="width: 50px;" class="jsgrid-cell text-right">
                                                <div class="btn-group js-status-{$task->id}">
                                                    {if $task->status == 0}<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Новая</button>{/if}
                                                    {if $task->status == 1}<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Перезвон</button>{/if}
                                                    {if $task->status == 2}<button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Недозвон</button>{/if}
                                                    {if $task->status == 3}<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Перспектива</button>{/if}
                                                    {if $task->status == 4}<button type="button" class="btn btn-danger btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Отказ</button>{/if}
                                                    {if $task->status == 5}<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Уже оплатил</button>{/if}
                                                    {if $task->status == 6}<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Получил <br>информацию <br>по долгу</button>{/if}
                                                    {if $task->status == 7}<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Подтвердил <br>Личность</button>{/if}
                                                    {if $task->status == 8}<button type="button" class="btn btn-danger btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Умер, тюрьма,<br> банкрот</button>{/if}
                                                    {if $task->status == 9}<button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Номер в <br>стоп листе </button>{/if}
                                                    {if $task->status == 10}<button type="button" class="btn btn-danger btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Негатив от<br> абонента</button>{/if}
                                                    {if $task->status == 11}<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Контакт с <br>третьим лицом</button>{/if}
                                                    {if $task->status == 12}<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Заявка на <br>рефинансирование</button>{/if}
                                                    <div class="dropdown-menu" x-placement="bottom-start">
                                                        {if $task->status != 1}<a class="dropdown-item text-primary js-toggle-status-recall" data-status="1" data-task="{$task->id}" href="javascript:void(0)">Перезвон</a>{/if}
                                                        {if $task->status != 2}<a class="dropdown-item text-warning js-toggle-status" data-status="2" data-task="{$task->id}" href="javascript:void(0)">Недозвон</a>{/if}
                                                        {if $task->status != 3}<a class="dropdown-item text-success js-toggle-status-perspective" data-status="3" data-task="{$task->id}" href="javascript:void(0)">Перспектива</a>{/if}
                                                        {if $task->status != 4}<a class="dropdown-item text-danger js-toggle-status" data-status="4" data-task="{$task->id}" href="javascript:void(0)">Отказ</a>{/if}
                                                        {if $task->status != 5}<a class="dropdown-item text-info js-toggle-status" data-status="5" data-task="{$task->id}" href="javascript:void(0)">Уже оплатил</a>{/if}
                                                        {if $task->status != 6}<a class="dropdown-item text-primary js-toggle-status" data-status="6" data-task="{$task->id}" href="javascript:void(0)">Получил информацию по долгу</a>{/if}
                                                        {if $task->status != 7}<a class="dropdown-item text-success js-toggle-status" data-status="7" data-task="{$task->id}" href="javascript:void(0)">Подтвердил Личность</a>{/if}
                                                        {if $task->status != 8}<a class="dropdown-item text-danger js-toggle-status" data-status="8" data-task="{$task->id}" href="javascript:void(0)">Умер, тюрьма, банкрот</a>{/if}
                                                        {if $task->status != 9}<a class="dropdown-item text-warning js-toggle-status" data-status="9" data-task="{$task->id}" href="javascript:void(0)">Номер в стоп листе</a>{/if}
                                                        {if $task->status != 10}<a class="dropdown-item text-danger js-toggle-status" data-status="10" data-task="{$task->id}" href="javascript:void(0)">Негатив от абонента</a>{/if}
                                                        {if $task->status != 11}<a class="dropdown-item text-success js-toggle-status" data-status="11" data-task="{$task->id}" href="javascript:void(0)">Контакт с третьим лицом</a>{/if}
                                                        {if $task->status != 12}<a class="dropdown-item text-info js-toggle-status" data-status="12" data-task="{$task->id}" href="javascript:void(0)">Заявка на рефинансирование</a>{/if}
                                                    </div>
                                                </div>
                                                {if $task->status == 1 && $task->recall_date}
                                                <small>{$task->recall_date|date} {$task->recall_date|time}</small>
                                                {/if}
                                                {if $task->status == 3 && $task->perspective_date}
                                                <small>{$task->perspective_date|date} {$task->perspective_date|time}</small>
                                                {/if}
                                            </td>
                                            <td style="width:75px;" class="jsgrid-cell text-right">
                                                <button type="button" class="btn btn-sm btn-warning update-user-balance" title="Обновить баланс" data-task="{$task->id}" data-user="{$task->user->id}">
                                                    <i class="mdi mdi-refresh"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success js-open-comment-form" title="Добавить комментарий" data-order="{$task->order->order_id}" data-user="{$task->user->id}" data-task="{$task->id}" data-uid="{$task->user->UID}">
                                                    <i class="mdi mdi-comment-text"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary js-open-sms-modal" title="Отправить смс" data-user="{$task->user->id}" data-task = "{$task->id}" data-zaim = "{$task->balance->zaim_number}"{if $task->sms_send}disabled{/if}>
                                                    <i class=" far fa-share-square"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="order-details" id="changelog_{$task->id}" style="display:none">
                                            <td colspan="11">
                                                <div class="row">

                                                    <div class="col-md-6">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h4 class="card-title">
                                                                    <span>Комментарии</span>
                                                                    <a href="javascript:void(0);" class="ml-3 js-open-comment-form btn btn-success btn-sm btn-rounded float-right" data-order="{$task->order->order_id}" data-user="{$task->user->id}" data-task="{$task->balance->id}" data-uid="{$task->user->UID}">
                                                                        <i class="mdi mdi-comment-text"></i> Добавить
                                                                    </a>
                                                                </h4>
                                                            </div>
                                                            <div class="js-comments comment-widgets cctasks-comments">

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="card">
                                                            <form class="js-calc-form">

                                                                <input type="hidden" class="js-calc-zaim-summ" value="{$task->balance->zaim_summ}" />
                                                                <input type="hidden" class="js-calc-percent" value="{$task->balance->percent}" />
                                                                <input type="hidden" class="js-calc-ostatok-od" value="{$task->balance->ostatok_od}" />
                                                                <input type="hidden" class="js-calc-ostatok-percents" value="{$task->balance->ostatok_percents}" />
                                                                <input type="hidden" class="js-calc-ostatok-peni" value="{$task->balance->ostatok_peni}" />
                                                                <input type="hidden" class="js-calc-payment-date" value="{$task->balance->payment_date}" />
                                                                <input type="hidden" class="js-calc-allready-added" value="{$task->balance->allready_added}" />

                                                                <input type="hidden" class="js-calc-prolongation-summ-insurance" value="{$task->balance->prolongation_summ_insurance}" />
                                                                <input type="hidden" class="js-calc-prolongation-summ-sms" value="{$task->balance->prolongation_summ_sms}" />
                                                                <input type="hidden" class="js-calc-prolongation-summ-cost" value="{$task->balance->prolongation_summ_cost}" />


                                                                <div class="card-body">
                                                                    <h4 class="card-title">
                                                                        <span>Калькулятор</span>
                                                                    </h4>
                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <div class="input-group mb-3">
                                                                                <input type="text" class="form-control singledate js-calc-input" value="" />
                                                                                <div class="input-group-append">
                                                                                    <span class="input-group-text">
                                                                                        <span class="ti-calendar"></span>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <button type="submit" class="btn btn-primary js-calc-run">Рассчитать</button>
                                                                        </div>
                                                                        <div class="js-calc-result col-12">

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>

                            {if $total_pages_num>1}

                            {* Количество выводимых ссылок на страницы *}
                        	{$visible_pages = 11}
                        	{* По умолчанию начинаем вывод со страницы 1 *}
                        	{$page_from = 1}

                        	{* Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой *}
                        	{if $current_page_num > floor($visible_pages/2)}
                        		{$page_from = max(1, $current_page_num-floor($visible_pages/2)-1)}
                        	{/if}

                        	{* Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно" *}
                        	{if $current_page_num > $total_pages_num-ceil($visible_pages/2)}
                        		{$page_from = max(1, $total_pages_num-$visible_pages-1)}
                        	{/if}

                        	{* До какой страницы выводить - выводим всё окно, но не более ощего количества страниц *}
                        	{$page_to = min($page_from+$visible_pages, $total_pages_num-1)}

                            <div class="jsgrid-pager-container" style="">
                                <div class="jsgrid-pager">
                                    Страницы:

                                    {if $current_page_num == 2}
                                    <span class="jsgrid-pager-nav-button "><a href="{url page=null}">Пред.</a></span>
                                    {elseif $current_page_num > 2}
                                    <span class="jsgrid-pager-nav-button "><a href="{url page=$current_page_num-1}">Пред.</a></span>
                                    {/if}

                                    <span class="jsgrid-pager-page {if $current_page_num==1}jsgrid-pager-current-page{/if}">
                                        {if $current_page_num==1}1{else}<a href="{url page=null}">1</a>{/if}
                                    </span>
                                   	{section name=pages loop=$page_to start=$page_from}
                                		{* Номер текущей выводимой страницы *}
                                		{$p = $smarty.section.pages.index+1}
                                		{* Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации *}
                                		{if ($p == $page_from + 1 && $p != 2) || ($p == $page_to && $p != $total_pages_num-1)}
                                		<span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                                            <a href="{url page=$p}">...</a>
                                        </span>
                                		{else}
                                		<span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                                            {if $p==$current_page_num}{$p}{else}<a href="{url page=$p}">{$p}</a>{/if}
                                        </span>
                                		{/if}
                                	{/section}
                                    <span class="jsgrid-pager-page {if $current_page_num==$total_pages_num}jsgrid-pager-current-page{/if}">
                                        {if $current_page_num==$total_pages_num}{$total_pages_num}{else}<a href="{url page=$total_pages_num}">{$total_pages_num}</a>{/if}
                                    </span>

                                    {if $current_page_num<$total_pages_num}
                                    <span class="jsgrid-pager-nav-button"><a href="{url page=$current_page_num+1}">След.</a></span>
                                    {/if}
                                    &nbsp;&nbsp; {$current_page_num} из {$total_pages_num}
                                </div>
                            </div>
                            {/if}

                            <div class="jsgrid-load-shader" style="display: none; position: absolute; inset: 0px; z-index: 10;">
                            </div>
                            <div class="jsgrid-load-panel" style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                                Идет загрузка...
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Column -->
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>
<div class="modal fade" id="loan_operations" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loan_operations_title">Операции по договору</h5>
        <button type="button" class="btn-close btn" data-bs-dismiss="modal" aria-label="Close">
            <i class="fas fa-times text-white"></i>
        </button>
      </div>
      <div class="modal-body">
      </div>
    </div>
  </div>
</div>

<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="" />
                    <input type="hidden" name="user_id" value="" />
                    <input type="hidden" name="block" value="cctasks" />
                    <input type="hidden" name="action" value="add_comment" />

                    <input type="hidden" name="task_id" value="" />
                    <input type="hidden" name="uid" value="" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_perspective" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Изменить статус на "Перспектива"</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_perspective" action="order/{$order->order_id}">

                    <input type="hidden" name="task_id" value="" />
                    <input type="hidden" name="action" value="add_perspective" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Когда обещает:</label>
                        <input type="text" name="perspective_date" class="form-control js-perspective" value="" />
                    </div>
                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_recall" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Изменить статус на "Перезвонить"</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_recall" action="order/{$order->order_id}">

                    <input type="hidden" name="task_id" value="" />
                    <input type="hidden" name="action" value="add_recall" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <div>
                            <label for="name" class="control-label text-white">Отправить номер в дайлер через:</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="dont-call" />
                            <label for="">не звонить</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="0" />
                            <label for="">0 ч.</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="1" />
                            <label for="">1 ч.</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="2" />
                            <label for="">2 ч.</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="3" />
                            <label for="">3 ч.</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="4" />
                            <label for="">4 ч.</label>
                        </div>

                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_send_sms" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отправить смс-сообщение?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="card">
                    <div class="card-body">

                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-sms-form">
                                    <input type="hidden" name="user_id" value="" />
                                    <input type="hidden" name="action" value="send_sms" />
                                    <input type="hidden" name="type" value="sms" />
                                    <input type="hidden" name="task_id" value="" />
                                    <input type="hidden" name="zaim_number" value="" />
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите шаблон сообщения:</label>
                                        <select name="template_id" class="form-control">
                                            {foreach $sms_templates as $sms_template}
                                            <option value="{$sms_template->id}" title="{$sms_template->template|escape}">{$sms_template->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <div class="row">
                                            <div class="col-3">
                                                <button type="button" class="btn btn-danger float-left waves-effect" data-dismiss="modal">Отменить</button>
                                            </div>
                                            <div class="col-9 text-right">
                                                <button type="button" class="mr-1 btn btn-info waves-effect waves-light js-send-sms">СМС</button>

                                                <button type="button" class="mr-1 btn btn-primary waves-effect waves-light js-send-viber">Viber</button>

                                               {* <button type="button" class="btn btn-success waves-effect waves-light js-send-whatsapp">Whatsapp</button>  *}
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_distribute" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Распределить договора</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_distribute" action="">

                    <input type="hidden" name="action" value="distribute" />
                    <input type="hidden" name="period" value="{$filter_period}" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label"><strong>Менеджеры для распределения:</strong></label>
                        <ul class="list-unstyled" style="max-height:250px;overflow:hidden auto;">

                        </ul>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Распределить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
