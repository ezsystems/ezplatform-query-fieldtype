const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('ezcontentquery-css', [
        path.resolve(__dirname, '../public/scss/ezplatform.scss'),
    ]);
};
