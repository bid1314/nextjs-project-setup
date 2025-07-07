/**
 * Garment Customizer JavaScript
 * 
 * Handles all interactive functionality for the garment customizer interface.
 */

(function($) {
    'use strict';

    // Customizer Class
    class GarmentCustomizer {
        constructor() {
            this.container = $('#gc-customizer');
            this.preview = this.container.find('.gc-preview');
            this.layers = this.container.find('.gc-preview__layers');
            this.controls = this.container.find('.gc-customizer__controls');
            
            this.garmentId = this.preview.data('garment-id');
            this.currentLayer = null;
            this.customizations = {};
            
            this.init();
        }

        init() {
            this.initLayerControls();
            this.initColorPicker();
            this.initTextControls();
            this.initLogoUpload();
            this.initDraggable();
            this.initActions();
            this.loadSavedState();
        }

        initLayerControls() {
            const self = this;

            // Layer visibility toggle
            this.container.on('click', '.gc-layer-item__visibility', function(e) {
                const layerId = $(this).closest('.gc-layer-item').data('layer-id');
                const layer = self.preview.find(`[data-layer-id="${layerId}"]`);
                
                $(this).toggleClass('is-hidden');
                layer.toggle();
                
                self.updateCustomizations(layerId, {
                    visible: !$(this).hasClass('is-hidden')
                });
            });

            // Layer selection
            this.container.on('click', '.gc-layer-item', function(e) {
                if (!$(e.target).is('.gc-layer-item__visibility')) {
                    const layerId = $(this).data('layer-id');
                    self.selectLayer(layerId);
                }
            });
        }

        initColorPicker() {
            const self = this;

            this.container.on('click', '.gc-color-picker__option', function() {
                const color = $(this).data('color');
                const layerId = self.currentLayer;

                $(this)
                    .addClass('is-active')
                    .siblings()
                    .removeClass('is-active');

                self.updateLayer(layerId, {
                    backgroundColor: color
                });

                self.updateCustomizations(layerId, {
                    color: color
                });
            });
        }

        initTextControls() {
            const self = this;

            // Text input
            this.container.on('input', '.gc-text-input__field', function() {
                const text = $(this).val();
                const layerId = self.currentLayer;

                self.updateLayer(layerId, {
                    text: text
                });

                self.updateCustomizations(layerId, {
                    text: text
                });
            });

            // Font controls
            this.container.on('change', '.gc-text-control__select', function() {
                const property = $(this).data('property');
                const value = $(this).val();
                const layerId = self.currentLayer;

                self.updateLayer(layerId, {
                    [property]: value
                });

                self.updateCustomizations(layerId, {
                    [property]: value
                });
            });
        }

        initLogoUpload() {
            const self = this;

            this.container.on('click', '.gc-logo-upload__button', function() {
                const frame = wp.media({
                    title: GC_DATA.i18n.selectLogo,
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });

                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    const layerId = self.currentLayer;

                    self.updateLayer(layerId, {
                        backgroundImage: `url(${attachment.url})`
                    });

                    self.updateCustomizations(layerId, {
                        logo: attachment.url,
                        logoId: attachment.id
                    });

                    // Update preview
                    self.container
                        .find('.gc-logo-upload__preview')
                        .addClass('has-logo')
                        .css('background-image', `url(${attachment.url})`);
                });

                frame.open();
            });
        }

        initDraggable() {
            const self = this;

            this.preview.find('.gc-preview__layer').draggable({
                containment: 'parent',
                stop: function(event, ui) {
                    const layerId = $(this).data('layer-id');
                    const position = {
                        x: (ui.position.left / self.preview.width()) * 100,
                        y: (ui.position.top / self.preview.height()) * 100
                    };

                    self.updateCustomizations(layerId, {
                        position: position
                    });
                }
            });
        }

        initActions() {
            const self = this;

            // Add to cart
            this.container.on('click', '.gc-customizer__add-to-cart', function() {
                self.addToCart();
            });

            // Request quote
            this.container.on('click', '.gc-customizer__request-quote', function() {
                self.requestQuote();
            });

            // Reset customizations
            this.container.on('click', '.gc-customizer__reset', function() {
                self.resetCustomizations();
            });
        }

        selectLayer(layerId) {
            this.currentLayer = layerId;
            
            this.container
                .find('.gc-layer-item')
                .removeClass('is-active');
                
            this.container
                .find(`.gc-layer-item[data-layer-id="${layerId}"]`)
                .addClass('is-active');

            this.preview
                .find('.gc-preview__layer')
                .removeClass('is-active');
                
            this.preview
                .find(`.gc-preview__layer[data-layer-id="${layerId}"]`)
                .addClass('is-active');
        }

        updateLayer(layerId, properties) {
            const layer = this.preview.find(`[data-layer-id="${layerId}"]`);
            layer.css(properties);
        }

        updateCustomizations(layerId, data) {
            if (!this.customizations[layerId]) {
                this.customizations[layerId] = {};
            }

            this.customizations[layerId] = {
                ...this.customizations[layerId],
                ...data
            };

            this.saveState();
            this.updatePrice();
        }

        saveState() {
            if (typeof Storage !== 'undefined') {
                localStorage.setItem(
                    `gc_customizations_${this.garmentId}`,
                    JSON.stringify(this.customizations)
                );
            }
        }

        loadSavedState() {
            if (typeof Storage !== 'undefined') {
                const saved = localStorage.getItem(`gc_customizations_${this.garmentId}`);
                if (saved) {
                    this.customizations = JSON.parse(saved);
                    this.applyCustomizations();
                }
            }
        }

        applyCustomizations() {
            const self = this;
            
            Object.keys(this.customizations).forEach(layerId => {
                const data = this.customizations[layerId];
                
                if (data.color) {
                    self.updateLayer(layerId, {
                        backgroundColor: data.color
                    });
                }
                
                if (data.text) {
                    self.updateLayer(layerId, {
                        text: data.text
                    });
                }
                
                if (data.logo) {
                    self.updateLayer(layerId, {
                        backgroundImage: `url(${data.logo})`
                    });
                }
                
                if (data.position) {
                    self.updateLayer(layerId, {
                        left: `${data.position.x}%`,
                        top: `${data.position.y}%`
                    });
                }
                
                if (typeof data.visible !== 'undefined') {
                    const layer = self.preview.find(`[data-layer-id="${layerId}"]`);
                    data.visible ? layer.show() : layer.hide();
                }
            });

            this.updatePrice();
        }

        updatePrice() {
            let total = parseFloat(GC_DATA.basePrice);

            Object.keys(this.customizations).forEach(layerId => {
                const layer = this.preview.find(`[data-layer-id="${layerId}"]`);
                const type = layer.data('layer-type');

                if (type === 'color') {
                    total += parseFloat(GC_DATA.prices.color);
                } else if (type === 'text') {
                    total += parseFloat(GC_DATA.prices.text);
                } else if (type === 'logo') {
                    total += parseFloat(GC_DATA.prices.logo);
                }
            });

            this.container
                .find('.gc-customizer__price-amount')
                .text(GC_DATA.formatPrice(total));
        }

        addToCart() {
            const self = this;

            $.ajax({
                url: GC_DATA.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'gc_add_to_cart',
                    nonce: GC_DATA.nonce,
                    garment_id: this.garmentId,
                    customizations: this.customizations
                },
                beforeSend: function() {
                    self.container.addClass('is-loading');
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.cart_url;
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(GC_DATA.i18n.errorMessage);
                },
                complete: function() {
                    self.container.removeClass('is-loading');
                }
            });
        }

        requestQuote() {
            const self = this;

            $.ajax({
                url: GC_DATA.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'gc_request_quote',
                    nonce: GC_DATA.nonce,
                    garment_id: this.garmentId,
                    customizations: this.customizations
                },
                beforeSend: function() {
                    self.container.addClass('is-loading');
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.quote_url;
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(GC_DATA.i18n.errorMessage);
                },
                complete: function() {
                    self.container.removeClass('is-loading');
                }
            });
        }

        resetCustomizations() {
            this.customizations = {};
            this.saveState();
            
            // Reset UI
            this.preview.find('.gc-preview__layer').removeAttr('style');
            this.container.find('.gc-layer-item__visibility').removeClass('is-hidden');
            this.container.find('.gc-color-picker__option').removeClass('is-active');
            this.container.find('.gc-text-input__field').val('');
            this.container.find('.gc-logo-upload__preview').removeClass('has-logo').removeAttr('style');
            
            this.updatePrice();
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#gc-customizer').length) {
            new GarmentCustomizer();
        }
    });

})(jQuery);
