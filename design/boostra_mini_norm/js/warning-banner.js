class WarningBanner {
    constructor(config) {
        this.config = config;
        this.storageKey = 'warning_banner_hidden_' + (config.site_id || 'global');
        this.banner = null;
        this.init();
    }
    
    init() {
        if (!this.shouldShow()) return;
        this.render();
    }
    
    shouldShow() {
        if (!this.config.enabled) return false;
        
        if (this.config.show_from_timestamp) {
            const serverTime = window.serverTimeMsk || Date.now();
            if (serverTime < this.config.show_from_timestamp) {
                return false;
            }
        }
        
        const isMainPage = window.location.pathname === '/' || window.location.pathname === '/init_user';
        
        if (!this.config.show_on_main_page && isMainPage) {
            return false;
        }
        
        if (this.config.timeout?.enabled) {
            const hiddenData = localStorage.getItem(this.storageKey);
            if (hiddenData) {
                try {
                    const { timestamp, minutes } = JSON.parse(hiddenData);
                    const minutesPassed = (Date.now() - timestamp) / (1000 * 60);
                    if (minutesPassed < minutes) {
                        return false;
                    }
                } catch (e) {
                    localStorage.removeItem(this.storageKey);
                }
            }
        }
        
        return true;
    }

    convertUrlsToLinks(text) {
      text = text
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');

        const urlRegex = /(https?:\/\/[^\s]+|[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.[a-zA-Z]{2,}(?::[0-9]+)?(?:\/[^\s]*)?)/g;

        return text.replace(urlRegex, (url) => {
            const href = url.startsWith('http://') || url.startsWith('https://')
                ? url
                : `http://${url}`;

            return `<a href="${href}" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">${url}</a>`;
        });
    }
    
    render() {
        this.banner = document.createElement('div');
        this.banner.className = `warning-banner warning-banner-${this.config.position} warning-banner-${this.config.style}`;
        
        const isMobile = window.innerWidth < 768;
        const isSidePosition = this.config.position === 'right' || this.config.position === 'left';
        const bannerContent = document.createElement('div');
        const closeButtonWidth = this.config.closeable ? (isMobile ? 40 : 50) : 0;
        
        let paddingStyle = '';
        if (closeButtonWidth > 0) {
            if (isSidePosition && this.config.position === 'right') {
                paddingStyle = `padding-left: ${closeButtonWidth}px;`;
            } else {
                paddingStyle = `padding-right: ${closeButtonWidth}px;`;
            }
        }
        
        bannerContent.style.cssText = `
            position: relative;
            width: 100%;
            ${isSidePosition ? '' : 'max-width: 1200px; margin: 0 auto;'}
            ${paddingStyle}
            box-sizing: border-box;
        `;
        bannerContent.innerHTML = this.convertUrlsToLinks(this.config.message);
        
        if (isSidePosition) {
            const links = bannerContent.querySelectorAll('a');
            links.forEach(link => {
                link.style.wordBreak = 'break-all';
                link.style.overflowWrap = 'break-word';
            });
        }
        
        if (this.config.closeable) {
            const closeButton = document.createElement('button');
            closeButton.innerHTML = '×';
            const buttonSize = isMobile ? 28 : 32;
            const buttonOffset = isMobile ? '10px' : '15px';
            
            let buttonPosition = '';
            if (isSidePosition) {
                if (this.config.position === 'left') {
                    buttonPosition = `top: ${buttonOffset}; right: ${buttonOffset};`;
                } else {
                    buttonPosition = `top: ${buttonOffset}; left: ${buttonOffset};`;
                }
            } else {
                buttonPosition = `top: 50%; right: ${buttonOffset}; transform: translateY(-50%);`;
            }
            
            closeButton.style.cssText = `
                position: absolute;
                ${buttonPosition}
                background: transparent;
                border: none;
                color: inherit;
                font-size: ${isMobile ? '20px' : '24px'};
                line-height: 1;
                cursor: pointer;
                padding: 0;
                width: ${buttonSize}px;
                height: ${buttonSize}px;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0.8;
                z-index: 1;
            `;
            closeButton.addEventListener('mouseenter', () => {
                closeButton.style.opacity = '1';
            });
            closeButton.addEventListener('mouseleave', () => {
                closeButton.style.opacity = '0.8';
            });
            closeButton.addEventListener('click', () => {
                this.hide();
            });
            bannerContent.appendChild(closeButton);
        }
        
        this.banner.appendChild(bannerContent);
        
        const styles = isMobile ? this.config.mobile : this.config.desktop;
        
        Object.assign(this.banner.style, {
            backgroundColor: styles.background_color,
            color: styles.text_color,
            fontSize: styles.font_size,
            fontWeight: styles.font_weight || 'normal',
            padding: isMobile ? (styles.padding || '10px 15px') : (styles.padding || '12px 20px'),
            borderRadius: isSidePosition && !isMobile ? '20px' : (styles.border_radius || '4px'),
            position: 'fixed',
            zIndex: 9999,
            textAlign: 'center',
            boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
            boxSizing: 'border-box'
        });
        
        if (isSidePosition) {
            Object.assign(this.banner.style, {
                width: isMobile ? '280px' : '450px',
                maxWidth: '90vw',
                top: '20px',
                bottom: 'auto',
                left: this.config.position === 'left' ? '20px' : 'auto',
                right: this.config.position === 'right' ? '20px' : 'auto'
            });
        } else {
            Object.assign(this.banner.style, {
                left: 0,
                right: 0,
                width: '100%'
            });
        }
        
        if (this.config.animation === 'slide') {
            if (isSidePosition) {
                this.banner.style.transform = this.config.position === 'right' ? 'translateX(100%)' : 'translateX(-100%)';
            } else {
                this.banner.style.transform = this.config.position === 'top' ? 'translateY(-100%)' : 'translateY(100%)';
            }
            this.banner.style.transition = 'transform 0.3s ease';
        } else if (this.config.animation === 'fade') {
            this.banner.style.opacity = '0';
            this.banner.style.transition = 'opacity 0.3s ease';
        }
        
        document.body.appendChild(this.banner);
        
        WarningBanner.recalculateBannerPositions();
        
        setTimeout(() => {
            if (this.config.animation === 'slide') {
                this.banner.style.transform = 'translate(0, 0)';
            } else if (this.config.animation === 'fade') {
                this.banner.style.opacity = '1';
            }
            WarningBanner.recalculateBannerPositions();
        }, 10);
    }
    
    static recalculateBannerPositions() {
        const topBanners = Array.from(document.querySelectorAll('.warning-banner-top'));
        let currentTop = 0;
        
        topBanners.forEach(banner => {
            banner.style.top = `${currentTop}px`;
            currentTop += banner.offsetHeight;
        });
        
        const bottomBanners = Array.from(document.querySelectorAll('.warning-banner-bottom'));
        let currentBottom = 0;
        
        bottomBanners.forEach(banner => {
            banner.style.bottom = `${currentBottom}px`;
            currentBottom += banner.offsetHeight;
        });
        
        const rightBanners = Array.from(document.querySelectorAll('.warning-banner-right'));
        let currentRightTop = 20;
        
        rightBanners.forEach(banner => {
            banner.style.top = `${currentRightTop}px`;
            currentRightTop += banner.offsetHeight + 10;
        });
        
        const leftBanners = Array.from(document.querySelectorAll('.warning-banner-left'));
        let currentLeftTop = 20;
        
        leftBanners.forEach(banner => {
            banner.style.top = `${currentLeftTop}px`;
            currentLeftTop += banner.offsetHeight + 10;
        });
        
        const totalTopHeight = topBanners.reduce((sum, b) => sum + b.offsetHeight, 0);
        const totalBottomHeight = bottomBanners.reduce((sum, b) => sum + b.offsetHeight, 0);
        
        document.body.style.paddingTop = totalTopHeight > 0 ? `${totalTopHeight}px` : '';
        document.body.style.paddingBottom = totalBottomHeight > 0 ? `${totalBottomHeight}px` : '';
    }
    
    hide() {
        if (this.config.timeout?.enabled && this.config.timeout?.minutes) {
            localStorage.setItem(this.storageKey, JSON.stringify({
                timestamp: Date.now(),
                minutes: this.config.timeout.minutes
            }));
        } else {
            localStorage.removeItem(this.storageKey);
        }
        
        if (this.banner) {
            if (this.config.animation === 'slide') {
                if (this.banner.classList.contains('warning-banner-top')) {
                    this.banner.style.transform = 'translateY(-100%)';
                } else if (this.banner.classList.contains('warning-banner-bottom')) {
                    this.banner.style.transform = 'translateY(100%)';
                } else if (this.banner.classList.contains('warning-banner-right')) {
                    this.banner.style.transform = 'translateX(100%)';
                } else if (this.banner.classList.contains('warning-banner-left')) {
                    this.banner.style.transform = 'translateX(-100%)';
                }
            } else if (this.config.animation === 'fade') {
                this.banner.style.opacity = '0';
            }
            
            const animationDuration = this.config.animation === 'none' ? 0 : 300;
            setTimeout(() => {
                this.banner.remove();
                WarningBanner.recalculateBannerPositions();
            }, animationDuration);
        }
    }
}

if (window.settings) {
    if (window.settings.automation_fail && window.settings.automation_fail.enabled) {
        new WarningBanner(window.settings.automation_fail);
    }
    
    if (window.settings.site_warning_banner_config) {
        try {
            const config = JSON.parse(window.settings.site_warning_banner_config);
            if (config.enabled) {
                new WarningBanner(config);
            }
        } catch (e) {
            console.error('Error parsing banner config:', e);
        }
    }
}
