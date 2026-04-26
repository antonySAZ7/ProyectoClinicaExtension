// Funcionalidad básica para el layout
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad para el menú móvil
    const mobileMenuButton = document.querySelector('[data-mobile-menu-button]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Funcionalidad para el dropdown del usuario
    const userMenuButton = document.querySelector('[data-user-menu-button]');
    const userMenu = document.querySelector('[data-user-menu]');
    
    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', function() {
            userMenu.classList.toggle('hidden');
        });
    }
});