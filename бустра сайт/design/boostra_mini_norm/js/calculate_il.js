class CalculateIL {
    constructor() {
        this.MAX_PDL_PERIOD = 16;
        this.MAX_PDL_AMOUNT = 30000;
        this.MIN_INSTL_AMOUNT = 31000;
        this.IL_INTERVAL = 14;

        this.$moneySlider = $('#money-edit');
        this.$periodSlider = $('#time-edit');
        this.$loanInfo = $('#full-loan-info');
        this.$submitBtn = $('#accept_edit_amount');

        if (this.$moneySlider.length === 0) {
            return;
        }

        this.params = calculator_il_params;
        this.minILPeriod = this.params.min_il_period || 0;
        this.periodValues = this.buildPeriodValues();
        this.basePercent = parseFloat(BASE_PERCENTS) / 100;
        this.MONTHS = [
            'января', 'февраля', 'марта', 'апреля',
            'мая', 'июня', 'июля', 'августа',
            'сентября', 'октября', 'ноября', 'декабря'
        ];
    }

    buildPeriodValues() {
        const values = [];
        const { min_period, max_period } = this.params;
        const pdlMax = Math.min(max_period, this.MAX_PDL_PERIOD);

        for (let day = min_period; day <= pdlMax; day++) {
            values.push(day);
        }

        if (max_period > this.MAX_PDL_PERIOD && this.minILPeriod > 0) {
            for (let day = this.minILPeriod; day <= max_period; day += this.IL_INTERVAL) {
                values.push(day);
            }
        }

        return values;
    }

    init() {
        this.$moneySlider.ionRangeSlider({
            type: 'single',
            min: this.params.min_amount,
            max: this.params.max_amount,
            from: this.$loanInfo.data('amount'),
            step: 1000,
            hide_min_max: true,
            postfix: ' <span>₽</span>',
            onChange: () => {
                this.checkMoneySlider();
                this.updateLoanType();
            }
        });

        this.$periodSlider.ionRangeSlider({
            type: 'single',
            values: this.periodValues,
            from: this.findPeriodIndex(this.$loanInfo.data('period')),
            hide_min_max: true,
            postfix: '',
            prettify: (value) => this.formatPeriod(parseInt(value)),
            onChange: () => {
                this.checkTimeSlider();
                this.updateLoanType();
            }
        });

        this.initPlusMinusButtons();
        this.initMinMaxLabels();
        this.initSubmitButton();
        this.updateLoanType();
        this.toggleSubmitButton();
    }

    updateLoanType() {
        const amount = parseInt(this.$moneySlider.val());
        const period = parseInt(this.$periodSlider.val());
        const formattedAmount = amount.toLocaleString('ru-RU');
        const hasPromocode = this.$loanInfo.data('promocode');

        let periodText;
        if (period > this.MAX_PDL_PERIOD) {
            const weeks = Math.floor(period / 7);
            periodText = weeks + this.pluralize(weeks, [' неделю', ' недели', ' недель']);
        } else {
            periodText = period + this.pluralize(period, [' день', ' дня', ' дней']);
        }

        const promocodeText = hasPromocode && period <= this.MAX_PDL_PERIOD ? ' (промокод)' : '';
        $('.slider-loan-type span').text(`${formattedAmount} руб. на ${periodText}${promocodeText}`);

        this.calculate();
        this.updateStyles(period);
        this.toggleSubmitButton();
    }

    calculate() {
        const amount = parseInt(this.$moneySlider.val());
        const period = parseInt(this.$periodSlider.val());
        const percent = parseFloat(this.$loanInfo.data('percent')) / 100;
        const isIL = period > this.MAX_PDL_PERIOD;

        const payDate = this.calculatePayDate(period);

        if (isIL) {
            const payment = this.calculateILPayment(amount, period);
            const formattedPayment = payment.toLocaleString('ru-RU');
            this.$loanInfo.html(`Платеж <span class="total">${formattedPayment}</span> руб. раз в 2 недели до <span class="date">${payDate}</span>`);
        } else {
            const total = Math.round(amount * period * percent + amount);
            const formattedTotal = total.toLocaleString('ru-RU');
            this.$loanInfo.html(`К возврату <span class="total">${formattedTotal}</span> руб. до <span class="date">${payDate}</span>`);
        }
    }

    calculateILPayment(amount, period) {
        const periodPercent = this.basePercent * this.IL_INTERVAL;
        const paymentsCount = period / this.IL_INTERVAL;
        const coef = Math.pow(1 + periodPercent, paymentsCount) / (Math.pow(1 + periodPercent, paymentsCount) - 1);
        return Math.ceil(amount * periodPercent * coef);
    }

    calculatePayDate(period) {
        const date = new Date();
        date.setDate(date.getDate() + period);
        return `${date.getDate()} ${this.MONTHS[date.getMonth()]}`;
    }

    updateStyles(period) {
        const isIL = period > this.MAX_PDL_PERIOD;

        if (isIL) {
            $('.irs-single, .irs-min, .irs-bar, .slider-loan-type span').removeClass('green bg-green');
            $('.irs-slider.single').css('border', '')
            this.$loanInfo.removeClass('green');
            $('.discount_title, .discount_subtitle').removeClass('green').addClass('red');
        } else {
            $('.irs-single, .irs-min, .slider-loan-type span').addClass('green');
            $('.irs-bar').addClass('bg-green');
            $('.irs-slider.single').css('border', '7px solid #2d2')
            this.$loanInfo.addClass('green');
            $('.discount_title, .discount_subtitle').addClass('green').removeClass('red');
        }
    }

    toggleSubmitButton() {
        const initAmount = parseInt(this.$moneySlider.data('init_value'));
        const initPeriod = parseInt(this.$periodSlider.data('init_value'));
        const amount = parseInt(this.$moneySlider.val());
        const period = parseInt(this.$periodSlider.val());
        const changed = amount !== initAmount || period !== initPeriod;

        this.$submitBtn.toggle(changed);
    }

    initSubmitButton() {
        this.$submitBtn.on('click', () => {
            const amount = parseInt(this.$moneySlider.val());
            const period = parseInt(this.$periodSlider.val());
            const orderId = this.$submitBtn.data('order');

            $('body').addClass('is_loading');

            $.post('/user?action=edit_amount', {
                edit_amount: amount,
                edit_period: period,
                order_id: orderId
            }).done((response) => {
                if (response.result) {
                    location.reload();
                }
            });
        });
    }

    initMinMaxLabels() {
        $('.money-edit .edit-amount-value').on('click', (e) => {
            const $label = $(e.currentTarget);
            const $parent = $label.closest('.money-edit');
            const $input = $parent.find('input');
            const slider = $input.data('ionRangeSlider');

            if (!slider) {
                return;
            }

            const isMax = $label.index() > 2;
            const newValue = isMax ? slider.options.max : slider.options.min;
            slider.update({ from: newValue });
            this.checkMoneySlider();
            this.updateLoanType();
        });

        $('.time-edit .edit-period-value').on('click', (e) => {
            const $label = $(e.currentTarget);
            const $parent = $label.closest('.time-edit');
            const $input = $parent.find('input');
            const slider = $input.data('ionRangeSlider');

            if (!slider) {
                return;
            }

            const isMax = $label.index() > 2;
            const newIndex = isMax ? this.periodValues.length - 1 : 0;
            slider.update({ from: newIndex });
            this.checkTimeSlider();
            this.updateLoanType();
        });
    }

    initPlusMinusButtons() {
        $('.ion-btn').on('click', (e) => {
            const $btn = $(e.currentTarget);
            const $parent = $btn.closest('.money-edit, .time-edit');
            const $input = $parent.find('input');
            const slider = $input.data('ionRangeSlider');

            if (!slider) {
                return;
            }

            const isPlus = $btn.hasClass('ion-plus');

            if ($parent.hasClass('money-edit')) {
                const newValue = isPlus ? slider.old_from + 1000 : slider.old_from - 1000;
                slider.update({ from: newValue });
                this.checkMoneySlider();
                this.updateLoanType();
            } else {
                const newIndex = isPlus ? slider.old_from + 1 : slider.old_from - 1;
                slider.update({ from: newIndex });
                this.checkTimeSlider();
                this.updateLoanType();
            }
        });
    }

    findPeriodIndex(value) {
        const index = this.periodValues.indexOf(value);
        return index !== -1 ? index : 0;
    }

    formatPeriod(days) {
        if (days > this.MAX_PDL_PERIOD) {
            const weeks = Math.round(days / 7);
            return weeks + this.pluralize(weeks, [' Неделя', ' Недели', ' Недель']);
        }
        return days + this.pluralize(days, [' День', ' Дня', ' Дней']);
    }

    pluralize(n, forms) {
        const cases = [2, 0, 1, 1, 1, 2];
        const index = (n % 100 > 4 && n % 100 < 20) ? 2 : cases[Math.min(n % 10, 5)];
        return forms[index];
    }

    checkMoneySlider() {
        const amount = parseInt(this.$moneySlider.val());
        const period = parseInt(this.$periodSlider.val());
        let needsUpdate = false;

        if (amount > this.MAX_PDL_AMOUNT && period <= this.MAX_PDL_PERIOD) {
            this.$periodSlider.data('ionRangeSlider').update({
                from: this.findPeriodIndex(this.minILPeriod)
            });
            needsUpdate = true;
        }
        if (amount <= this.MAX_PDL_AMOUNT && period > this.MAX_PDL_PERIOD) {
            this.$periodSlider.data('ionRangeSlider').update({
                from: this.findPeriodIndex(this.MAX_PDL_PERIOD)
            });
            needsUpdate = true;
        }

        if (needsUpdate) {
            this.updateLoanType();
        }
    }

    checkTimeSlider() {
        const amount = parseInt(this.$moneySlider.val());
        const period = parseInt(this.$periodSlider.val());
        let needsUpdate = false;

        if (period > this.MAX_PDL_PERIOD && amount <= this.MAX_PDL_AMOUNT) {
            this.$moneySlider.data('ionRangeSlider').update({
                from: this.MIN_INSTL_AMOUNT
            });
            needsUpdate = true;
        }
        if (period <= this.MAX_PDL_PERIOD && amount > this.MAX_PDL_AMOUNT) {
            this.$moneySlider.data('ionRangeSlider').update({
                from: this.MAX_PDL_AMOUNT
            });
            needsUpdate = true;
        }

        if (needsUpdate) {
            this.updateLoanType();
        }
    }
}

const calculateIL = new CalculateIL();
calculateIL.init();
