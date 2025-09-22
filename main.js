document.addEventListener('DOMContentLoaded', function() {

    // --- Custom Number Spinner Logic ---
    function setupCustomNumberInputs() {
        document.querySelectorAll('.custom-number-input').forEach(container => {
            const input = container.querySelector('input[type="text"]');
            const decBtn = container.querySelector('.btn-decrement');
            const incBtn = container.querySelector('.btn-increment');

            if(!input || !decBtn || !incBtn) return;

            const updateValue = (amount) => {
                let currentVal = parseInt(input.value, 10);
                if (isNaN(currentVal)) currentVal = 1;

                let newVal = currentVal + amount;
                input.value = newVal;
                // Trigger change event for other listeners
                input.dispatchEvent(new Event('change', { bubbles: true }));
            };

            decBtn.addEventListener('click', () => updateValue(-1));
            incBtn.addEventListener('click', () => updateValue(1));
        });
    }
    setupCustomNumberInputs();

    // --- Time Input Masking ---
    function applyTimeMask(element) {
        if (!element) return;
        try { IMask(element, { mask: 'HH:MM', blocks: { HH: { mask: IMask.MaskedRange, from: 0, to: 23 }, MM: { mask: IMask.MaskedRange, from: 0, to: 59 } }, lazy: false });
        } catch (e) { console.error("IMask could not be initialized.", e); }
    }
    document.querySelectorAll('.time-input').forEach(applyTimeMask);

    // --- Date Input & Validation Logic ---
    const dayInput = document.querySelector('input[name="log_day"], input[name="leave_day"]');
    const monthInput = document.querySelector('select[name="log_month"], select[name="leave_month"]');
    const yearInput = document.querySelector('input[name="log_year"], input[name="leave_year"]');

    function updateMaxDays() {
        if (!dayInput || !monthInput || !yearInput) return;
        const month = parseInt(monthInput.value, 10);
        const year = parseInt(yearInput.value, 10);
        let maxDays = 31;
        if (month >= 7 && month <= 11) maxDays = 30;
        else if (month === 12) {
            const leap_years = [1, 5, 9, 13, 17, 22, 26, 30];
            maxDays = leap_years.includes(year % 33) ? 30 : 29;
        }
        if (parseInt(dayInput.value, 10) > maxDays) dayInput.value = maxDays;
    }

    if (monthInput && yearInput) {
        monthInput.addEventListener('change', updateMaxDays);
        yearInput.addEventListener('change', updateMaxDays);
        updateMaxDays();
    }

    // --- Add/Remove Work Intervals ---
    const addWorkBtn = document.getElementById('add-interval');
    const workContainer = document.getElementById('time-intervals-container');
    function checkIntervalLimit() {
        if (!workContainer || !addWorkBtn) return;
        addWorkBtn.disabled = workContainer.querySelectorAll('.row').length >= 2;
    }
    function addWorkInterval(container) {
        const newInterval = document.createElement('div');
        newInterval.classList.add('row', 'g-2', 'mb-2', 'align-items-end');
        newInterval.innerHTML = `<div class="col-md"><label class="form-label">ساعت ورود</label><input type="text" class="form-control time-input" name="start_time[]" placeholder="HH:MM" required></div><div class="col-md"><label class="form-label">ساعت خروج</label><input type="text" class="form-control time-input" name="end_time[]" placeholder="HH:MM" required></div><div class="col-md-auto"><button type="button" class="btn btn-sm btn-danger remove-interval">-</button></div>`;
        container.appendChild(newInterval);
        newInterval.querySelectorAll('.time-input').forEach(applyTimeMask);
    }
    if (addWorkBtn && workContainer) {
        addWorkBtn.addEventListener('click', () => { addWorkInterval(workContainer); checkIntervalLimit(); });
    }
    const formCard = document.getElementById('log-form-card');
    if (formCard) {
        formCard.addEventListener('click', (e) => {
            if (e.target && e.target.classList.contains('remove-interval')) {
                e.target.closest('.row').remove();
                checkIntervalLimit();
            }
        });
    }
    if(workContainer) checkIntervalLimit();

    // --- "Fetch Date" & "Log Now" buttons (Only on index.php) ---
    const fetchDateBtn = document.getElementById('fetch-date-btn');
    if (fetchDateBtn) {
        fetchDateBtn.addEventListener('click', () => {
            if (!dayInput || !monthInput || !yearInput) return;
            const dateStr = `${yearInput.value}/${String(monthInput.value).padStart(2, '0')}/${String(dayInput.value).padStart(2, '0')}`;
            window.location.href = 'index.php?date=' + dateStr;
        });
    }
    const logNowBtn = document.getElementById('log-now-btn');
    if (logNowBtn) {
        logNowBtn.addEventListener('click', () => {
            try {
                const now = new persianDate();
                dayInput.value = now.date();
                monthInput.value = now.month();
                yearInput.value = now.year();
                const timeNow = new Date();
                const firstStartTimeInput = document.querySelector('input[name="start_time[]"]');
                if (firstStartTimeInput) {
                    firstStartTimeInput.value = `${String(timeNow.getHours()).padStart(2, '0')}:${String(timeNow.getMinutes()).padStart(2, '0')}`;
                    applyTimeMask(firstStartTimeInput);
                }
                updateMaxDays();
            } catch (e) { alert("خطا: کتابخانه تاریخ شمسی (`persian-date.min.js`) بارگذاری نشده است."); }
        });
    }

    // --- Form Persistence (Only on index.php) ---
    const logForm = document.getElementById('log-form');
    const isEditPage = new URLSearchParams(window.location.search).has('date');

    if (logForm && dayInput && monthInput && yearInput && !isEditPage) {
        const STORAGE_KEY = 'dailyLogState';

        const saveState = () => {
            const firstStartTimeInput = logForm.querySelector('input[name="start_time[]"]');
            if (!firstStartTimeInput) return;
            const state = {
                day: dayInput.value,
                month: monthInput.value,
                year: yearInput.value,
                startTime: firstStartTimeInput.value,
                timestamp: new Date().toISOString().slice(0, 10) // YYYY-MM-DD
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        };

        const loadState = () => {
            const savedStateJSON = localStorage.getItem(STORAGE_KEY);
            if (!savedStateJSON) return;

            const savedState = JSON.parse(savedStateJSON);
            const today = new Date().toISOString().slice(0, 10);

            if (savedState.timestamp !== today) {
                localStorage.removeItem(STORAGE_KEY);
                return;
            }

            dayInput.value = savedState.day;
            monthInput.value = savedState.month;
            yearInput.value = savedState.year;

            const firstStartTimeInput = logForm.querySelector('input[name="start_time[]"]');
            if (firstStartTimeInput) {
                firstStartTimeInput.value = savedState.startTime;
                applyTimeMask(firstStartTimeInput);
            }
            updateMaxDays();
        };

        loadState();
        logForm.addEventListener('input', saveState);
    }
});
