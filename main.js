document.addEventListener('DOMContentLoaded', async () => {
    const clockElement = document.getElementById('clock');
    const dayOfWeekElement = document.getElementById('day-of-week');
    const fullDateElement = document.getElementById('full-date');
    const weekInfoElement = document.getElementById('week-info');
    const sunriseElement = document.getElementById('sunrise-time');
    const sunsetElement = document.getElementById('sunset-time');
    const customTextWrapper = document.getElementById('custom-text-wrapper');
    const customTextDisplay = document.getElementsByClassName('custom-text-display').item(0);
    const editButton = document.getElementById('edit-button');
    const modal = document.getElementById('settings-modal');
    const modalInput = document.getElementById('modal-text-input');
    const saveButton = document.getElementById('save-button');

    // API endpoints (assumes `api/` is a subfolder of this app)
    const SERVER_GET_URL = 'api/api.php?action=get';
    const SERVER_SET_URL = 'api/api.php?action=set';

    async function fetchServerDefault() {
        try {
            const res = await fetch(SERVER_GET_URL, {credentials: 'same-origin'});
            if (!res.ok) throw new Error('network');
            const data = await res.json();
            return data;
        } catch (e) {
            console.warn('fetchServerDefault error', e);
            return null;
        }
    }

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
        // Prefer local override; otherwise fetch server default
        return (async () => {
            const savedText = localStorage.getItem('customDesktopText');
            if (savedText && savedText.trim() !== '') {
                modalInput.value = savedText;
                customTextWrapper.classList.add('visible');
                updateCustomTextDisplay(); // Call updateCustomTextDisplay to apply styling
                return;
            }

            // No local override — try loading server default
            try {
                const server = await fetchServerDefault();
                if (server && server.message && server.message.trim() !== '') {
                    modalInput.value = server.message;
                    customTextDisplay.textContent = server.message;
                    customTextWrapper.classList.add('visible');
                    window._serverMessageVersion = server.version || null;
                    updateCustomTextDisplay();
                }
            } catch (e) {
                // silently ignore — app still works with empty message
                console.warn('Failed to load server default message', e);
            }
        })();
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

    // Function to bold the first letter of each word
    function boldFirstLetterOfEachWord(text) {
        // This regex matches the first letter of each word,
        // ignoring leading non-letter characters.
        // (\p{L}) captures any Unicode letter
        // \P{L} matches any character that is NOT a Unicode letter
        return text.replace(/(^|\s)(\P{L}*)(\p{L})/gu, (match, p1, p2, p3) => {
            return `${p1}${p2}<span class="first-letter-bold">${p3}</span>`;
        });
    }

    // Update custom text display based on modal input and toggle
    function updateCustomTextDisplay() {
        const input = document.getElementById('modal-text-input').value;
        const useFirstLetter = document.getElementById('first-letter-toggle').checked;
        const display = document.getElementsByClassName('custom-text-display').item(0);
        if (useFirstLetter) {
            display.innerHTML = firstLetterMethod(input);
            display.classList.add('transformed-text');
        } else {
            display.innerHTML = boldFirstLetterOfEachWord(input);
            display.classList.remove('transformed-text');
        }
    }

    // Event listeners for modal input and toggle
    window.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('modal-text-input');
        const toggle = document.getElementById('first-letter-toggle');
        const saveBtn = document.getElementById('save-button');
        const display = document.getElementsByClassName('custom-text-display').item(0);
        if (input) input.addEventListener('input', updateCustomTextDisplay);
        if (toggle) toggle.addEventListener('change', updateCustomTextDisplay);
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                const useFirstLetter = toggle.checked;
                const value = input.value;
                if (useFirstLetter) {
                    display.textContent = firstLetterMethod(value);
                } else {
                    display.innerHTML = boldFirstLetterOfEachWord(value);
                }
                // Optionally close modal here if desired
                document.getElementById('settings-modal').classList.add('hidden');
            });
        }
    });

    function loadFirstLetterTogglePreference() {
        const firstLetterToggle = document.getElementById('first-letter-toggle');
        const savedToggleState = localStorage.getItem('firstLetterMethodEnabled');
        if (savedToggleState !== null) {
            firstLetterToggle.checked = JSON.parse(savedToggleState);
        }
        updateCustomTextDisplay(); // Ensure display updates based on loaded toggle state
    }

    // Inițializare
    updateTime();
    setInterval(updateTime, 1000);
    updateSunInfo();
    loadCustomText();
    loadLayoutPreference();
    loadFirstLetterTogglePreference(); // Load toggle preference on page load

    // Event listener for first-letter-toggle to save its state
    const firstLetterToggle = document.getElementById('first-letter-toggle');
    if (firstLetterToggle) {
        firstLetterToggle.addEventListener('change', (e) => {
            localStorage.setItem('firstLetterMethodEnabled', JSON.stringify(e.target.checked));
            updateCustomTextDisplay(); // Update display immediately on toggle change
        });
    }
});
