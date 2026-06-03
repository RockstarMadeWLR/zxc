document.addEventListener('DOMContentLoaded', function() {
    // Слайдер
    const track = document.querySelector('.slider-track');
    if (track) {
        const slides = document.querySelectorAll('.slide');
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');
        let currentIndex = 0;
        const totalSlides = slides.length;
        let autoSlideInterval;

        function updateSlider() {
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSlider();
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateSlider();
        }

        function startAutoSlide() {
            autoSlideInterval = setInterval(nextSlide, 3000);
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', () => {
                stopAutoSlide();
                prevSlide();
                startAutoSlide();
            });

            nextBtn.addEventListener('click', () => {
                stopAutoSlide();
                nextSlide();
                startAutoSlide();
            });
        }

        startAutoSlide();
    }

    // Анимация для уведомлений
    const statusForms = document.querySelectorAll('.status-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            setTimeout(() => {
                const toast = document.getElementById('notification-toast');
                if (toast) {
                    toast.classList.remove('hidden');
                    toast.classList.add('show');
                    setTimeout(() => {
                        toast.classList.remove('show');
                        toast.classList.add('hidden');
                    }, 2000);
                }
            }, 100);
        });
    });
});