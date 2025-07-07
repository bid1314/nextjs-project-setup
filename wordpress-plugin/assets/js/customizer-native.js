jQuery(document).ready(function($) {
    // Cache DOM elements
    const $container = $('.gc-customizer-container');
    const $previewCanvas = $('.gc-preview-canvas');
    const $layersList = $('.gc-layers-list');
    const $priceAmount = $('.gc-price-amount');
    
    // State management
    let customizationState = {
        layers: {},
        totalPrice: parseFloat($priceAmount.text()) || 0
    };

    // Layer management
    $('.gc-layer-control').each(function() {
        const $control = $(this);
        const layerId = $control.data('layer-id');
        
        // Initialize layer state
        customizationState.layers[layerId] = {
            type: $control.find('.gc-layer').data('layer-type'),
            content: '',
            style: {}
        };

        // Color picker handling
        $control.find('.gc-color-option').on('click', function() {
            const color = $(this).data('color');
            updateLayer(layerId, { style: { backgroundColor: color } });
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });

        // Text input handling
        $control.find('.gc-text-input').on('input', function() {
            const text = $(this).val();
            updateLayer(layerId, { content: text });
        });

        // Font controls
        $control.find('.gc-font-family').on('change', function() {
            updateLayer(layerId, { style: { fontFamily: $(this).val() } });
        });

        $control.find('.gc-font-size').on('input', function() {
            updateLayer(layerId, { style: { fontSize: $(this).val() + 'px' } });
        });

        $control.find('.gc-text-align button').on('click', function() {
            const align = $(this).data('align');
            updateLayer(layerId, { style: { textAlign: align } });
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });

        // Logo upload handling
        $control.find('.gc-logo-input').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    updateLayer(layerId, { 
                        content: e.target.result,
                        style: { backgroundImage: `url(${e.target.result})` }
                    });
                };
                reader.readAsDataURL(file);

                // Trigger logo validation
                validateLogo(file);
            }
        });
    });

    // Update layer in preview
    function updateLayer(layerId, updates) {
        const $layer = $previewCanvas.find(`[data-layer-id="${layerId}"]`);
        const layerState = customizationState.layers[layerId];

        // Update state
        if (updates.content !== undefined) {
            layerState.content = updates.content;
        }
        if (updates.style) {
            layerState.style = { ...layerState.style, ...updates.style };
        }

        // Update DOM
        if (layerState.type === 'text') {
            $layer.find('.gc-text-layer').text(layerState.content);
        } else if (layerState.type === 'logo') {
            $layer.find('.gc-logo-layer').css('background-image', layerState.style.backgroundImage);
        }

        // Apply styles
        $layer.css(layerState.style);

        // Save state
        saveCustomizationState();
    }

    // Logo validation
    function validateLogo(file) {
        const formData = new FormData();
        formData.append('action', 'gc_validate_logo');
        formData.append('logo', file);
        formData.append('nonce', gcCustomizer.nonce);

        $.ajax({
            url: gcCustomizer.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (!response.success) {
                    alert(response.data.message);
                }
            }
        });
    }

    // Save customization state
    function saveCustomizationState() {
        $.ajax({
            url: gcCustomizer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'gc_save_customization',
                nonce: gcCustomizer.nonce,
                garment_id: gcCustomizer.garmentId,
                state: JSON.stringify(customizationState)
            },
            success: function(response) {
                if (!response.success) {
                    console.error('Failed to save customization state:', response);
                }
            }
        });
    }

    // Add to cart handling
    $('.gc-add-to-cart').on('click', function() {
        $.ajax({
            url: gcCustomizer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'gc_add_to_cart',
                nonce: gcCustomizer.nonce,
                garment_id: gcCustomizer.garmentId,
                customization: JSON.stringify(customizationState)
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.cart_url;
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Request quote handling
    $('.gc-request-quote').on('click', function() {
        $.ajax({
            url: gcCustomizer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'gc_request_quote',
                nonce: gcCustomizer.nonce,
                garment_id: gcCustomizer.garmentId,
                customization: JSON.stringify(customizationState)
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.quote_url;
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Make layers draggable in preview
    $previewCanvas.find('.gc-layer').draggable({
        containment: 'parent',
        stop: function(event, ui) {
            const layerId = $(this).data('layer-id');
            updateLayer(layerId, {
                style: {
                    left: ui.position.left + 'px',
                    top: ui.position.top + 'px'
                }
            });
        }
    });

    // Make layers resizable
    $previewCanvas.find('.gc-layer').resizable({
        containment: 'parent',
        handles: 'all',
        stop: function(event, ui) {
            const layerId = $(this).data('layer-id');
            updateLayer(layerId, {
                style: {
                    width: ui.size.width + 'px',
                    height: ui.size.height + 'px'
                }
            });
        }
    });
});
