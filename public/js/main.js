document.addEventListener('DOMContentLoaded', function() {

    // --- Function to apply mask to time inputs ---
    function applyTimeMask(element) {
        IMask(element, {
            mask: 'HH:MM',
            blocks: {
                HH: { mask: IMask.MaskedRange, from: 0, to: 23, maxLength: 2, placeholderChar: 'H' },
                MM: { mask: IMask.MaskedRange, from: 0, to: 59, maxLength: 2, placeholderChar: 'M' }
            },
            lazy: false
        });
    }

    // --- Initialize Jalali Date Picker ---
    const dateInput = document.getElementById('log_date');
    if (dateInput) {
        new persianDatepicker(dateInput, {
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false
        });
    }

    // --- Apply mask to initial time elements ---
    document.querySelectorAll('.time-input').forEach(applyTimeMask);

    // --- Handle adding new WORK intervals ---
    const addWorkIntervalBtn = document.getElementById('add-interval');
    const workContainer = document.getElementById('time-intervals-container');
    if (addWorkIntervalBtn && workContainer) {
        addWorkIntervalBtn.addEventListener('click', function() {
            const newInterval = document.createElement('div');
            newInterval.classList.add('row', 'mb-2');
            newInterval.innerHTML = `
                <div class="col"><label class="form-label">ساعت شروع</label><input type="text" class="form-control time-input" name="start_time[]" placeholder="HH:MM" required></div>
                <div class="col"><label class="form-label">ساعت پایان</label><input type="text" class="form-control time-input" name="end_time[]" placeholder="HH:MM" required></div>
                <div class="col-auto d-flex align-items-end"><button type="button" class="btn btn-danger remove-interval">-</button></div>
            `;
            workContainer.appendChild(newInterval);
            newInterval.querySelectorAll('.time-input').forEach(applyTimeMask);
        });
    }

    // --- Handle adding new BREAK intervals ---
    const addBreakIntervalBtn = document.getElementById('add-break-interval');
    const breakContainer = document.getElementById('break-intervals-container');
    if (addBreakIntervalBtn && breakContainer) {
        addBreakIntervalBtn.addEventListener('click', function() {
            const newInterval = document.createElement('div');
            newInterval.classList.add('row', 'mb-2');
            newInterval.innerHTML = `
                <div class="col"><label class="form-label">شروع استراحت</label><input type="text" class="form-control time-input" name="break_start_time[]" placeholder="HH:MM"></div>
                <div class="col"><label class="form-label">پایان استراحت</label><input type="text" class="form-control time-input" name="break_end_time[]" placeholder="HH:MM"></div>
                <div class="col-auto d-flex align-items-end"><button type="button" class="btn btn-danger remove-interval">-</button></div>
            `;
            breakContainer.appendChild(newInterval);
            newInterval.querySelectorAll('.time-input').forEach(applyTimeMask);
        });
    }

    // --- Handle removing intervals (both work and break) using event delegation ---
    const formCard = document.querySelector('.card');
    if (formCard) {
        formCard.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-interval')) {
                e.target.closest('.row').remove();
            }
        });
    }
});
