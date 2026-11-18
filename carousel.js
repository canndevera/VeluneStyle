document.addEventListener('DOMContentLoaded', () => {
    const slides = Array.from(document.querySelectorAll('.hero-slide'));
    const prevButton = document.querySelector('.hero-control.prev');
    const nextButton = document.querySelector('.hero-control.next');
    if (!slides.length) return;

    let index = 0;
    let intervalId;

    function showSlide(nextIndex) {
        slides[index].classList.remove('active');
        index = (nextIndex + slides.length) % slides.length;
        slides[index].classList.add('active');
    }

    function startAutoPlay() {
        clearInterval(intervalId);
        intervalId = setInterval(() => showSlide(index + 1), 3000);
    }

    prevButton?.addEventListener('click', () => {
        showSlide(index - 1);
        startAutoPlay();
    });

    nextButton?.addEventListener('click', () => {
        showSlide(index + 1);
        startAutoPlay();
    });

    startAutoPlay();
});
