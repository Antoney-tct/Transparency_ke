//video thumbsnail
        document.addEventListener('DOMContentLoaded', function() {
            const mainVideo = document.getElementById('mainVideo');
            const thumbnails = document.querySelectorAll('.thumbnail');
            
            //  change video
            function changeVideo(videoSrc) {
                // Change  source
                mainVideo.src = videoSrc;
                
                // Load and play 
                mainVideo.load();
                mainVideo.play().catch(e => {
                    console.error('Video play failed:', e);
                });
            }
            
            // Set the videos in order from the first one
            const firstThumbnail = document.querySelector('.thumbnail.active');
            if (firstThumbnail) {
                const initialVideo = firstThumbnail.getAttribute('data-video');
                changeVideo(initialVideo);
            }
            
            // click functionality
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    // Get the video path from data attribute
                    const videoSrc = this.getAttribute('data-video');
                    
                    // Change the active class
                    thumbnails.forEach(thumb => thumb.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Change the video
                    changeVideo(videoSrc);
                });
            });
            
            // Handle video errors
            mainVideo.addEventListener('error', function() {
                console.error('Error loading video:', mainVideo.src);
                alert('Error loading the video. Please try another one.');
            });
        });
   