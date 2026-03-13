/* jshint esversion: 8 */
class SiteSettingsApp {
    constructor() {
        this.initBannerColorSync();
    }

    initBannerColorSync() {
        const syncColorPair = (picker, text) => {
            if (picker && text) {
                picker.addEventListener('input', () => {
                    text.value = picker.value;
                });
                text.addEventListener('input', () => {
                    const value = text.value.trim();
                    if (/^#[0-9A-Fa-f]{6}$/i.test(value)) {
                        picker.value = value;
                    }
                });
            }
        };

        syncColorPair(document.querySelector('.banner-desktop-bg-color'), document.querySelector('.banner-desktop-bg-text'));
        syncColorPair(document.querySelector('.banner-desktop-text-color'), document.querySelector('.banner-desktop-text-text'));
        syncColorPair(document.querySelector('.banner-mobile-bg-color'), document.querySelector('.banner-mobile-bg-text'));
        syncColorPair(document.querySelector('.banner-mobile-text-color'), document.querySelector('.banner-mobile-text-text'));

        const styleSelect = document.getElementById('banner_style');
        const customSettings = document.querySelectorAll('.banner-custom-settings');

        const toggleCustomSettings = () => {
            const style = styleSelect.value;
            const isCustom = style === 'custom';

            customSettings.forEach(container => {
                container.style.display = isCustom ? 'block' : 'none';
            });
        };

        if (styleSelect) {
            toggleCustomSettings();

            styleSelect.addEventListener('change', () => {
                const style = styleSelect.value;
                const styleColors = {
                    'info': {bg: '#2196F3', text: '#ffffff'},
                    'warning': {bg: '#FF9800', text: '#ffffff'},
                    'error': {bg: '#F44336', text: '#ffffff'},
                    'success': {bg: '#4CAF50', text: '#ffffff'}
                };

                toggleCustomSettings();

                if (style !== 'custom' && styleColors[style]) {
                    const colors = styleColors[style];
                    const desktopBgPicker = document.querySelector('.banner-desktop-bg-color');
                    const desktopBgText = document.querySelector('.banner-desktop-bg-text');
                    const desktopTextPicker = document.querySelector('.banner-desktop-text-color');
                    const desktopTextText = document.querySelector('.banner-desktop-text-text');
                    const mobileBgPicker = document.querySelector('.banner-mobile-bg-color');
                    const mobileBgText = document.querySelector('.banner-mobile-bg-text');
                    const mobileTextPicker = document.querySelector('.banner-mobile-text-color');
                    const mobileTextText = document.querySelector('.banner-mobile-text-text');

                    if (desktopBgPicker) desktopBgPicker.value = colors.bg;
                    if (desktopBgText) desktopBgText.value = colors.bg;
                    if (desktopTextPicker) desktopTextPicker.value = colors.text;
                    if (desktopTextText) desktopTextText.value = colors.text;
                    if (mobileBgPicker) mobileBgPicker.value = colors.bg;
                    if (mobileBgText) mobileBgText.value = colors.bg;
                    if (mobileTextPicker) mobileTextPicker.value = colors.text;
                    if (mobileTextText) mobileTextText.value = colors.text;
                }
            });
        }

        const closeableSelect = document.getElementById('banner_closeable');
        const timeoutSettings = document.querySelector('.banner-timeout-settings');

        const toggleTimeoutSettings = () => {
            if (closeableSelect && timeoutSettings) {
                timeoutSettings.style.display = closeableSelect.value === '1' ? 'block' : 'none';
            }
        };

        if (closeableSelect) {
            toggleTimeoutSettings();
            closeableSelect.addEventListener('change', toggleTimeoutSettings);
        }

        const timeoutEnabledSelect = document.getElementById('banner_timeout_enabled');
        const timeoutMinutesRow = document.getElementById('banner_timeout_minutes_row');

        const toggleTimeoutMinutesRow = () => {
            if (timeoutEnabledSelect && timeoutMinutesRow) {
                timeoutMinutesRow.style.display = timeoutEnabledSelect.value === '1' ? 'block' : 'none';
            }
        };

        if (timeoutEnabledSelect) {
            toggleTimeoutMinutesRow();
            timeoutEnabledSelect.addEventListener('change', toggleTimeoutMinutesRow);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new SiteSettingsApp();
});
