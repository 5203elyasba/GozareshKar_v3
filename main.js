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
        } catch (e) {
            console.error("IMask could not be initialized.", e);
        }
    }

    // --- Date Input Logic ---
    const dayInput = document.querySelector('input[name="log_day"]');
    const monthInput = document.querySelector('input[name="log_month"]');
    const yearInput = document.querySelector('input[name="log_year"]');

    function updateMaxDays() {
        if (!dayInput || !monthInput || !yearInput) return;
        const month = parseInt(monthInput.value, 10);
        const year = parseInt(yearInput.value, 10);
        let maxDays = 31;
        if (month >= 7 && month <= 11) {
            maxDays = 30;
        } else if (month === 12) {
            // Simple leap year check for Jalali calendar is complex.
            // A common approximation is that years that have a remainder of 1, 5, 9, 13, 17, 22, 26, 30 when divided by 33 are leap years.
            // For simplicity here, we'll assume 29 days unless a more precise library is needed later.
            const leap_years_pattern = [1, 5, 9, 13, 17, 22, 26, 30];
            if(leap_years_pattern.includes(year % 33)) {
                 maxDays = 30;
            } else {
                 maxDays = 29;
            }
        }
        dayInput.max = maxDays;
        if (parseInt(dayInput.value, 10) > maxDays) {
            dayInput.value = maxDays;
        }
    }

    if (monthInput && yearInput) {
        monthInput.addEventListener('change', updateMaxDays);
        yearInput.addEventListener('change', updateMaxDays);
        // Initial check
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
        const intervalCount = workContainer.querySelectorAll('.row').length;
        addWorkBtn.disabled = intervalCount >= 2;
    }

    if (addWorkBtn && workContainer) {
        addWorkBtn.addEventListener('click', () => {
            addWorkInterval(workContainer);
            checkIntervalLimit();
        });
    }

    // --- Event Listener for "Fetch Date" button ---
    const fetchDateBtn = document.getElementById('fetch-date-btn');
    if (fetchDateBtn) {
        fetchDateBtn.addEventListener('click', function() {
            const day = document.querySelector('input[name="log_day"]').value.padStart(2, '0');
            const month = document.querySelector('input[name="log_month"]').value.padStart(2, '0');
            const year = document.querySelector('input[name="log_year"]').value;
            if (day && month && year) {
                const dateStr = `${year}/${month}/${day}`;
                window.location.href = 'index.php?date=' + dateStr;
            }
        });
    }

    // --- Form Persistence using localStorage ---
    const form = document.getElementById('log-form');
    const dayInputForPersistence = form.querySelector('input[name="log_day"]');
    const monthInputForPersistence = form.querySelector('select[name="log_month"]');
    const yearInputForPersistence = form.querySelector('input[name="log_year"]');

    function saveFormState() {
        const firstStartTime = document.querySelector('input[name="start_time[]"]');
        const state = {
            day: dayInputForPersistence.value,
            month: monthInputForPersistence.value,
            year: yearInputForPersistence.value,
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

            // Clear if saved data is from a previous day
            if (now.getDate() !== savedDate.getDate() || now.getMonth() !== savedDate.getMonth() || now.getFullYear() !== savedDate.getFullYear()) {
                localStorage.removeItem('formState');
                return;
            }

            // Repopulate form
            dayInputForPersistence.value = state.day;
            monthInputForPersistence.value = state.month;
            yearInputForPersistence.value = state.year;
            const firstStartTime = document.querySelector('input[name="start_time[]"]');
            if (firstStartTime) {
                firstStartTime.value = state.startTime;
            }
            updateMaxDays(); //
        }
    }

    // Don't load state if we are in edit mode from a URL
    if (!new URLSearchParams(window.location.search).has('date')) {
        loadFormState();
    }

    if(form) {
        form.addEventListener('input', saveFormState);
    }


    // --- Event Listener for removing intervals ---
    const formCard = document.getElementById('log-form-card');
    if (formCard) {
        formCard.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-interval')) {
                e.target.closest('.row').remove();
                checkIntervalLimit(); // Re-check limit after removing
            }
        });
    }

    // Initial check on page load
    checkIntervalLimit();
});
