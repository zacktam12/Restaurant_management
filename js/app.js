// Custom JavaScript to replace Bootstrap functionality

// Utility functions
function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Enhanced DOM ready function
function domReady(callback) {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", callback);
    } else {
        callback();
    }
}

// Utility functions
function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Enhanced DOM ready function
function domReady(callback) {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", callback);
    } else {
        callback();
    }
}

// Modal functionality
domReady(function() {
    // Enhanced modal functionality with animations
    var modals = document.querySelectorAll(".modal");
    var modalButtons = document.querySelectorAll("button[data-name], button[data-toggle='modal']");
    var closeButtons = document.querySelectorAll(".btn-close, .modal .close");
    
    // When the user clicks on a button, open the modal 
    modalButtons.forEach(function(button) {
        button.addEventListener("click", function(e) {
            e.preventDefault();
            var targetModalId = this.getAttribute('data-target') || this.getAttribute('data-bs-target');
            if (targetModalId) {
                var modal = document.querySelector(targetModalId);
                if (modal) {
                    modal.classList.add("show");
                    modal.style.display = "block";
                    document.body.style.overflow = "hidden";
                    
                    // Add backdrop
                    var backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
            } else {
                // Handle existing place modal
                var modal = document.getElementById("placeModal");
                if (modal) {
                    modal.classList.add("show");
                    modal.style.display = "block";
                    document.body.style.overflow = "hidden";
                    
                    // Add backdrop
                    var backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                    
                    // Populate modal with data
                    var name = this.getAttribute('data-name');
                    var description = this.getAttribute('data-description');
                    var city = this.getAttribute('data-city');
                    var country = this.getAttribute('data-country');
                    var rating = this.getAttribute('data-rating');
                    var category = this.getAttribute('data-category');
                    
                    // Update the modal's content
                    document.getElementById('placeModalLabel').textContent = name + ' Details';
                    document.getElementById('modal-place-name').textContent = name;
                    document.getElementById('modal-place-description').textContent = description;
                    document.getElementById('modal-place-location').textContent = city + ', ' + country;
                    document.getElementById('modal-place-rating').textContent = rating;
                    document.getElementById('modal-place-category').textContent = category.charAt(0).toUpperCase() + category.slice(1);
                    document.getElementById('modal-place-name-input').value = name;
                    document.getElementById('modal-place-city-input').value = city;
                }
            }
        });
    });
    
    // When the user clicks on <span> (x), close the modal
    closeButtons.forEach(function(button) {
        button.addEventListener("click", function() {
            var modal = this.closest(".modal");
            if (modal) {
                modal.classList.remove("show");
                modal.style.display = "none";
                document.body.style.overflow = "";
                
                // Remove backdrop
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            }
        });
    });
    
    // When the user clicks anywhere outside of the modal, close it
    window.addEventListener("click", function(event) {
        if (event.target.classList.contains("modal") && event.target.classList.contains("fade")) {
            event.target.classList.remove("show");
            event.target.style.display = "none";
            document.body.style.overflow = "";
            
            // Remove backdrop
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    });
    
    // Escape key closes modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(function(modal) {
                if (modal.classList.contains('show')) {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                    
                    // Remove backdrop
                    var backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            });
        }
    });
    
    // Enhanced alert dismiss functionality with animations
    var alertCloseButtons = document.querySelectorAll(".alert .btn-close");
    alertCloseButtons.forEach(function(button) {
        button.addEventListener("click", function() {
            var alert = this.closest(".alert");
            alert.classList.remove("show");
            alert.classList.add("fade");
            setTimeout(function() {
                alert.style.display = "none";
                alert.classList.remove("fade");
            }, 150);
        });
    });
    
    // Auto-dismiss alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert:not(.alert-dismissible)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.classList.remove('show');
            alert.classList.add('fade');
            setTimeout(function() {
                alert.style.display = 'none';
            }, 150);
        }, 5000);
    });
    
    // Search functionality (avoid page reload on every keystroke)
    // Submit when user presses Enter, and auto-submit when field is cleared.
    var searchInputs = document.querySelectorAll('#search, .search-input');
    searchInputs.forEach(function(searchInput) {
        if (!searchInput) return;

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });

        searchInput.addEventListener('input', debounce(function() {
            if (this.value !== '') return;
            var form = this.closest('form');
            if (form) {
                form.submit();
            }
        }, 300));
    });
    
    // Auto-submit when category changes
    var categorySelects = document.querySelectorAll('#category, .category-select');
    categorySelects.forEach(function(categorySelect) {
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                var form = this.closest('form');
                if (form) {
                    // Add loading indicator
                    var submitButton = form.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.innerHTML = '<span class="spinner"></span> Loading...';
                        submitButton.disabled = true;
                    }
                    
                    // Submit form
                    form.submit();
                }
            });
        }
    });

    // Auto-submit when cuisine changes (restaurants filter)
    var cuisineSelects = document.querySelectorAll('#cuisine, .cuisine-select');
    cuisineSelects.forEach(function(cuisineSelect) {
        if (cuisineSelect) {
            cuisineSelect.addEventListener('change', function() {
                var form = this.closest('form');
                if (form) {
                    // Submit form
                    form.submit();
                }
            });
        }
    });
    
    // Enhanced tab functionality with smooth transitions
    var tabButtons = document.querySelectorAll('.tab-button, [data-bs-toggle="tab"]');
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            var tabId = this.getAttribute('data-tab') || this.getAttribute('data-bs-target');
            
            // Remove active class from all buttons and panes
            document.querySelectorAll('.tab-button, [data-bs-toggle="tab"]').forEach(function(btn) {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(function(pane) {
                pane.classList.remove('active', 'show');
                pane.style.display = 'none';
            });
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            if (tabId) {
                var targetPane = document.querySelector(tabId);
                if (targetPane) {
                    targetPane.style.display = 'block';
                    setTimeout(function() {
                        targetPane.classList.add('active', 'show');
                    }, 10);
                }
            }
        });
    });
    
    // Enhanced confirmation dialog for delete actions
    var deleteButtons = document.querySelectorAll('button[type="submit"][class*="btn-outline-danger"]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            var itemName = this.getAttribute('data-item-name') || 'this item';
            var confirmMessage = this.getAttribute('data-confirm-message') || 'Are you sure you want to delete ' + itemName + '?';
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            } else {
                // Add loading state
                this.innerHTML = '<span class="spinner"></span> Deleting...';
                this.disabled = true;
            }
        });
    });
    
    // Enhanced toggle password visibility
    var togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = this.getAttribute('data-target');
            var passwordInput = document.getElementById(targetId);
            var icon = this.querySelector('i') || this;
            
            if (passwordInput && passwordInput.type === 'password') {
                passwordInput.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
                this.title = 'Hide password';
            } else if (passwordInput) {
                passwordInput.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
                this.title = 'Show password';
            }
        });
    });
    
    // Enhanced form validation with real-time feedback
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        // Real-time validation
        var requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(function(field) {
            field.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.classList.add('is-invalid');
                    // Create error message if not exists
                    if (!this.parentNode.querySelector('.invalid-feedback')) {
                        var errorMsg = document.createElement('div');
                        errorMsg.className = 'invalid-feedback';
                        errorMsg.textContent = 'This field is required.';
                        this.parentNode.appendChild(errorMsg);
                    }
                } else {
                    this.classList.remove('is-invalid');
                    var errorMsg = this.parentNode.querySelector('.invalid-feedback');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    var errorMsg = this.parentNode.querySelector('.invalid-feedback');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            var requiredFields = form.querySelectorAll('[required]');
            var isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    // Create error message if not exists
                    if (!field.parentNode.querySelector('.invalid-feedback')) {
                        var errorMsg = document.createElement('div');
                        errorMsg.className = 'invalid-feedback';
                        errorMsg.textContent = 'This field is required.';
                        field.parentNode.appendChild(errorMsg);
                    }
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                    var errorMsg = field.parentNode.querySelector('.invalid-feedback');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first invalid field
                var firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            } else {
                // Add loading state to submit button
                var submitButton = form.querySelector('button[type="submit"]');
                if (submitButton && !submitButton.hasAttribute('data-no-loading')) {
                    var originalText = submitButton.innerHTML;
                    submitButton.setAttribute('data-original-text', originalText);
                    submitButton.innerHTML = '<span class="spinner"></span> Processing...';
                    submitButton.disabled = true;
                }
            }
        });
    });
    
    // Enhanced dynamic field visibility based on role selection
    var roleSelects = document.querySelectorAll('#role, .role-select');
    roleSelects.forEach(function(roleSelect) {
        if (roleSelect) {
            var professionalDetailsField = document.getElementById('professionalDetailsField') || 
                                          roleSelect.closest('form').querySelector('.professional-details-field');
            
            roleSelect.addEventListener('change', function() {
                if (this.value === 'tour_guide') {
                    if (professionalDetailsField) {
                        professionalDetailsField.style.display = 'block';
                        professionalDetailsField.classList.add('fade', 'show');
                    }
                } else {
                    if (professionalDetailsField) {
                        professionalDetailsField.style.display = 'none';
                        professionalDetailsField.classList.remove('fade', 'show');
                    }
                }
            });
            
            // Trigger change event on page load if needed
            if (roleSelect.value === 'tour_guide') {
                if (professionalDetailsField) {
                    professionalDetailsField.style.display = 'block';
                    professionalDetailsField.classList.add('fade', 'show');
                }
            }
        }
    });
    
    // Smooth scroll for anchor links
    var anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            var targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                var targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
    
    // Add loading spinners for buttons with data-loading attribute
    var loadingButtons = document.querySelectorAll('button[data-loading]');
    loadingButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var loadingText = this.getAttribute('data-loading') || 'Loading...';
            var originalText = this.innerHTML;
            this.setAttribute('data-original-text', originalText);
            this.innerHTML = '<span class="spinner"></span> ' + loadingText;
            this.disabled = true;
        });
    });
    
    // Tooltip functionality (simple implementation)
    var tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(function(element) {
        var title = element.getAttribute('title') || element.getAttribute('data-bs-title');
        if (title) {
            element.addEventListener('mouseenter', function() {
                // Create tooltip element
                var tooltip = document.createElement('div');
                tooltip.className = 'custom-tooltip';
                tooltip.textContent = title;
                tooltip.style.position = 'absolute';
                tooltip.style.backgroundColor = '#000';
                tooltip.style.color = '#fff';
                tooltip.style.padding = '5px 10px';
                tooltip.style.borderRadius = '4px';
                tooltip.style.fontSize = '12px';
                tooltip.style.zIndex = '1000';
                tooltip.style.whiteSpace = 'nowrap';
                
                document.body.appendChild(tooltip);
                
                // Position tooltip
                var rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
                tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
                
                // Store reference
                this.tooltipElement = tooltip;
            });
            
            element.addEventListener('mouseleave', function() {
                if (this.tooltipElement) {
                    this.tooltipElement.remove();
                    this.tooltipElement = null;
                }
            });
        }
    });
    
    // Toast notifications (simple implementation)
    function showToast(message, type) {
        var toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.top = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '10000';
            document.body.appendChild(toastContainer);
        }
        
        var toast = document.createElement('div');
        toast.className = 'toast show';
        toast.style.minWidth = '250px';
        toast.style.marginBottom = '10px';
        toast.style.borderRadius = '4px';
        toast.style.padding = '15px';
        toast.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        toast.style.opacity = '1';
        toast.style.transition = 'opacity 0.3s';
        
        if (type === 'success') {
            toast.style.backgroundColor = '#d4edda';
            toast.style.borderColor = '#c3e6cb';
            toast.style.color = '#155724';
        } else if (type === 'error') {
            toast.style.backgroundColor = '#f8d7da';
            toast.style.borderColor = '#f5c6cb';
            toast.style.color = '#721c24';
        } else if (type === 'warning') {
            toast.style.backgroundColor = '#fff3cd';
            toast.style.borderColor = '#ffeaa7';
            toast.style.color = '#856404';
        } else {
            toast.style.backgroundColor = '#d1ecf1';
            toast.style.borderColor = '#bee5eb';
            toast.style.color = '#0c5460';
        }
        
        toast.textContent = message;
        toastContainer.appendChild(toast);
        
        // Auto hide after 3 seconds
        setTimeout(function() {
            toast.style.opacity = '0';
            setTimeout(function() {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
    
    // Expose showToast globally
        // Initialize any tooltips on page load
    setTimeout(function() {
        var autoTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"][data-bs-trigger="auto"]');
        autoTooltips.forEach(function(element) {
            var title = element.getAttribute('title') || element.getAttribute('data-bs-title');
            if (title) {
                // Show tooltip immediately and hide after 2 seconds
                var event = new MouseEvent('mouseenter');
                element.dispatchEvent(event);
                
                setTimeout(function() {
                    var leaveEvent = new MouseEvent('mouseleave');
                    element.dispatchEvent(leaveEvent);
                }, 2000);
            }
        });
    }, 1000);
    
    // Add ripple effect to buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn')) {
            var button = e.target.closest('.btn');
            var ripple = document.createElement('span');
            ripple.classList.add('ripple');
            
            var rect = button.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var x = e.clientX - rect.left - size / 2;
            var y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            button.appendChild(ripple);
            
            setTimeout(function() {
                ripple.remove();
            }, 600);
        }
    });
    
    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        // Close modals with Escape key
        if (e.key === 'Escape') {
            var modals = document.querySelectorAll('.modal.show');
            modals.forEach(function(modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                
                // Remove backdrop
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            });
        }
        
        // Focus management for modals
        if (e.key === 'Tab') {
            var modals = document.querySelectorAll('.modal.show');
            modals.forEach(function(modal) {
                var focusableElements = modal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                var firstElement = focusableElements[0];
                var lastElement = focusableElements[focusableElements.length - 1];
                
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        lastElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        firstElement.focus();
                        e.preventDefault();
                    }
                }
            });
        }
    });
    
    // Add scroll to top functionality
    var scrollToTopBtn = document.createElement('button');
    scrollToTopBtn.id = 'scrollToTop';
    scrollToTopBtn.innerHTML = '&uarr;';
    scrollToTopBtn.style.position = 'fixed';
    scrollToTopBtn.style.bottom = '20px';
    scrollToTopBtn.style.right = '20px';
    scrollToTopBtn.style.width = '40px';
    scrollToTopBtn.style.height = '40px';
    scrollToTopBtn.style.borderRadius = '50%';
    scrollToTopBtn.style.backgroundColor = '#0d6efd';
    scrollToTopBtn.style.color = 'white';
    scrollToTopBtn.style.border = 'none';
    scrollToTopBtn.style.cursor = 'pointer';
    scrollToTopBtn.style.display = 'none';
    scrollToTopBtn.style.zIndex = '1000';
    scrollToTopBtn.style.fontSize = '20px';
    scrollToTopBtn.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    scrollToTopBtn.setAttribute('title', 'Scroll to top');
    document.body.appendChild(scrollToTopBtn);
    
    window.addEventListener('scroll', throttle(function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.style.display = 'block';
        } else {
            scrollToTopBtn.style.display = 'none';
        }
    }, 100));
    
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Add dark mode toggle functionality
    function initDarkMode() {
        var darkModeToggle = document.createElement('button');
        darkModeToggle.id = 'darkModeToggle';
        darkModeToggle.innerHTML = '&#9789;';
        darkModeToggle.style.position = 'fixed';
        darkModeToggle.style.bottom = '20px';
        darkModeToggle.style.right = '70px';
        darkModeToggle.style.width = '40px';
        darkModeToggle.style.height = '40px';
        darkModeToggle.style.borderRadius = '50%';
        darkModeToggle.style.backgroundColor = '#212529';
        darkModeToggle.style.color = 'white';
        darkModeToggle.style.border = 'none';
        darkModeToggle.style.cursor = 'pointer';
        darkModeToggle.style.zIndex = '1000';
        darkModeToggle.style.fontSize = '20px';
        darkModeToggle.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
        darkModeToggle.setAttribute('title', 'Toggle dark mode');
        document.body.appendChild(darkModeToggle);
        
        // Check for saved theme or default to light
        var currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
        
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            var isDarkMode = document.body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        });
    }
    
    // Initialize dark mode
    initDarkMode();
    
    // Navbar toggle functionality
    domReady(function() {
        var navbarToggles = document.querySelectorAll('.navbar-toggler');
        navbarToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                var target = this.getAttribute('data-bs-target') || this.getAttribute('data-target');
                if (target) {
                    var navbarCollapse = document.querySelector(target);
                    if (navbarCollapse) {
                        if (navbarCollapse.classList.contains('show')) {
                            navbarCollapse.classList.remove('show');
                            this.classList.remove('collapsed');
                            this.setAttribute('aria-expanded', 'false');
                        } else {
                            navbarCollapse.classList.add('show');
                            this.classList.add('collapsed');
                            this.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            });
        });
    });
    
    // Add loading spinner CSS
    var spinnerStyle = document.createElement('style');
    spinnerStyle.textContent = `
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .dark-mode .spinner {
            border-color: rgba(0, 0, 0, 0.3);
            border-top-color: #000;
        }
        
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.7);
            transform: scale(0);
            animation: ripple 0.6s linear;
        }
        
        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        
        .dark-mode {
            --bs-body-color: #fff;
            --bs-body-bg: #121212;
            --bs-card-bg: #1e1e1e;
            --bs-border-color: #333;
        }
        
        .dark-mode .form-control,
        .dark-mode .form-select {
            background-color: #2d2d2d;
            color: #fff;
            border-color: #444;
        }
        
        .dark-mode .navbar {
            background-color: #1a1a1a;
        }
        
        .dark-mode .btn-primary {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }
        
        .dark-mode .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
        }
        
        /* Ensure navbar is visible */
        .navbar-collapse.show {
            display: block !important;
        }
        
        .navbar-collapse:not(.show) {
            display: none !important;
        }
        
        @media (min-width: 992px) {
            .navbar-collapse {
                display: flex !important;
            }
        }
    `;
    document.head.appendChild(spinnerStyle);
});