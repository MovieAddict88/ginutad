</div>
    </div>
    
    <script>
        // Mobile navigation toggle with enhanced functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileNavToggle = document.getElementById('mobileNavToggle');
            const sidebar = document.getElementById('sidebar');
            const menuIcon = document.querySelector('#mobileNavToggle .material-icons');
            
            if (mobileNavToggle && sidebar) {
                // Toggle sidebar with icon change
                mobileNavToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isActive = sidebar.classList.toggle('active');
                    mobileNavToggle.setAttribute('aria-expanded', isActive);
                    
                    // Change icon based on state
                    if (menuIcon) {
                        menuIcon.textContent = isActive ? 'close' : 'menu';
                    }
                    
                    // Prevent body scroll when sidebar is open on mobile
                    if (window.innerWidth <= 768) {
                        document.body.style.overflow = isActive ? 'hidden' : '';
                    }
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(event) {
                    if (window.innerWidth <= 768 && 
                        !sidebar.contains(event.target) && 
                        !mobileNavToggle.contains(event.target) && 
                        sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        mobileNavToggle.setAttribute('aria-expanded', 'false');
                        if (menuIcon) {
                            menuIcon.textContent = 'menu';
                        }
                        document.body.style.overflow = '';
                    }
                });
                
                // Close sidebar with Escape key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && 
                        sidebar.classList.contains('active') && 
                        window.innerWidth <= 768) {
                        sidebar.classList.remove('active');
                        mobileNavToggle.setAttribute('aria-expanded', 'false');
                        if (menuIcon) {
                            menuIcon.textContent = 'menu';
                        }
                        document.body.style.overflow = '';
                    }
                });
                
                // Handle window resize with debouncing
                let resizeTimer;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        // Auto-close sidebar on resize to desktop
                        if (window.innerWidth > 768) {
                            sidebar.classList.remove('active');
                            mobileNavToggle.setAttribute('aria-expanded', 'false');
                            if (menuIcon) {
                                menuIcon.textContent = 'menu';
                            }
                            document.body.style.overflow = '';
                        }
                        
                        // Update toggle button visibility
                        if (window.innerWidth > 768) {
                            mobileNavToggle.style.display = 'none';
                        } else {
                            mobileNavToggle.style.display = 'flex';
                        }
                    }, 250);
                });
                
                // Close sidebar when clicking on sidebar links on mobile
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('active');
                            mobileNavToggle.setAttribute('aria-expanded', 'false');
                            if (menuIcon) {
                                menuIcon.textContent = 'menu';
                            }
                            document.body.style.overflow = '';
                        }
                    });
                });
                
                // Initialize button visibility
                if (window.innerWidth > 768) {
                    mobileNavToggle.style.display = 'none';
                } else {
                    mobileNavToggle.style.display = 'flex';
                }
            }
            
            // Add smooth scroll to top for mobile
            const scrollToTopBtn = document.getElementById('scrollToTop');
            if (scrollToTopBtn) {
                scrollToTopBtn.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
                
                // Show/hide scroll to top button
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 300) {
                        scrollToTopBtn.style.display = 'flex';
                    } else {
                        scrollToTopBtn.style.display = 'none';
                    }
                });
            }
            
            // Table row click handlers
            const clickableRows = document.querySelectorAll('.table-clickable tbody tr');
            clickableRows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Don't trigger if clicking on a button or link
                    if (e.target.tagName === 'BUTTON' || 
                        e.target.tagName === 'A' || 
                        e.target.closest('button') || 
                        e.target.closest('a')) {
                        return;
                    }
                    
                    // Find the first link in the row and follow it
                    const link = this.querySelector('a');
                    if (link) {
                        window.location.href = link.href;
                    }
                });
            });
            
            // Form validation enhancement
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    let firstInvalidField = null;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.style.borderColor = '#f72585';
                            if (!firstInvalidField) {
                                firstInvalidField = field;
                            }
                        } else {
                            field.style.borderColor = '';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        if (firstInvalidField) {
                            firstInvalidField.focus();
                        }
                        return false;
                    }
                });
            });
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500);
                }, 5000);
            });
            
            // Initialize tooltips
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = this.getAttribute('title');
                    tooltip.style.position = 'absolute';
                    tooltip.style.background = 'var(--dark)';
                    tooltip.style.color = 'white';
                    tooltip.style.padding = '5px 10px';
                    tooltip.style.borderRadius = '4px';
                    tooltip.style.fontSize = '0.8rem';
                    tooltip.style.zIndex = '9999';
                    tooltip.style.whiteSpace = 'nowrap';
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.top = (rect.top - 35) + 'px';
                    tooltip.style.left = (rect.left + rect.width / 2) + 'px';
                    tooltip.style.transform = 'translateX(-50%)';
                    
                    document.body.appendChild(tooltip);
                    
                    this._tooltip = tooltip;
                });
                
                element.addEventListener('mouseleave', function() {
                    if (this._tooltip && this._tooltip.parentNode) {
                        this._tooltip.parentNode.removeChild(this._tooltip);
                    }
                });
            });
        });
        
        // Performance monitoring (optional)
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.name === 'first-contentful-paint') {
                        console.log('FCP:', entry.startTime);
                    }
                }
            });
            observer.observe({entryTypes: ['paint']});
        }
    </script>
    
    <!-- Prism.js for syntax highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-generic.min.js"></script>

    <!-- Clipboard.js for copy functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab functionality for tutorial page
        const tabButtons = document.querySelectorAll('.carrier-tabs .tab-btn');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                const targetContent = document.getElementById(targetId);

                // Update button states
                button.parentElement.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Update content visibility
                targetContent.parentElement.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                targetContent.classList.add('active');
            });
        });

        // Initialize Clipboard.js
        var clipboard = new ClipboardJS('.copy-btn');

        clipboard.on('success', function(e) {
            const button = e.trigger;
            const originalText = button.querySelector('.copy-text').innerHTML;

            button.classList.add('copied');
            button.querySelector('.copy-text').innerHTML = 'Copied!';
            button.querySelector('.material-icons').innerHTML = 'check';

            setTimeout(() => {
                button.classList.remove('copied');
                button.querySelector('.copy-text').innerHTML = originalText;
                button.querySelector('.material-icons').innerHTML = 'content_copy';
            }, 2000);

            e.clearSelection();
        });

        clipboard.on('error', function(e) {
            console.error('Action:', e.action);
            console.error('Trigger:', e.trigger);
        });
    });
    </script>

    <!-- Scroll to Top Button (optional - add to long pages) -->
    <button id="scrollToTop" style="
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: var(--box-shadow);
        z-index: 100;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    ">
        <span class="material-icons">arrow_upward</span>
    </button>
</body>
</html>