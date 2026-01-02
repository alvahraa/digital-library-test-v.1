// ==========================================
// SAKURA ANIMATION - Jatuh dari atas sampai bawah
// ==========================================

// Layer Background - di belakang konten (banyak)
function createSakuraBg() {
    const container = document.getElementById('sakura-container-bg');
    if (!container) return;
    
    const sakura = document.createElement("div");
    sakura.classList.add("sakura-bg");
    
    // Posisi random dari kiri ke kanan (0-100%)
    sakura.style.left = Math.random() * 100 + "%";
    
    // Start dari atas (top: -20px sampai -50px)
    sakura.style.top = (Math.random() * -30 - 20) + "px";
    
    // Ukuran random
    const size = Math.random() * 12 + 8;
    sakura.style.width = size + "px";
    sakura.style.height = size + "px";
    
    // Durasi animasi random (lambat 15-25 detik)
    const duration = Math.random() * 10 + 15;
    sakura.style.animationDuration = duration + "s";

    container.appendChild(sakura);

    // Hapus setelah selesai animasi
    setTimeout(() => {
        sakura.remove();
    }, (duration + 2) * 1000);
}

// Layer Foreground - di depan konten (sedikit saja)
function createSakuraFg() {
    const container = document.getElementById('sakura-container-fg');
    if (!container) return;
    
    const sakura = document.createElement("div");
    sakura.classList.add("sakura-fg");
    
    // Posisi random dari kiri ke kanan
    sakura.style.left = Math.random() * 100 + "%";
    
    // Start dari atas
    sakura.style.top = (Math.random() * -30 - 20) + "px";
    
    // Ukuran sedikit lebih besar untuk layer depan
    const size = Math.random() * 14 + 10;
    sakura.style.width = size + "px";
    sakura.style.height = size + "px";
    
    // Durasi animasi random (lambat 12-20 detik)
    const duration = Math.random() * 8 + 12;
    sakura.style.animationDuration = duration + "s";

    container.appendChild(sakura);

    // Hapus setelah selesai animasi
    setTimeout(() => {
        sakura.remove();
    }, (duration + 2) * 1000);
}

// Spawn sakura background (banyak, di belakang)
setInterval(() => {
    createSakuraBg();
}, 400);

setInterval(() => {
    if(Math.random() > 0.5) {
        createSakuraBg();
    }
}, 600);

// Spawn sakura foreground (sedikit, di depan font) - jarang muncul
setInterval(() => {
    // Hanya 15% chance muncul di depan
    if(Math.random() > 0.85) {
        createSakuraFg();
    }
}, 2500);


// ==========================================
// 🏠 LANDING PAGE - STATS COUNTER ANIMATION
// ==========================================

let statsAnimated = false;

function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2500; // 2.5 detik (balance antara smooth & cepat)
    const startTime = performance.now();
    
    // Easing function untuk smooth animation (ease-out-quad)
    function easeOutQuad(t) {
        return t * (2 - t);
    }

    const updateCounter = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Apply easing
        const easedProgress = easeOutQuad(progress);
        const current = Math.floor(easedProgress * target);
        
        // Format angka
        if (element.textContent.includes('%')) {
            element.textContent = current + '%';
        } else {
            element.textContent = current.toLocaleString('id-ID') + '+';
        }
        
        // Lanjutkan animasi jika belum selesai
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        } else {
            // Set nilai akhir yang pasti
            const finalValue = target.toLocaleString('id-ID');
            if (element.textContent.includes('%')) {
                element.textContent = target + '%';
            } else {
                element.textContent = finalValue + '+';
            }
        }
    };

    requestAnimationFrame(updateCounter);
}

// Check if stats section is visible - AKURAT
function checkStatsVisible() {
    const statsSection = document.querySelector('.stats');
    if (!statsSection || statsAnimated) return;

    const rect = statsSection.getBoundingClientRect();
    const windowHeight = window.innerHeight || document.documentElement.clientHeight;

    // Trigger ketika section terlihat jelas
    const sectionMiddle = rect.top + (rect.height / 2);
    const isVisible = sectionMiddle >= 0 && sectionMiddle <= windowHeight * 0.8;

    if (isVisible) {
        statsAnimated = true;
        
        // Trigger animasi untuk semua counter dengan delay ringan
        const counters = document.querySelectorAll('.stat-number[data-target]');
        counters.forEach((counter, index) => {
            setTimeout(() => {
                animateCounter(counter);
            }, index * 100); // Delay 100ms antar counter
        });
    }
}

// Check saat scroll
let ticking = false;
window.addEventListener('scroll', () => {
    if (!ticking) {
        window.requestAnimationFrame(() => {
            checkStatsVisible();
            ticking = false;
        });
        ticking = true;
    }
});

// Check saat load
window.addEventListener('load', checkStatsVisible);

// Check setelah DOM ready
document.addEventListener('DOMContentLoaded', checkStatsVisible);


// ==========================================
// 🏠 LANDING PAGE - MODAL FUNCTIONS
// ==========================================

function openModal(type) {
    const modal = document.getElementById(type + 'Modal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(type) {
    const modal = document.getElementById(type + 'Modal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function switchModal(from, to) {
    closeModal(from);
    setTimeout(() => openModal(to), 200);
}

// Close modal when clicking outside
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});