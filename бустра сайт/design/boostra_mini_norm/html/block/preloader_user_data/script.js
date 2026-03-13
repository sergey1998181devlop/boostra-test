class DataAnalysisSlider {
    constructor() {
        this.currentSlide = 0;
        this.slides = document.querySelectorAll('.slide');
        this.progressFill = document.querySelector('.progress-fill');
        this.totalSlides = this.slides.length;
        this.isTransitioning = false;
        this.timerInterval = null;

        this.init();
    }
    
    init() {
        this.updateProgress();
        this.startTimer(); // Запускаем таймер автоматически
    }

    checkScorings(isTimeOut = false) {
        $.ajax({
            url: '/ajax/check_scorings_nk.php',
            data: {
                action: 'check',
                timeout: isTimeOut
            },
            success: function (data) {
                let result = data.result;
                if (result.ready) {
                    $.removeCookie('bnn-timer');
                    location.reload();
                }
            }
        });
    }

    // Добавляем метод для таймера
    startTimer() {
        const TIMER_INIT = 180;
        let totalTime = $.cookie('bnn-timer') ?? TIMER_INIT;
        if (totalTime > TIMER_INIT) totalTime = TIMER_INIT;

        this.timerInterval = setInterval(() => {
            totalTime--;

            if (totalTime >= 0) {
                if (totalTime >= 10) {
                    $.cookie('bnn-timer', totalTime);
                }

                if (totalTime > 0 && totalTime % 10 === 0) {
                    this.checkScorings();
                    this.nextSlide(); // Используем метод класса
                }
            } else {
                clearInterval(this.timerInterval);
                this.checkScorings(true);
            }
        }, 1000);
    }
    
    nextSlide() {
        if (this.isTransitioning) return;
        
        let nextSlide = (this.currentSlide + 1) % this.totalSlides;
        if (nextSlide > this.totalSlides) {
            nextSlide = 0;
        }
        this.goToSlide(nextSlide);
    }
    
    previousSlide() {
        if (this.isTransitioning) return;
        
        const prevSlide = this.currentSlide === 0 ? this.totalSlides - 1 : this.currentSlide - 1;
        this.goToSlide(prevSlide);
    }
    
    goToSlide(slideIndex) {
        if (this.isTransitioning || slideIndex === this.currentSlide) return;
        
        this.isTransitioning = true;
        
        // Добавляем анимацию выхода
        this.slides[this.currentSlide].classList.add('fade-out');
        
        setTimeout(() => {
            // Убираем активный класс с текущего слайда
            this.slides[this.currentSlide].classList.remove('active', 'fade-out');
            
            // Устанавливаем новый слайд
            this.currentSlide = slideIndex;
            
            // Добавляем активный класс новому слайду
            this.slides[this.currentSlide].classList.add('active', 'fade-in');
            
            // Обновляем прогресс
            this.updateProgress();
            
            // Убираем анимацию входа
            setTimeout(() => {
                this.slides[this.currentSlide].classList.remove('fade-in');
                this.isTransitioning = false;
            }, 600);
            
        }, 300);
    }

    updateProgress() {
        const progress = ((this.currentSlide + 1) / this.totalSlides) * 100;
        this.progressFill.style.width = `${progress}%`;
        
        // Добавляем эффект пульсации для прогресса
        this.progressFill.style.animation = 'none';
        setTimeout(() => {
            this.progressFill.style.animation = '';
        }, 10);
    }
}

// Дополнительные эффекты и утилиты
class SliderEffects {
    constructor() {
        this.initLoadingAnimations();
    }
    
    initLoadingAnimations() {
        // Анимация появления элементов при загрузке
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s ease-out';
                }
            });
        });
        
        document.querySelectorAll('.card, .indicator, .nav-btn').forEach(el => {
            observer.observe(el);
        });
    }
}

// CSS анимации для эффектов
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .slide.active .card {
        animation: cardAppear 0.8s ease-out;
    }
    
    @keyframes cardAppear {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    .indicator.active {
        animation: indicatorPulse 0.3s ease-out;
    }
    
    @keyframes indicatorPulse {
        from {
            transform: scale(1);
        }
        to {
            transform: scale(1.2);
        }
    }
    
    @keyframes buttonBounce {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
    }
`;
document.head.appendChild(style);

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    const slider = new DataAnalysisSlider();
    const effects = new SliderEffects();
    
    // Добавляем обработчик изменения размера окна
    window.addEventListener('resize', () => {
        // Обновляем позиционирование при изменении размера
        slider.updateProgress();
    });
    
    // Предотвращаем скролл страницы при использовании слайдера
    document.addEventListener('wheel', (e) => {
        if (e.target.closest('.slider-container')) {
            e.preventDefault();
        }
    }, { passive: false });
    
    console.log('Слайдер анализа данных инициализирован!');
});

// Экспорт для возможного внешнего использования
window.DataAnalysisSlider = DataAnalysisSlider;
window.inactivityPopupEnabled = false;
