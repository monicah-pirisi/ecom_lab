/**
 * Checkout JavaScript
 * Handles checkout process and simulated payment modal
 */

// Get CSRF token from the page
function getCSRFToken() {
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    return tokenInput ? tokenInput.value : '';
}

// Show notification message
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : '✗'}</span>
            <span class="notification-message">${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Create payment modal HTML
function createPaymentModal() {
    const modalHTML = `
        <div id="payment-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Simulate Payment</h2>
                    <span class="modal-close" onclick="closePaymentModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="payment-info">
                        <div class="payment-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                <line x1="1" y1="10" x2="23" y2="10"></line>
                            </svg>
                        </div>
                        <h3>Simulated Payment</h3>
                        <p>This is a <strong>demo payment</strong> for testing purposes.</p>
                        <p>No real payment will be processed.</p>

                        <div class="order-summary">
                            <h4>Order Summary</h4>
                            <div class="summary-row">
                                <span>Total Amount:</span>
                                <span class="amount" id="modal-total">$0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Currency:</span>
                                <span id="modal-currency">USD</span>
                            </div>
                            <div class="summary-row">
                                <span>Payment Method:</span>
                                <span id="modal-payment-method">Credit Card</span>
                            </div>
                        </div>

                        <div class="payment-form">
                            <div class="form-group">
                                <label for="payment-method-select">Select Payment Method:</label>
                                <select id="payment-method-select" class="form-control">
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="Mobile Money">Mobile Money</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
                    <button class="btn btn-success" onclick="confirmPayment()" id="confirm-payment-btn">
                        <span class="btn-text">Yes, I've Paid</span>
                        <span class="btn-loader" style="display: none;">
                            <svg class="spinner" width="20" height="20" viewBox="0 0 50 50">
                                <circle cx="25" cy="25" r="20" fill="none" stroke="white" stroke-width="5" stroke-dasharray="80" stroke-dashoffset="60"></circle>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('payment-modal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

// Open payment modal
function openPaymentModal(totalAmount, currency = 'USD') {
    // Create modal if it doesn't exist
    createPaymentModal();

    const modal = document.getElementById('payment-modal');
    const totalElement = document.getElementById('modal-total');
    const currencyElement = document.getElementById('modal-currency');

    // Update modal content
    if (totalElement) {
        totalElement.textContent = '$' + parseFloat(totalAmount).toFixed(2);
    }
    if (currencyElement) {
        currencyElement.textContent = currency;
    }

    // Show modal
    modal.style.display = 'flex';

    // Animate in
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);

    // Prevent body scroll
    document.body.style.overflow = 'hidden';

    // Update payment method display when changed
    const paymentMethodSelect = document.getElementById('payment-method-select');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', (e) => {
            const methodDisplay = document.getElementById('modal-payment-method');
            if (methodDisplay) {
                methodDisplay.textContent = e.target.value;
            }
        });
    }
}

// Close payment modal
function closePaymentModal() {
    const modal = document.getElementById('payment-modal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }
}

// Confirm payment and process checkout
async function confirmPayment() {
    const confirmBtn = document.getElementById('confirm-payment-btn');
    const btnText = confirmBtn.querySelector('.btn-text');
    const btnLoader = confirmBtn.querySelector('.btn-loader');

    // Get payment method
    const paymentMethodSelect = document.getElementById('payment-method-select');
    const paymentMethod = paymentMethodSelect ? paymentMethodSelect.value : 'Credit Card';

    // Disable button and show loader
    confirmBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoader.style.display = 'inline-block';

    try {
        // Prepare form data
        const formData = new URLSearchParams();
        formData.append('csrf_token', getCSRFToken());
        formData.append('currency', 'USD');
        formData.append('payment_method', paymentMethod);

        // Send request to process checkout
        const response = await fetch('../actions/process_checkout_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Close modal
            closePaymentModal();

            // Show success message
            showSuccessModal(data);

            // Clear cart badge
            if (window.cartFunctions && window.cartFunctions.updateCartBadge) {
                window.cartFunctions.updateCartBadge(0);
            }

            // Redirect to order confirmation after 3 seconds
            setTimeout(() => {
                window.location.href = `view/order_confirmation.php?order_id=${data.order_id}&reference=${data.order_reference}`;
            }, 3000);
        } else {
            // Show error message
            closePaymentModal();
            showNotification(data.message || 'Payment failed. Please try again.', 'error');

            // Re-enable button
            confirmBtn.disabled = false;
            btnText.style.display = 'inline-block';
            btnLoader.style.display = 'none';
        }
    } catch (error) {
        console.error('Error processing checkout:', error);
        closePaymentModal();
        showNotification('An error occurred while processing your order. Please try again.', 'error');

        // Re-enable button
        confirmBtn.disabled = false;
        btnText.style.display = 'inline-block';
        btnLoader.style.display = 'none';
    }
}

// Show success modal
function showSuccessModal(orderData) {
    const successModalHTML = `
        <div id="success-modal" class="modal show">
            <div class="modal-content success-modal-content">
                <div class="success-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h2>Payment Successful!</h2>
                <p>Your order has been placed successfully.</p>
                <div class="order-details">
                    <div class="detail-row">
                        <span>Order Reference:</span>
                        <strong>${orderData.order_reference}</strong>
                    </div>
                    <div class="detail-row">
                        <span>Order ID:</span>
                        <strong>#${orderData.order_id}</strong>
                    </div>
                    <div class="detail-row">
                        <span>Total Amount:</span>
                        <strong>$${orderData.total_amount}</strong>
                    </div>
                    <div class="detail-row">
                        <span>Payment Method:</span>
                        <strong>${orderData.payment_method}</strong>
                    </div>
                </div>
                <p class="redirect-message">Redirecting to order confirmation...</p>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', successModalHTML);
}

// Initialize checkout page
function initializeCheckout() {
    // Get checkout button
    const checkoutBtn = document.querySelector('.checkout-btn, #checkout-btn, .btn-checkout');

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', (e) => {
            e.preventDefault();

            // Get total amount from page
            const totalElement = document.querySelector('.cart-total-amount, #cart-total, .total-amount');
            const totalAmount = totalElement ? parseFloat(totalElement.textContent.replace(/[^0-9.]/g, '')) : 0;

            if (totalAmount <= 0) {
                showNotification('Your cart is empty!', 'error');
                return;
            }

            // Open payment modal
            openPaymentModal(totalAmount);
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('payment-modal');
        if (modal && e.target === modal) {
            closePaymentModal();
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePaymentModal();
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCheckout);
} else {
    initializeCheckout();
}

// Export functions for global use
window.checkoutFunctions = {
    openPaymentModal,
    closePaymentModal,
    confirmPayment,
    showNotification
};
