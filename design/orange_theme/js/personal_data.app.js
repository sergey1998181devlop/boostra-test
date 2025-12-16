;function PersonalDataApp()
{
    var app = this;
    
    app.$form;
    app.validator;
    
    app.init = function(){
        app.$form = $('#personal_data');
    };

    app.init_events = function(){

        // Совпадает с адресом проживания
        $('#equal').change(function(){
            _copy_address();
            if ($(this).is(':checked'))
                app.$form.find('#living_block').slideUp('slow');
            else
                app.$form.find('#living_block').slideDown('slow');
        });
        
        app.$form.find('[name=gender]').change(function(){
            app.$form.find('[name=gender]').closest('label').removeClass('error');
        })
        
    };
    
    app.init_masks = function(){
        
    };
    
    app.init_validator = function(){
        app.validator = app.$form.validate({
    		errorElement: "span",
    		rules: {
    			"contact_person_name": {
    				russian: true,
                    required: true
    			},
    			"contact_person2_name": {
    				russian: true,
                    required: true
    			},
    			"contact_person3_name": {
    				russian: true,
                    required: true
    			},
                "contact_person_relation": {
                    required: true
                },
                "contact_person2_relation": {
                    required: true
                },
                "contact_person3_relation": {
                    required: true
                },
                "subdivisionCode": {
                    subdivisionCode: true
                },
                "passportCode": {
                    passportCode: true
                },
                "marital_status": {
                    required: true
                },
    		},
            submitHandler: function(form) {

                if (app.$form.find('[name=gender]').length > 0 && app.$form.find('[name=gender]:checked').length == 0)
                {
                    app.$form.find('[name=gender]').each(function(){
                        $(this).closest('label').addClass('error');
                    });
                    $('body, html').animate({ scrollTop: app.$form.find('[name=gender]').closest('.clearfix').offset().top}, 1100);
                    
                }
                else
                {
                    app.$form.find('[name=gender]').removeClass('error');
                    
                    if (app.$form.hasClass('js-send-feedback'))
                    {
                        let _target = app.$form.data('target');
                        sendMetric('reachGoal', _target)
                    }
                    app.$form.addClass('loading');
                    form.submit();

                }
                
            }
    	})
    }
    
    var _copy_address = function(){
        app.$form.find('[name=Faktindex]').val(app.$form.find('[name=Regindex]').val())
        app.$form.find('[name=Faktregion]').val(app.$form.find('[name=Regregion]').val())
        app.$form.find('[name=Faktcity]').val(app.$form.find('[name=Regcity]').val())
        app.$form.find('[name=Faktstreet]').val(app.$form.find('[name=Regstreet]').val())
        app.$form.find('[name=Fakthousing]').val(app.$form.find('[name=Reghousing]').val())
        app.$form.find('[name=Faktbuilding]').val(app.$form.find('[name=Regbuilding]').val())
        app.$form.find('[name=Faktroom]').val(app.$form.find('[name=Regroom]').val())
    };
    
    ;(function(){
        app.init();
        app.init_events();
        app.init_masks();
        app.init_validator();
    })();
};


$(function(){
    if ($('#personal_data').length > 0)
        new PersonalDataApp();
});