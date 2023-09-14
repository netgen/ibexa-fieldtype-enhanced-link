jQuery(function($) {
    // Get references to all select elements with the specified name attribute
    const allowedLinkTypesSelects = $('select[name^="ContentClass_ngenhancedlink_link_type"]');

    function handleAllowedLinkTypesChange(selectElement) {
        const allowedLinkType = $(selectElement).find('option:selected'); // $(this) refers to the current select element
        const templateId = allowedLinkType.data('template-id');
        const internalLinkOptionsDiv = $(`.internal-link-options-block-${templateId}`);
        const externalLinkOptionsDiv = $(`.external-link-options-block-${templateId}`);
        if (selectElement.val() === '0') {
            internalLinkOptionsDiv.show();
            externalLinkOptionsDiv.show();
        } else if (selectElement.val() === '1') {
            internalLinkOptionsDiv.show();
            externalLinkOptionsDiv.hide();
        } else if (selectElement.val() === '2') {
            internalLinkOptionsDiv.hide();
            externalLinkOptionsDiv.show();
        }
    }

    // Loop through each select element and log a message
    allowedLinkTypesSelects.each(function(index) {
        // Initial call to set the initial state for each select element
        handleAllowedLinkTypesChange($(this));

        // Listen for changes in each select element
        $(this).on('change', function() {
            handleAllowedLinkTypesChange($(this));
        });
    });
});

jQuery(function($) {
    // Get references to all select elements with the specified name attribute
    const allowedLinkTypesSelects = $('select[name^="ContentClass_ngenhancedlink_link_type"]');

    function handleAllowedLinkTypesChange(selectElement) {
        const allowedLinkType = $(selectElement).find('option:selected'); // $(this) refers to the current select element
        const templateId = allowedLinkType.data('template-id');
        const internalLinkOptionsDiv = $(`.internal-link-block-${templateId}`);
        const externalLinkOptionsDiv = $(`.external-link-block-${templateId}`);
        if (selectElement.val() === '0') {
            internalLinkOptionsDiv.show();
            externalLinkOptionsDiv.hide();
        } else if (selectElement.val() === '1') {
            internalLinkOptionsDiv.hide();
            externalLinkOptionsDiv.show();
        }
    }

    // Loop through each select element and log a message
    allowedLinkTypesSelects.each(function(index) {
        // Initial call to set the initial state for each select element
        handleAllowedLinkTypesChange($(this));

        // Listen for changes in each select element
        $(this).on('change', function() {
            handleAllowedLinkTypesChange($(this));
        });
    });
});
