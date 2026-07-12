document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const storedTheme = localStorage.getItem('assetflow-theme');
    if (storedTheme === 'dark') {
        body.classList.add('theme-dark');
    }

    document.querySelectorAll('.data-table').forEach((table) => {
        if (window.DataTable) {
            new DataTable(table);
        }
    });

    document.querySelectorAll('.js-chart').forEach((chart) => {
        if (!window.Chart) return;
        const payload = JSON.parse(chart.dataset.chart || '{"type":"bar","rows":[]}');
        const rows = payload.rows || [];
        new Chart(chart, {
            type: payload.type || 'bar',
            data: {
                labels: rows.map((row) => row.label),
                datasets: [{
                    label: chart.closest('.panel')?.querySelector('h2')?.textContent || 'AssetFlow',
                    data: rows.map((row) => Number(row.value || 0)),
                    borderColor: '#176b87',
                    backgroundColor: ['#176b87', '#bf6f13', '#6f7d2c', '#8a5a44', '#4b6584', '#2d6a4f', '#6d597a', '#457b9d', '#2a9d8f', '#e76f51']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: payload.type === 'pie' || payload.type === 'doughnut' } },
                scales: payload.type === 'pie' || payload.type === 'doughnut' ? {} : { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    });

    document.querySelectorAll('.menu-toggle').forEach((button) => {
        button.addEventListener('click', () => body.classList.toggle('sidebar-open'));
    });

    document.querySelectorAll('.js-dark-toggle, .js-dark-toggle-btn').forEach((control) => {
        if (control.matches('input')) {
            control.checked = body.classList.contains('theme-dark');
        }
        control.addEventListener('click', () => {
            body.classList.toggle('theme-dark');
            localStorage.setItem('assetflow-theme', body.classList.contains('theme-dark') ? 'dark' : 'light');
            document.querySelectorAll('.js-dark-toggle').forEach((input) => {
                input.checked = body.classList.contains('theme-dark');
            });
        });
    });

    document.querySelectorAll('.js-page-search').forEach((input) => {
        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            document.querySelectorAll('tbody tr, .notification, .workflow-list article, .calendar-board > div').forEach((row) => {
                row.classList.toggle('d-none', query !== '' && !row.textContent.toLowerCase().includes(query));
            });
        });
    });

    document.querySelectorAll('.js-show-spinner').forEach((button) => {
        button.addEventListener('click', () => {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.add('show');
                setTimeout(() => overlay.classList.remove('show'), 700);
            }
            const toastEl = document.getElementById('appToast');
            if (toastEl && window.bootstrap) {
                bootstrap.Toast.getOrCreateInstance(toastEl).show();
            }
        });
    });

    document.querySelectorAll('.js-export-csv').forEach((button) => {
        button.addEventListener('click', () => {
            const table = button.closest('.panel')?.querySelector('table');
            if (!table) return;
            const rows = [...table.querySelectorAll('tr')].map((row) => [...row.children].map((cell) => `"${cell.textContent.trim().replaceAll('"', '""')}"`).join(','));
            const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${button.closest('.panel')?.querySelector('h2')?.textContent || 'report'}.csv`;
            link.click();
            URL.revokeObjectURL(link.href);
        });
    });

    document.querySelectorAll('.js-export-excel').forEach((button) => {
        button.addEventListener('click', () => {
            const table = button.closest('.panel')?.querySelector('table');
            if (!table) return;
            const blob = new Blob(['\ufeff' + table.outerHTML], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${button.closest('.panel')?.querySelector('h2')?.textContent || 'report'}.xls`;
            link.click();
            URL.revokeObjectURL(link.href);
        });
    });

    document.querySelectorAll('.js-booking-calendar').forEach((calendar) => {
        let view = 'week';
        const bookings = JSON.parse(calendar.dataset.bookings || '[]');
        const render = () => {
            const today = new Date();
            const start = new Date(today.getFullYear(), today.getMonth(), view === 'week' ? today.getDate() : 1);
            const days = view === 'week' ? 7 : new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
            calendar.replaceChildren(...Array.from({ length: days }, (_, index) => {
                const day = new Date(start.getFullYear(), start.getMonth(), start.getDate() + index);
                const key = day.toISOString().slice(0, 10);
                const entries = bookings.filter((booking) => String(booking.starts_at || '').slice(0, 10) === key);
                const cell = document.createElement('div');
                const label = document.createElement('strong');
                label.textContent = day.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                cell.append(label);
                if (!entries.length) {
                    const empty = document.createElement('small');
                    empty.textContent = 'No bookings';
                    cell.append(empty);
                }
                entries.forEach((booking) => {
                    const resource = document.createElement('span');
                    resource.textContent = booking.resource_name || 'Resource';
                    const status = document.createElement('small');
                    status.textContent = booking.status || '';
                    cell.append(resource, status);
                });
                return cell;
            }));
        };
        document.querySelectorAll('.js-calendar-view').forEach((button) => button.addEventListener('click', () => {
            view = button.dataset.view || 'week';
            document.querySelectorAll('.js-calendar-view').forEach((item) => item.classList.toggle('active', item === button));
            render();
        }));
        render();
    });
});
