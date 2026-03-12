document.addEventListener('alpine:init', () => {
    Alpine.data('calculator', (config) => ({
        isOrganic: config.isOrganic || false,
        isDeveloper: config.isDeveloper || false,
        useSamePage: config.useSamePage || false,
        activeSlide: 0,
        // Изменяем объявление amount
        _amount: 30000, // Приватное свойство для хранения значения
        _period: 16,
        percent: 0,
        infoMessage: '\u00A0',
        showFloatingBtn: false,
        configs: {
            physical: {
                sum: { min: 1000, max: 50000, step: 1000, initial: 30000 },
                period: { min: 5, max: 16, step: 1, initial: 16 },
                percent: 0.8,
                title: 'Первый заём <b><a href="/files/docs/polozhenie0.pdf" target="_blank">бесплатно*</a></b>',
                sumLabels: '<span>1 000</span><span>50 000</span>',
                periodLabels: '<span>5</span><span>16</span>',
                periodUnit: ' дней'
            },
            physicalOrganic: {
                sum: { min: 1000, max: 50000, step: 1000, initial: 30000 },
                period: { min: 5, max: 30, step: 1, initial: 16 },
                percent: 0.8,
                title: 'Первый заём <b><a href="/files/docs/polozhenie0.pdf" target="_blank">бесплатно*</a></b>',
                sumLabels: '<span>1 000</span><span>50 000</span>',
                periodLabels: '<span>5</span><span>30</span>',
                periodUnit: ' дней'
            },
            legalEntity: {
                sum: { min: 50000, max: 500000, step: 10000, initial: 500000 },
                period: { min: 9, max: 26, step: 1, initial: 26 },
                percent: 1.85,
                title: 'Онлайн заём от <b>1.85%</b>',
                sumLabels: '<span>50 000</span><span>500 000</span>',
                periodLabels: '<span>9 недель</span><span>26 недель</span>',
                periodUnit: ' недель'
            }
        },
        // Геттер для amount с parseInt
        get amount() {
            return parseInt(this._amount, 10) || 0;
        },
        get period() {
            return parseInt(this._period) || 0;
        },
        get currentConfig() {
            if (!this.isOrganic) {
                return this.configs.physical;
            }
            return this.isIP ? this.configs.legalEntity : this.configs.physicalOrganic;
        },
        get sumConfig() {
            return this.currentConfig.sum;
        },
        get periodConfig() {
            return this.currentConfig.period;
        },
        get title() {
            return this.currentConfig.title;
        },
        get sumLabels() {
            return this.currentConfig.sumLabels;
        },
        get periodLabels() {
            return this.currentConfig.periodLabels;
        },
        get periodUnit() {
            return this.currentConfig.periodUnit;
        },
        /**
         * Отображение процента
         * @returns {number|*|number}
         */
        get displayPercent() {
            if (this.amount === 30000 && !this.isIP) {
                return 0;
            }

            return this.currentConfig.percent;
        },
        get showWithoutCoeff() {
            if (this.activeSlide === 1) {
                return false;
            }
            return this.amount >= 30000;
        },
        // Показывать блок "Готовый график" при sum > 30000 (для обоих режимов)
        get showReadyPlan() {
            // Для ИП + органика не показываем готовый график
            if (this.isIP) {
                return false;
            }
            return this.amount > 30000;
        },
        // Показывать стандартный блок (вы вернете) статистики при sum <= 30000 и не ИП
        get showStatBlock() {
            if (this.isIP) {
                return false;
            }
            return this.amount <= 30000;
        },
        // Показывать ползунок срока для органики при sum <= 30000
        get showPeriodSlider() {
            if (!this.isOrganic) {
                return false;
            }
            // Для ИП всегда показываем
            if (this.isIP) {
                return true;
            }
            // Для физ. лиц органики — только при sum <= 30000
            return this.amount <= 30000;
        },
        /**
         * Если это вкладка ИП
         * @returns {boolean}
         */
        get isIP() {
            return this.activeSlide === 1;
        },
        get total() {
            const amount = parseInt(this.amount, 10) || 0;
            const period = parseInt(this.period, 10) || 0;

            if (!this.isIP && amount === 30000) {
                return 30000;
            }

            const prc = this.percent;
            return amount + Math.round(amount * period * (prc / 100));
        },
        // Сумма платежа для ИЛ займа, 2 раза в неделю
        get paymentAmount() {
            const amount = parseInt(this.amount, 10) || 0;
            if (amount <= 30000) return 0;
            return this.calcBiweeklyPayment(amount);
        },
        // Расчёт аннуитетного платежа по формуле из ТЗ
        calcBiweeklyPayment(P) {
            const r = 0.008; // Ежедневная ставка 0.8%
            const d = 14; // Период 14 дней
            const n = P > 5000 ? 12 : 6;
            // Периодная ставка за 14 дней
            const i = Math.pow(1 + r, d) - 1;
            // Аннуитетный платёж
            return Math.round(P * (i * Math.pow(1 + i, n)) / (Math.pow(1 + i, n) - 1));
        },
        // Дата последнего платежа
        get lastPaymentDate() {
            const amount = parseInt(this.amount, 10) || 0;
            if (amount <= 30000) return ''; // На всякий случай заглушка

            const n = this.getPaymentsCount(amount);
            const totalDays = n * 14; // Каждый платёж раз в 14 дней, тут считаем сколько нам дней доступно

            const today = new Date();
            const lastDate = new Date(today.getTime() + totalDays * 24 * 60 * 60 * 1000);

            return this.formatDate(lastDate);
        },
        // Получить число платежей
        getPaymentsCount(P) {
            return (P / 6) <= 5000 ? 6 : 12;
        },
        // Сеттер для amount
        set amount(value) {
            this._amount = parseInt(value, 10) || 0;
        },
        set period(value) {
            this._period = parseInt(value) || 0;
        },
        formatDate(date) {
            const day = date.getDate();
            const monthNames = [
                'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
                'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
            ];
            const month = monthNames[date.getMonth()];
            const year = date.getFullYear();
            return day + ' ' + month + ' ' + year + ' года';
        },

        init() {
            this.applyConfig();
            this.setupFloatingBtnVisibility();

            // Экспорт submitOrder в глобальную область для вызова из других мест
            window.submitOrder = () => this.submitOrder();
        },

        setupFloatingBtnVisibility() {
            const checkVisibility = () => {
                const heroBtn = document.getElementById('hero-btn');
                const heroRange = document.getElementById('hero-range');

                if (!heroBtn || !heroRange) return;

                const rectBtn = heroBtn.getBoundingClientRect();
                const rectRange = heroRange.getBoundingClientRect();
                const windowHeight = window.innerHeight || document.documentElement.clientHeight;

                const isVisibleBtn = rectBtn.top >= 0 && rectBtn.bottom <= windowHeight;
                const isVisibleRange = rectRange.top >= 0 && rectRange.bottom <= windowHeight;

                const isMobile = Math.min(screen.width, screen.height) <= 768;

                const popup = document.getElementById('inactive-user-popup');
                const isPopupVisible = popup && popup.style.display === 'flex';

                if (!isMobile || isPopupVisible) {
                    this.showFloatingBtn = false;
                    return;
                }

                this.showFloatingBtn = !(isVisibleBtn || isVisibleRange);
            };

            document.addEventListener('scroll', checkVisibility);
            window.addEventListener('load', checkVisibility);
            window.addEventListener('resize', checkVisibility);
            window.addEventListener('orientationchange', () => setTimeout(checkVisibility, 150));

            // Initial check
            checkVisibility();
        },
        applyConfig() {
            const cfg = this.currentConfig;
            this.amount = cfg.sum.initial;
            this.period = cfg.period.initial;
            this.percent = cfg.percent;
            this.infoMessage = '\u00A0';
        },
        setSlide(id) {
            if (this.activeSlide === id) return;
            this.activeSlide = id;
            this.applyConfig();
        },
        onSumChange() {
            this.infoMessage = '\u00A0'
        },
        onPeriodChange(el) {
            // Если не ИЛ, то не позволяем сделать период больше
            if (!this.isIP && this.amount <= 30000 && this.period > 16) {
                this._period = 16;
                this.infoMessage = 'Займы на срок более 16 суток доступны со второго займа'
            } else {
                this.infoMessage = '\u00A0'
            }
        },
        formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        },
        sliderStyle(value, min, max) {
            const prc = ((value - min) / (max - min)) * 100;
            return 'background-size: ' + prc + '% 100%';
        },
        submitOrder() {
            const amount = parseInt(this.amount, 10);
            const period = parseInt(this.period, 10);
            const isLegalEntity = this.isOrganic && this.activeSlide === 1;

            // Отправка метрики (если не developer mode)
            if (!this.isDeveloper && typeof sendMetric === 'function') {
                sendMetric('reachGoal', 'main_page_get_zaim_new_design2');
            }

            if (isLegalEntity) {
                const redirectUrl = 'https://freecapital.ru/external?amount=' + amount + '&period=' + period + '&utm_source=boostra_calc';
                if (this.useSamePage) {
                    window.location.href = redirectUrl;
                } else {
                    window.open(redirectUrl, '_blank');
                    if (typeof clickHunter === 'function') {
                        clickHunter(3);
                    }
                }
            } else {
                const url = '/init_user?amount=' + amount + '&period=' + period;
                if (this.useSamePage) {
                    window.location.href = url;
                } else {
                    window.open(url, '_blank');
                    if (typeof clickHunter === 'function') {
                        clickHunter(3);
                    }
                }
            }
        }
    }));
});
