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

    // --- Initialize Jalali Date Picker ---
    try {
        const dateInput = document.getElementById('log_date');
        if (dateInput) {
            new persianDatepicker(dateInput, {
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: false,
                onSelect: function(unix) {
                    const selectedDate = new persianDate(unix).format('YYYY/MM/DD');
                    window.location.href = 'index.php?date=' + selectedDate;
                }
            });
        }
    } catch (e) {
        console.error("persianDatepicker could not be initialized.", e);
    }

    // --- Apply mask to initial time elements ---
    document.querySelectorAll('.time-input').forEach(applyTimeMask);

    // --- Generic function to add new interval rows ---
    function addInterval(container, namePrefix) {
        const newInterval = document.createElement('div');
        newInterval.classList.add('row', 'g-2', 'mb-2', 'align-items-end');
        const isWork = namePrefix === 'start_time';
        const label = isWork ? 'کاری' : 'استراحت';
        const required = isWork ? 'required' : '';

        newInterval.innerHTML = `
            <div class="col-md"><label class="form-label">شروع ${label}</label><input type="text" class="form-control time-input" name="${namePrefix}[]" placeholder="HH:MM" ${required}></div>
            <div class="col-md"><label class="form-label">پایان ${label}</label><input type="text" class="form-control time-input" name="${namePrefix.replace('start', 'end')}[]" placeholder="HH:MM" ${required}></div>
            <div class="col-md-auto"><button type="button" class="btn btn-danger remove-interval">-</button></div>
        `;
        container.appendChild(newInterval);
        newInterval.querySelectorAll('.time-input').forEach(applyTimeMask);
    }

    // --- Handle adding new WORK intervals ---
    const addWorkBtn = document.getElementById('add-interval');
    const workContainer = document.getElementById('time-intervals-container');
    if (addWorkBtn && workContainer) {
        addWorkBtn.addEventListener('click', () => addInterval(workContainer, 'start_time'));
    }

    // --- Handle adding new BREAK intervals ---
    const addBreakBtn = document.getElementById('add-break-interval');
    const breakContainer = document.getElementById('break-intervals-container');
    if (addBreakBtn && breakContainer) {
        addBreakBtn.addEventListener('click', () => addInterval(breakContainer, 'break_start_time'));
    }

    // --- Handle removing intervals using event delegation ---
    const formCard = document.getElementById('log-form-card');
    if (formCard) {
        formCard.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-interval')) {
                e.target.closest('.row').remove();
            }
        });
    }
});
