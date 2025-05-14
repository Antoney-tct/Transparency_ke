
  const hero = document.querySelector(".hero");
  const images = ["image-1", "image-2", "image-3"];
  let currentImageIndex = 0;

  function changeBackgroundImage() {
    hero.classList.remove(images[currentImageIndex]);
    currentImageIndex = (currentImageIndex + 1) % images.length;
    hero.classList.add(images[currentImageIndex]);
    for (let i = 0; i < images.length; i++) {
      const imageClass = images[i];
      const beforeElement = hero.querySelector(`.${imageClass}::before`);
      if (beforeElement) {
        if (i === currentImageIndex) {
          beforeElement.style.opacity = 1;
        } else {
          beforeElement.style.opacity = 0;
        }
      }
    }
  }

  setInterval(changeBackgroundImage, 5000);
