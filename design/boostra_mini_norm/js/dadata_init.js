function formatResult(item, short_value) {
    let _block = '',
        c = "(" + short_value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + ")",
        item_value = item.value.replace(RegExp(c, "gi"), "<strong>$1</strong>")

    _block += '<span>' + item_value + '</span>';
    return _block;
}

$(document).ready( function() {
    const regBlock = document.querySelector('.register')
    const faktBlock = document.querySelector('.living')

    const regRegion = document.querySelector('#Regregion')
    const faktRegion = document.querySelector('#Faktregion')

    const regCity = document.querySelector('[name="Regcity"]')
    const faktCity = document.querySelector('[name="Faktcity"]')

    const regStreet = document.querySelector('[name="Regstreet"]')
    const faktStreet = document.querySelector('[name="Faktstreet"]')

    const regHousing = document.querySelector('[name="Reghousing"]')
    const faktHousing = document.querySelector('[name="Fakthousing"]')

    const regBuilding = document.querySelector('[name="Regbuilding"]')
    const faktBuilding = document.querySelector('[name="Faktbuilding"]')

    const regRoom = document.querySelector('[name="Regroom"]')
    const faktRoom = document.querySelector('[name="Faktroom"]')

    const equalButton = document.querySelector('#equal')

    const inputs = [
        regRegion,
        faktRegion,

        regCity,
        faktCity,

        regStreet,
        faktStreet,

        regHousing,
        faktHousing,

        regBuilding,
        faktBuilding,

        regRoom,
        faktRoom,
    ]

    function getFloatingLabelElement(target) {
        if (target === null || target.labels === null) {
            return null
        }

        return target.labels[0].querySelector('.floating-label:not(.select-from-list-hint)')
    }

    function refreshFloatingLabels() {

        for (let input of inputs) {

            if (input === null) {
                continue
            }

            const floatingLabel = getFloatingLabelElement(input)

            if (floatingLabel === null) {
                continue
            }

            resetFloatingLabelClasses(floatingLabel)

            input.value ? floatingLabel.classList.add('label-top') : floatingLabel.classList.add('label-default')
        }
    }

    function resetFloatingLabelClasses(floatingLabel) {
        floatingLabel.classList.remove('label-top-small')
        floatingLabel.classList.remove('label-top')
        floatingLabel.classList.remove('label-default')
    }

    class UserAddress {
        init() {
            refreshFloatingLabels()

            this.addInputsHandlersForFloatingLabels()

            this.disableLatinLettersInInputs()
            this.disableLettersInInputs()

            this.addSelectHintHandlers()

            this.addEqualButtonHandlers()

            this.addRegRegionHandlers()
            this.addFaktRegionHandlers()

            this.addRegCityHandlers()
            this.addFaktCityHandlers()

            this.addRegStreetHandlers()
            this.addFaktStreetHandlers()
        }

        addInputsHandlersForFloatingLabels() {
            for (let input of inputs) {

                if (input === null) {
                    continue
                }

                input.addEventListener('focus', () => {
                    const floatingLabel = getFloatingLabelElement(input)

                    if (floatingLabel === null) {
                        return
                    }

                    resetFloatingLabelClasses(floatingLabel)
                    floatingLabel.classList.add('label-top-small')
                })

                input.addEventListener('focusout', () => {
                    const floatingLabel = getFloatingLabelElement(input)

                    if (floatingLabel === null) {
                        return
                    }

                    resetFloatingLabelClasses(floatingLabel)

                    input.value ? floatingLabel.classList.add('label-top') : floatingLabel.classList.add('label-default')
                })
            }
        }

        disableLatinLettersInInputs() {
            for (let input of inputs) {

                if (input === null) {
                    continue
                }

                input.addEventListener('input', event => {
                    event.target.value = event.target.value.replace(/[A-Za-z]/g, '')
                })
            }
        }

        disableLettersInInputs() {
            const inputs = [
                regHousing,
                faktHousing,

                regBuilding,
                faktBuilding,

                regRoom,
                faktRoom,
            ];

            for (let input of inputs) {

                if (input === null) {
                    continue
                }

                input.addEventListener('input', event => {
                    event.target.value = event.target.value.replace(/[^0-9]/g, '')
                })
            }
        }

        addSelectHintHandlers() {
            const inputs = [
                regCity,
                faktCity,
            ];

            for (let input of inputs) {

                if (input === null) {
                    continue
                }

                input.addEventListener('input', event => {
                    const {target} = event
                    const existingHintElement = document.querySelector('.select-from-list-hint')

                    if (target.value.length >= 2) {
                        const newHintElement = `<span class="floating-label select-from-list-hint">Выберите из списка</span>`
                    } else if (existingHintElement) {
                        existingHintElement.remove()
                    }
                })

                input.addEventListener('focusout', () => {
                    document.querySelector('.select-from-list-hint')?.remove()
                })
            }
        }

        addEqualButtonHandlers() {
            equalButton?.addEventListener('change', () => {
                refreshFloatingLabels()
            })
        }

        addRegRegionHandlers() {
            regRegion?.addEventListener('change', () => {
                regBlock.querySelectorAll('.floating-label')
                    .forEach(element => element.style.removeProperty('top'))

                regBlock.querySelectorAll('.floating-label')
                    .forEach(element => element.style.removeProperty('top'))

                regCity.value = ''
                regCity.removeAttribute('readonly')
                regCity.setAttribute('data-selected', '0')

                regStreet.value = ''
                regStreet.removeAttribute('readonly')
                regStreet.setAttribute('data-selected', '0')

                regHousing.value = ''
                regHousing.removeAttribute('readonly')
                regHousing.setAttribute('data-selected', '0')

                regBuilding.value = ''
                regRoom.value = ''

                document.querySelector('.regcity-close')?.remove()
                document.querySelector('.regstreet-close')?.remove()
                document.querySelector('.reghousing-close')?.remove()

                refreshFloatingLabels()
            })
        }

        addFaktRegionHandlers() {
            faktRegion?.addEventListener('change', () => {
                faktBlock.querySelectorAll('.floating-label')
                    .forEach(element => element.style.removeProperty('top'))

                faktBlock.querySelectorAll('.floating-label')
                    .forEach(element => element.style.removeProperty('top'))

                faktCity.value = ''
                faktCity.removeAttribute('readonly')
                faktCity.setAttribute('data-selected', '0')

                faktStreet.value = ''
                faktStreet.removeAttribute('readonly')
                faktStreet.setAttribute('data-selected', '0')

                faktHousing.value = ''
                faktHousing.removeAttribute('readonly')
                faktHousing.setAttribute('data-selected', '0')

                faktBuilding.value = ''
                faktRoom.value = ''

                document.querySelector('.faktcity-close')?.remove()
                document.querySelector('.faktstreet-close')?.remove()
                document.querySelector('.fakthousing-close')?.remove()

                refreshFloatingLabels()
            })
        }

        addRegCityHandlers() {
            $(regCity).on('blur', function () {
                if (Number($(this).attr('data-selected')) !== 1) {
                    $(this).val('');
                }
            })

            $(regCity).autocomplete({
                serviceUrl: 'ajax/dadata.php?action=only_city',
                minChars: 2,
                params: {
                    'fias_id': function () {
                        return regRegion.dataset?.fias_id || '';
                    },
                    'region': function () {
                        return regRegion.value || '';
                    }
                },
                onSelect: (item) => {
                    let city = this.getCity(item)

                    $(regCity)
                        .attr('data-fias_id', '')
                        .attr('data-selected', 1)
                        .attr('value', city)
                        .attr('readonly', 'readonly');

                    $("#regcity-label").children(".floating-label").attr('id', 'disabled-regcity-label')
                    $(regCity).parent().append(`<span class="regcity-close" style="position:absolute;top:21px;right:40px;cursor:pointer;font-size: 18px;">X</span>`);
                    if (!!item.data.postal_code) {
                        $('[name="Regindex"]').val(item.data.postal_code)
                    }
                    $('[name="Regcity_shorttype"]').val(item.data.city_type);
                    $('[name="Regregion_shorttype"]').val(item.data.region_type);
                    regRegion.dataset.fias_id = ''
                    refreshFloatingLabels()
                },
                formatResult: formatResult
            })

            $(document).on('click', '.regcity-close', () => {
                $(regCity)
                    .attr('data-selected', 0)
                    .val("")
                $(regCity).removeAttr('readonly')
                $('.regcity-close').remove()

                getFloatingLabelElement(regCity)?.style.removeProperty('top')
            })
        }

        getCity(item) {
            if (item.data.city && item.data.settlement) {
                return [item.data.city, item.data.settlement].join(' ');
            }

            if (item.data.settlement) {
                return item.data.settlement;
            }

            if (item.data.city) {
                return item.data.city;
            }

            return item.value;
        }

        addFaktCityHandlers() {
            $(faktCity).on('blur', function () {
                if (Number($(this).attr('data-selected')) !== 1) {
                    $(this).val('');
                }
            })

            $(faktCity).autocomplete({
                serviceUrl: 'ajax/dadata.php?action=only_city',
                minChars: 2,
                params: {
                    'fias_id': function () {
                        return faktRegion.dataset?.fias_id || '';
                    },
                    'region': function () {
                        return faktRegion.value || '';
                    }
                },
                onSelect: (item) => {
                    let city = this.getCity(item)

                    $(faktCity)
                        .attr('data-fias_id', '')
                        .attr('data-selected', 1)
                        .attr('value', city)
                        .attr('readonly', 'readonly');
                    $("#faktcity-label").children(".floating-label").attr('id', 'disabled-faktcity-label')
                    $(faktCity).parent().append(`<span class="faktcity-close" style="position:absolute;top:21px;right:40px;cursor:pointer;font-size: 18px;">X</span>`);
                    if (!!item.data.postal_code) {
                        $('[name="Faktindex"]').val(item.data.postal_code)
                    }
                    $('[name="Faktcity_shorttype"]').val(item.data.city_type);
                    $('[name="Faktregion_shorttype"]').val(item.data.region_type);
                    faktRegion.dataset.fias_id = ''

                    refreshFloatingLabels()
                },
                formatResult: formatResult
            })

            $(document).on('click', '.faktcity-close', () => {
                $(faktCity)
                    .attr('data-selected', 0)
                    .val("")
                $(faktCity).removeAttr('readonly')
                $('.faktcity-close').remove()

                getFloatingLabelElement(faktCity)?.style.removeProperty('top')
            })
        }

        addRegStreetHandlers() {
            $(regStreet).autocomplete({
                serviceUrl: 'ajax/dadata.php?action=street',
                minChars: 3,
                params: {
                    'fias_id': function () {
                        return regCity.dataset.fias_id || '';
                    },
                    'region': function () {
                        return regRegion.value || '';
                    },
                    'city': function () {
                        return regCity.value
                    }
                },
                onSelect: function (item) {
                    $(regStreet)
                        .attr('data-fias_id', item.data.fias_id)
                        .attr('data-selected', 1)
                        .attr('value', item.data.street)
                        .attr('readonly', 'readonly');

                    $("#regstreet-label").children(".floating-label").attr('id', 'disabled-regstreet-label')
                    $(regStreet).parent().append(`<span class="regstreet-close" style="position:absolute;top:21px;right:40px;cursor:pointer;font-size: 18px;">X</span>`);
                    if (!!item.data.postal_code) {
                        $('[name="Regindex"]').val(item.data.postal_code)
                    }
                    $('[name="Regstreet_shorttype"]').val(item.data.street_type);

                    refreshFloatingLabels()
                },
                formatResult: formatResult
            });

            $(document).on('click', '.regstreet-close', () => {
                $(regStreet)
                    .attr('data-selected', 0)
                    .val("")
                $(regStreet).removeAttr('readonly')
                $('.regstreet-close').remove()

                getFloatingLabelElement(regStreet)?.style.removeProperty('top')
            })
        }

        addFaktStreetHandlers() {
            $(faktStreet).autocomplete({
                serviceUrl: 'ajax/dadata.php?action=street',
                minChars: 3,
                params: {
                    'fias_id': function () {
                        return faktCity.dataset.fias_id || '';
                    },
                    'region': function () {
                        return faktRegion.value || '';
                    },
                    'city': function () {
                        return faktCity.value
                    }
                },
                onSelect: function (item) {
                    $(faktStreet)
                        .attr('data-fias_id', item.data.fias_id)
                        .attr('data-selected', 1)
                        .attr('value', item.data.street)
                        .attr('readonly', 'readonly');
                    $("#faktstreet-label").children(".floating-label").attr('id', 'disabled-faktstreet-label')
                    $(faktStreet).parent().append(`<span class="faktstreet-close" style="position:absolute;top:21px;right:40px;cursor:pointer;font-size: 18px;">X</span>`);
                    if (!!item.data.postal_code) {
                        $('[name="Faktindex"]').val(item.data.postal_code)
                    }
                    $('[name="Faktstreet_shorttype"]').val(item.data.street_type);

                    refreshFloatingLabels()
                },
                formatResult: formatResult
            });

            $(document).on('click', '.faktstreet-close', () => {
                $(faktStreet)
                    .attr('data-selected', 0)
                    .val("")
                $(faktStreet).removeAttr('readonly')
                $('.faktstreet-close').remove()

                getFloatingLabelElement(faktStreet)?.style.removeProperty('top')
            })
        }

        addRegHousingHandlers() {
            $(regHousing).autocomplete({
                serviceUrl: 'ajax/dadata.php?action=house',
                params: {
                    'fias_id': function () {
                        return $(regStreet).attr('data-fias_id') || '';
                    },
                    'input': 'Reghousing'
                },
                onSelect: function (item) {
                    $(regHousing)
                        .attr('data-fias_id', item.data.fias_id)
                        .attr('data-selected', 1)
                        .attr('value', item.data.house)
                        .attr('readonly', 'readonly');
                    $("#reghousing-label").children(".floating-label").attr('id', 'disabled-reghousing-label')
                    $(regHousing).parent().append(`<span class="reghousing-close" style="position:absolute;top:0;right:0;cursor:pointer">X</span>`);
                    if (!!item.data.postal_code) {
                        $('[name="Regindex"]').val(item.data.postal_code)
                    }

                    refreshFloatingLabels()
                },
                formatResult: formatResult

            });

            $(document).on('click', '.reghousing-close', () => {
                $(regHousing)
                    .attr('data-selected', 0)
                    .val("")
                $(regHousing).removeAttr('readonly')
                $('.reghousing-close').remove()

                getFloatingLabelElement(regHousing)?.style.removeProperty('top')
            })
        }

        addFaktHousingHandlers() {
            $(faktHousing).autocomplete({
                serviceUrl: 'ajax/dadata.php?action=house',
                params: {
                    'fias_id': function () {
                        return $(faktStreet).attr('data-fias_id') || '';
                    },
                    'input': 'Fakthousing'
                },
                onSelect: function (item) {
                    $(faktHousing)
                        .attr('data-fias_id', item.data.fias_id)
                        .attr('data-selected', 1)
                        .attr('value', item.data.house)
                        .attr('readonly', 'readonly');
                    $("#fakthousing-label").children(".floating-label").attr('id', 'disabled-fakthousing-label')
                    $(faktHousing).parent().append(`<span class="fakthousing-close" style="position:absolute;top:0;right:0;cursor:pointer">X</span>`);
                    if (!!item.data.postal_code) {
                        $('[name="Faktindex"]').val(item.data.postal_code)
                    }

                    refreshFloatingLabels()
                },
                formatResult: formatResult
            });

            $(document).on('click', '.fakthousing-close', () => {
                $(faktHousing)
                    .attr('data-selected', 0)
                    .val("")
                $(faktHousing).removeAttr('readonly')
                $('.fakthousing-close').remove()

                getFloatingLabelElement(faktHousing)?.style.removeProperty('top')
            })
        }
    }

    const userAddress = new UserAddress()
    userAddress.init()
})