// Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    }

    // Chart initialization (if Chart.js is loaded)
    if (typeof Chart !== 'undefined') {
        const charts = document.querySelectorAll('.chart-container');
        charts.forEach(function(container) {
            const ctx = container.querySelector('canvas');
            if (ctx) {
                new Chart(ctx, {
                    type: container.dataset.chartType || 'bar',
                    data: JSON.parse(container.dataset.chartData || '{}'),
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        });
    }
});