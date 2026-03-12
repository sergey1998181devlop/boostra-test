{* Findzen banner: simple image link *}
<style>
    .findzen-banner-link {
        display: block;
        max-width: 712px;
        margin: 16px 0;
    }
    .findzen-banner-image {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 12px;
    }
</style>

<a href="{$findzen_url}" target="_blank" rel="noopener" class="findzen-banner-link">
    <img
        class="findzen-banner-image"
        src="/design/{$settings->theme|escape}/img/banners/findzen_get_plan.webp"
        alt="ФинДзен — получить план"
    >
</a>
<br>
