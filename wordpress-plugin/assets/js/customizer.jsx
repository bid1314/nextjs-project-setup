import React, { useState, useEffect, useCallback, createContext, useContext } from 'react';
import { render } from 'react-dom';

// Context for global state management
const CustomizerContext = createContext();
const useCustomizer = () => useContext(CustomizerContext);

// Error Boundary Component
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    console.error('Customizer Error:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
          <h3 className="text-red-800 font-semibold">Something went wrong</h3>
          <p className="text-red-600 text-sm mt-1">Please refresh the page and try again.</p>
        </div>
      );
    }
    return this.props.children;
  }
}

// Toast Notification Component
const Toast = ({ message, type, onClose }) => {
  useEffect(() => {
    const timer = setTimeout(onClose, 5000);
    return () => clearTimeout(timer);
  }, [onClose]);

  const bgColor = type === 'error' ? 'bg-red-500' : 'bg-green-500';
  
  return (
    <div className={`fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300`}>
      <div className="flex items-center justify-between">
        <span>{message}</span>
        <button onClick={onClose} className="ml-4 text-white hover:text-gray-200">
          ×
        </button>
      </div>
    </div>
  );
};

// Live Preview Component
const LivePreview = ({ garment, customization }) => {
  return (
    <div className="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 min-h-96 flex items-center justify-center">
      <div className="text-center">
        <div className="w-64 h-64 bg-white rounded-lg shadow-lg mx-auto mb-4 flex items-center justify-center">
          {garment ? (
            <div className="text-center">
              <h3 className="font-semibold text-lg mb-2">{garment.title}</h3>
              <div className="space-y-2 text-sm">
                {customization.color && (
                  <div className="flex items-center justify-center">
                    <div 
                      className="w-4 h-4 rounded-full mr-2 border"
                      style={{ backgroundColor: customization.color }}
                    ></div>
                    <span>Color: {customization.color}</span>
                  </div>
                )}
                {customization.text && (
                  <div>Text: "{customization.text}"</div>
                )}
                {customization.logo && (
                  <div>Logo: {customization.logo.name}</div>
                )}
              </div>
            </div>
          ) : (
            <div className="text-gray-500">
              <div className="w-16 h-16 bg-gray-200 rounded-lg mx-auto mb-2"></div>
              <p>Select a garment to preview</p>
            </div>
          )}
        </div>
        <p className="text-sm text-gray-600">Live Preview</p>
      </div>
    </div>
  );
};

// Color Picker Component
const ColorPicker = ({ value, onChange, label }) => {
  const colors = [
    '#000000', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF',
    '#FFFF00', '#FF00FF', '#00FFFF', '#FFA500', '#800080'
  ];

  return (
    <div className="space-y-2">
      <label className="block text-sm font-medium text-gray-700">{label}</label>
      <div className="flex flex-wrap gap-2">
        {colors.map(color => (
          <button
            key={color}
            onClick={() => onChange(color)}
            className={`w-8 h-8 rounded-full border-2 ${
              value === color ? 'border-gray-800' : 'border-gray-300'
            }`}
            style={{ backgroundColor: color }}
            title={color}
          />
        ))}
      </div>
      <input
        type="color"
        value={value || '#000000'}
        onChange={(e) => onChange(e.target.value)}
        className="w-full h-10 rounded border border-gray-300"
      />
    </div>
  );
};

// Logo Upload Component
const LogoUpload = ({ value, onChange }) => {
  const [validating, setValidating] = useState(false);
  const [validationResult, setValidationResult] = useState(null);

  const handleFileChange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    onChange(file);
    
    // Validate logo content
    setValidating(true);
    try {
      const formData = new FormData();
      formData.append('logo', file);
      
      const response = await fetch(gcSettings.restUrl + 'gc/v1/logo/validate', {
        method: 'POST',
        headers: {
          'X-WP-Nonce': gcSettings.nonce,
        },
        body: JSON.stringify({ logo_url: URL.createObjectURL(file) }),
      });
      
      const result = await response.json();
      setValidationResult(result);
    } catch (error) {
      setValidationResult({ valid: false, message: 'Validation failed' });
    } finally {
      setValidating(false);
    }
  };

  return (
    <div className="space-y-2">
      <label className="block text-sm font-medium text-gray-700">Logo Upload</label>
      <input
        type="file"
        accept="image/*"
        onChange={handleFileChange}
        className="w-full p-2 border border-gray-300 rounded"
      />
      {validating && (
        <div className="text-sm text-blue-600">Validating logo content...</div>
      )}
      {validationResult && (
        <div className={`text-sm ${validationResult.valid ? 'text-green-600' : 'text-red-600'}`}>
          {validationResult.message}
        </div>
      )}
      {value && (
        <div className="text-sm text-gray-600">
          Selected: {value.name}
        </div>
      )}
    </div>
  );
};

// Customization Panel Component
const CustomizationPanel = ({ garment, customization, onCustomizationChange }) => {
  if (!garment) {
    return (
      <div className="p-4 text-center text-gray-500">
        Select a garment to start customizing
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <h3 className="text-lg font-semibold">Customize {garment.title}</h3>
      
      <ColorPicker
        label="Garment Color"
        value={customization.color}
        onChange={(color) => onCustomizationChange('color', color)}
      />

      <div className="space-y-2">
        <label className="block text-sm font-medium text-gray-700">Custom Text</label>
        <input
          type="text"
          value={customization.text || ''}
          onChange={(e) => onCustomizationChange('text', e.target.value)}
          placeholder="Enter custom text"
          className="w-full p-2 border border-gray-300 rounded"
        />
      </div>

      <LogoUpload
        value={customization.logo}
        onChange={(logo) => onCustomizationChange('logo', logo)}
      />

      <div className="space-y-2">
        <label className="block text-sm font-medium text-gray-700">Size</label>
        <select
          value={customization.size || 'M'}
          onChange={(e) => onCustomizationChange('size', e.target.value)}
          className="w-full p-2 border border-gray-300 rounded"
        >
          <option value="XS">Extra Small</option>
          <option value="S">Small</option>
          <option value="M">Medium</option>
          <option value="L">Large</option>
          <option value="XL">Extra Large</option>
        </select>
      </div>
    </div>
  );
};

// Shopping Cart Component
const ShoppingCart = ({ cart, onClearCart }) => {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <div className="relative">
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 transition"
      >
        Cart ({cart.length})
      </button>
      
      {isOpen && (
        <div className="absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
          <div className="p-4">
            <h3 className="font-semibold mb-3">Shopping Cart</h3>
            {cart.length === 0 ? (
              <p className="text-gray-500">Your cart is empty</p>
            ) : (
              <div className="space-y-3">
                {cart.map((item, index) => (
                  <div key={index} className="border-b border-gray-100 pb-2">
                    <div className="font-medium">Garment #{item.garment_id}</div>
                    <div className="text-sm text-gray-600">
                      {item.customization.color && `Color: ${item.customization.color}`}
                      {item.customization.text && `, Text: "${item.customization.text}"`}
                    </div>
                  </div>
                ))}
                <button
                  onClick={onClearCart}
                  className="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600 transition"
                >
                  Clear Cart
                </button>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

// RFQ Modal Component
const RFQModal = ({ isOpen, onClose, onSubmit }) => {
  const [formData, setFormData] = useState({ name: '', email: '', message: '' });
  const [submitting, setSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      await onSubmit(formData);
      setFormData({ name: '', email: '', message: '' });
      onClose();
    } catch (error) {
      console.error('RFQ submission failed:', error);
    } finally {
      setSubmitting(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 className="text-lg font-semibold mb-4">Request for Quote</h3>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input
              type="text"
              required
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              className="w-full p-2 border border-gray-300 rounded"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              type="email"
              required
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              className="w-full p-2 border border-gray-300 rounded"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Message</label>
            <textarea
              required
              rows={4}
              value={formData.message}
              onChange={(e) => setFormData({ ...formData, message: e.target.value })}
              className="w-full p-2 border border-gray-300 rounded"
            />
          </div>
          <div className="flex space-x-3">
            <button
              type="submit"
              disabled={submitting}
              className="flex-1 bg-black text-white py-2 rounded hover:bg-gray-800 transition disabled:opacity-50"
            >
              {submitting ? 'Submitting...' : 'Submit'}
            </button>
            <button
              type="button"
              onClick={onClose}
              className="flex-1 bg-gray-300 text-gray-700 py-2 rounded hover:bg-gray-400 transition"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

// Main Customizer Component
const Customizer = () => {
  const [garments, setGarments] = useState([]);
  const [selectedGarment, setSelectedGarment] = useState(null);
  const [customization, setCustomization] = useState({});
  const [cart, setCart] = useState([]);
  const [loading, setLoading] = useState(false);
  const [toast, setToast] = useState(null);
  const [rfqModalOpen, setRfqModalOpen] = useState(false);

  // API Service
  const apiService = {
    async fetchGarments() {
      const response = await fetch(gcSettings.restUrl + 'gc/v1/garments', {
        headers: { 'X-WP-Nonce': gcSettings.nonce },
      });
      if (!response.ok) throw new Error('Failed to fetch garments');
      return response.json();
    },

    async updateGarment(id, data) {
      const response = await fetch(gcSettings.restUrl + 'gc/v1/garment/' + id, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': gcSettings.nonce,
        },
        body: JSON.stringify({ meta: data }),
      });
      if (!response.ok) throw new Error('Failed to update garment');
      return response.json();
    },

    async addToCart(garmentId, customizationData) {
      const response = await fetch(gcSettings.restUrl + 'gc/v1/cart/add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': gcSettings.nonce,
        },
        body: JSON.stringify({
          garment_id: garmentId,
          customization: customizationData,
        }),
      });
      if (!response.ok) throw new Error('Failed to add to cart');
      return response.json();
    },

    async getCart() {
      const response = await fetch(gcSettings.restUrl + 'gc/v1/cart', {
        headers: { 'X-WP-Nonce': gcSettings.nonce },
      });
      if (!response.ok) throw new Error('Failed to get cart');
      return response.json();
    },

    async clearCart() {
      const response = await fetch(gcSettings.restUrl + 'gc/v1/cart/clear', {
        method: 'POST',
        headers: { 'X-WP-Nonce': gcSettings.nonce },
      });
      if (!response.ok) throw new Error('Failed to clear cart');
      return response.json();
    },

    async submitRFQ(data) {
      const response = await fetch(gcSettings.restUrl + 'gc/v1/rfq/submit', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': gcSettings.nonce,
        },
        body: JSON.stringify(data),
      });
      if (!response.ok) throw new Error('Failed to submit RFQ');
      return response.json();
    },
  };

  // Load initial data
  useEffect(() => {
    const loadData = async () => {
      setLoading(true);
      try {
        const [garmentsData, cartData] = await Promise.all([
          apiService.fetchGarments(),
          apiService.getCart(),
        ]);
        setGarments(garmentsData);
        setCart(cartData);
        if (garmentsData.length > 0) {
          setSelectedGarment(garmentsData[0]);
        }
      } catch (error) {
        showToast('Failed to load data: ' + error.message, 'error');
      } finally {
        setLoading(false);
      }
    };
    loadData();
  }, []);

  const showToast = (message, type = 'success') => {
    setToast({ message, type });
  };

  const handleGarmentChange = (garmentId) => {
    const garment = garments.find(g => g.id === parseInt(garmentId));
    setSelectedGarment(garment);
    setCustomization({});
  };

  const handleCustomizationChange = (key, value) => {
    setCustomization(prev => ({ ...prev, [key]: value }));
  };

  const handleSaveCustomization = async () => {
    if (!selectedGarment) return;
    setLoading(true);
    try {
      await apiService.updateGarment(selectedGarment.id, customization);
      showToast('Customization saved successfully!');
    } catch (error) {
      showToast('Failed to save customization: ' + error.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleAddToCart = async () => {
    if (!selectedGarment) return;
    setLoading(true);
    try {
      const result = await apiService.addToCart(selectedGarment.id, customization);
      setCart(result.cart);
      showToast('Added to cart successfully!');
    } catch (error) {
      showToast('Failed to add to cart: ' + error.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleClearCart = async () => {
    setLoading(true);
    try {
      await apiService.clearCart();
      setCart([]);
      showToast('Cart cleared successfully!');
    } catch (error) {
      showToast('Failed to clear cart: ' + error.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleRFQSubmit = async (data) => {
    await apiService.submitRFQ(data);
    showToast('RFQ submitted successfully!');
  };

  if (loading && garments.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-96">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading customizer...</p>
        </div>
      </div>
    );
  }

  return (
    <CustomizerContext.Provider value={{ garments, selectedGarment, customization, cart }}>
      <div className="max-w-7xl mx-auto p-4">
        {/* Header */}
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold text-gray-900">Garment Customizer</h1>
          <div className="flex space-x-3">
            <ShoppingCart cart={cart} onClearCart={handleClearCart} />
            <button
              onClick={() => setRfqModalOpen(true)}
              className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
            >
              Request Quote
            </button>
          </div>
        </div>

        {/* Main Content */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Left Panel - Controls */}
          <div className="space-y-6">
            {/* Garment Selection */}
            <div className="bg-white p-6 rounded-lg shadow-sm border">
              <h2 className="text-xl font-semibold mb-4">Select Garment</h2>
              <select
                value={selectedGarment ? selectedGarment.id : ''}
                onChange={(e) => handleGarmentChange(e.target.value)}
                className="w-full p-3 border border-gray-300 rounded-lg"
              >
                <option value="">Choose a garment...</option>
                {garments.map(garment => (
                  <option key={garment.id} value={garment.id}>
                    {garment.title}
                  </option>
                ))}
              </select>
            </div>

            {/* Customization Panel */}
            <div className="bg-white p-6 rounded-lg shadow-sm border">
              <CustomizationPanel
                garment={selectedGarment}
                customization={customization}
                onCustomizationChange={handleCustomizationChange}
              />
            </div>

            {/* Action Buttons */}
            {selectedGarment && (
              <div className="flex space-x-3">
                <button
                  onClick={handleSaveCustomization}
                  disabled={loading}
                  className="flex-1 bg-gray-800 text-white py-3 rounded-lg hover:bg-gray-700 transition disabled:opacity-50"
                >
                  {loading ? 'Saving...' : 'Save Customization'}
                </button>
                <button
                  onClick={handleAddToCart}
                  disabled={loading}
                  className="flex-1 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition disabled:opacity-50"
                >
                  {loading ? 'Adding...' : 'Add to Cart'}
                </button>
              </div>
            )}
          </div>

          {/* Right Panel - Live Preview */}
          <div className="bg-white p-6 rounded-lg shadow-sm border">
            <h2 className="text-xl font-semibold mb-4">Live Preview</h2>
            <LivePreview garment={selectedGarment} customization={customization} />
          </div>
        </div>

        {/* RFQ Modal */}
        <RFQModal
          isOpen={rfqModalOpen}
          onClose={() => setRfqModalOpen(false)}
          onSubmit={handleRFQSubmit}
        />

        {/* Toast Notifications */}
        {toast && (
          <Toast
            message={toast.message}
            type={toast.type}
            onClose={() => setToast(null)}
          />
        )}
      </div>
    </CustomizerContext.Provider>
  );
};

// Initialize the app
document.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('gc-customizer-root');
  if (root) {
    render(
      <ErrorBoundary>
        <Customizer />
      </ErrorBoundary>,
      root
    );
  }
});
