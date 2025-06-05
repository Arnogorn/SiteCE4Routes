/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

import './bootstrap.js';



console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


// Variables globales
window.appInitialized = window.appInitialized || false;

// ==========================================
// ATTENTE DE BOOTSTRAP
// ==========================================
function waitForBootstrap(callback, maxAttempts = 50) {
    let attempts = 0;

    function check() {
        if (typeof bootstrap !== 'undefined') {
            callback();
        } else if (attempts < maxAttempts) {
            attempts++;
            setTimeout(check, 100);
        } else {
            // Log uniquement en cas d'échec
            console.warn('⚠️ Bootstrap non disponible après 5s');
        }
    }

    check();
}

// ==========================================
// FIX MENU BURGER
// ==========================================
function optimizeBurgerMenu() {
    const burgerButton = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('#navbarContent');

    if (!burgerButton || !navbarCollapse || burgerButton.dataset.optimized) return;

    burgerButton.dataset.optimized = 'true';
    // ✅ Log supprimé

    let collapseInstance = bootstrap.Collapse.getInstance(navbarCollapse);
    if (!collapseInstance) {
        collapseInstance = new bootstrap.Collapse(navbarCollapse, { toggle: false });
    }

    burgerButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (navbarCollapse.classList.contains('show')) {
            collapseInstance.hide();
        } else {
            collapseInstance.show();
        }
    }, { passive: false });
}

// ==========================================
// FIX DROPDOWNS
// ==========================================
function ensureDropdownsWork() {
    const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');

    if (dropdownElements.length === 0) return;



    dropdownElements.forEach(element => {
        if (!bootstrap.Dropdown.getInstance(element)) {
            try {
                new bootstrap.Dropdown(element);
            } catch (error) {
                // Garde seulement les logs d'erreur importants
                console.warn('Erreur dropdown:', error);
            }
        }
    });
}

// ==========================================
// TÉLÉPHONE FOOTER
// ==========================================
function setupPhoneDisplay() {
    const phoneLink = document.getElementById('phone-link');
    const phoneDisplay = document.getElementById('phone-display');
    const phoneLinkMobile = document.getElementById('phone-link-mobile');
    const phoneDisplayMobile = document.getElementById('phone-display-mobile');

    if (!phoneLink && !phoneLinkMobile) return;

    const parts = ['06', '98', '73', '76', '00'];
    const fullNumber = parts.join('');
    const formattedNumber = parts.join(' ');
    const shortNumber = parts.slice(0, 2).join(' ') + '...';
    const phoneRevealed = document.cookie.includes('phoneRevealed=true');

    // Setup Desktop
    if (phoneLink && phoneDisplay && !phoneLink.dataset.phoneSetup) {
        phoneLink.dataset.phoneSetup = 'true';

        if (phoneRevealed) {
            phoneLink.href = 'tel:' + fullNumber;
            phoneDisplay.textContent = formattedNumber;
        } else {
            phoneLink.addEventListener('click', function(e) {
                e.preventDefault();
                this.href = 'tel:' + fullNumber;
                phoneDisplay.textContent = formattedNumber;

                if (phoneDisplayMobile) phoneDisplayMobile.textContent = shortNumber;
                if (phoneLinkMobile) phoneLinkMobile.href = 'tel:' + fullNumber;

                document.cookie = 'phoneRevealed=true; path=/';
            });
        }
    }

    // Setup Mobile
    if (phoneLinkMobile && phoneDisplayMobile && !phoneLinkMobile.dataset.phoneSetup) {
        phoneLinkMobile.dataset.phoneSetup = 'true';

        if (phoneRevealed) {
            phoneLinkMobile.href = 'tel:' + fullNumber;
            phoneDisplayMobile.textContent = shortNumber;
        } else {
            phoneLinkMobile.addEventListener('click', function(e) {
                e.preventDefault();
                this.href = 'tel:' + fullNumber;
                phoneDisplayMobile.textContent = shortNumber;

                if (phoneDisplay) phoneDisplay.textContent = formattedNumber;
                if (phoneLink) phoneLink.href = 'tel:' + fullNumber;

                document.cookie = 'phoneRevealed=true; path=/';

                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = 'scale(1)', 150);
            });
        }
    }
}

// ==========================================
// ANIMATIONS FOOTER
// ==========================================
function initFooterAnimations() {
    const footer = document.querySelector('.footer-sticky');
    if (!footer || footer.dataset.animated) return;

    footer.dataset.animated = 'true';

    // Padding pour le main
    const main = document.querySelector('main');
    if (main) {
        const footerHeight = footer.offsetHeight;
        main.style.paddingBottom = (footerHeight + 20) + 'px';
    }

    // Animation d'apparition
    footer.style.transform = 'translateY(100%)';
    footer.style.transition = 'transform 0.5s ease-out';
    setTimeout(() => footer.style.transform = 'translateY(0)', 100);

    // Feedback mobile
    const mobileLinks = document.querySelectorAll('.footer-mobile-section a');
    mobileLinks.forEach(link => {
        if (link.dataset.feedbackAdded) return;
        link.dataset.feedbackAdded = 'true';

        ['touchstart', 'mousedown'].forEach(event => {
            link.addEventListener(event, () => {
                link.style.transform = 'scale(0.95)';
                link.style.transition = 'transform 0.1s ease';
            });
        });

        ['touchend', 'mouseup'].forEach(event => {
            link.addEventListener(event, () => {
                setTimeout(() => link.style.transform = 'scale(1)', 100);
            });
        });
    });
}

// ==========================================
// INITIALISATION PRINCIPALE
// ==========================================
function initializeApp() {
    if (window.appInitialized) return;


    window.appInitialized = true;

    waitForBootstrap(() => {

        optimizeBurgerMenu();
        ensureDropdownsWork();
        setupPhoneDisplay();
        initFooterAnimations();

    });
}

// ==========================================
// EVENT LISTENERS
// ==========================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}

// Support Turbo
document.addEventListener('turbo:load', () => {
    window.appInitialized = false;
    setTimeout(initializeApp, 25);
});

// Responsive
window.addEventListener('resize', () => {
    const footer = document.querySelector('.footer-sticky');
    const main = document.querySelector('main');
    if (footer && main) {
        main.style.paddingBottom = (footer.offsetHeight + 20) + 'px';
    }
});


//===================================
// PAIEMENT FAMILLE
//===================================

