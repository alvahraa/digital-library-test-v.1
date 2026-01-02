// ==========================================
// DASHBOARD - ALL IN ONE
// Sakura Animation + Slideshow + Counter Animation
// ==========================================

(function() {
    'use strict';
    
    console.log('🚀 Dashboard Complete JS loaded');

    // ==========================================
    // SAKURA ANIMATION
    // ==========================================

    function createSakuraBg() {
        const container = document.getElementById('sakura-container-bg');
        if (!container) return;
        
        const sakura = document.createElement("div");
        sakura.classList.add("sakura-bg");
        
        sakura.style.left = Math.random() * 100 + "%";
        sakura.style.top = (Math.random() * -30 - 20) + "px";
        
        const size = Math.random() * 12 + 8;
        sakura.style.width = size + "px";
        sakura.style.height = size + "px";
        
        const duration = Math.random() * 10 + 15;
        sakura.style.animationDuration = duration + "s";

        container.appendChild(sakura);

        setTimeout(() => {
            sakura.remove();
        }, (duration + 2) * 1000);
    }

    function createSakuraFg() {
        const container = document.getElementById('sakura-container-fg');
        if (!container) return;
        
        const sakura = document.createElement("div");
        sakura.classList.add("sakura-fg");
        
        sakura.style.left = Math.random() * 100 + "%";
        sakura.style.top = (Math.random() * -30 - 20) + "px";
        
        const size = Math.random() * 14 + 10;
        sakura.style.width = size + "px";
        sakura.style.height = size + "px";
        
        const duration = Math.random() * 8 + 12;
        sakura.style.animationDuration = duration + "s";

        container.appendChild(sakura);

        setTimeout(() => {
            sakura.remove();
        }, (duration + 2) * 1000);
    }

    // Spawn sakura
    setInterval(() => {
        createSakuraBg();
    }, 400);

    setInterval(() => {
        if(Math.random() > 0.5) {
            createSakuraBg();
        }
    }, 600);

    setInterval(() => {
        if(Math.random() > 0.85) {
            createSakuraFg();
        }
    }, 2500);

    console.log('🌸 Sakura animation started');

    // ==========================================
    // WALLPAPER SLIDESHOW
    // ==========================================

    let currentSlide = 0;
    let slideInterval = null;

    const slides = document.querySelectorAll('.wallpaper-slide');
    const indicators = document.querySelectorAll('.indicator');

    console.log('📊 Found', slides.length, 'slides and', indicators.length, 'indicators');

    function showSlide(index) {
        if (slides.length === 0 || indicators.length === 0) {
            console.error('❌ Slides or indicators not found!');
            return;
        }
        
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
        
        console.log('✅ Showing slide:', index + 1);
    }

    function nextSlide() {
        if (slides.length === 0) return;
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function startSlideshow() {
        if (slides.length === 0 || indicators.length === 0) {
            console.error('❌ Cannot start slideshow - elements missing');
            return;
        }
        
        console.log('▶️ Starting slideshow...');
        
        showSlide(0);
        
        slideInterval = setInterval(nextSlide, 5000);
        
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', function() {
                console.log('👆 Clicked indicator:', index + 1);
                currentSlide = index;
                showSlide(currentSlide);
                
                clearInterval(slideInterval);
                slideInterval = setInterval(nextSlide, 5000);
            });
        });
        
        console.log('✅ Slideshow started successfully!');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startSlideshow);
    } else {
        startSlideshow();
    }

    window.addEventListener('load', function() {
        if (slides.length > 0 && !slides[0].classList.contains('active')) {
            console.log('🔄 Re-initializing slideshow on window load');
            startSlideshow();
        }
    });

    console.log('✅ Dashboard Complete JS initialized!');

    // ==========================================
    // COUNTER ANIMATION (Lambat ke Cepat)
    // ==========================================
    
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000; // 2 detik
        const start = 0;
        const startTime = performance.now();
        
        // Ease Out Quart: Lambat di awal, cepat di akhir
        function easeOutQuart(t) {
            return 1 - Math.pow(1 - t, 4);
        }
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Apply easing
            const easedProgress = easeOutQuart(progress);
            const current = Math.floor(start + (target - start) * easedProgress);
            
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = target; // Pastikan nilai akhir tepat
            }
        }
        
        requestAnimationFrame(update);
    }
    
    // Trigger animation when element is in viewport
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && entry.target.textContent === '0') {
                animateCounter(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe all stat numbers
    document.querySelectorAll('.stat-number').forEach(el => {
        observer.observe(el);
    });
    
    console.log('🔢 Counter animation initialized!');

})(); // End IIFE