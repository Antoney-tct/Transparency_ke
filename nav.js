const hiddenLinkTrigger = document.getElementById('hiddenLinkTrigger');
const hiddenSection = document.getElementById('hiddenSection');
const closeButton = document.getElementById('closeButton');

hiddenLinkTrigger.addEventListener('click', () => {
    hiddenSection.classList.add('active');
});

closeButton.addEventListener('click', () => {
    hiddenSection.classList.remove('active');
});
