document.addEventListener("DOMContentLoaded", function() {
    const thumbnails = document.querySelectorAll('.video-thumbnails img');
    const mainVideo = document.getElementById('main-video');
    const mainSource = mainVideo.querySelector('source');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const videoSrc = thumb.getAttribute('data-video');
            mainSource.src = videoSrc;
            mainVideo.load();
            mainVideo.play();
        });
    });
});