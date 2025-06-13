// cpd-session.js
// Handles CPD session modal gallery logic

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cpd-session-gallery').forEach(function (gallery) {
        const mediaItems = gallery.querySelectorAll('.gallery-media');
        if (mediaItems.length <= 1) return;
        let current = 0;
        let intervalId = null;

        function showMedia(idx) {
            mediaItems.forEach((el, i) => {
                el.style.display = (i === idx) ? 'block' : 'none';
            });
        }

        // Initial display
        showMedia(current);

        // Only auto-slide and no arrows on homepage (index.php)
        const isHome = window.location.pathname.endsWith('index.php') || window.location.pathname === '/' || window.location.pathname === '';
        if (isHome) {
            // Auto-slide, loop only once
            let looped = false;
            intervalId = setInterval(function () {
                if (current < mediaItems.length - 1) {
                    current++;
                    showMedia(current);
                } else if (!looped) {
                    looped = true;
                    current = 0;
                    showMedia(current);
                    clearInterval(intervalId); // Stop after one loop
                }
            }, 2500); // 2.5 seconds per slide
            return;
        }

        // Auto-slide, loop only once and stop at the last image for non-home pages
        let looped = false;
        intervalId = setInterval(function () {
            if (current < mediaItems.length - 1) {
            current++;
            showMedia(current);
            } else if (!looped) {
            looped = true;
            clearInterval(intervalId); // Stop at the last image after one loop
            }
        }, 2500); // 2.5 seconds per slide
    });
}); 