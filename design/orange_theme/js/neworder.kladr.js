function birth_place_handler(a,b)
{
    console.log(a, b)
}

var region, city, street, building, buildingAdd, buildingFlat;
$(function () {
    var noEnter = function (e) {
        var code = ('which' in e) ? e.which : e.keyCode;
        if (code == 13) {
            return false;
        }
    };
    var token = '51dfe5d42fb2b43e3300006e';
    var key = '86a2c2a06f1b2451a87d05512cc2c3edfdf41969';

    var $birth_place = $('[name="birth_place"]').keydown(noEnter);;
    $birth_place.kladr({
        token: token,
        key: key,
        type: $.kladr.type.city,
        contentType: 'city', 
        withParent: 1,
        callback: 'birth_place_handler',
    });

    region = $('[name="Regregion"]').keydown(noEnter);
    city = $('[name="Regcity"]').prop("readonly", true).keydown(noEnter);
    street = $('[name="Regstreet"]').prop("readonly", true).keydown(noEnter);
    building = $('[name="Reghousing"]').keydown(noEnter);
    buildingAdd = $('[name="Regbuilding"]').keydown(noEnter);
    buildingFlat = $('[name="Regroom"]').keydown(noEnter);
    region_shorttype = $('[name="Regregion_shorttype"]');
    city_shorttype = $('[name="Regcity_shorttype"]');
    street_shorttype = $('[name="Regstreet_shorttype"]');
    // Подключение плагина для поля ввода города
    var region_select = function (obj) {
        
        console.log(obj)
        $('#obl_subj').val(obj.name);
        $('#prop_region,#region').val(obj.name);
        
        region_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#prop_zip,#post_code').val(obj.zip);
        }
        $('label[for=' + region.attr('id') + ']').text(obj.type);
        $('#prop_okato').val(obj.okato);
        $('#prop_city_type').val(obj.type);
        city.prop("readonly", false);
        city.kladr('parentType', $.kladr.type.region);
        city.kladr('parentId', obj.id);
        tryToggleCopyAddressButton();
    };
    //return;
    region.kladr({
        token: token,
        key: key,
        type: $.kladr.type.region,
        //withParents: true,
        verify: true,
        select: region_select,
        check: region_select,
        labelFormat: kladrCityLabelFormat
    });
    var city_select = function (obj) {

        $('label[for=' + city.attr('id') + ']').text(obj.type);
        $('#prop_okato').val(obj.okato);

        city_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#prop_zip,#post_code').val(obj.zip);
        }
        $('#prop_city_type').val(obj.type);
        street.kladr('parentType', $.kladr.type.city);
        street.kladr('parentId', obj.id);
        street.prop("readonly", false);
        building.kladr('parentType', $.kladr.type.city);
        building.kladr('parentId', obj.id);
        tryToggleCopyAddressButton();
    };
    //return;
    city.kladr({
        token: token,
        key: key,
        type: $.kladr.type.city,
        //parentType: $.kladr.type.region,
        //parentId:'region' ,
        //withParents: true,
        verify: true,
        select: city_select,
        check: city_select,
        labelFormat: kladrCityLabelFormat
    });

    //city.kladr('controller').setValueByName(city.val());
    // Подключение плагина для поля ввода улицы
    var street_select = function (obj) {

        $('label[for=' + street.attr('id') + ']').text(obj.type);
        $('#prop_street_type_long').val(obj.type);
        $('#prop_street_type_short').val(obj.typeShort);
        
        street_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#prop_zip,#post_code').val(obj.zip);
        }
        building.kladr('parentType', $.kladr.type.street);
        building.kladr('parentId', obj.id);
        building.prop("readonly", false);
        tryToggleCopyAddressButton();
    };
    street.kladr({
        token: token,
        key: key,
        type: $.kladr.type.street,
        //parentType: $.kladr.type.city,
        verify: false,
        select: street_select,
        check: street_select
    });
    // Подключение плагина для поля ввода номера дома
    building.kladr({
        token: token,
        key: key,
        type: $.kladr.type.building,
        //parentType: $.kladr.type.street,
        verify: false,
        select: function (obj) {
            buildingAdd.prop("readonly", false);
            buildingFlat.prop("readonly", false);
            tryToggleCopyAddressButton();
        },
        check: function (obj) {
            buildingAdd.prop("readonly", false);
            buildingFlat.prop("readonly", false);
            tryToggleCopyAddressButton();
        }
    });


    workregion = $('[name="Workregion"]').keydown(noEnter);
    workcity = $('[name="Workcity"]').prop("readonly", true).keydown(noEnter);
    workstreet = $('[name="Workstreet"]').prop("readonly", true).keydown(noEnter);
    workbuilding = $('[name="Workhousing"]').prop("readonly", true).keydown(noEnter);
    workbuildingAdd = $('[name="Workbuilding"]').prop("readonly", true).keydown(noEnter);
    workbuildingFlat = $('[name="Workroom"]').prop("readonly", true).keydown(noEnter);
    workregion_shorttype = $('[name="Workregion_shorttype"]');
    workcity_shorttype = $('[name="Workcity_shorttype"]');
    workstreet_shorttype = $('[name="Workstreet_shorttype"]');
    // Подключение плагина для поля ввода города
    var workregion_select = function (obj) {
        
        console.log(obj)
        $('#obl_subj').val(obj.name);
        $('#prop_region,#region').val(obj.name);
        
        workregion_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#work_zip').val(obj.zip);
        }
        $('label[for=' + workregion.attr('id') + ']').text(obj.type);
        $('#work_okato').val(obj.okato);
        workcity_shorttype.val(obj.type);
        workcity.prop("readonly", false);
        workcity.kladr('parentType', $.kladr.type.region);
        workcity.kladr('parentId', obj.id);
        tryToggleCopyAddressButton();
    };
    //return;
    workregion.kladr({
        token: token,
        key: key,
        type: $.kladr.type.region,
        //withParents: true,
        verify: true,
        select: workregion_select,
        check: workregion_select,
        labelFormat: kladrCityLabelFormat
    });
    var workcity_select = function (obj) {

        $('label[for=' + workcity.attr('id') + ']').text(obj.type);
        $('#prop_okato').val(obj.okato);

        workcity_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#work_zip').val(obj.zip);
        }

        workstreet.kladr('parentType', $.kladr.type.city);
        workstreet.kladr('parentId', obj.id);
        workstreet.prop("readonly", false);
        workbuilding.kladr('parentType', $.kladr.type.city);
        workbuilding.kladr('parentId', obj.id);
        tryToggleCopyAddressButton();
    };
    //return;
    workcity.kladr({
        token: token,
        key: key,
        type: $.kladr.type.city,
        //parentType: $.kladr.type.region,
        //parentId:'region' ,
        //withParents: true,
        verify: true,
        select: workcity_select,
        check: workcity_select,
        labelFormat: kladrCityLabelFormat
    });

    //city.kladr('controller').setValueByName(city.val());
    // Подключение плагина для поля ввода улицы
    var workstreet_select = function (obj) {

        $('label[for=' + workstreet.attr('id') + ']').text(obj.type);
        $('#prop_street_type_long').val(obj.type);
        $('#prop_street_type_short').val(obj.typeShort);
        
        workstreet_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#work_code').val(obj.zip);
        }
        workbuilding.kladr('parentType', $.kladr.type.street);
        workbuilding.kladr('parentId', obj.id);
        workbuilding.prop("readonly", false);
        tryToggleCopyAddressButton();
    };
    workstreet.kladr({
        token: token,
        key: key,
        type: $.kladr.type.street,
        //parentType: $.kladr.type.city,
        verify: true,
        select: workstreet_select,
        check: workstreet_select
    });
    // Подключение плагина для поля ввода номера дома
    workbuilding.kladr({
        token: token,
        key: key,
        type: $.kladr.type.building,
        //parentType: $.kladr.type.street,
        verify: false,
        select: function (obj) {
            workbuildingAdd.prop("readonly", false);
            workbuildingFlat.prop("readonly", false);
            tryToggleCopyAddressButton();
        },
        check: function (obj) {
            workbuildingAdd.prop("readonly", false);
            workbuildingFlat.prop("readonly", false);
            tryToggleCopyAddressButton();
        }
    });

/*****/

    prog_region = $('[name="Faktregion"]').keydown(noEnter);
    prog_city = $('[name="Faktcity"]').prop("readonly", true).keydown(noEnter);
    prog_street = $('[name="Faktstreet"]').prop("readonly", true).keydown(noEnter);
    prog_building = $('[name="Fakthousing"]').keydown(noEnter);
    prog_buildingAdd = $('[name="Faktbuilding"]').keydown(noEnter);
    prog_buildingFlat = $('[name="Faktroom"]').keydown(noEnter);
    prog_region_shorttype = $('[name="Faktregion_shorttype"]');
    prog_city_shorttype = $('[name="Faktcity_shorttype"]');
    prog_street_shorttype = $('[name="Faktstreet_shorttype"]');
    // Подключение плагина для поля ввода города
    var prog_region_select = function (obj) {
        
        //console.log(obj)
        $('#obl_subj').val(obj.name);
        $('#prog_region,#region').val(obj.name);

        prog_region_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#prog_zip,#post_code').val(obj.zip);
        }
        $('label[for=' + prog_region.attr('id') + ']').text(obj.type);
        $('#prog_okato').val(obj.okato);
        $('#prog_city_type').val(obj.type);
        prog_city.prop("readonly", false);
        prog_city.kladr('parentType', $.kladr.type.region);
        prog_city.kladr('parentId', obj.id);
        tryToggleCopyAddressButton();
    };
    //return;
    prog_region.kladr({
        token: token,
        key: key,
        type: $.kladr.type.region,
        //withParents: true,
        verify: true,
        select: prog_region_select,
        check: prog_region_select,
        labelFormat: kladrCityLabelFormat
    });
    var prog_city_select = function (obj) {

        $('label[for=' + prog_city.attr('id') + ']').text(obj.type);
        $('#prog_okato').val(obj.okato);

        prog_city_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#prog_zip,#post_code').val(obj.zip);
        }
        $('#prog_city_type').val(obj.type);
        prog_street.kladr('parentType', $.kladr.type.city);
        prog_street.kladr('parentId', obj.id);
        prog_street.prop("readonly", false);
        prog_building.kladr('parentType', $.kladr.type.city);
        prog_building.kladr('parentId', obj.id);
        tryToggleCopyAddressButton();
    };
    //return;
    prog_city.kladr({
        token: token,
        key: key,
        type: $.kladr.type.city,
        //parentType: $.kladr.type.region,
        //parentId:'region' ,
        //withParents: true,
        verify: true,
        select: prog_city_select,
        check: prog_city_select,
        labelFormat: kladrCityLabelFormat
    });

    //city.kladr('controller').setValueByName(city.val());
    // Подключение плагина для поля ввода улицы
    var prog_street_select = function (obj) {

        $('label[for=' + prog_street.attr('id') + ']').text(obj.type);
        $('#prog_street_type_long').val(obj.type);
        $('#prog_street_type_short').val(obj.typeShort);

        prog_street_shorttype.val(obj.typeShort);
        
        if (obj.zip) {
            $('#prog_zip,#post_code').val(obj.zip);
        }
        prog_building.kladr('parentType', $.kladr.type.street);
        prog_building.kladr('parentId', obj.id);
        prog_building.prop("readonly", false);
        tryToggleCopyAddressButton();
    };
    prog_street.kladr({
        token: token,
        key: key,
        type: $.kladr.type.street,
        //parentType: $.kladr.type.city,
        verify: false,
        select: prog_street_select,
        check: prog_street_select
    });
    // Подключение плагина для поля ввода номера дома
    prog_building.kladr({
        token: token,
        key: key,
        type: $.kladr.type.building,
        //parentType: $.kladr.type.street,
        verify: false,
        select: function (obj) {
            buildingAdd.prop("readonly", false);
            buildingFlat.prop("readonly", false);
            tryToggleCopyAddressButton();
        },
        check: function (obj) {
            buildingAdd.prop("readonly", false);
            buildingFlat.prop("readonly", false);
            tryToggleCopyAddressButton();
        }
    });






    var region_prog = $('[name="prog_region"]').keydown(noEnter);
    var city_prog = $('[name="prog_city"]').keydown(noEnter);
    var street_prog = $('[name="prog_street"]').prop("readonly", true).keydown(noEnter);
    var building_prog = $('[name="prog_home"]').prop("readonly", true).keydown(noEnter);
    var buildingAddProg = $('[name="prog_home_build"]').prop("readonly", true).keydown(noEnter);
    var buildingFlatProg = $('[name="prog_flat"]').prop("readonly", true).keydown(noEnter);
    var region_prog_select = function (obj) {
        $('#obl_subj').val(obj.name);
        $('#prog_region').val(obj.name);
        $('label[for=' + region_prog.attr('id') + ']').text(obj.type);
        //$('#prog_okato').val(obj.okato);
        $('#prog_city_type').val(obj.type);
        city_prog.kladr('parentType', $.kladr.type.region);
        city_prog.kladr('parentId', obj.id);
        tryToggleCopyAddressButton();
    };
    region_prog.kladr({
        token: token,
        key: key,
        type: $.kladr.type.region,
        //withParents: true,
        verify: true,
        select: region_prog_select,
        check: region_prog_select,
        labelFormat: kladrCityLabelFormat
    });
    var city_prog_select = function (obj) {
        $('label[for=' + city_prog.attr('id') + ']').text(obj.type);
        $('#prog_okato').val(obj.okato);
        $('#prog_city_type').val(obj.type);
        street_prog.kladr('parentType', $.kladr.type.city);
        street_prog.kladr('parentId', obj.id);
        street_prog.prop("readonly", false);
        building_prog.kladr('parentType', $.kladr.type.city);
        building_prog.kladr('parentId', obj.id);
        if (obj.zip)
            $('#prog_zip').val(obj.zip);
    };
    city_prog.kladr({
        token: token,
        key: key,
        type: $.kladr.type.city,
        //withParents: true,
        verify: true,
        select: city_prog_select,
        check: city_prog_select,
        labelFormat: kladrCityLabelFormat
    });
    // Подключение плагина для поля ввода улицы
    street_prog.kladr({
        token: token,
        key: key,
        type: $.kladr.type.street,
        verify: true,
        select: function (obj) {

            $('label[for=' + street_prog.attr('id') + ']').text(obj.type);
            $('#prog_street_type_long').val(obj.type);
            $('#prog_street_type_short').val(obj.typeShort);
            if (obj.zip)
                $('#prog_zip').val(obj.zip);
            building_prog.kladr('parentType', $.kladr.type.street);
            building_prog.kladr('parentId', obj.id);
            building_prog.prop("readonly", false);
        },
        check: function (obj) {
            if (obj) {
                $('label[for=' + street_prog.attr('id') + ']').text(obj.type);
                $('#prog_street_type_long').val(obj.type);
                $('#prog_street_type_short').val(obj.typeShort);
                if (obj.zip)
                    $('#prog_zip').val(obj.zip);
                building_prog.kladr('parentType', $.kladr.type.street);
                building_prog.kladr('parentId', obj.id);
                building_prog.prop("readonly", false);
            }
        }
    });
    // Подключение плагина для поля ввода номера дома
    building_prog.kladr({
        token: token,
        key: key,
        type: $.kladr.type.building,
        verify: true,
        select: function (obj) {
            buildingAddProg.prop("readonly", false);
            buildingFlatProg.prop("readonly", false);
        },
        check: function (obj) {
            buildingAddProg.prop("readonly", false);
            buildingFlatProg.prop("readonly", false);
        }
    });

    var work_region = $('[name="work_region"]').keydown(noEnter);
    var work_city = $('[name="work_city"]').keydown(noEnter);
    var work_street = $('[name="work_street"]').prop("readonly", true).keydown(noEnter);
    var work_building = $('[name="work_housing"]').prop("readonly", true).keydown(noEnter);
    var work_buildingAdd = $('[name="work_building"]').prop("readonly", true).keydown(noEnter);
    var work_buildingFlat = $('[name="work_room"]').prop("readonly", true).keydown(noEnter);
    var work_region_select = function (obj) {
        $('#obl_subj').val(obj.name);
        $('#prog_region').val(obj.name);
        $('label[for=' + region.attr('id') + ']').text(obj.type);
        $('#work_okato').val(obj.okato);
        $('#work_city_type').val(obj.type);
        work_city.kladr('parentType', $.kladr.type.region);
        work_city.kladr('parentId', obj.id);
        tryToggleCopyAddressButton();
    };
    work_region.kladr({
        token: token,
        key: key,
        type: $.kladr.type.region,
        //withParents: true,
        verify: true,
        select: work_region_select,
        check: work_region_select,
        labelFormat: kladrCityLabelFormat
    });
    var work_city_select = function (obj) {
        if (obj.parents.length > 0) {
            obl_name = obj.parents['0'].name + ' ' + obj.parents['0'].type.toLowerCase() + ' - ';
            $('#work_obl_subj').val(obj.parents['0'].name + ' ' + obj.parents['0'].type.toLowerCase());
            $('#work_region').val(obj.parents['0'].name + ' ' + obj.parents['0'].type.toLowerCase());
            if (obj.parents.length > 1) {
                obl_name += obj.parents['1'].name + ' ' + obj.parents['1'].type.toLowerCase() + ' - ';
                $('#work_region').val(obj.parents['1'].name + ' ' + obj.parents['1'].type.toLowerCase());
            }
        } else {
            obl_name = obj.name + ' - ';
            $('#work_obl_subj').val(obj.name);
            $('#work_region').val(obj.name);
        }

        $('label[for=' + work_city.attr('id') + ']').text(obl_name + obj.type);
        $('#work_okato').val(obj.okato);
        $('#work_city_type').val(obj.type);
        work_street.kladr('parentType', $.kladr.type.city);
        work_street.kladr('parentId', obj.id);
        work_street.prop("readonly", false);
        work_building.kladr('parentType', $.kladr.type.city);
        work_building.kladr('parentId', obj.id);
    };
    work_city.kladr({
        token: token,
        key: key,
        type: $.kladr.type.city,
        //withParents: true,
        verify: true,
        select: work_city_select,
        check: work_city_select,
        labelFormat: kladrCityLabelFormat
    });
    // Подключение плагина для поля ввода улицы
    work_street.kladr({
        token: token,
        key: key,
        type: $.kladr.type.street,
        verify: true,
        select: function (obj) {

            $('label[for=' + work_street.attr('id') + ']').text(obj.type);
            $('#work_street_type_long').val(obj.type);
            $('#work_street_type_short').val(obj.typeShort);
            if (obj.zip)
                $('#work_zip').val(obj.zip);
            work_building.kladr('parentType', $.kladr.type.street);
            work_building.kladr('parentId', obj.id);
            work_building.prop("readonly", false);
        },
        check: function (obj) {
            if (obj) {
                $('label[for=' + work_street.attr('id') + ']').text(obj.type);
                $('#work_street_type_long').val(obj.type);
                $('#work_street_type_short').val(obj.typeShort);
                if (obj.zip)
                    $('#work_zip').val(obj.zip);
                work_building.kladr('parentType', $.kladr.type.street);
                work_building.kladr('parentId', obj.id);
                work_building.prop("readonly", false);
            }
        }
    });
    // Подключение плагина для поля ввода номера дома
    work_building.kladr({
        token: token,
        key: key,
        type: $.kladr.type.building,
        verify: true,
        select: function (obj) {
            work_buildingAdd.prop("readonly", false);
            work_buildingFlat.prop("readonly", false);
        },
        check: function (obj) {
            work_buildingAdd.prop("readonly", false);
            work_buildingFlat.prop("readonly", false);
        }
    });

    function kladrCityLabelFormat(obj, query) {
        var label = '';

        var name = obj.name.toLowerCase();
        query = query.name.toLowerCase();

        var start = name.indexOf(query);
        start = start > 0 ? start : 0;

        if (obj.typeShort) {
            label += obj.typeShort + '. ';
        }

        if (query.length < obj.name.length) {
            label += obj.name.substr(0, start);
            label += '<strong>' + obj.name.substr(start, query.length) + '</strong>';
            label += obj.name.substr(start + query.length, obj.name.length - query.length - start);
        } else {
            label += '<strong>' + obj.name + '</strong>';
        }

        if (obj.parents) {
            for (var k = obj.parents.length - 1; k > -1; k--) {
                var parent = obj.parents[k];
                if (parent.name) {
                    if (label)
                        label += '<small>, </small>';
                    label += '<small>' + parent.name + ' ' + parent.typeShort + '.</small>';
                }
            }
        }

        return label;
    }

    function tryToggleCopyAddressButton() {
        var lIsEnabled_bl = city.val() && street.val() && building.val();
        $('#copy_address').prop("disabled", !lIsEnabled_bl);
    }

});
function copyAddress() {
    if ($('#sinc').attr("checked")) {
        $('#prog_city').val(city.val());
        $('#prog_street').val(street.val()).prop("readonly", false);
        $('#prog_home').val(building.val()).prop("readonly", false);
        $('#prog_home_build').val(buildingAdd.val()).prop("readonly", false);
        $('#prog_flat').val(buildingFlat.val()).prop("readonly", false);
        $('#prog_phone').val($('#prop_phone').val());
        $('#prog_region').val(region.val());
        $('#prog_zip').val($('#prop_zip').val());
        $('#prog_city_type').val($('#prop_city_type').val());
        $('#prog_street_type_long').val($('#prop_street_type_long').val());
        $('#prog_street_type_short').val($('#prop_street_type_short').val());
    }
}