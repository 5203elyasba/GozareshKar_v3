document.addEventListener('DOMContentLoaded', function() {
    const addIntervalBtn = document.getElementById('add-interval');

    if (addIntervalBtn) {
        addIntervalBtn.addEventListener('click', function() {
            const container = document.getElementById('time-intervals-container');
            const newInterval = document.createElement('div');
            newInterval.classList.add('row', 'mb-2', 'time-interval-row');

            // Note: Using text inputs for time to ensure compatibility and avoid browser-specific time picker issues.
            // User can be instructed to enter time in HH:MM format.
            newInterval.innerHTML = `
                <div class="col">
                    <label class="form-label">ساعت شروع</label>
                    <input type="time" class="form-control" name="start_time[]" required>
                </div>
                <div class="col">
                    <label class="form-label">ساعت پایان</label>
                    <input type="time" class="form-control" name="end_time[]" required>
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-interval">-</button>
                </div>
            `;
            container.appendChild(newInterval);
        });
    }

    const container = document.getElementById('time-intervals-container');
    if (container) {
        // Use event delegation to handle clicks on dynamically added remove buttons
        container.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-interval')) {
                // Find the closest parent row and remove it
                e.target.closest('.time-interval-row').remove();
            }
        });
    }
});
