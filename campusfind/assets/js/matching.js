// Smart Matching JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const matchItems = document.querySelectorAll('.match-item');
    matchItems.forEach(function(item) {
        item.addEventListener('click', function() {
            const details = this.querySelector('.match-details');
            if (details) {
                details.classList.toggle('show');
            }
        });
    });

    // Auto-refresh matches every 30 seconds
    const matchContainer = document.getElementById('matches-container');
    if (matchContainer) {
        setInterval(function() {
            fetch(window.location.href + '?ajax=1')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMatches = doc.getElementById('matches-container');
                    if (newMatches) {
                        matchContainer.innerHTML = newMatches.innerHTML;
                    }
                })
                .catch(error => console.log('Match refresh error:', error));
        }, 30000);
    }
});