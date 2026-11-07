const track = document.querySelector('.slider__track');
const slides = document.querySelectorAll('.slider__slide');
const bars = document.querySelectorAll('.slider__bar');
const slideName = document.getElementById('slideName');

const slideTitles = [
    'ВАЗ-2106 — «шестёрка»',
    'ВАЗ-2107 — «семёрка»',
    'ВАЗ-2199 — классика с характером',
];

if (track && slides.length && bars.length) {
    let currentIndex = 0;
    const slideDuration = 4000;
    let timer;

    function goToSlide(index) {
        currentIndex = index;
        track.style.transform = `translateX(${-currentIndex * 100}%)`;

        slides.forEach((slide, i) => {
            slide.classList.toggle('slider__slide--active', i === currentIndex);
        });

        bars.forEach((bar) => {
            bar.classList.remove('slider__bar--active');
            void bar.offsetWidth;
        });

        bars[currentIndex].classList.add('slider__bar--active');

        if (slideName) {
            slideName.textContent = slideTitles[currentIndex] || '';
        }

        clearTimeout(timer);
        timer = setTimeout(() => {
            goToSlide((currentIndex + 1) % slides.length);
        }, slideDuration);
    }

    bars.forEach((bar, index) => {
        bar.addEventListener('click', () => goToSlide(index));
    });

    goToSlide(0);
}
