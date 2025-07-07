jQuery(document).ready(function($) {
    // Initialize color pickers
    if ($.fn.wpColorPicker) {
        $('.gc-color-picker').wpColorPicker();
    }

    // Layers Management
    var layersContainer = $('#gc-layers-container');
    var layersData = [];
    
    try {
        layersData = JSON.parse($('#gc_layers').val() || '[]');
    } catch (e) {
        console.error('Error parsing layers data:', e);
        layersData = [];
    }

    function updateLayersTextarea() {
        $('#gc_layers').val(JSON.stringify(layersData, null, 2));
    }

    function addLayer(layer = { name: '', type: 'color', position: 0 }) {
        var index = layersData.length;
        var template = `
            <div class="gc-layer-item" data-index="${index}">
                <div class="gc-layer-header">
                    <span class="gc-layer-drag-handle dashicons dashicons-move"></span>
                    <input type="text" 
                           class="gc-layer-name" 
                           value="${layer.name}" 
                           placeholder="Layer Name"
                           data-field="name">
                    <select class="gc-layer-type" data-field="type">
                        <option value="color" ${layer.type === 'color' ? 'selected' : ''}>Color</option>
                        <option value="text" ${layer.type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="logo" ${layer.type === 'logo' ? 'selected' : ''}>Logo</option>
                    </select>
                    <button type="button" class="button gc-remove-layer">Remove</button>
                </div>
                <div class="gc-layer-content">
                    ${layer.type === 'color' ? renderColorOptions(layer) : ''}
                    ${layer.type === 'text' ? renderTextOptions(layer) : ''}
                    ${layer.type === 'logo' ? renderLogoOptions(layer) : ''}
                </div>
            </div>
        `;
        layersContainer.append(template);
        layersData.push(layer);
        updateLayersTextarea();
    }

    function renderColorOptions(layer) {
        return `
            <div class="gc-layer-options">
                <label>Default Color:</label>
                <input type="text" 
                       class="gc-color-picker" 
                       value="${layer.defaultColor || '#000000'}"
                       data-field="defaultColor">
            </div>
        `;
    }

    function renderTextOptions(layer) {
        return `
            <div class="gc-layer-options">
                <label>Default Text:</label>
                <input type="text" 
                       value="${layer.defaultText || ''}"
                       placeholder="Enter default text"
                       data-field="defaultText">
                <label>Font Size (px):</label>
                <input type="number" 
                       value="${layer.fontSize || '16'}"
                       min="8"
                       max="72"
                       data-field="fontSize">
            </div>
        `;
    }

    function renderLogoOptions(layer) {
        return `
            <div class="gc-layer-options">
                <label>Max Width (px):</label>
                <input type="number" 
                       value="${layer.maxWidth || '200'}"
                       min="50"
                       max="1000"
                       data-field="maxWidth">
                <label>Max Height (px):</label>
                <input type="number" 
                       value="${layer.maxHeight || '200'}"
                       min="50"
                       max="1000"
                       data-field="maxHeight">
            </div>
        `;
    }

    // Add Layer Button
    $('.gc-add-layer').on('click', function() {
        addLayer();
    });

    // Remove Layer
    layersContainer.on('click', '.gc-remove-layer', function() {
        var layerItem = $(this).closest('.gc-layer-item');
        var index = layerItem.data('index');
        layersData.splice(index, 1);
        layerItem.remove();
        updateLayersTextarea();
    });

    // Layer Type Change
    layersContainer.on('change', '.gc-layer-type', function() {
        var layerItem = $(this).closest('.gc-layer-item');
        var index = layerItem.data('index');
        var type = $(this).val();
        
        layersData[index].type = type;
        
        var content = '';
        switch(type) {
            case 'color':
                content = renderColorOptions(layersData[index]);
                break;
            case 'text':
                content = renderTextOptions(layersData[index]);
                break;
            case 'logo':
                content = renderLogoOptions(layersData[index]);
                break;
        }
        
        layerItem.find('.gc-layer-content').html(content);
        if (type === 'color') {
            layerItem.find('.gc-color-picker').wpColorPicker();
        }
        updateLayersTextarea();
    });

    // Update layer data on input change
    layersContainer.on('change', 'input, select', function() {
        var layerItem = $(this).closest('.gc-layer-item');
        var index = layerItem.data('index');
        var field = $(this).data('field');
        
        if (field) {
            layersData[index][field] = $(this).val();
            updateLayersTextarea();
        }
    });

    // Make layers sortable
    if ($.fn.sortable) {
        layersContainer.sortable({
            handle: '.gc-layer-drag-handle',
            update: function() {
                var newLayersData = [];
                layersContainer.find('.gc-layer-item').each(function() {
                    var index = $(this).data('index');
                    newLayersData.push(layersData[index]);
                });
                layersData = newLayersData;
                updateLayersTextarea();
            }
        });
    }

    // Initialize existing layers
    layersData.forEach(function(layer) {
        addLayer(layer);
    });

    // Colors Management
    var colorsContainer = $('#gc-colors-container');
    var colorsData = [];
    
    try {
        colorsData = JSON.parse($('#gc_colors').val() || '[]');
    } catch (e) {
        console.error('Error parsing colors data:', e);
        colorsData = [];
    }

    function updateColorsTextarea() {
        $('#gc_colors').val(JSON.stringify(colorsData, null, 2));
    }

    function addColor(color = '#000000') {
        var index = colorsData.length;
        var template = `
            <div class="gc-color-item" data-index="${index}">
                <input type="text" class="gc-color-picker" value="${color}">
                <button type="button" class="button gc-remove-color">Remove</button>
            </div>
        `;
        colorsContainer.append(template);
        colorsData.push(color);
        colorsContainer.find('.gc-color-picker').last().wpColorPicker({
            change: function(event, ui) {
                var colorItem = $(this).closest('.gc-color-item');
                var index = colorItem.data('index');
                colorsData[index] = ui.color.toString();
                updateColorsTextarea();
            }
        });
        updateColorsTextarea();
    }

    // Add Color Button
    $('.gc-add-color').on('click', function() {
        addColor();
    });

    // Remove Color
    colorsContainer.on('click', '.gc-remove-color', function() {
        var colorItem = $(this).closest('.gc-color-item');
        var index = colorItem.data('index');
        colorsData.splice(index, 1);
        colorItem.remove();
        updateColorsTextarea();
    });

    // Initialize existing colors
    colorsData.forEach(function(color) {
        addColor(color);
    });
});
