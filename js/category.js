/**
 * Category Management JavaScript
 * Handles frontend category operations with AJAX calls
 */

class CategoryManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadCategories();
    }

    bindEvents() {
        // Bind form submission events
        const addForm = document.getElementById('addCategoryForm');
        const updateForm = document.getElementById('updateCategoryForm');
        
        if (addForm) {
            addForm.addEventListener('submit', (e) => this.handleAddCategory(e));
        }
        
        if (updateForm) {
            updateForm.addEventListener('submit', (e) => this.handleUpdateCategory(e));
        }

        // Bind delete buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-category-btn')) {
                this.handleDeleteCategory(e);
            }
        });

        // Bind edit buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('edit-category-btn')) {
                this.handleEditCategory(e);
            }
        });
    }

    /**
     * Validate category information
     * @param {Object} data - Category data to validate
     * @returns {Object} - Validation result
     */
    validateCategory(data) {
        const errors = [];
        
        // Validate category name
        if (!data.cat_name || data.cat_name.trim().length === 0) {
            errors.push('Category name is required');
        } else if (data.cat_name.trim().length < 2) {
            errors.push('Category name must be at least 2 characters long');
        } else if (data.cat_name.trim().length > 100) {
            errors.push('Category name must not exceed 100 characters');
        }

        // Validate category type
        const validTypes = ['cuisine', 'restaurant_type', 'dietary'];
        if (!data.cat_type || !validTypes.includes(data.cat_type)) {
            errors.push('Please select a valid category type');
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Show modal/popup with message
     * @param {string} message - Message to display
     * @param {string} type - Type of message (success, error, info)
     */
    showModal(message, type = 'info') {
        // Create modal if it doesn't exist
        let modal = document.getElementById('categoryModal');
        if (!modal) {
            modal = this.createModal();
        }

        const modalBody = modal.querySelector('.modal-body');
        const modalTitle = modal.querySelector('.modal-title');
        
        // Set message and type
        modalBody.innerHTML = message;
        modal.className = `modal fade ${type}`;
        
        // Set title based on type
        switch(type) {
            case 'success':
                modalTitle.textContent = 'Success';
                break;
            case 'error':
                modalTitle.textContent = 'Error';
                break;
            default:
                modalTitle.textContent = 'Information';
        }

        // Show modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }

    /**
     * Create modal element
     * @returns {HTMLElement} - Modal element
     */
    createModal() {
        const modalHTML = `
            <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="categoryModalLabel">Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Message will be inserted here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        return document.getElementById('categoryModal');
    }

    /**
     * Handle add category form submission
     * @param {Event} e - Form submission event
     */
    async handleAddCategory(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = {
            cat_name: formData.get('cat_name'),
            cat_type: formData.get('cat_type')
        };

        // Validate data
        const validation = this.validateCategory(data);
        if (!validation.isValid) {
            this.showModal(validation.errors.join('<br>'), 'error');
            return;
        }

        try {
            const response = await this.addCategory(data);
            if (response.success) {
                this.showModal(response.message, 'success');
                e.target.reset();
                this.loadCategories(); // Reload categories
            } else {
                this.showModal(response.message, 'error');
            }
        } catch (error) {
            this.showModal('An error occurred while adding the category', 'error');
            console.error('Error:', error);
        }
    }

    /**
     * Handle update category form submission
     * @param {Event} e - Form submission event
     */
    async handleUpdateCategory(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = {
            cat_id: formData.get('cat_id'),
            cat_name: formData.get('cat_name'),
            cat_type: formData.get('cat_type')
        };

        // Validate data
        const validation = this.validateCategory(data);
        if (!validation.isValid) {
            this.showModal(validation.errors.join('<br>'), 'error');
            return;
        }

        try {
            const response = await this.updateCategory(data);
            if (response.success) {
                this.showModal(response.message, 'success');
                this.loadCategories(); // Reload categories
            } else {
                this.showModal(response.message, 'error');
            }
        } catch (error) {
            this.showModal('An error occurred while updating the category', 'error');
            console.error('Error:', error);
        }
    }

    /**
     * Handle delete category
     * @param {Event} e - Click event
     */
    async handleDeleteCategory(e) {
        e.preventDefault();
        
        const catId = e.target.getAttribute('data-cat-id');
        const catName = e.target.getAttribute('data-cat-name');
        
        if (!confirm(`Are you sure you want to delete the category "${catName}"?`)) {
            return;
        }

        try {
            const response = await this.deleteCategory(catId);
            if (response.success) {
                this.showModal(response.message, 'success');
                this.loadCategories(); // Reload categories
            } else {
                this.showModal(response.message, 'error');
            }
        } catch (error) {
            this.showModal('An error occurred while deleting the category', 'error');
            console.error('Error:', error);
        }
    }

    /**
     * Handle edit category
     * @param {Event} e - Click event
     */
    handleEditCategory(e) {
        e.preventDefault();
        
        const catId = e.target.getAttribute('data-cat-id');
        const catName = e.target.getAttribute('data-cat-name');
        const catType = e.target.getAttribute('data-cat-type');
        
        // Populate update form
        const updateForm = document.getElementById('updateCategoryForm');
        if (updateForm) {
            updateForm.querySelector('[name="cat_id"]').value = catId;
            updateForm.querySelector('[name="cat_name"]').value = catName;
            updateForm.querySelector('[name="cat_type"]').value = catType;
            
            // Scroll to form
            updateForm.scrollIntoView({ behavior: 'smooth' });
        }
    }

    /**
     * Asynchronously invoke add category action
     * @param {Object} data - Category data
     * @returns {Promise} - Response promise
     */
    async addCategory(data) {
        const formData = new URLSearchParams();
        formData.append('cat_name', data.cat_name);
        formData.append('cat_type', data.cat_type);

        const response = await fetch('actions/add_category_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });

        return await response.json();
    }

    /**
     * Asynchronously invoke update category action
     * @param {Object} data - Category data
     * @returns {Promise} - Response promise
     */
    async updateCategory(data) {
        const formData = new URLSearchParams();
        formData.append('cat_id', data.cat_id);
        formData.append('cat_name', data.cat_name);
        formData.append('cat_type', data.cat_type);

        const response = await fetch('actions/update_category_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });

        return await response.json();
    }

    /**
     * Asynchronously invoke delete category action
     * @param {string} catId - Category ID
     * @returns {Promise} - Response promise
     */
    async deleteCategory(catId) {
        const response = await fetch(`actions/delete_category_action.php?cat_id=${catId}`, {
            method: 'GET'
        });

        return await response.json();
    }

    /**
     * Asynchronously invoke fetch categories action
     * @returns {Promise} - Response promise
     */
    async fetchCategories() {
        const response = await fetch('actions/fetch_category_action.php', {
            method: 'GET'
        });

        return await response.json();
    }

    /**
     * Load and display categories
     */
    async loadCategories() {
        try {
            const response = await this.fetchCategories();
            if (response.success) {
                this.displayCategories(response.data);
            } else {
                this.showModal('Failed to load categories: ' + response.message, 'error');
            }
        } catch (error) {
            this.showModal('An error occurred while loading categories', 'error');
            console.error('Error:', error);
        }
    }

    /**
     * Display categories in the UI
     * @param {Array} categories - Array of categories
     */
    displayCategories(categories) {
        const container = document.getElementById('categoriesContainer');
        if (!container) return;

        if (categories.length === 0) {
            container.innerHTML = '<p class="text-muted">No categories found.</p>';
            return;
        }

        // Group categories by type
        const grouped = this.groupCategoriesByType(categories);
        
        let html = '';
        for (const [type, cats] of Object.entries(grouped)) {
            html += `<div class="category-type-section mb-4">
                <h3 class="category-type-title">${this.getTypeDisplayName(type)}</h3>
                <div class="row">`;
            
            cats.forEach(cat => {
                html += `<div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">${cat.cat_name}</h5>
                            <p class="card-text">ID: ${cat.cat_id}</p>
                            <div class="btn-group">
                                <button class="btn btn-warning btn-sm edit-category-btn" 
                                        data-cat-id="${cat.cat_id}" 
                                        data-cat-name="${cat.cat_name}" 
                                        data-cat-type="${cat.cat_type}">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm delete-category-btn" 
                                        data-cat-id="${cat.cat_id}" 
                                        data-cat-name="${cat.cat_name}">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            
            html += '</div></div>';
        }
        
        container.innerHTML = html;
    }

    /**
     * Group categories by type
     * @param {Array} categories - Array of categories
     * @returns {Object} - Grouped categories
     */
    groupCategoriesByType(categories) {
        return categories.reduce((groups, category) => {
            const type = category.cat_type;
            if (!groups[type]) {
                groups[type] = [];
            }
            groups[type].push(category);
            return groups;
        }, {});
    }

    /**
     * Get display name for category type
     * @param {string} type - Category type
     * @returns {string} - Display name
     */
    getTypeDisplayName(type) {
        const typeNames = {
            'cuisine': 'Cuisine Regions',
            'restaurant_type': 'Restaurant Type',
            'dietary': 'Special Dietary'
        };
        return typeNames[type] || type;
    }
}

// Initialize CategoryManager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new CategoryManager();
});
