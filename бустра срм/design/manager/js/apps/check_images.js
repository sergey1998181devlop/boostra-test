;function CheckImagesApp()
{
    var app = this;
    
    app.$block = $('.js-check-images');
    
    app.run = function(){
        $.ajax({
		url: FRONT_URL+'/ajax/filestorage.php',
            data: {
                'user_id': app.$block.data('user')
            },
            success: function(resp){
                
                if (!!resp.files)
                {
                    $.each(resp.files, function(k, f){
                        
                        if (!!f.exists)
                        {
                            status = f.status == 2 ? 'text-success far fa-check-circle' : '';
                            $('#file_'+f.id).find('.js-label-exists').html(`<i class="${status}"></i>`);
                            var _th_src = $('#file_'+f.id).find('.js-image-thumb').attr('src');
                            $('#file_'+f.id).find('.js-image-thumb').attr('src', _th_src+'?'+Math.random());
                        }
                        else
                            $('#file_'+f.id).find('.js-label-exists').html('<i class="text-danger fas fa-ban"></i>');
                    });
                    
                    $('.js-check-images').find('.spinner-border').remove();
                    $('.js-images-spinner').remove();
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка при проверке наличия файлов в хранилище:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                $('.js-images-spinner').remove();
            }
        });
    };
    
    ;(function(){
        app.run();
    })();
}
$(function(){
    if ($('.js-check-images').length > 0)
        new CheckImagesApp();
})
