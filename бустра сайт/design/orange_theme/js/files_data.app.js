function FilesDataApp(form_selector)
{
    var app = this;
    
    app.$form = $(form_selector);
    app.$new_other_file;
    
    app.init = function(){
console.log('run')    
        
        app.$new_other_file = app.$form.find('#new_other_file').clone(true);
        app.$new_other_file.removeAttr('id').hide();
        app.$form.find('#new_other_file').remove();
        app.$form.find('#add_file').click(function(){
            var $new = app.$new_other_file.clone(true);

            let index = $("#other_files .user-file").length;
            $($new).find('label').attr('for', 'user_file_user_file_' + index);
            $($new).find('input').attr('id', 'user_file_user_file_' + index);

            app.$form.find('#other_files').append($new);
            $new.fadeIn()
        });

        app.video = document.getElementById('video');
        app.photo = document.getElementById('photo');
        app.$get_photo = document.getElementById('get_photo');
        app.$save_photo = document.getElementById('save_photo');
        app.$input_image = null;

        let width = 320,
            height = 0;

        app.init_photo = function (button) {

            let streaming = false,
                canvas = document.getElementById('canvas'),
                camera = $('#camera');

                app.$input_image = $(button).closest('div').find('input[type="file"]')[0];

            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                .then(function(stream) {
                    camera.removeClass('error');
                    app.video.srcObject = stream;
                    app.video.play();
                })
                .catch(function(err) {
                    camera.addClass('error');
                    $('#camera .text-red').html("Произошла ошибка: <b>\"" + err + "\"</b>\n Подключите заново камеру и разрешите доступ.");
                });

            app.video.addEventListener('canplay', function(ev){
                if (!streaming) {
                    height = app.video.videoHeight / (app.video.videoWidth/width);

                    if (isNaN(height)) {
                        height = width / (4/3);
                    }

                    app.video.setAttribute('width', width);
                    app.video.setAttribute('height', height);

                    canvas.setAttribute('width', width);
                    canvas.setAttribute('height', height);
                    streaming = true;
                }
            }, false);
        }

        $(app.$get_photo).on('click', function () {
            if (app.video.srcObject) {
                let img_ratio = 3,
                    context = canvas.getContext('2d');

                // загрузим фото в обрезанном размере в canvas для превью
                canvas.width = width;
                canvas.height = height;
                context.drawImage(app.video, 0, 0, width, height);

                let data = canvas.toDataURL('image/png');

                app.photo.width = width / img_ratio;
                app.photo.height = height / img_ratio;
                app.photo.src = data;
                app.photo.style.display = 'block';

                // загрузим фото в полном размере в canvas
                canvas.width = app.video.videoWidth;
                canvas.height = app.video.videoHeight;
                context.drawImage(app.video, 0, 0, app.video.videoWidth, app.video.videoHeight);

                app.save_photo.show();
            }
        });

        $(app.$save_photo).on('click', function () {
            let data = canvas.toDataURL('image/png');

            // добавим файл в input и вызовем триггер
            let dt  = new DataTransfer();
            let blobBin = atob(data.split(',')[1]);
            let array = [];
            for(let i = 0; i < blobBin.length; i++) {
                array.push(blobBin.charCodeAt(i));
            }
            let file = new Blob([new Uint8Array(array)], {type: 'image/png'});
            dt.items.add(new File([file], 'user_photo_' + Math.random().toString(16) + '.png', {type:"image/png"}));

            app.$input_image.files = dt.files;

            app.photo.src = data;
            $(app.$input_image).trigger('change');
            $.magnificPopup.close();
        });

        app.destroy_camera = function () {
            app.save_photo.hide();
            app.video.pause();
            app.video.src="";

            if (app.video.srcObject) {
                app.video.srcObject.getTracks().map(function (val) {
                    val.stop();
                });
            }

            app.photo.src = '';
            app.photo.style.display = 'none';
        }

        app.save_photo = $("#camera #save_photo");
        $(document).on('click', '.mobile_camera', function (){
            const button = $(this);
            $.magnificPopup.open({
                items: {
                    src: '#camera'
                },
                type: 'inline',
                showCloseBtn: false,
                preloader: true,
                callbacks: {
                    open: function() {
                        app.init_photo(button);
                    },
                    close: function() {
                        app.destroy_camera();
                    }
                }
            });
        });

        $(document).on('change', '[type=file]', function(){
            app.upload(this);
        });
        
        $(document).on('click', '.remove-file, .js-remove-file', function(e){
            e.preventDefault();

            if (1 || confirm('Вы хотите удалить файл? Операцию не возможно будет отменить.'))
            {
                app.remove($(this));
            }
            return false;
        });

    };
    
    app.remove = function($this){
        
        var file_id = $this.data('id');
        var $fileblock = $this.closest('.file-block');

        $.ajax({
            url: 'ajax/upload.php',
            data: {
                id: file_id,
                action: 'remove'
            },
            type: 'POST',
            dataType: 'json',
            beforeLoad: function(){

            },
            success: function(resp){
                if (!!resp.error)
                {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else if (resp.error == 'error_uploading')
                        error_text = 'Файл не удалось загрузить, попробуйте еще.';
                    else
                        error_text = resp.error;
                        
                    $fileblock.find('.alert').html(error_text).fadeIn();
                }
                else
                {
                    $fileblock.find('.alert').fadeOut();
                    
                    $this.closest('.file-label').remove();                    
                    $fileblock.find('.file-field').fadeIn();
                    _show_submit_button();

                }
            }
        });
        
    };
    
    app.upload = function(input){
        
        var $this = $(input);
        
        var $fileblock = $this.closest('.file-block');
        var _type = $this.data('type');
        
        var form_data = new FormData();
                    
        form_data.append('type', _type);        
        form_data.append('action', 'add');  
        form_data.append('file', input.files[0])      

        $.ajax({
            url: 'http://51.250.17.98/ajax/upload.php',
            data: form_data,
            type: 'POST',
            dataType: 'json',
            processData : false,
            contentType : false
        });

        $.ajax({
            url: 'ajax/upload.php',
            data: form_data,
            type: 'POST',
            dataType: 'json',
            processData : false,
            contentType : false, 
            beforeSend: function(){
                $fileblock.addClass('loading');
            },
            success: function(resp){
                $fileblock.removeClass('loading');
                if (!!resp.error)
                {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else if (resp.error == 'error_uploading')
                        error_text = 'Файл не удалось загрузить, попробуйте еще.';
                    else if (resp.error == 'extension')
                        error_text = 'Файл не удалось загрузить, Недопустимое расширение файла. Допускается загрузка форматов: '+resp.allowed_extensions.join(', ');
                    else
                        error_text = resp.error;
                        
                    $fileblock.find('.alert').html(error_text).fadeIn();
                }
                else
                {
                    $fileblock.removeClass('error').find('.alert').fadeOut();
                    
                    if (_type == 'passport')
                    {
                        var _input_id = ''
                    }
                    else
                    {
                        var _input_id = _type;
                    }
                    
                    var _preview = '';
                    _preview += '<label class="file-label">';
                    _preview += '<div class="file-label-image">';
                    _preview += '<img src="'+resp.filename+'" />';
                    _preview += '</div>';
                    _preview += '<span class="js-remove-file" data-id="'+resp.id+'">Удалить</span>';
                    _preview += '<input type="hidden" id="'+_input_id+'" name="user_files[]" value="'+resp.id+'" />';
                    _preview += '</label>';
/*                        
                    _preview += '<div class="user_file">';
//                    _preview += '<div class="image-status uploaded" title="Файл загружен, отправьте его на проверку. Не отправленные файлы, через 5 дней после загрузки, будут удалены"><span></span></div>';
                    _preview += '<div class="file-label-image"><img src="'+resp.filename+'" /></div>';
                    _preview += '<a href="javascript:void(0);" class="remove-file" data-id="'+resp.id+'">&times;</a>';
                    _preview += '</div>';
*/                    
                    
//                    if (_type != 'passport')
                    $fileblock.find('.file-field').hide();
                    $fileblock.find('.user_files').append(_preview).fadeIn();
                    
                    
                    
                    _show_submit_button();
                }
                
            }
        });                
    };
    
    app.check_files = function(){
        
        var scrolled = 0;
        var _valid = 1;
        
        /*var $face1 = app.$form.find('.face1-file');
        if (!$face1.find('#face1').length || $face1.find('#face1').val() == '')
        {
            $face1.addClass('error');
            _valid = 0;
            
            if (!scrolled)
                $('body, html').animate({ scrollTop: $face1.offset().top}, 1100);
            scrolled = 1;
        }
        else
        {
            $face1.removeClass('error');
        }
        
        var $face2 = app.$form.find('.face2-file');
        if (!$face2.find('#face2').length || $face2.find('#face2').val() == '')
        {
            $face2.addClass('error');
            _valid = 0;
            
            if (!scrolled)
                $('body, html').animate({ scrollTop: $face2.offset().top }, 1100);
            scrolled = 1;
        }
        else
        {
            $face2.removeClass('error');
        }*/
        
        var $passport1 = app.$form.find('.passport1-file');
        if (!$passport1.find('#passport1').length || $passport1.find('#passport1').val() == '')
        {
            $passport1.addClass('error');
            _valid = 0;
            
            if (!scrolled)
                $('body, html').animate({ scrollTop: $passport1.offset().top }, 1100);
            scrolled = 1;
        }
        else
        {
            $passport1.removeClass('error');
        }
        
        var $passport2 = app.$form.find('.passport2-file');
        if (!$passport2.find('#passport2').length || $passport2.find('#passport2').val() == '')
        {
            $passport2.addClass('error');
            _valid = 0;
            
            if (!scrolled)
                $('body, html').animate({ scrollTop: $passport2.offset().top }, 1100);
            scrolled = 1;
        }
        else
        {
            $passport2.removeClass('error');
        }
        /*
        var $passport3 = app.$form.find('.passport3-file');
        if (!$passport3.find('#passport3').length || $passport3.find('#passport3').val() == '')
        {
            $passport3.addClass('error');
            _valid = 0;
            
            if (!scrolled)
                $('body, html').animate({ scrollTop: $passport3.offset().top }, 1100);
            scrolled = 1;
        }
        else
        {
            $passport3.removeClass('error');
        }
        */
        var $passport4 = app.$form.find('.passport4-file');
        if (!$passport4.find('#passport4').length || $passport4.find('#passport4').val() == '')
        {
            $passport4.addClass('error');
            _valid = 0;
            
            if (!scrolled)
                $('body, html').animate({ scrollTop: $passport4.offset().top }, 1100);
            scrolled = 1;
        }
        else
        {
            $passport4.removeClass('error');
        }
        
        var $selfi = app.$form.find('.selfi-file');
        if (!$selfi.find('#selfi').length || $selfi.find('#selfi').val() == '')
        {
            $selfi.addClass('error');
            _valid = 0;
            
            if (!scrolled)
                $('body, html').animate({ scrollTop: $selfi.offset().top }, 1100);
            scrolled = 1;
        }
        else
        {
            $selfi.removeClass('error');
        }
        
        return _valid;
    }
    
    app.init_submit = function(){
        app.$form.submit(function(e){
            e.preventDefault();
            
            if (!app.check_files())
                return false;
            var url = is_developer ? 'ajax/loan.php' : 'ajax/loan.php';
            $.ajax({
                url:url,
                data: {
                    action: 'first_loan'
                    
                },
                beforeSend: function(){
                    app.$form.addClass('loading');
                },
                success: function(resp){
                    
                    if (!!resp.error)
                    {
                        if (resp.error == 'exception')
                        {
                            var _message = 'Центральный сервер не отвечает.<br />Попробуйте повторить позже.';
                            app.$form.find('.js-error-block p').html(_message);
                        }
                        else if (resp.error == 'not_accepted')
                        {
                            var _message = 'Заявка не принята.<br />Свяжитесь с клиентским центром.';
                            app.$form.find('.js-error-block p').html(_message);
                        }
                        else if (resp.error == 'account_removed')
                        {
                            var _message = 'Вы ранее удалили свой аккаунт.';
                            app.$form.find('.js-error-block p').html(_message);
                        }
                        else if (resp.error == 'not_user_UID')
                        {
                            var _message = 'Не удалось создать профиль клиента.<br />Свяжитесь с клиентским центром.';
                            app.$form.find('.js-error-block p').html(_message);
                        }
                        else if (resp.error == 'files_not_sent')
                        {
                            var _message = 'Не удалось передать файлы.<br />Попробуйте еще.';
                            app.$form.find('.js-error-block p').html(_message);
                        }
                        else
                        {
                            app.$form.find('.js-error-block p').html(resp.error);
                        }
                        
                        app.$form.find('#file_form').remove();
                        app.$form.find('.js-error-block').fadeIn();
                        
                        console.log(resp.error);
                    
                        app.$form.removeClass('loading');
                    }
                    else if (!!resp.success)
                    {
                        sendMetric('reachGoal','identification');
                        location.reload();
                    }
                    else
                    {
                        console.info(resp);
                    }
                    
                }
            })
        });
    };

    var _show_submit_button = function(){
        

        var _show = app.$form.find('#face1').length && app.$form.find('#face2').length && app.$form.find('#passport1').length && app.$form.find('#passport2').length && app.$form.find('#passport4').length;
        
        var _show = true;
        
//        _show = app.$form.find('[name="user_files[]"]').length >=4;
        
        if (_show) 
            app.$form.find('[name=confirm]').fadeIn();
        else
            app.$form.find('[name=confirm]').fadeOut();

    }; 
    
    ;(function(){
        app.init();
        app.init_submit();
        _show_submit_button();
    })();
};
$(function(){
    if ($('#files_form').length > 0)
        new FilesDataApp('#files_form');
});

// добавляем атрибут к input, чтобы вызвать камеру
$('.get_mobile_photo').on('click', function (e){
    e.preventDefault();
    let id = $(this).attr('for'),
        input = document.getElementById(id);

    input.setAttribute('capture', 'camera');
    input.click();
});

// удаляем возможность вызвать камеру
$('.file-label .photo_btn:not(.get_mobile_photo), .file-field .file-label-image').on('click', function (e) {
    e.preventDefault();
    let id = $(this).attr('for'),
        input = document.getElementById(id);

    input.removeAttribute('capture');
    input.click();
});
  