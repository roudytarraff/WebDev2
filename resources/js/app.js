import './bootstrap';

document.addEventListener('click', (event) => {
    const deleteTrigger = event.target.closest('[data-confirm-delete]');

    if (deleteTrigger && ! window.confirm(deleteTrigger.dataset.confirmDelete)) {
        event.preventDefault();
    }

    const openButton = event.target.closest('[data-sidebar-open]');
    const closeButton = event.target.closest('[data-sidebar-close]');
    const sidebar = document.getElementById('adminSidebar');

    if (! sidebar) {
        return;
    }

    if (openButton) {
        sidebar.classList.add('is-open');
    }

    if (closeButton) {
        sidebar.classList.remove('is-open');
    }
});
