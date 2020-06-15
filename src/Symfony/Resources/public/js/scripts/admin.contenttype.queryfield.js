(function(global, doc) {
    const SELECTOR_CONTAINER = '.ezcontentquery-settings.options';
    const SELECTOR_UI_SWITCHER = '.ezcontentquery-settings-toggle-ui-config';
    const SELECTOR_CONFIG_UI = '.ezcontentquery-settings-query-configuration-ui';
    const SELECTOR_CONFIG_TEXT = '.ezcontentquery-settings-query-configuration-text';

    const toggleConfig = (container, selector, displayed) => {
        const config = container.querySelector(selector);
        if (displayed ===  true) {
            config.style.removeProperty('display');
        } else {
            config.style.setProperty('display', 'none');
        }
    };

    const toggleUi = (event) => {
        const checkbox = event.target;
        const isUiEnabled = checkbox.closest('.ez-data-source__label').classList.toggle('is-checked', checkbox.checked);
        console.log("isUiEnabled", isUiEnabled);
        toggleConfig(checkbox.closest(SELECTOR_CONTAINER), SELECTOR_CONFIG_UI, isUiEnabled);
        toggleConfig(checkbox.closest(SELECTOR_CONTAINER), SELECTOR_CONFIG_TEXT, !isUiEnabled);
    };

    const optionsContainers = doc.querySelectorAll(SELECTOR_CONTAINER);
    optionsContainers.forEach((optionsContainer) => {
        const uiSwitcher = optionsContainer.querySelector(SELECTOR_UI_SWITCHER);
        uiSwitcher.querySelector('input[type=checkbox]').addEventListener('change', toggleUi);
    });
})(window, window.document);
