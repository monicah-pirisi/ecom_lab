/**
 * Product Filter and Search Enhancement JavaScript
 * Provides dynamic filtering, async search, and improved UX
 */

(function() {
    'use strict';

    // Debounce function for search
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

    // Initialize live search if search input exists
    const searchInput = document.querySelector('input[name="query"]');
    if (searchInput) {
        // Add search suggestions (optional enhancement)
        const handleSearchInput = debounce(function(e) {
            const query = e.target.value.trim();
            if (query.length >= 3) {
                // Could implement autocomplete here
                console.log('Searching for:', query);
            }
        }, 300);

        searchInput.addEventListener('input', handleSearchInput);

        // Add clear button to search
        const clearBtn = document.createElement('button');
        clearBtn.innerHTML = '<i class="fas fa-times"></i>';
        clearBtn.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; display: none;';
        clearBtn.onclick = function() {
            searchInput.value = '';
            this.style.display = 'none';
        };

        if (searchInput.parentElement.style.position !== 'relative') {
            searchInput.parentElement.style.position = 'relative';
        }
        searchInput.parentElement.appendChild(clearBtn);

        searchInput.addEventListener('input', function() {
            clearBtn.style.display = this.value ? 'block' : 'none';
        });
    }

    // Enhanced filter dropdowns
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Add loading state
            this.style.opacity = '0.6';
            setTimeout(() => {
                this.style.opacity = '1';
            }, 200);
        });
    });

    // Smooth scroll for product cards
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });

    // Add loading indicators for filter buttons
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.tagName === 'BUTTON' && this.type === 'submit') {
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                this.disabled = true;
            }
        });
    });

    // Lazy loading for product images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });

        document.querySelectorAll('img.product-image[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Add animation on scroll for product cards
    if ('IntersectionObserver' in window) {
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                    }, index * 50);
                    cardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.product-card').forEach(card => {
            card.style.opacity = '0';
            cardObserver.observe(card);
        });
    }

    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

    // Enhance pagination links
    const paginationLinks = document.querySelectorAll('.page-link-custom');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.classList.contains('disabled') && !this.classList.contains('active')) {
                // Scroll to top smoothly
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    // Add keyboard navigation for search
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.blur();
            }
        });
    }

    // Quick filter toggle for mobile
    const filterSection = document.querySelector('.filters-section');
    if (filterSection && window.innerWidth < 768) {
        const toggleBtn = document.createElement('button');
        toggleBtn.innerHTML = '<i class="fas fa-filter me-2"></i>Show Filters';
        toggleBtn.className = 'filter-btn mb-3';
        toggleBtn.onclick = function() {
            const form = filterSection.querySelector('form');
            if (form.style.display === 'none') {
                form.style.display = 'block';
                this.innerHTML = '<i class="fas fa-times me-2"></i>Hide Filters';
            } else {
                form.style.display = 'none';
                this.innerHTML = '<i class="fas fa-filter me-2"></i>Show Filters';
            }
        };

        filterSection.insertBefore(toggleBtn, filterSection.querySelector('form'));
        filterSection.querySelector('form').style.display = 'none';
    }

    // Add price formatting
    document.querySelectorAll('.product-price').forEach(priceEl => {
        const price = parseFloat(priceEl.textContent.replace(/[^0-9.]/g, ''));
        if (!isNaN(price)) {
            priceEl.setAttribute('data-price', price);
        }
    });

    // Log initialization
    console.log('Product filter enhancements loaded successfully');
})();
