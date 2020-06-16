const path = require('path');

module.exports = (eZConfig, eZConfigManager) => {
    eZConfigManager.add({
        eZConfig,
        entryName: 'ezplatform-admin-ui-content-type-edit-js',
        newItems: [path.resolve(__dirname, '../public/js/scripts/admin.contenttype.queryfield.js')],
    });
};

