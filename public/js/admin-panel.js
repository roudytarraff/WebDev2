document.addEventListener('click', (event) => {
    const deleteTrigger = event.target.closest('[data-confirm-delete]');

    if (deleteTrigger && ! window.confirm(deleteTrigger.dataset.confirmDelete)) {
        event.preventDefault();
    }

    const sidebar = document.getElementById('adminSidebar');

    if (! sidebar) {
        return;
    }

    if (event.target.closest('[data-sidebar-open]')) {
        sidebar.classList.add('is-open');
    }

    if (event.target.closest('[data-sidebar-close]')) {
        sidebar.classList.remove('is-open');
    }
});
