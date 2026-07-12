document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const root = document.documentElement;
    const storedTheme = localStorage.getItem('assetflow-theme');
    if (storedTheme === 'dark') {
        body.classList.add('theme-dark');
    }
    root.style.colorScheme = body.classList.contains('theme-dark') ? 'dark' : 'light';
    if (localStorage.getItem('assetflow-sidebar') === 'collapsed') {
        body.classList.add('sidebar-collapsed');
    }

    const palette = ['#15803d', '#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#0891b2', '#7c3aed', '#64748b', '#0f766e', '#ea580c'];

    document.querySelectorAll('.kpi strong').forEach((counter) => {
        const target = Number(counter.textContent.replace(/[^\d.-]/g, ''));
        if (!Number.isFinite(target)) return;
        const duration = 850;
        const start = performance.now();
        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            counter.textContent = String(Math.round(target * eased));
            if (progress < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });

    document.querySelectorAll('.status-pill').forEach((pill) => {
        const text = pill.textContent.trim().toLowerCase();
        pill.classList.toggle('warning', ['reserved', 'maintenance', 'under maintenance', 'pending'].includes(text));
        pill.classList.toggle('danger', ['lost', 'retired', 'disposed', 'rejected', 'damaged', 'missing'].includes(text));
    });

    document.querySelectorAll('.data-table').forEach((table) => {
        if (window.DataTable) {
            new DataTable(table, {
                responsive: true,
                pageLength: 10,
                language: {
                    search: '',
                    searchPlaceholder: 'Search records',
                    lengthMenu: '_MENU_ rows',
                    info: '_START_ to _END_ of _TOTAL_'
                }
            });
        }
    });

    document.querySelectorAll('.js-chart').forEach((chart) => {
        if (!window.Chart) return;
        const payload = JSON.parse(chart.dataset.chart || '{"type":"bar","rows":[]}');
        const rows = payload.rows || [];
        const dark = body.classList.contains('theme-dark');
        new Chart(chart, {
            type: payload.type || 'bar',
            data: {
                labels: rows.map((row) => row.label),
                datasets: [{
                    label: chart.closest('.panel')?.querySelector('h2')?.textContent || 'AssetFlow',
                    data: rows.map((row) => Number(row.value || 0)),
                    borderColor: '#15803d',
                    borderWidth: payload.type === 'line' ? 3 : 1,
                    tension: .38,
                    fill: payload.type === 'line',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    backgroundColor: payload.type === 'line' ? 'rgba(21, 128, 61, .14)' : palette
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: { duration: 900, easing: 'easeOutQuart' },
                plugins: {
                    legend: {
                        display: payload.type === 'pie' || payload.type === 'doughnut',
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, color: dark ? '#edf3f7' : '#172033' }
                    }
                },
                scales: payload.type === 'pie' || payload.type === 'doughnut' ? {} : {
                    x: { grid: { display: false }, ticks: { color: dark ? '#a9b5c2' : '#667085' } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: dark ? '#a9b5c2' : '#667085' }, grid: { color: dark ? '#2c3a4a' : '#e5e7eb' } }
                }
            }
        });
    });

    document.querySelectorAll('.menu-toggle').forEach((button) => {
        button.addEventListener('click', () => body.classList.toggle('sidebar-open'));
    });
    document.querySelectorAll('.sidebar-collapse').forEach((button) => {
        button.addEventListener('click', () => {
            body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('assetflow-sidebar', body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'open');
        });
    });
    document.querySelectorAll('.sidebar nav a').forEach((link) => {
        link.addEventListener('click', () => body.classList.remove('sidebar-open'));
    });

    document.querySelectorAll('.js-dark-toggle, .js-dark-toggle-btn').forEach((control) => {
        if (control.matches('input')) {
            control.checked = body.classList.contains('theme-dark');
        }
        control.addEventListener('click', () => {
            body.classList.toggle('theme-dark');
            localStorage.setItem('assetflow-theme', body.classList.contains('theme-dark') ? 'dark' : 'light');
            root.style.colorScheme = body.classList.contains('theme-dark') ? 'dark' : 'light';
            document.querySelectorAll('.js-dark-toggle').forEach((input) => {
                input.checked = body.classList.contains('theme-dark');
            });
        });
    });

    document.querySelectorAll('.js-page-search').forEach((input) => {
        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            document.querySelectorAll('tbody tr, .notification, .workflow-list article, .calendar-board > div, .activity-list li').forEach((row) => {
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
