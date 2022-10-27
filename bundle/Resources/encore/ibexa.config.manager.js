const path = require('path');

module.exports = (ibexaConfig, ibexaConfigManager) => {
    ibexaConfigManager.add({
        ibexaConfig,
        entryName: 'ibexa-admin-ui-content-type-edit-js',
        newItems: [path.resolve(__dirname, '../public/js/scripts/admin.contenttype.ngenhancedlink.default.location.js')],
    });

    ibexaConfigManager.add({
        ibexaConfig,
        entryName: 'ibexa-admin-ui-content-edit-parts-js',
        newItems: [path.resolve(__dirname, '../public/js/scripts/fieldType/ngenhancedlink.js')],
    });

    ibexaConfigManager.add({
        ibexaConfig,
        entryName: 'ibexa-admin-ui-content-edit-parts-css',
        newItems: [path.resolve(__dirname, '../public/scss/fieldType/edit/_ngenhancedlink.scss')],
    });
    //
    // ibexaConfigManager.add({
    //     ibexaConfig,
    //     entryName: 'ibexa-admin-ui-layout-css',
    //     newItems: [path.resolve(__dirname, '../public/scss/matrix-content-type.scss')],
    // });
};
