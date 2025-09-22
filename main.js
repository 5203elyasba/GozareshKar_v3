document.addEventListener('DOMContentLoaded', function() {

    // --- Time Input Masking ---
    function applyTimeMask(element) {
        if (!element) return;
        try { IMask(element, { mask: 'HH:MM', blocks: { HH: { mask: IMask.MaskedRange, from: 0, to: 23 }, MM: { mask: IMask.MaskedRange, from: 0, to: 59 } }, lazy: false });
        } catch (e) { console.error("IMask could not be initialized.", e); }
    }
    document.querySelectorAll('.time-input').forEach(applyTimeMask);

    // --- Date Input & Validation Logic ---
    const dayInput = document.querySelector('input[name="log_day"]');
    const monthInput = document.querySelector('select[name="log_month"]');
    const yearInput = document.querySelector('input[name="log_year"]');

    function setupCustomNumberInputs() {
        document.querySelectorAll('.custom-number-input').forEach(container => {
            const input = container.querySelector('input[type="text"]');
            const decBtn = container.querySelector('.btn-decrement');
            const incBtn = container.querySelector('.btn-increment');
            if (!input || !decBtn || !incBtn) return;
            decBtn.addEventListener('click', () => { let val = parseInt(input.value) || 1; if(val > 1) input.value = val - 1; });
            incBtn.addEventListener('click', () => { let val = parseInt(input.value) || 0; if(val < 31) input.value = val + 1; });
        });
    }
    if (dayInput) setupCustomNumberInputs();

    // --- Add/Remove Work Intervals ---
    const addWorkBtn = document.getElementById('add-interval');
    const workContainer = document.getElementById('time-intervals-container');

    function updateRemoveButtons() {
        const rows = workContainer.querySelectorAll('.time-interval-row');
        rows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-interval');
            if (removeBtn) {
                removeBtn.style.display = (rows.length > 1) ? 'inline-block' : 'none';
            }
        });
    }

    if (addWorkBtn && workContainer) {
        addWorkBtn.addEventListener('click', () => {
            const newInterval = document.createElement('div');
            newInterval.classList.add('row', 'g-2', 'mb-2', 'align-items-center', 'time-interval-row');
            newInterval.innerHTML = `
                <input type="hidden" name="log_id[]" value="">
                <div class="col"><label class="form-label">ساعت ورود</label><input type="text" class="form-control time-input" name="start_time[]"></div>
                <div class="col-auto"><button type="button" class="btn btn-outline-primary btn-sm btn-log-now" data-type="start">ثبت</button></div>
                <div class="col"><label class="form-label">ساعت خروج</label><input type="text" class="form-control time-input" name="end_time[]"></div>
                <div class="col-auto"><button type="button" class="btn btn-outline-primary btn-sm btn-log-now" data-type="end">ثبت</button></div>
                <div class="col-auto"><button type="button" class="btn btn-sm btn-danger remove-interval">-</button></div>
            `;
            workContainer.appendChild(newInterval);
            newInterval.querySelectorAll('.time-input').forEach(applyTimeMask);
            updateRemoveButtons();
        });
    }

    workContainer.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('remove-interval')) {
            e.target.closest('.time-interval-row').remove();
            updateRemoveButtons();
        }
    });
    if (workContainer) updateRemoveButtons();


    // --- "Fetch Date" Button ---
    const fetchDateBtn = document.getElementById('fetch-date-btn');
    if (fetchDateBtn) {
        fetchDateBtn.addEventListener('click', () => {
            if (!dayInput || !monthInput || !yearInput) return;
            const dateStr = `${yearInput.value}/${String(monthInput.value).padStart(2, '0')}/${String(dayInput.value).padStart(2, '0')}`;
            window.location.href = 'index.php?date=' + dateStr;
        });
    }

    // --- "Log Now" AJAX Logic ---
    workContainer.addEventListener('click', function(e) {
        if (!e.target.classList.contains('btn-log-now')) return;

        const button = e.target;
        const row = button.closest('.time-interval-row');
        const logIdInput = row.querySelector('input[name="log_id[]"]');
        const type = button.dataset.type;
        const timeInput = (type === 'start') ? row.querySelector('input[name="start_time[]"]') : row.querySelector('input[name="end_time[]"]');

        if (!timeInput.value.match(/^\d{2}:\d{2}$/)) { alert('فرمت زمان باید HH:MM باشد.'); return; }

        const jalaliDate = `${yearInput.value}/${String(monthInput.value).padStart(2,'0')}/${String(dayInput.value).padStart(2,'0')}`;

        button.disabled = true;
        button.textContent = '...';

        fetch('save_time_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                log_id: logIdInput.value,
                log_date: jalaliDate,
                time: timeInput.value,
                type: type
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                button.textContent = 'ثبت شد';
                button.classList.replace('btn-outline-primary', 'btn-success');
                timeInput.readOnly = true;
                if (data.log_id && !logIdInput.value) {
                    logIdInput.value = data.log_id;
                }
            } else {
                alert('خطا: ' + data.message);
                button.disabled = false;
                button.textContent = 'ثبت';
            }
        })
        .catch(err => {
            alert('خطای شبکه.');
            button.disabled = false;
            button.textContent = 'ثبت';
        });
    });
});
