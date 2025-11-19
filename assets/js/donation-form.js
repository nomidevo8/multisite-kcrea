jQuery(document).ready(function ($) {
    $('form[data-form_id]').each(function () {
        const $form = $(this);

        const $interval = $form.find('select[name="billing_interval"]');
        const $hiddenStart = $form.find('input[name="billing_start_date"]');

        if (!$interval.length || !$hiddenStart.length) return;

        // Create visible select if not exists
        let $visibleSelect = $form.find('select.billing_start_date_select');
        if (!$visibleSelect.length) {
            $hiddenStart.after(`
                <div class="ff-el-input--label asterisk-right">
                    <label>Billing Start Date</label>
                </div>
                <div class="ff-el-input--content">
                    <select class="ff-el-form-control billing_start_date_select">
                        <option value="">Select</option>
                    </select>
                </div>
            `);
            $visibleSelect = $form.find('select.billing_start_date_select');
        }

        const formatDateDisplay = (d) =>
            d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const formatDateValue = (d) => {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        };

        function updateBillingStartOptions() {
            const val = $interval.val();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            $visibleSelect.empty().append('<option value="">Select</option>');

            // Always add today as first option
            $visibleSelect.append(
                `<option value="${formatDateValue(today)}">Today (${formatDateDisplay(today)})</option>`
            );

            let stepDays = 0, stepMonths = 0, stepYears = 0;

            switch (val) {
                case 'daily': stepDays = 1; break;
                case 'weekly': stepDays = 7; break;
                case 'every_two_week': stepDays = 14; break;
                case 'monthly': stepMonths = 1; break;
                case 'yearly': stepYears = 1; break;
            }

            for (let i = 1; i <= 3; i++) {
                const d = new Date(today);

                // For daily, start at least 2 days from today
                if (val === 'daily') d.setDate(today.getDate() + 3 + (i - 1) * stepDays);
                else if (stepDays) d.setDate(today.getDate() + stepDays * i);
                else if (stepMonths) d.setMonth(today.getMonth() + stepMonths * i);
                else if (stepYears) d.setFullYear(today.getFullYear() + stepYears * i);

                const label = `Next ${i} ${val.replace(/_/g, ' ')}${i > 1 ? 's' : ''}`;
                $visibleSelect.append(
                    `<option value="${formatDateValue(d)}">${label} (${formatDateDisplay(d)})</option>`
                );
            }
        }


        $interval.on('change', updateBillingStartOptions);
        $visibleSelect.on('change', function () {
            const selected = $(this).val();
            $hiddenStart.val(selected).trigger('change');
        });

        if ($interval.val()) updateBillingStartOptions();
    });
});
