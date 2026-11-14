(() => {
    const root = document.documentElement;
    const button = document.querySelector('.theme-toggle');
    const meta = document.querySelector('meta[name="theme-color"]');

    const syncThemeControl = () => {
        const isDark = root.dataset.theme === 'dark';
        const nextLabel = isDark ? 'Light mode' : 'Dark mode';
        if (button) {
            button.setAttribute('aria-label', `Switch to ${isDark ? 'light' : 'dark'} mode`);
            button.setAttribute('title', `Switch to ${isDark ? 'light' : 'dark'} mode`);
            const icon = button.querySelector('.theme-icon');
            const label = button.querySelector('.theme-label');
            if (icon) icon.textContent = isDark ? '☀' : '☾';
            if (label) label.textContent = nextLabel;
        }
        if (meta) meta.content = isDark ? '#111c22' : '#f4f7fb';
    };

    if (button) {
        button.addEventListener('click', () => {
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = next;
            try {
                localStorage.setItem('healthtrack-theme', next);
            } catch (error) {
                // Theme still changes for this page if storage is disabled.
            }
            syncThemeControl();
        });
    }

    const bookingDetails = document.getElementById('book');
    if (bookingDetails && window.location.hash === '#book') bookingDetails.open = true;

    const patientId = document.getElementById('patient-id');
    const patientName = document.getElementById('patient-name');
    const patientPurok = document.getElementById('patient-purok');
    const patientContact = document.querySelector('[name="patient_contact"]');
    if (patientId && patientId.value) {
        const originalName = patientName ? patientName.value : '';
        const originalPurok = patientPurok ? patientPurok.value : '';
        let contactEdited = false;
        if (patientContact) {
            patientContact.addEventListener('input', () => {
                contactEdited = true;
            });
        }
        const unlinkIfChanged = () => {
            if ((patientName && patientName.value !== originalName)
                || (patientPurok && patientPurok.value !== originalPurok)) {
                patientId.value = '';
                if (patientContact && !contactEdited) patientContact.value = '';
            }
        };
        if (patientName) patientName.addEventListener('input', unlinkIfChanged);
        if (patientPurok) patientPurok.addEventListener('change', unlinkIfChanged);
    }

    syncThemeControl();
})();
