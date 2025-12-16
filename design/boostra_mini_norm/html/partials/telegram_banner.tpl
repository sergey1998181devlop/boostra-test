<div class="telegram_banner">
    <div class="telegram_banner_background"></div>
    <div class="telegram_banner_icon">
        <svg width="220" viewBox="0 0 423 228" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M421.241 24.2645L357.49 250.506C352.677 266.471 340.138 270.445 322.318 262.928L225.176
            209.062L178.309 242.99C173.119 246.896 168.788 250.154 158.784 250.154L165.771 175.713L345.797
            53.2984C353.628 48.0526 344.091 45.1344 333.635 50.3916L111.073 155.854L15.2585 133.281C-5.57932
            128.387 -5.95654 117.6 19.6041 110.072L394.368 1.4189C411.72 -3.47496 426.899 4.31435 421.241 24.2645Z"
                fill="#038AEE"
                fill-opacity="0.1">
            </path>
        </svg>
    </div>
    <div class="telegram_banner_info">
        {$tg_banner_text}
        <a href="https://t.me/boostra_helpbot_bot?start=phone-{$phone}_source-{$source}"
           target="_blank"
           class="telegram_banner_button">Подписаться</a>
    </div>
</div>

<style>
    .telegram_banner_background {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0.1;
        z-index: 0;
        background: #038AEE;
    }
    .telegram_banner {
        position: relative;
        border: 1px solid #038AEE;
        border-radius: 20px;
        text-align: left;
        padding: 25px;
        overflow: hidden;
        width: 450px;
        margin: {$margin};
        z-index: 0;
    }
    .telegram_banner_icon {
        position: absolute;
        right: 25px;
        top: 20px;
    }
    .telegram_banner_info {
        z-index: 0;
        position: relative;
    }
    .telegram_banner_button {
        padding: 10px;
        text-align: center;
        display: block;
        background: #038AEE;
        border-radius: 20px;
        color: #fff;
        font-weight: bold;
        margin-top: 15px;
        text-decoration: none !important;
    }
    @media (max-width: 767px) {
        .telegram_banner {
            width: auto !important;
        }
    }
</style>