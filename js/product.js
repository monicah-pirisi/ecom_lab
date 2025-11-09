/**
 * Product Management JavaScript
 * Handles AJAX operations for product CRUD functionality with bulk image upload
 */

// Global variables
let editMode = false;
let editProductId = null;
let selectedFiles = [];
const CSRF_TOKEN = document.querySelector('input[name="csrf_token"]').value;

/**
 * Validate product form data
 * @param {FormData} formData - Form data to validate
 * @returns {Object} - Validation result {valid: boolean, errors: array}
 */
function validateProductData(formData) {
    const errors = [];
    const title = formData.get('product_title');
    const cat = formData.get('product_cat');
    const brand = formData.get('product_brand');
    const price = formData.get('product_price');

    // Validate title
    if (!title || title.trim() === '') {
        errors.push('Product title is required');
    } else if (title.trim().length < 3) {
        errors.push('Product title must be at least 3 characters long');
    }

    // Validate category
    if (!cat || cat === '' || cat === '0') {
        errors.push('Category is required');
    }

    // Validate brand
    if (!brand || brand === '' || brand === '0') {
        errors.push('Brand is required');
    }

    // Validate price
    if (!price || price === '') {
        errors.push('Price is required');
    } else if (isNaN(parseFloat(price)) || parseFloat(price) < 0) {
        errors.push('Price must be a valid positive number');
    }

    return {
        valid: errors.length === 0,
        errors: errors
    };
}

/**
 * Show modal with message
 * @param {string} message - Message to display
 * @param {boolean} isSuccess - Whether the message is a success or error
 */
function showModal(message, isSuccess = true) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('productModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'productModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <div class="modal-icon"></div>
                <p class="modal-message"></p>
                <button class="modal-btn">OK</button>
            </div>
        `;
        document.body.appendChild(modal);

        // Close modal handlers
        const closeBtn = modal.querySelector('.modal-close');
        const okBtn = modal.querySelector('.modal-btn');

        closeBtn.onclick = () => modal.style.display = 'none';
        okBtn.onclick = () => modal.style.display = 'none';

        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    }

    // Update modal content
    const modalContent = modal.querySelector('.modal-content');
    const modalIcon = modal.querySelector('.modal-icon');
    const modalMessage = modal.querySelector('.modal-message');

    modalContent.className = `modal-content ${isSuccess ? 'modal-success' : 'modal-error'}`;
    modalIcon.innerHTML = isSuccess ? '✓' : '✕';
    modalMessage.textContent = message;

    // Show modal
    modal.style.display = 'block';
}

/**
 * Handle multiple image preview
 * @param {Event} e - Change event from file input
 */
function handleImagePreview(e) {
    const files = Array.from(e.target.files);
    selectedFiles = files;

    const fileNameEl = document.getElementById('fileName');
    const imagePreview = document.getElementById('imagePreview');

    if (files.length > 0) {
        fileNameEl.textContent = `Selected: ${files.length} file(s)`;
        imagePreview.innerHTML = ''; // Clear previous previews

        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'image-preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="image-preview-remove" onclick="removeImagePreview(${index})">×</button>
                    `;
                    imagePreview.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        fileNameEl.textContent = '';
        imagePreview.innerHTML = '';
    }
}

/**
 * Remove image from preview and selected files
 * @param {number} index - Index of file to remove
 */
function removeImagePreview(index) {
    // Remove from selectedFiles array
    selectedFiles.splice(index, 1);

    // Update file input
    const input = document.getElementById('product_images');
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    input.files = dataTransfer.files;

    // Trigger preview update
    handleImagePreview({ target: input });
}

/**
 * Fetch and display all products
 */
async function fetchProducts() {
    try {
        const response = await fetch('../actions/fetch_product_action.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            displayProducts(result.products);
        } else {
            console.error('Failed to fetch products:', result.message);
            displayProducts([]);
        }
    } catch (error) {
        console.error('Error fetching products:', error);
        showModal('Failed to load products. Please refresh the page.', false);
    }
}

/**
 * Display products in the UI
 * @param {Array} products - Array of product objects
 */
function displayProducts(products) {
    const productsContainer = document.getElementById('productsContainer');
    if (!productsContainer) return;

    if (!products || products.length === 0) {
        productsContainer.innerHTML = `
            <div class="empty-state">
                <h3>No products found</h3>
                <p>Start adding products using the form above.</p>
            </div>
        `;
        return;
    }

    const productsHTML = `
        <div class="product-grid">
            ${products.map(product => createProductCard(product)).join('')}
        </div>
    `;

    productsContainer.innerHTML = productsHTML;
}

/**
 * Create product card HTML
 * @param {Object} product - Product object
 * @returns {string} - HTML string
 */
function createProductCard(product) {
    const imageUrl = product.product_image ?
        `../${escapeHtml(product.product_image)}` :
        'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="220" viewBox="0 0 300 220"%3E%3Crect fill="%23f8f9fa" width="300" height="220"/%3E%3Ctext fill="%236c757d" font-family="sans-serif" font-size="16" dy="110" font-weight="bold" x="50%25" y="50%25" text-anchor="middle"%3ENo Image%3C/text%3E%3C/svg%3E';

    const description = product.product_desc ?
        escapeHtml(product.product_desc).substring(0, 100) + (product.product_desc.length > 100 ? '...' : '') :
        'No description available';

    return `
        <div class="product-card">
            <img src="${imageUrl}" alt="${escapeHtml(product.product_title)}" class="product-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'300\\' height=\\'220\\' viewBox=\\'0 0 300 220\\'%3E%3Crect fill=\\'%23f8f9fa\\' width=\\'300\\' height=\\'220\\'/%3E%3Ctext fill=\\'%236c757d\\' font-family=\\'sans-serif\\' font-size=\\'16\\' dy=\\'110\\' font-weight=\\'bold\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\'%3ENo Image%3C/text%3E%3C/svg%3E'">
            <div class="product-details">
                <h3 class="product-title">${escapeHtml(product.product_title)}</h3>
                <div class="product-price">GHS ${parseFloat(product.product_price).toFixed(2)}</div>
                <p class="product-info"><strong>Category:</strong> ${escapeHtml(product.cat_name || 'N/A')}</p>
                <p class="product-info"><strong>Brand:</strong> ${escapeHtml(product.brand_name || 'N/A')}</p>
                ${product.product_keywords ? `<p class="product-info"><strong>Keywords:</strong> ${escapeHtml(product.product_keywords)}</p>` : ''}
                <p class="product-description">${description}</p>
                <div class="product-actions">
                    <button onclick="editProduct(${product.product_id})" class="btn btn-warning">Edit</button>
                    <button onclick="deleteProduct(${product.product_id}, '${escapeHtml(product.product_title)}')" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} - Escaped text
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Add a new product
 * @param {FormData} formData - Form data
 */
async function addProduct(formData) {
    try {
        const response = await fetch('../actions/add_product_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showModal(result.message + (result.images_uploaded > 0 ? ` (${result.images_uploaded} image(s) uploaded)` : ''), true);
            resetForm();
            await fetchProducts(); // Reload products
        } else {
            showModal(result.message, false);
        }
    } catch (error) {
        console.error('Error adding product:', error);
        showModal('An error occurred while adding the product. Please try again.', false);
    }
}

/**
 * Update an existing product
 * @param {FormData} formData - Form data
 */
async function updateProduct(formData) {
    try {
        const response = await fetch('../actions/update_product_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showModal(result.message + (result.images_uploaded > 0 ? ` (${result.images_uploaded} new image(s) uploaded)` : ''), true);
            resetForm();
            await fetchProducts(); // Reload products
        } else {
            showModal(result.message, false);
        }
    } catch (error) {
        console.error('Error updating product:', error);
        showModal('An error occurred while updating the product. Please try again.', false);
    }
}

/**
 * Delete a product
 * @param {number} productId - Product ID to delete
 * @param {string} productTitle - Product title for confirmation
 */
async function deleteProduct(productId, productTitle) {
    if (!confirm(`Are you sure you want to delete "${productTitle}"? This action cannot be undone.`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../actions/delete_product_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showModal(result.message, true);
            await fetchProducts(); // Reload products
        } else {
            showModal(result.message, false);
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        showModal('An error occurred while deleting the product. Please try again.', false);
    }
}

/**
 * Edit a product
 * @param {number} productId - Product ID to edit
 */
async function editProduct(productId) {
    try {
        // Fetch product details
        const response = await fetch(`../actions/fetch_product_action.php`, {
            method: 'GET'
        });

        const result = await response.json();

        if (result.success) {
            const product = result.products.find(p => p.product_id == productId);
            if (product) {
                // Populate form
                document.getElementById('product_id').value = product.product_id;
                document.getElementById('product_title').value = product.product_title;
                document.getElementById('product_cat').value = product.product_cat;
                document.getElementById('product_brand').value = product.product_brand;
                document.getElementById('product_price').value = product.product_price;
                document.getElementById('product_desc').value = product.product_desc || '';
                document.getElementById('product_keywords').value = product.product_keywords || '';

                // Show current image if exists
                const imagePreview = document.getElementById('imagePreview');
                if (product.product_image) {
                    imagePreview.innerHTML = `
                        <div class="image-preview-item">
                            <img src="../${product.product_image}" alt="Current image">
                            <div style="text-align: center; margin-top: 5px; font-size: 12px; color: #6c757d;">Current Image</div>
                        </div>
                    `;
                }

                // Set edit mode
                editMode = true;
                editProductId = productId;

                // Update form UI
                document.getElementById('formTitle').textContent = 'Update Product';
                const submitBtn = document.querySelector('#productForm button[type="submit"]');
                submitBtn.textContent = 'Update Product';

                // Show cancel button
                const cancelBtn = document.getElementById('cancelBtn');
                cancelBtn.style.display = 'inline-block';

                // Scroll to form
                document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
            }
        }
    } catch (error) {
        console.error('Error loading product for edit:', error);
        showModal('Failed to load product details. Please try again.', false);
    }
}

/**
 * Reset form to add mode
 */
function resetForm() {
    editMode = false;
    editProductId = null;
    selectedFiles = [];

    // Reset form
    document.getElementById('productForm').reset();
    document.getElementById('product_id').value = '';

    // Clear previews
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('fileName').textContent = '';

    // Update form UI
    document.getElementById('formTitle').textContent = 'Add New Product';
    const submitBtn = document.querySelector('#productForm button[type="submit"]');
    submitBtn.textContent = 'Add Product';

    // Hide cancel button
    const cancelBtn = document.getElementById('cancelBtn');
    cancelBtn.style.display = 'none';
}

/**
 * Handle form submission
 * @param {Event} e - Form submit event
 */
async function handleFormSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);

    // Validate data
    const validation = validateProductData(formData);
    if (!validation.valid) {
        showModal(validation.errors.join('\n'), false);
        return;
    }

    // Add CSRF token
    formData.append('csrf_token', CSRF_TOKEN);

    if (editMode && editProductId) {
        // Update existing product
        formData.set('product_id', editProductId);
        await updateProduct(formData);
    } else {
        // Add new product
        await addProduct(formData);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('productForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Image upload handler
    const imageInput = document.getElementById('product_images');
    if (imageInput) {
        imageInput.addEventListener('change', handleImagePreview);
    }

    // Cancel button handler
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', resetForm);
    }

    // Initial fetch of products
    fetchProducts();
});
