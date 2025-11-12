/**
 * Cart JavaScript
 * Handles all cart-related UI interactions and AJAX calls
 */

// Get CSRF token from the page
function getCSRFToken() {
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    return tokenInput ? tokenInput.value : '';
}

// Show notification message
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : '✗'}</span>
            <span class="notification-message">${message}</span>
        </div>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Show with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    // Auto-hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Update cart badge count in navigation
function updateCartBadge(count) {
    const cartBadge = document.querySelector('.cart-badge, #cart-count');
    if (cartBadge) {
        cartBadge.textContent = count;
        if (count > 0) {
            cartBadge.style.display = 'inline-block';
        } else {
            cartBadge.style.display = 'none';
        }
    }
}

// Add to cart function
async function addToCart(productId, quantity = 1, showMessage = true) {
    try {
        const formData = new URLSearchParams();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        formData.append('csrf_token', getCSRFToken());

        const response = await fetch('actions/add_to_cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            if (showMessage) {
                showNotification(data.message, 'success');
            }
            updateCartBadge(data.cart_count);
            return true;
        } else {
            if (showMessage) {
                showNotification(data.message, 'error');
            }
            return false;
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        if (showMessage) {
            showNotification('An error occurred. Please try again.', 'error');
        }
        return false;
    }
}

// Update cart item quantity
async function updateCartQuantity(productId, quantity) {
    try {
        const formData = new URLSearchParams();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        formData.append('csrf_token', getCSRFToken());

        const response = await fetch('actions/update_quantity_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Update cart badge
            updateCartBadge(data.cart_count);

            // Update cart total if on cart page
            const cartTotal = document.querySelector('.cart-total-amount, #cart-total');
            if (cartTotal) {
                cartTotal.textContent = '$' + data.cart_total;
            }

            // If quantity is 0, remove the row
            if (quantity === 0) {
                const cartRow = document.querySelector(`[data-product-id="${productId}"]`);
                if (cartRow) {
                    cartRow.remove();
                }
            } else {
                // Update subtotal for this item
                updateItemSubtotal(productId);
            }

            // Check if cart is empty
            checkEmptyCart();

            showNotification(data.message, 'success');
            return true;
        } else {
            showNotification(data.message, 'error');
            return false;
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        showNotification('An error occurred. Please try again.', 'error');
        return false;
    }
}

// Remove item from cart
async function removeFromCart(productId) {
    // Confirm removal
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return false;
    }

    try {
        const formData = new URLSearchParams();
        formData.append('product_id', productId);
        formData.append('csrf_token', getCSRFToken());

        const response = await fetch('actions/remove_from_cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Remove the cart row
            const cartRow = document.querySelector(`[data-product-id="${productId}"]`);
            if (cartRow) {
                cartRow.remove();
            }

            // Update cart badge
            updateCartBadge(data.cart_count);

            // Update cart total
            const cartTotal = document.querySelector('.cart-total-amount, #cart-total');
            if (cartTotal) {
                cartTotal.textContent = '$' + data.cart_total;
            }

            // Check if cart is empty
            checkEmptyCart();

            showNotification(data.message, 'success');
            return true;
        } else {
            showNotification(data.message, 'error');
            return false;
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
        showNotification('An error occurred. Please try again.', 'error');
        return false;
    }
}

// Empty entire cart
async function emptyCart() {
    // Confirm emptying cart
    if (!confirm('Are you sure you want to empty your entire cart?')) {
        return false;
    }

    try {
        const formData = new URLSearchParams();
        formData.append('csrf_token', getCSRFToken());

        const response = await fetch('actions/empty_cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Remove all cart rows
            const cartItems = document.querySelectorAll('.cart-item, [data-product-id]');
            cartItems.forEach(item => item.remove());

            // Update cart badge
            updateCartBadge(0);

            // Update cart total
            const cartTotal = document.querySelector('.cart-total-amount, #cart-total');
            if (cartTotal) {
                cartTotal.textContent = '$0.00';
            }

            // Show empty cart message
            checkEmptyCart();

            showNotification(data.message, 'success');
            return true;
        } else {
            showNotification(data.message, 'error');
            return false;
        }
    } catch (error) {
        console.error('Error emptying cart:', error);
        showNotification('An error occurred. Please try again.', 'error');
        return false;
    }
}

// Update item subtotal (recalculate based on quantity and price)
function updateItemSubtotal(productId) {
    const cartRow = document.querySelector(`[data-product-id="${productId}"]`);
    if (!cartRow) return;

    const quantityInput = cartRow.querySelector('.quantity-input, input[name="quantity"]');
    const priceElement = cartRow.querySelector('.item-price, [data-price]');
    const subtotalElement = cartRow.querySelector('.item-subtotal, .subtotal');

    if (quantityInput && priceElement && subtotalElement) {
        const quantity = parseInt(quantityInput.value) || 0;
        const price = parseFloat(priceElement.dataset.price || priceElement.textContent.replace(/[^0-9.]/g, ''));
        const subtotal = quantity * price;

        subtotalElement.textContent = '$' + subtotal.toFixed(2);
    }

    // Recalculate cart total
    recalculateCartTotal();
}

// Recalculate entire cart total
function recalculateCartTotal() {
    let total = 0;
    const subtotalElements = document.querySelectorAll('.item-subtotal, .subtotal');

    subtotalElements.forEach(element => {
        const amount = parseFloat(element.textContent.replace(/[^0-9.]/g, ''));
        if (!isNaN(amount)) {
            total += amount;
        }
    });

    const cartTotal = document.querySelector('.cart-total-amount, #cart-total');
    if (cartTotal) {
        cartTotal.textContent = '$' + total.toFixed(2);
    }
}

// Check if cart is empty and show appropriate message
function checkEmptyCart() {
    const cartItems = document.querySelectorAll('.cart-item, [data-product-id]');

    if (cartItems.length === 0) {
        const cartContainer = document.querySelector('.cart-items-container, #cart-items');
        if (cartContainer) {
            cartContainer.innerHTML = `
                <div class="empty-cart-message">
                    <img src="images/empty-cart.png" alt="Empty Cart" style="max-width: 200px; opacity: 0.5;" onerror="this.style.display='none'">
                    <h3>Your cart is empty</h3>
                    <p>Add some products to your cart to continue shopping!</p>
                    <a href="view/all_product.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            `;
        }

        // Hide checkout button
        const checkoutBtn = document.querySelector('.checkout-btn, #checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.style.display = 'none';
        }
    }
}

// Handle quantity input changes
function handleQuantityChange(event) {
    const input = event.target;
    const productId = input.closest('[data-product-id]')?.dataset.productId;

    if (!productId) return;

    let quantity = parseInt(input.value);

    // Validate quantity
    if (isNaN(quantity) || quantity < 0) {
        quantity = 1;
        input.value = 1;
    }

    // Update cart
    updateCartQuantity(productId, quantity);
}

// Debounce function to prevent too many API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize cart page
function initializeCart() {
    // Add event listeners to quantity inputs
    const quantityInputs = document.querySelectorAll('.quantity-input, input[name="quantity"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', handleQuantityChange);
        input.addEventListener('input', debounce(handleQuantityChange, 500));
    });

    // Add event listeners to remove buttons
    const removeButtons = document.querySelectorAll('.remove-btn, .btn-remove');
    removeButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = button.closest('[data-product-id]')?.dataset.productId;
            if (productId) {
                removeFromCart(productId);
            }
        });
    });

    // Add event listener to empty cart button
    const emptyCartBtn = document.querySelector('.empty-cart-btn, #empty-cart-btn');
    if (emptyCartBtn) {
        emptyCartBtn.addEventListener('click', (e) => {
            e.preventDefault();
            emptyCart();
        });
    }

    // Check if cart is empty on page load
    checkEmptyCart();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCart);
} else {
    initializeCart();
}

// Export functions for use in other scripts
window.cartFunctions = {
    addToCart,
    updateCartQuantity,
    removeFromCart,
    emptyCart,
    showNotification,
    updateCartBadge
};
