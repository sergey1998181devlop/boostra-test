export default class Configs {
    constructor() {
        this.dev = !!window.siteConfig.js_config_is_dev;
    }
}
