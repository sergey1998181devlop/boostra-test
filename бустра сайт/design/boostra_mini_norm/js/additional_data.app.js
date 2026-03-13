;function AdditionalDataApp()
{
    var app = this;
    
    app.$form;
    app.validator;
    
    app.counter = 9;
    
    app.init = function(){
        app.$form = $('#additional_data');

        const formattedInput = app.$form.find('input[name="income_base_formated"]');
        formattedInput.on('input', (e) => {
            const rawInput = app.$form.find('input[name="income_base"]');
            // Remove all non-digit characters from input
            let valWithoutSpaces = e.target.value.replace(/[^0-9]/g, '');

            if (valWithoutSpaces.startsWith('0')) {
                valWithoutSpaces = valWithoutSpaces.substring(1);
            }
            
            // Update raw input with cleaned value
            rawInput.val(valWithoutSpaces);
            
            // Apply formatting (spaces every 3 digits)
            if (valWithoutSpaces.length > 3) {
                const firstGroupLength = valWithoutSpaces.length % 3;
                const formattedValue = valWithoutSpaces.split('').reduce((acc, curr, currInd) => {
                    return (currInd - firstGroupLength) % 3 === 0 && currInd !== 0 ? acc + " " + curr : acc + curr;
                }, '');
                formattedInput.val(formattedValue);
            } else {
                formattedInput.val(valWithoutSpaces);
            }
        })
        
        $('.js-not-change').change(function(){
            if (app.counter > 0)
            {
                $('.js-not-change').attr('checked', true)
                app.counter--;
            }
            
            if ($(this).is(':checked'))
            {
                $('#service_insurance_hidden').val(1)
            }
            else
            {
                $('#service_insurance_hidden').val(0)
            }
        });
    };
    
    app.init_dadata = function(){
        
        $('[name="workplace"]').autocomplete({
            serviceUrl: 'ajax/dadata.php?action=inn',
            onSelect: function(item){

                $('[name="workplace"]').val(item.data.name.full);   
                $('[name="work_full_address"]').val(item.data.address.value);   
                $('[name=workdirector_name]').val(item.data.management.name);
                $('[name="Regregion"]').val(item.data.address.data.region).removeData('fias_id');
                $('[name="Regcity"]').val(item.data.address.data.city).removeData('fias_id');

                $('[name="Regstreet"]').val(item.data.address.data.street).removeData('fias_id');
                $('[name="Reghousing"]').val(item.data.address.data.house).removeData('fias_id');
                $('[name="Regbuilding"]').val(item.data.address.data.block);                
                $('[name="Regroom"]').val(item.data.address.data.flat).removeData('fias_id');
                $('[name="Regindex"]').val(item.data.address.data.postal_code);
                $('[name="Regregion_shorttype"]').val(item.data.address.data.region_type);
                $('[name="Regcity_shorttype"]').val(item.data.address.data.city_type);
                $('[name="Regstreet_shorttype"]').val(item.data.address.data.street_type);
            },
            formatResult: function(item, short_value){

                var _block = '';
        		var c = "(" + short_value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + ")";
        		var item_value = item.value.replace(RegExp(c, "gi"), "<strong>$1</strong>")
                
                _block += '<span>'+item_value+'</span>';
                _block += '<small>'+item.data.address.value+'</small>';

                return _block;
            }
        })
        
        $('[name="work_full_address"]').autocomplete({
            serviceUrl: 'ajax/dadata.php?action=full_address',
            onSelect: function(item){

                $('[name="work_full_address"]').val(item.unrestricted_value);   
                $('[name="Regregion"]').val(item.data.region).removeData('fias_id');
                $('[name="Regcity"]').val(item.data.city).removeData('fias_id');

                $('[name="Regstreet"]').val(item.data.street).removeData('fias_id');
                $('[name="Reghousing"]').val(item.data.house).removeData('fias_id');
                $('[name="Regbuilding"]').val(item.data.block);                
                $('[name="Regroom"]').val(item.data.flat).removeData('fias_id');
                $('[name="Regindex"]').val(item.data.postal_code);
                $('[name="Regregion_shorttype"]').val(item.data.region_type);
                $('[name="Regcity_shorttype"]').val(item.data.city_type);
                $('[name="Regstreet_shorttype"]').val(item.data.street_type);
            },
            formatResult: function(item, short_value) {
                return '<span>'+item.unrestricted_value+'</span>';
            }
        })
        
    };
    
    app.init_work_scope = function(){
        app.$form.find('select[name=work_scope]').change(function(){
            var _val = $(this).val();
            
            if (_val == 'Пенсионер' || _val == 'Безработный')
            {
                app.$form.find('.js-pensioner-hidden').each(function(){
                    $(this).removeAttr('required');
                    $(this).attr('disabled', true);
                    $(this).hide()
                });
            }
            else if (_val == 'Иное')
            {
                app.$form.find('#work_scope_select').hide().find('select').val('').attr('disabled', 'true');
                app.$form.find('#work_scope_input').show().find('input').removeAttr('disabled').val('');
            }
            else
            {
                app.$form.find('.js-pensioner-hidden').show().removeAttr('disabled');
                app.$form.find('#work_scope_input').hide().find('input').attr('disabled', 'true');
                app.$form.find('#work_scope_select').show().find('select').removeAttr('disabled');    
            }
            
        });
        
        app.$form.find('.close_work_scope').click(function(e){
            e.preventDefault();
            
            app.$form.find('#work_scope_input').hide().find('input').attr('disabled', 'true');
            app.$form.find('#work_scope_select').show().find('select').removeAttr('disabled').val(''); 
        });
    }
    
    app.init_validator = function(){
        app.validator = app.$form.validate({
    		errorElement: "span",
    		rules: {
    			"income_base": {
    				digits: true
    			},
                "work_scope": {
                    required: true
                },
                social_fb: {
                  require_from_group: [1, ".social-group"]
                },
                social_inst: {
                  require_from_group: [1, ".social-group"]
                },
                social_vk: {
                  require_from_group: [1, ".social-group"]
                },
                social_ok: {
                  require_from_group: [1, ".social-group"]
                }

            },
            
            submitHandler: function(form) {                

                if (app.$form.find('.js-input-accept').length > 0 && !app.$form.find('.js-input-accept').is(':checked'))
                {
                    app.$form.find('.js-accept-block').addClass('error');
                }
                else if ($('.js-need-verify-modal').not(':checked').length > 0)
                {
                	// $.magnificPopup.open({
                	// 	items: {
                	// 		src: '#accept'
                	// 	},
                	// 	type: 'inline',
                    //     showCloseBtn: false
                	// });

                    $('#not_checked_info').show();
                    $('.conditions').show();
                    return false;
                }
                else
                {
                    sendMetric('reachGoal','extra');
                    VK.Goal('submit_application');
    
                    var date = new Date();
                    var local_time = parseInt(date.getTime() / 1000);
    console.info('local_time', local_time);
                    $('#local_time').val(local_time);
    
    
                    app.$form.addClass('loading')
                    form.submit();
                    
                }
                
            }
    	})
    }
    
    ;(function(){
        app.init();
        app.init_validator();
        
        app.init_dadata();
        
        app.init_work_scope();
    })();
};


$(function(){
    if ($('#additional_data').length > 0)
        new AdditionalDataApp();
});