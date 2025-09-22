document.addEventListener('DOMContentLoaded', function() {

    // --- Function to apply mask to time inputs ---
    function applyTimeMask(element) {
        if (!element) return;
        try {
            IMask(element, {
                mask: 'HH:MM',
                blocks: {
                    HH: { mask: IMask.MaskedRange, from: 0, to: 23, maxLength: 2 },
                    MM: { mask: IMask.MaskedRange, from: 0, to: 59, maxLength: 2 }
                },
                lazy: false
            });
        } catch (e) { console.error("IMask could not be initialized.", e); }
    }

    // --- Date Input Logic ---
    const dayInput = document.querySelector('input[name="log_day"]');
    const monthInput = document.querySelector('select[name="log_month"]');
    const yearInput = document.querySelector('input[name="log_year"]');

    function updateMaxDays() {
        if (!dayInput || !monthInput || !yearInput) return;
        const month = parseInt(monthInput.value, 10);
        const year = parseInt(yearInput.value, 10);
        let maxDays = 31;
        if (month >= 7 && month <= 11) {
            maxDays = 30;
        } else if (month === 12) {
            const leap_years_pattern = [1, 5, 9, 13, 17, 22, 26, 30];
            maxDays = leap_years_pattern.includes(year % 33) ? 30 : 29;
        }
        dayInput.max = maxDays;
        if (parseInt(dayInput.value, 10) > maxDays) {
            dayInput.value = maxDays;
        }
    }

    if (monthInput && yearInput) {
        monthInput.addEventListener('change', updateMaxDays);
        yearInput.addEventListener('change', updateMaxDays);
        updateMaxDays();
    }

    // --- Apply mask to initial time elements ---
    document.querySelectorAll('.time-input').forEach(applyTimeMask);

    // --- Function to add new work interval rows ---
    function addWorkInterval(container) {
        const newInterval = document.createElement('div');
        newInterval.classList.add('row', 'g-2', 'mb-2', 'align-items-end');
        newInterval.innerHTML = `
            <div class="col-md"><label class="form-label">ساعت شروع</label><input type="text" class="form-control time-input" name="start_time[]" placeholder="HH:MM" required></div>
            <div class="col-md"><label class="form-label">ساعت پایان</label><input type="text" class="form-control time-input" name="end_time[]" placeholder="HH:MM" required></div>
            <div class="col-md-auto"><button type="button" class="btn btn-danger remove-interval">-</button></div>
        `;
        container.appendChild(newInterval);
        newInterval.querySelectorAll('.time-input').forEach(applyTimeMask);
    }

    // --- Event Listeners for adding intervals ---
    const addWorkBtn = document.getElementById('add-interval');
    const workContainer = document.getElementById('time-intervals-container');
    function checkIntervalLimit() {
        if (!workContainer || !addWorkBtn) return;
        const intervalCount = workContainer.querySelectorAll('.row').length;
        addWorkBtn.disabled = intervalCount >= 2;
    }
    if (addWorkBtn && workContainer) {
        addWorkBtn.addEventListener('click', () => { addWorkInterval(workContainer); checkIntervalLimit(); });
    }

    // --- Event Listener for "Log Now" button ---
    const logNowBtn = document.getElementById('log-now-btn');
    if (logNowBtn) {
        logNowBtn.addEventListener('click', function() {
            try {
                const now = new persianDate();
                dayInput.value = now.date();
                monthInput.value = now.month();
                yearInput.value = now.year();

                const timeNow = new Date();
                const hours = timeNow.getHours().toString().padStart(2, '0');
                const minutes = timeNow.getMinutes().toString().padStart(2, '0');

                const firstStartTimeInput = document.querySelector('input[name="start_time[]"]');
                if (firstStartTimeInput) {
                    firstStartTimeInput.value = `${hours}:${minutes}`;
                    applyTimeMask(firstStartTimeInput);
                }
                updateMaxDays();
            } catch (e) {
                console.error("persianDate library is required for this feature.", e);
                alert("خطا: کتابخانه تاریخ شمسی بارگذاری نشده است.");
            }
        });
    }

    // --- Event Listener for "Fetch Date" button ---
    const fetchDateBtn = document.getElementById('fetch-date-btn');
    if (fetchDateBtn) {
        fetchDateBtn.addEventListener('click', function() {
            const day = dayInput.value.padStart(2, '0');
            const month = monthInput.value.padStart(2, '0');
            const year = yearInput.value;
            if (day && month && year) { window.location.href = 'index.php?date=' + `${year}/${month}/${day}`; }
        });
    }

    // --- Form Persistence using localStorage ---
    const form = document.getElementById('log-form');
    function saveFormState() {
        const firstStartTime = document.querySelector('input[name="start_time[]"]');
        const state = {
            day: dayInput.value, month: monthInput.value, year: yearInput.value,
            startTime: firstStartTime ? firstStartTime.value : '',
            timestamp: new Date().getTime()
        };
        localStorage.setItem('formState', JSON.stringify(state));
    }
    function loadFormState() {
        const savedState = localStorage.getItem('formState');
        if (savedState) {
            const state = JSON.parse(savedState);
            const now = new Date();
            const savedDate = new Date(state.timestamp);
            if (now.toDateString() !== savedDate.toDateString()) {
                localStorage.removeItem('formState');
                return;
            }
            dayInput.value = state.day;
            monthInput.value = state.month;
            yearInput.value = state.year;
            const firstStartTime = document.querySelector('input[name="start_time[]"]');
            if (firstStartTime) { firstStartTime.value = state.startTime; }
            updateMaxDays();
        }
    }
    if (!new URLSearchParams(window.location.search).has('date')) {
        loadFormState();
    }
    if(form) { form.addEventListener('input', saveFormState); }

    // --- Event Listener for removing intervals ---
    const formCard = document.getElementById('log-form-card');
    if (formCard) {
        formCard.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-interval')) {
                e.target.closest('.row').remove();
                checkIntervalLimit();
            }
        });
    }
    checkIntervalLimit();
});
