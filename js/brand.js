/**
 * Brand Management JavaScript
 * Handles AJAX operations for brand CRUD functionality
 */

// Global variables
let editMode = false;
let editBrandId = null;
const CSRF_TOKEN = document.querySelector('input[name="csrf_token"]').value;

/**
 * Validate brand form data
 * @param {Object} data - Form data to validate
 * @returns {Object} - Validation result {valid: boolean, errors: array}
 */
function validateBrandData(data) {
    const errors = [];

    // Validate brand name
    if (!data.brand_name || data.brand_name.trim() === '') {
        errors.push('Brand name is required');
    } else if (data.brand_name.trim().length < 2) {
        errors.push('Brand name must be at least 2 characters long');
    } else if (data.brand_name.trim().length > 100) {
        errors.push('Brand name must not exceed 100 characters');
    }

    // Validate category
    if (!data.brand_cat || data.brand_cat === '' || data.brand_cat === '0') {
        errors.push('Category is required');
    } else if (isNaN(parseInt(data.brand_cat))) {
        errors.push('Invalid category selected');
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
    let modal = document.getElementById('brandModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'brandModal';
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
 * Fetch and display all brands for current user
 */
async function fetchBrands() {
    try {
        const response = await fetch('../actions/fetch_brand_action.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            displayBrands(result.brands);
        } else {
            console.error('Failed to fetch brands:', result.message);
        }
    } catch (error) {
        console.error('Error fetching brands:', error);
        showModal('Failed to load brands. Please refresh the page.', false);
    }
}

/**
 * Display brands in the UI grouped by category
 * @param {Array} brands - Array of brand objects
 */
function displayBrands(brands) {
    const brandsSection = document.querySelector('.brands-section');
    if (!brandsSection) return;

    // Clear existing content except the title
    const title = brandsSection.querySelector('h2');
    brandsSection.innerHTML = '';
    if (title) brandsSection.appendChild(title);

    if (!brands || brands.length === 0) {
        brandsSection.innerHTML += `
            <div class="empty-state">
                <h3>No brands found</h3>
                <p>Create your first brand using the form above.</p>
            </div>
        `;
        return;
    }

    // Group brands by category
    const grouped = {};
    brands.forEach(brand => {
        const catName = brand.cat_name || 'Uncategorized';
        if (!grouped[catName]) {
            grouped[catName] = {
                cat_id: brand.brand_cat,
                cat_name: catName,
                cat_type: brand.cat_type,
                brands: []
            };
        }
        grouped[catName].brands.push(brand);
    });

    // Display grouped brands
    Object.values(grouped).forEach(category => {
        const categoryDiv = document.createElement('div');
        categoryDiv.className = 'category-group';
        categoryDiv.innerHTML = `
            <h3>${escapeHtml(category.cat_name)}</h3>
            <div class="brand-grid">
                ${category.brands.map(brand => `
                    <div class="brand-card">
                        <h4>${escapeHtml(brand.brand_name)}</h4>
                        <p><strong>ID:</strong> ${escapeHtml(brand.brand_id)}</p>
                        <p><strong>Category:</strong> ${escapeHtml(brand.cat_name)}</p>
                        <div class="brand-actions">
                            <button onclick="editBrand(${brand.brand_id})" class="btn btn-warning">Edit</button>
                            <button onclick="deleteBrand(${brand.brand_id}, '${escapeHtml(brand.brand_name)}')" class="btn btn-danger">Delete</button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        brandsSection.appendChild(categoryDiv);
    });
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} - Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Add a new brand
 * @param {FormData} formData - Form data
 */
async function addBrand(formData) {
    try {
        const response = await fetch('../actions/add_brand_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showModal(result.message, true);
            document.getElementById('brandForm').reset();
            await fetchBrands(); // Reload brands
        } else {
            showModal(result.message, false);
        }
    } catch (error) {
        console.error('Error adding brand:', error);
        showModal('An error occurred while adding the brand. Please try again.', false);
    }
}

/**
 * Update an existing brand
 * @param {FormData} formData - Form data
 */
async function updateBrand(formData) {
    try {
        const response = await fetch('../actions/update_brand_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showModal(result.message, true);
            cancelEdit();
            await fetchBrands(); // Reload brands
        } else {
            showModal(result.message, false);
        }
    } catch (error) {
        console.error('Error updating brand:', error);
        showModal('An error occurred while updating the brand. Please try again.', false);
    }
}

/**
 * Delete a brand
 * @param {number} brandId - Brand ID to delete
 * @param {string} brandName - Brand name for confirmation
 */
async function deleteBrand(brandId, brandName) {
    if (!confirm(`Are you sure you want to delete "${brandName}"? This action cannot be undone.`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('brand_id', brandId);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../actions/delete_brand_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showModal(result.message, true);
            await fetchBrands(); // Reload brands
        } else {
            showModal(result.message, false);
        }
    } catch (error) {
        console.error('Error deleting brand:', error);
        showModal('An error occurred while deleting the brand. Please try again.', false);
    }
}

/**
 * Edit a brand
 * @param {number} brandId - Brand ID to edit
 */
async function editBrand(brandId) {
    try {
        // Fetch brand details
        const response = await fetch(`../actions/fetch_brand_action.php`, {
            method: 'GET'
        });

        const result = await response.json();

        if (result.success) {
            const brand = result.brands.find(b => b.brand_id == brandId);
            if (brand) {
                // Populate form
                document.getElementById('brand_name').value = brand.brand_name;
                document.getElementById('brand_cat').value = brand.brand_cat;

                // Set edit mode
                editMode = true;
                editBrandId = brandId;

                // Update form UI
                document.querySelector('.form-section h2').textContent = 'Update Brand';
                const submitBtn = document.querySelector('#brandForm button[type="submit"]');
                submitBtn.textContent = 'Update Brand';

                // Add cancel button if not exists
                if (!document.getElementById('cancelEditBtn')) {
                    const cancelBtn = document.createElement('button');
                    cancelBtn.id = 'cancelEditBtn';
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'btn btn-secondary';
                    cancelBtn.textContent = 'Cancel';
                    cancelBtn.style.marginLeft = '15px';
                    cancelBtn.onclick = cancelEdit;
                    submitBtn.parentNode.appendChild(cancelBtn);
                }

                // Scroll to form
                document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
            }
        }
    } catch (error) {
        console.error('Error loading brand for edit:', error);
        showModal('Failed to load brand details. Please try again.', false);
    }
}

/**
 * Cancel edit mode
 */
function cancelEdit() {
    editMode = false;
    editBrandId = null;

    // Reset form
    document.getElementById('brandForm').reset();

    // Update form UI
    document.querySelector('.form-section h2').textContent = 'Create New Brand';
    const submitBtn = document.querySelector('#brandForm button[type="submit"]');
    submitBtn.textContent = 'Create Brand';

    // Remove cancel button
    const cancelBtn = document.getElementById('cancelEditBtn');
    if (cancelBtn) {
        cancelBtn.remove();
    }
}

/**
 * Handle form submission
 * @param {Event} e - Form submit event
 */
async function handleFormSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        brand_name: formData.get('brand_name'),
        brand_cat: formData.get('brand_cat')
    };

    // Validate data
    const validation = validateBrandData(data);
    if (!validation.valid) {
        showModal(validation.errors.join('\n'), false);
        return;
    }

    // Add CSRF token
    formData.append('csrf_token', CSRF_TOKEN);

    if (editMode && editBrandId) {
        // Update existing brand
        formData.append('brand_id', editBrandId);
        await updateBrand(formData);
    } else {
        // Add new brand
        await addBrand(formData);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('brandForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Initial fetch of brands
    fetchBrands();
});
