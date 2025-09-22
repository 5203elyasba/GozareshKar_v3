document.addEventListener('DOMContentLoaded', function() {

    // --- Custom Number Spinner Logic ---
    function setupCustomNumberInputs() { /* ... same as before ... */ }
    setupCustomNumberInputs(document);

    // --- Time Input Masking ---
    function applyTimeMask(element) { /* ... same as before ... */ }
    document.querySelectorAll('.time-input').forEach(applyTimeMask);

    // --- Date Input & Validation Logic ---
    const dayInput = document.querySelector('input[name="log_day"], input[name="leave_day"]');
    const monthInput = document.querySelector('select[name="log_month"], select[name="leave_month"]');
    const yearInput = document.querySelector('input[name="log_year"], input[name="leave_year"]');
    function updateMaxDays() { /* ... same as before ... */ }
    if (monthInput && yearInput) {
        monthInput.addEventListener('change', updateMaxDays);
        yearInput.addEventListener('change', updateMaxDays);
        dayInput.addEventListener('change', updateMaxDays);
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
        newInterval.innerHTML = `<div class="col-md"><label class="form-label">ساعت ورود</label><input type="text" class="form-control time-input" name="start_time[]" placeholder="HH:MM" required></div><div class="col-md"><label class="form-label">ساعت خروج</label><input type="text" class="form-control time-input" name="end_time[]" placeholder="HH:MM" required></div><div class="col-md-auto"><button type="button" class="btn btn-danger remove-interval">-</button></div>`;
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
    checkIntervalLimit();

    // --- "Fetch Date" & "Log Now" buttons ---
    // ... logic for these buttons ...

    // --- Form Persistence using localStorage ---
    const form = document.getElementById('log-form');
    const firstStartTimeInput = form ? form.querySelector('input[name="start_time[]"]') : null;

    function saveFormState() {
        if (!dayInput || !monthInput || !yearInput) return;
        localStorage.setItem('formState', JSON.stringify({
            day: dayInput.value, month: monthInput.value, year: yearInput.value,
            startTime: firstStartTimeInput ? firstStartTimeInput.value : '',
            timestamp: new Date().getTime()
        }));
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
            if (firstStartTimeInput) { firstStartTimeInput.value = state.startTime; }
            updateMaxDays();
        }
    }

    // Attach listeners only on the main logging form
    if (form) {
        // Load state only if not in edit mode
        if (!new URLSearchParams(window.location.search).has('date')) {
            loadFormState();
        }
        // Save state on any change to the relevant fields
        dayInput.addEventListener('change', saveFormState);
        monthInput.addEventListener('change', saveFormState);
        yearInput.addEventListener('change', saveFormState);
        if (firstStartTimeInput) {
            firstStartTimeInput.addEventListener('input', saveFormState);
        }
    }
});
