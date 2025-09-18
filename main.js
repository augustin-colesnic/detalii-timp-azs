document.addEventListener('DOMContentLoaded', () => {
    const clockElement = document.getElementById('clock');
    const dayOfWeekElement = document.getElementById('day-of-week');
    const fullDateElement = document.getElementById('full-date');
    const weekInfoElement = document.getElementById('week-info');
    const sunriseElement = document.getElementById('sunrise-time');
    const sunsetElement = document.getElementById('sunset-time');
    const customTextWrapper = document.getElementById('custom-text-wrapper');
    const customTextDisplay = document.getElementById('custom-text-display');
    const editButton = document.getElementById('edit-button');
    const modal = document.getElementById('settings-modal');
    const modalInput = document.getElementById('modal-text-input');
    const saveButton = document.getElementById('save-button');

    const ZILE_SAPTAMANA = ['Duminică', 'Luni', 'Marți', 'Miercuri', 'Joi', 'Vineri', 'Sâmbătă'];
    const LUNI_AN = ['ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie'];

    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        clockElement.textContent = `${hours}:${minutes}:${seconds}`;
        dayOfWeekElement.textContent = ZILE_SAPTAMANA[now.getDay()];
        fullDateElement.textContent = `${now.getDate()} ${LUNI_AN[now.getMonth()]} ${now.getFullYear()}`;
        weekInfoElement.textContent = `Săptămâna ${getWeekNumber(now)}, Anul ${now.getFullYear()}`;
    }

    function getWeekNumber(d) {
        d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
    }

    function updateSunInfo() {
        navigator.geolocation.getCurrentPosition(position => {
            const { latitude, longitude } = position.coords;
            const times = SunCalc.getTimes(new Date(), latitude, longitude);
            const formatTime = (date) => `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
            sunriseElement.textContent = `Răsare: ${formatTime(times.sunrise)}`;
            sunsetElement.textContent = `Apune: ${formatTime(times.sunset)}`;
        }, () => {
            const defaultCoords = { latitude: 44.4268, longitude: 26.1025 };
            const times = SunCalc.getTimes(new Date(), defaultCoords.latitude, defaultCoords.longitude);
            const formatTime = (date) => `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
            sunriseElement.textContent = `Răsare: ${formatTime(times.sunrise)} (București)`;
            sunsetElement.textContent = `Apune: ${formatTime(times.sunset)} (București)`;
        });
    }

    function loadCustomText() {
        const savedText = localStorage.getItem('customDesktopText');
        if (savedText && savedText.trim() !== '') {
            customTextDisplay.textContent = savedText;
            modalInput.value = savedText;
            customTextWrapper.classList.add('visible');
        }
    }

    function loadLayoutPreference() {
        const savedLayout = localStorage.getItem('desktopLayout') || 'extended';
        if (savedLayout === 'compact') {
            document.body.classList.add('compact-view');
            document.getElementById('layout-compact').checked = true;
        } else {
            document.body.classList.remove('compact-view');
            document.getElementById('layout-extended').checked = true;
        }
    }

    function closeModal() { modal.classList.add('hidden'); }

    editButton.addEventListener('click', () => {
        modal.classList.remove('hidden');
        modalInput.focus();
    });

    saveButton.addEventListener('click', () => {
        const newText = modalInput.value;
        customTextDisplay.textContent = newText;
        localStorage.setItem('customDesktopText', newText);

        if (newText.trim() !== '') {
            customTextWrapper.classList.add('visible');
        } else {
            customTextWrapper.classList.remove('visible');
        }

        closeModal();
    });

    document.querySelectorAll('input[name="layout"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const newLayout = e.target.value;
            localStorage.setItem('desktopLayout', newLayout);
            if (newLayout === 'compact') {
                document.body.classList.add('compact-view');
            } else {
                document.body.classList.remove('compact-view');
            }
        });
    });

    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    modalInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') saveButton.click(); });

    // First Letter Method: convert text to initials
    function firstLetterMethod(text) {
        return text.replace(/(\p{L})\p{L}+/gu, '$1');
    }

    // Update custom text display based on modal input and toggle
    function updateCustomTextDisplay() {
        const input = document.getElementById('modal-text-input').value;
        const useFirstLetter = document.getElementById('first-letter-toggle').checked;
        const display = document.getElementById('custom-text-display');
        display.textContent = useFirstLetter ? firstLetterMethod(input) : input;
    }

    // Event listeners for modal input and toggle
    window.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('modal-text-input');
        const toggle = document.getElementById('first-letter-toggle');
        const saveBtn = document.getElementById('save-button');
        const display = document.getElementById('custom-text-display');
        if (input) input.addEventListener('input', updateCustomTextDisplay);
        if (toggle) toggle.addEventListener('change', updateCustomTextDisplay);
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                const useFirstLetter = toggle.checked;
                const value = input.value;
                display.textContent = useFirstLetter ? firstLetterMethod(value) : value;
                // Optionally close modal here if desired
                document.getElementById('settings-modal').classList.add('hidden');
            });
        }
    });

    // Inițializare
    updateTime();
    setInterval(updateTime, 1000);
    updateSunInfo();
    loadCustomText();
    loadLayoutPreference();
});
