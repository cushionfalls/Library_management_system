<style>
/* Preloader CSS */
#magic-preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: radial-gradient(circle at center, #3b0764 0%, #1f0436 100%);
    z-index: 999999;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
}

#magic-preloader.hidden-preloader {
    opacity: 0;
    visibility: hidden;
}

/* Magical Book Animation */
.book-container {
    position: relative;
    width: 160px;
    height: 120px;
    perspective: 1000px;
    margin-bottom: 40px;
}

.book {
    width: 100%;
    height: 100%;
    position: absolute;
    transform-style: preserve-3d;
    transform: rotateX(40deg);
    animation: floatingBook 2s ease-in-out infinite alternate;
}

@keyframes floatingBook {
    0% { transform: translateY(0px) rotateX(40deg); }
    100% { transform: translateY(-10px) rotateX(45deg); }
}

.book-half {
    width: 50%;
    height: 100%;
    position: absolute;
    top: 0;
    transform-style: preserve-3d;
}

.left-half {
    left: 0;
    transform-origin: right center;
    animation: openLeft 0.8s ease-out forwards;
}

.right-half {
    right: 0;
    transform-origin: left center;
    animation: openRight 0.8s ease-out forwards;
}

@keyframes openLeft {
    0% { transform: rotateY(90deg); }
    100% { transform: rotateY(0deg); }
}

@keyframes openRight {
    0% { transform: rotateY(-90deg); }
    100% { transform: rotateY(0deg); }
}

.book-cover {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #6d28d9, #4c1d95);
    position: absolute;
    border: 2px solid rgba(255,255,255,0.2);
}

.left-half .book-cover {
    border-radius: 10px 0 0 10px;
    box-shadow: inset 4px 0 10px rgba(0,0,0,0.3);
}

.right-half .book-cover {
    border-radius: 0 10px 10px 0;
    box-shadow: inset -4px 0 10px rgba(0,0,0,0.3);
}

.book-page {
    width: 95%;
    height: 94%;
    background: #fdf8ff;
    position: absolute;
    top: 3%;
}

.left-half .book-page {
    right: 0;
    border-radius: 8px 0 0 8px;
    box-shadow: inset 2px 0 5px rgba(0,0,0,0.1);
    transform: translateZ(1px);
}

.right-half .book-page {
    left: 0;
    border-radius: 0 8px 8px 0;
    box-shadow: inset -2px 0 5px rgba(0,0,0,0.1);
    transform: translateZ(1px);
}

/* Flipping pages inside */
.flip-page {
    width: 95%;
    height: 94%;
    background: #fdf8ff;
    position: absolute;
    top: 3%;
    right: 0;
    border-radius: 8px 0 0 8px;
    transform-origin: right center;
    animation: flipAnim 1.2s ease-in-out infinite;
    transform: translateZ(2px);
    opacity: 0;
}

.flip-1 { animation-delay: 0.8s; }
.flip-2 { animation-delay: 1.4s; }

@keyframes flipAnim {
    0% { transform: translateZ(2px) rotateY(0deg); opacity: 0; }
    20% { opacity: 1; }
    80% { opacity: 1; }
    100% { transform: translateZ(2px) rotateY(180deg); opacity: 0; }
}

/* Flying Pages */
.flying-page {
    position: absolute;
    width: 15px;
    height: 20px;
    background: rgba(253, 248, 255, 0.8);
    border-radius: 1px;
    box-shadow: 0 0 5px rgba(255,255,255,0.5);
    opacity: 0;
}

.fp-1 { animation: flyOut 1.2s linear infinite 0.5s; left: 50%; top: 50%; }
.fp-2 { animation: flyOut 1.5s linear infinite 0.7s; left: 50%; top: 50%; }
.fp-3 { animation: flyOut 1.3s linear infinite 0.9s; left: 50%; top: 50%; }
.fp-4 { animation: flyOut 1.1s linear infinite 1.1s; left: 50%; top: 50%; }

@keyframes flyOut {
    0% { transform: translate(-50%, -50%) rotate(0deg) scale(0.5); opacity: 1; }
    100% { transform: translate(calc(-50% + 80px * var(--tx)), -100px) rotate(360deg) scale(1.5); opacity: 0; }
}

.fp-1 { --tx: 0.6; }
.fp-2 { --tx: -0.8; }
.fp-3 { --tx: 1.2; }
.fp-4 { --tx: -1.0; }

/* Magical Glow */
.glow-light {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120px;
    height: 120px;
    background: radial-gradient(circle, rgba(139,92,246,0.8) 0%, rgba(139,92,246,0) 70%);
    border-radius: 50%;
    animation: pulseGlow 1s ease-in-out infinite alternate;
    z-index: -1;
}

@keyframes pulseGlow {
    0% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
    100% { transform: translate(-50%, -50%) scale(1.5); opacity: 1; }
}

/* Text Styling */
.preloader-title {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
    letter-spacing: 2px;
    margin-bottom: 10px;
    text-shadow: 0 0 20px rgba(139, 92, 246, 0.8);
}

/* Particle system for magical dust */
.particle {
    position: absolute;
    background: #fff;
    border-radius: 50%;
    opacity: 0;
    pointer-events: none;
}
</style>

<div id="magic-preloader">
    <div class="book-container">
        <div class="glow-light"></div>
        <div class="book">
            <div class="book-half left-half">
                <div class="book-cover"></div>
                <div class="book-page"></div>
                <div class="flip-page flip-1"></div>
                <div class="flip-page flip-2"></div>
            </div>
            <div class="book-half right-half">
                <div class="book-cover"></div>
                <div class="book-page"></div>
            </div>
        </div>
        <div class="flying-page fp-1"></div>
        <div class="flying-page fp-2"></div>
        <div class="flying-page fp-3"></div>
        <div class="flying-page fp-4"></div>
    </div>
    <h1 class="preloader-title">Paper Library</h1>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const preloader = document.getElementById("magic-preloader");
        
        // Generate magical dust particles
        for (let i = 0; i < 20; i++) {
            let particle = document.createElement("div");
            particle.className = "particle";
            
            // Random properties
            let size = Math.random() * 4 + 1;
            let posX = Math.random() * 100;
            let posY = Math.random() * 100;
            let animDuration = Math.random() * 2 + 1; // Faster particles
            let animDelay = Math.random() * 1;
            
            particle.style.width = size + "px";
            particle.style.height = size + "px";
            particle.style.left = posX + "vw";
            particle.style.top = posY + "vh";
            particle.style.boxShadow = "0 0 " + (size*2) + "px #a855f7";
            
            // Inline animation definition for uniqueness
            particle.animate([
                { transform: 'translate(0, 0)', opacity: 0 },
                { opacity: Math.random() * 0.5 + 0.3, offset: 0.5 },
                { transform: 'translate(' + (Math.random()*100 - 50) + 'px, -' + (Math.random()*100 + 50) + 'px)', opacity: 0 }
            ], {
                duration: animDuration * 1000,
                delay: animDelay * 1000,
                iterations: Infinity,
                easing: 'ease-in-out'
            });
            
            preloader.appendChild(particle);
        }

        // Check session storage
        const hasLoaded = sessionStorage.getItem('preloader_shown');
        
        if (!hasLoaded) {
            window.addEventListener('load', function() {
                // Faster preloader: show for 1.2s instead of 2.5s
                setTimeout(function() {
                    preloader.classList.add('hidden-preloader');
                    setTimeout(() => { preloader.style.display = 'none'; }, 500); // reduced transition time to 500ms
                    sessionStorage.setItem('preloader_shown', 'true');
                }, 1200);
            });
        } else {
            preloader.style.display = 'none';
        }
    });
</script>
