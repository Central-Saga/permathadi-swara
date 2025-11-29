import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Register ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

/**
 * Initialize all landing page animations
 */
export function initLandingAnimations() {
    // Navbar dan hero harus dimulai bersamaan tanpa delay
    const initAllAnimations = () => {
        // Cek apakah hero section sudah ada di DOM
        const heroSection = document.querySelector('[data-gsap="hero-title"]') || 
                           document.querySelector('[data-gsap="hero-description"]');
        const aboutHeroSection = document.querySelector('[data-gsap="about-hero-title"]') || 
                                 document.querySelector('[data-gsap="about-hero-description"]');
        const programHeroSection = document.querySelector('[data-gsap="program-hero-title"]') || 
                                  document.querySelector('[data-gsap="program-hero-description"]');
        
        if (heroSection) {
            // Init navbar dan hero bersamaan tanpa delay
            initNavbarAnimation();
            initHeroAnimations();
            // Footer bisa sedikit delay
            initFooterAnimation();
            return true;
        } else if (aboutHeroSection) {
            // Init navbar dan about hero bersamaan tanpa delay
            initNavbarAnimation();
            initAboutHeroAnimations();
            // Footer bisa sedikit delay
            initFooterAnimation();
            return true;
        } else if (programHeroSection) {
            // Init navbar dan program hero bersamaan tanpa delay
            initNavbarAnimation();
            initProgramHeroAnimations();
            // Footer bisa sedikit delay
            initFooterAnimation();
            return true;
        }
        
        // Fallback: Jika tidak ada hero section yang ditemukan, tetap init navbar dan footer
        // Ini memastikan navbar dan footer selalu muncul
        const navbar = document.querySelector('[data-gsap="navbar"]');
        const footer = document.querySelector('[data-gsap="footer"]');
        if (navbar || footer) {
            if (navbar) initNavbarAnimation();
            if (footer) initFooterAnimation();
            return true;
        }
        
        return false;
    };

    // Fungsi untuk init dengan retry mechanism jika elemen belum ada
    // Menggunakan delay minimal untuk retry
    const initWithRetry = (maxRetries = 5, delay = 0) => {
        if (initAllAnimations()) {
            // Berhasil init, lanjutkan dengan lazy loading
            initLazyLoading();
            return;
        }
        
        // Jika belum berhasil dan masih ada retry, coba lagi dengan delay minimal
        if (maxRetries > 0) {
            // Gunakan requestAnimationFrame untuk retry yang lebih cepat
            requestAnimationFrame(() => {
                initWithRetry(maxRetries - 1, delay);
            });
        } else {
            // Jika sudah habis retry, tetap init navbar dan footer sebagai fallback
            // Ini memastikan navbar dan footer selalu muncul
            initNavbarAnimation();
            initFooterAnimation();
            // Tetap init lazy loading
            initLazyLoading();
        }
    };

    // Coba langsung init semua animasi - sangat agresif
    // Langsung coba init tanpa menunggu apapun
    if (!initAllAnimations()) {
        // Jika belum berhasil, coba lagi dengan retry
        initWithRetry();
    } else {
        initLazyLoading();
    }

    // Backup: Jika DOM belum ready, tunggu DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (!heroAnimated) {
                initAllAnimations();
            }
        }, { once: true });
    }
    
    // Jika Livewire ada, re-init saat morph (untuk navigasi Livewire)
    if (window.Livewire) {
        window.Livewire.hook('morph.updated', () => {
            // Reset hero animated flag untuk halaman baru
            heroAnimated = false;
            aboutHeroAnimated = false;
            programHeroAnimated = false;
            // Re-init semua animasi
            initNavbarAnimation();
            initFooterAnimation();
            initHeroAnimations();
            initAboutHeroAnimations();
            initProgramHeroAnimations();
            // Re-init lazy loading
            initLazyLoading();
        });
    }
}

// Track if hero section has been animated
let heroAnimated = false;
let aboutHeroAnimated = false;
let programHeroAnimated = false;

/**
 * Initialize navbar animation - fade-in dari atas
 * Navbar dan hero dimulai bersamaan untuk timing yang konsisten
 */
function initNavbarAnimation() {
    const navbar = document.querySelector('[data-gsap="navbar"]');
    if (!navbar) return;

    // Skip jika sudah ter-animate
    if (navbar.hasAttribute('data-animated')) return;
    navbar.setAttribute('data-animated', 'true');

    // Animate navbar dengan fade-in dan slide-down
    // Timing sama dengan hero untuk konsistensi
    gsap.to(navbar, {
        opacity: 1,
        y: 0,
        duration: 0.6,
        ease: 'power2.out',
        delay: 0 // No delay, start immediately
    });
}

/**
 * Initialize footer animation - fade-in dari bawah
 * Footer fade-in setelah hero section atau bersamaan
 */
function initFooterAnimation() {
    const footer = document.querySelector('[data-gsap="footer"]');
    if (!footer) return;

    // Skip jika sudah ter-animate
    if (footer.hasAttribute('data-animated')) return;
    footer.setAttribute('data-animated', 'true');

    // Animate footer dengan fade-in dan slide-up
    // Delay sedikit agar tidak terlalu cepat, tapi tetap smooth
    gsap.to(footer, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: 'power2.out',
        delay: 0.3
    });
}

/**
 * Initialize hero section animations (NO SCROLL TRIGGER)
 * Hero section langsung di-animate saat pertama kali page diakses
 */
function initHeroAnimations() {
    const heroTitle = document.querySelector('[data-gsap="hero-title"]');
    const heroDescription = document.querySelector('[data-gsap="hero-description"]');
    const heroButtons = document.querySelectorAll('[data-gsap="hero-button"]');
    const heroImage = document.querySelector('[data-gsap="hero-image"]');
    const heroBlur1 = document.querySelector('[data-gsap="hero-blur-1"]');
    const heroBlur2 = document.querySelector('[data-gsap="hero-blur-2"]');

    // Check if hero elements exist
    if (!heroTitle && !heroDescription && heroButtons.length === 0 && !heroImage) {
        return; // Exit if no hero elements found
    }

    // Check if hero section sudah ter-animate (untuk mencegah re-animation)
    if (heroAnimated) {
        return; // Skip jika sudah ter-animate
    }

    // Initial states sudah di-set di CSS (opacity: 0, transform)
    // GSAP akan langsung fade-in dan animate tanpa delay

    // Create timeline for hero animations - NO SCROLL TRIGGER
    // Timeline akan langsung play saat dibuat tanpa delay
    const heroTimeline = gsap.timeline({
        paused: false,
        delay: 0
    });

    // Animate blur backgrounds dan title bersamaan untuk menghilangkan delay
    // Title langsung muncul tanpa menunggu blur selesai
    if (heroBlur1) {
        heroTimeline.to(heroBlur1, {
            opacity: 1,
            scale: 1,
            duration: 1.5,
            ease: 'power2.out'
        }, 0); // Start immediately
    }

    if (heroBlur2) {
        heroTimeline.to(heroBlur2, {
            opacity: 1,
            scale: 1,
            duration: 1.5,
            ease: 'power2.out'
        }, 0); // Start immediately
    }

    // Animate title dengan fade-in dan slide-up - langsung dimulai bersamaan dengan blur
    if (heroTitle) {
        heroTimeline.to(heroTitle, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, 0); // Start immediately, no delay
    }

    // Animate description dengan fade-in dan slide-up - sedikit delay setelah title
    if (heroDescription) {
        heroTimeline.to(heroDescription, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, 0.1); // Start 0.1s after title
    }

    // Animate buttons dengan fade-in dan slide-up (stagger) - setelah description
    if (heroButtons.length > 0) {
        heroTimeline.to(heroButtons, {
            opacity: 1,
            y: 0,
            duration: 0.6,
            stagger: 0.1,
            ease: 'power2.out'
        }, 0.2); // Start 0.2s after title
    }

    // Animate image dengan fade-in dan slide-up - sedikit delay
    if (heroImage) {
        heroTimeline.to(heroImage, {
            opacity: 1,
            y: 0,
            duration: 1,
            ease: 'power2.out'
        }, 0.15); // Start 0.15s after title
    }

    // Mark hero section sebagai sudah ter-animate
    heroAnimated = true;

    // Timeline sudah langsung play karena paused: false
    // Pastikan timeline benar-benar play tanpa delay
    heroTimeline.play(0); // Force play from start (time 0)
}

/**
 * Initialize lazy loading for sections using Intersection Observer
 * Section baru akan di-render dan di-animate ketika mendekati viewport
 */
function initLazyLoading() {
    // Hapus observer lama jika ada
    if (window.lazySectionObserver) {
        window.lazySectionObserver.disconnect();
    }

    // Buat Intersection Observer untuk lazy loading
    window.lazySectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const section = entry.target;
                const sectionType = section.getAttribute('data-lazy-section');
                
                // Mark section as loaded
                section.setAttribute('data-loaded', 'true');
                
                // Initialize animations berdasarkan tipe section
                if (sectionType === 'stats') {
                    initStatsAnimations();
                } else if (sectionType === 'features') {
                    initFeaturesAnimations();
                } else if (sectionType === 'testimonials') {
                    initTestimonialsAnimations();
                } else if (sectionType === 'about-profile') {
                    initAboutProfileAnimations();
                } else if (sectionType === 'about-vision') {
                    initAboutVisionAnimations();
                } else if (sectionType === 'about-services') {
                    initAboutServicesAnimations();
                } else if (sectionType === 'about-advantages') {
                    initAboutAdvantagesAnimations();
                } else if (sectionType === 'about-contact') {
                    initAboutContactAnimations();
                }
                
                // Stop observing setelah di-load
                window.lazySectionObserver.unobserve(section);
            }
        });
    }, {
        rootMargin: '100px', // Start loading 100px sebelum masuk viewport
        threshold: 0.01
    });

    // Observe semua lazy sections
    const lazySections = document.querySelectorAll('[data-lazy-section]');
    lazySections.forEach(section => {
        window.lazySectionObserver.observe(section);
    });
}

/**
 * Stats Section: Counter animation + fade in on scroll
 */
function initStatsAnimations() {
    const statsSection = document.querySelector('[data-gsap="stats-section"]');
    const statCards = document.querySelectorAll('[data-gsap="stat-card"]');
    const statNumbers = document.querySelectorAll('[data-gsap="stat-number"]');

    if (!statsSection) return;
    
    // Skip jika sudah di-animate
    if (statsSection.hasAttribute('data-animated')) return;
    statsSection.setAttribute('data-animated', 'true');

    // Set initial states
    if (statCards.length === 0) return;
    
    gsap.set(statCards, {
        opacity: 0,
        y: 50,
        scale: 0.9
    });

    // Animate cards on scroll
    gsap.to(statCards, {
        opacity: 1,
        y: 0,
        scale: 1,
        duration: 0.8,
        stagger: 0.2,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: statsSection,
            start: 'top 80%',
            end: 'top 50%',
            toggleActions: 'play none none none'
        }
    });

    // Counter animation for numbers
    statNumbers.forEach((numberEl) => {
        const targetText = numberEl.textContent.trim();
        const match = targetText.match(/(\d+)/);
        
        if (match) {
            const targetNumber = parseInt(match[1]);
            const suffix = targetText.replace(match[0], '');
            
            // Reset to 0
            numberEl.textContent = `0${suffix}`;
            
            ScrollTrigger.create({
                trigger: numberEl.closest('[data-gsap="stat-card"]'),
                start: 'top 80%',
                onEnter: () => {
                    gsap.to({ value: 0 }, {
                        value: targetNumber,
                        duration: 2,
                        ease: 'power2.out',
                        onUpdate: function() {
                            const currentValue = Math.ceil(this.targets()[0].value);
                            numberEl.textContent = `${currentValue}${suffix}`;
                        }
                    });
                },
                once: true
            });
        }
    });
}

/**
 * Features Section: Stagger animation for feature cards
 */
function initFeaturesAnimations() {
    const featuresSection = document.querySelector('[data-gsap="features-section"]');
    const featuresHeading = document.querySelector('[data-gsap="features-heading"]');
    const featureCards = document.querySelectorAll('[data-gsap="feature-card"]');
    const featureIcons = document.querySelectorAll('[data-gsap="feature-icon"]');

    if (!featuresSection) return;
    
    // Skip jika sudah di-animate
    if (featuresSection.hasAttribute('data-animated')) return;
    featuresSection.setAttribute('data-animated', 'true');

    // Set initial states
    if (featuresHeading) {
        gsap.set(featuresHeading, {
            opacity: 0,
            y: 30
        });
    }

    if (featureCards.length > 0) {
        gsap.set(featureCards, {
            opacity: 0,
            y: 40
        });
    }

    if (featureIcons.length > 0) {
        gsap.set(featureIcons, {
            opacity: 0,
            scale: 0
        });
    }

    // Animate heading
    gsap.to(featuresHeading, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: featuresSection,
            start: 'top 80%',
            toggleActions: 'play none none none'
        }
    });

    // Animate icons first
    gsap.to(featureIcons, {
        opacity: 1,
        scale: 1,
        duration: 0.6,
        stagger: 0.15,
        ease: 'back.out(1.7)',
        scrollTrigger: {
            trigger: featuresSection,
            start: 'top 75%',
            toggleActions: 'play none none none'
        }
    });

    // Animate cards with stagger
    gsap.to(featureCards, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: featuresSection,
            start: 'top 70%',
            toggleActions: 'play none none none'
        }
    });
}

/**
 * Testimonials Section: Fade in on scroll
 */
function initTestimonialsAnimations() {
    const testimonialsSection = document.querySelector('[data-gsap="testimonials-section"]');
    const testimonialLogo = document.querySelector('[data-gsap="testimonial-logo"]');
    const testimonialQuote = document.querySelector('[data-gsap="testimonial-quote"]');
    const testimonialAuthor = document.querySelector('[data-gsap="testimonial-author"]');

    if (!testimonialsSection) return;
    
    // Skip jika sudah di-animate
    if (testimonialsSection.hasAttribute('data-animated')) return;
    testimonialsSection.setAttribute('data-animated', 'true');

    // Set initial states
    const testimonialElements = [testimonialLogo, testimonialQuote, testimonialAuthor].filter(Boolean);
    if (testimonialElements.length === 0) return;
    
    gsap.set(testimonialElements, {
        opacity: 0,
        y: 30
    });

    // Create timeline for testimonials
    const testimonialTimeline = gsap.timeline({
        scrollTrigger: {
            trigger: testimonialsSection,
            start: 'top 80%',
            toggleActions: 'play none none none'
        }
    });

    // Animate logo
    if (testimonialLogo) {
        testimonialTimeline.to(testimonialLogo, {
            opacity: 1,
            y: 0,
            duration: 0.6,
            ease: 'power2.out'
        });
    }

    // Animate quote
    if (testimonialQuote) {
        testimonialTimeline.to(testimonialQuote, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, '-=0.3');
    }

    // Animate author
    if (testimonialAuthor) {
        testimonialTimeline.to(testimonialAuthor, {
            opacity: 1,
            y: 0,
            duration: 0.6,
            ease: 'power2.out'
        }, '-=0.4');
    }
}

/**
 * Initialize About Hero section animations (NO SCROLL TRIGGER)
 * About Hero section langsung di-animate saat pertama kali page diakses
 */
function initAboutHeroAnimations() {
    const aboutHeroTitle = document.querySelector('[data-gsap="about-hero-title"]');
    const aboutHeroDescription = document.querySelector('[data-gsap="about-hero-description"]');
    const aboutHeroBlur1 = document.querySelector('[data-gsap="about-hero-blur-1"]');
    const aboutHeroBlur2 = document.querySelector('[data-gsap="about-hero-blur-2"]');

    // Check if about hero elements exist
    if (!aboutHeroTitle && !aboutHeroDescription) {
        return; // Exit if no about hero elements found
    }

    // Check if about hero section sudah ter-animate (untuk mencegah re-animation)
    if (aboutHeroAnimated) {
        return; // Skip jika sudah ter-animate
    }

    // Set initial states
    if (aboutHeroBlur1) {
        gsap.set(aboutHeroBlur1, {
            opacity: 0,
            scale: 0.8
        });
    }

    if (aboutHeroBlur2) {
        gsap.set(aboutHeroBlur2, {
            opacity: 0,
            scale: 0.8
        });
    }

    if (aboutHeroTitle) {
        gsap.set(aboutHeroTitle, {
            opacity: 0,
            y: 30
        });
    }

    if (aboutHeroDescription) {
        gsap.set(aboutHeroDescription, {
            opacity: 0,
            y: 30
        });
    }

    // Create timeline for about hero animations - NO SCROLL TRIGGER
    const aboutHeroTimeline = gsap.timeline({
        paused: false,
        delay: 0
    });

    // Animate blur backgrounds dan title bersamaan
    if (aboutHeroBlur1) {
        aboutHeroTimeline.to(aboutHeroBlur1, {
            opacity: 1,
            scale: 1,
            duration: 1.5,
            ease: 'power2.out'
        }, 0);
    }

    if (aboutHeroBlur2) {
        aboutHeroTimeline.to(aboutHeroBlur2, {
            opacity: 1,
            scale: 1,
            duration: 1.5,
            ease: 'power2.out'
        }, 0);
    }

    // Animate title dengan fade-in dan slide-up
    if (aboutHeroTitle) {
        aboutHeroTimeline.to(aboutHeroTitle, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, 0);
    }

    // Animate description dengan fade-in dan slide-up
    if (aboutHeroDescription) {
        aboutHeroTimeline.to(aboutHeroDescription, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, 0.1);
    }

    // Mark about hero section sebagai sudah ter-animate
    aboutHeroAnimated = true;

    // Force play timeline
    aboutHeroTimeline.play(0);
}

/**
 * Initialize Program Hero section animations (NO SCROLL TRIGGER)
 * Program Hero section langsung di-animate saat pertama kali page diakses
 */
function initProgramHeroAnimations() {
    const programHeroTitle = document.querySelector('[data-gsap="program-hero-title"]');
    const programHeroDescription = document.querySelector('[data-gsap="program-hero-description"]');
    const programHeroBlur1 = document.querySelector('[data-gsap="program-hero-blur-1"]');
    const programHeroBlur2 = document.querySelector('[data-gsap="program-hero-blur-2"]');

    // Check if program hero elements exist
    if (!programHeroTitle && !programHeroDescription) {
        return; // Exit if no program hero elements found
    }

    // Check if program hero section sudah ter-animate (untuk mencegah re-animation)
    if (programHeroAnimated) {
        return; // Skip jika sudah ter-animate
    }

    // Set initial states
    if (programHeroBlur1) {
        gsap.set(programHeroBlur1, {
            opacity: 0,
            scale: 0.8
        });
    }

    if (programHeroBlur2) {
        gsap.set(programHeroBlur2, {
            opacity: 0,
            scale: 0.8
        });
    }

    if (programHeroTitle) {
        gsap.set(programHeroTitle, {
            opacity: 0,
            y: 30
        });
    }

    if (programHeroDescription) {
        gsap.set(programHeroDescription, {
            opacity: 0,
            y: 30
        });
    }

    // Create timeline for program hero animations - NO SCROLL TRIGGER
    const programHeroTimeline = gsap.timeline({
        paused: false,
        delay: 0
    });

    // Animate blur backgrounds dan title bersamaan
    if (programHeroBlur1) {
        programHeroTimeline.to(programHeroBlur1, {
            opacity: 1,
            scale: 1,
            duration: 1.5,
            ease: 'power2.out'
        }, 0);
    }

    if (programHeroBlur2) {
        programHeroTimeline.to(programHeroBlur2, {
            opacity: 1,
            scale: 1,
            duration: 1.5,
            ease: 'power2.out'
        }, 0);
    }

    // Animate title dengan fade-in dan slide-up
    if (programHeroTitle) {
        programHeroTimeline.to(programHeroTitle, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, 0);
    }

    // Animate description dengan fade-in dan slide-up
    if (programHeroDescription) {
        programHeroTimeline.to(programHeroDescription, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, 0.1);
    }

    // Mark program hero section sebagai sudah ter-animate
    programHeroAnimated = true;

    // Force play timeline
    programHeroTimeline.play(0);
}

/**
 * About Profile Section: Fade in on scroll
 */
function initAboutProfileAnimations() {
    const profileSection = document.querySelector('[data-gsap="about-profile-section"]');
    const profileContent = document.querySelector('[data-gsap="about-profile-content"]');
    const profileVisual = document.querySelector('[data-gsap="about-profile-visual"]');

    if (!profileSection) return;
    
    // Skip jika sudah di-animate
    if (profileSection.hasAttribute('data-animated')) return;
    profileSection.setAttribute('data-animated', 'true');

    // Set initial states
    const profileElements = [profileContent, profileVisual].filter(Boolean);
    if (profileElements.length === 0) return;
    
    gsap.set(profileElements, {
        opacity: 0,
        y: 40
    });

    // Animate with stagger
    gsap.to(profileElements, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: profileSection,
            start: 'top 80%',
            toggleActions: 'play none none none'
        }
    });
}

/**
 * About Vision Section: Fade in and scale on scroll
 */
function initAboutVisionAnimations() {
    const visionSection = document.querySelector('[data-gsap="about-vision-section"]');
    const visionCard = document.querySelector('[data-gsap="about-vision-card"]');
    const visionItems = document.querySelectorAll('[data-gsap="about-vision-item"]');

    if (!visionSection) return;
    
    // Skip jika sudah di-animate
    if (visionSection.hasAttribute('data-animated')) return;
    visionSection.setAttribute('data-animated', 'true');

    // Set initial states
    if (visionCard) {
        gsap.set(visionCard, {
            opacity: 0,
            scale: 0.95
        });
    }

    if (visionItems.length > 0) {
        gsap.set(visionItems, {
            opacity: 0,
            y: 30
        });
    }

    // Animate card
    if (visionCard) {
        gsap.to(visionCard, {
            opacity: 1,
            scale: 1,
            duration: 0.8,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: visionSection,
                start: 'top 80%',
                toggleActions: 'play none none none'
            }
        });
    }

    // Animate items with stagger
    if (visionItems.length > 0) {
        gsap.to(visionItems, {
            opacity: 1,
            y: 0,
            duration: 0.6,
            stagger: 0.15,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: visionSection,
                start: 'top 75%',
                toggleActions: 'play none none none'
            }
        });
    }
}

/**
 * About Services Section: Stagger animation for service cards
 */
function initAboutServicesAnimations() {
    const servicesSection = document.querySelector('[data-gsap="about-services-section"]');
    const servicesHeading = document.querySelector('[data-gsap="about-services-heading"]');
    const serviceCards = document.querySelectorAll('[data-gsap="about-service-card"]');
    const serviceIcons = document.querySelectorAll('[data-gsap="about-service-icon"]');

    if (!servicesSection) return;
    
    // Skip jika sudah di-animate
    if (servicesSection.hasAttribute('data-animated')) return;
    servicesSection.setAttribute('data-animated', 'true');

    // Set initial states
    if (servicesHeading) {
        gsap.set(servicesHeading, {
            opacity: 0,
            y: 30
        });
    }

    if (serviceCards.length > 0) {
        gsap.set(serviceCards, {
            opacity: 0,
            y: 40
        });
    }

    if (serviceIcons.length > 0) {
        gsap.set(serviceIcons, {
            opacity: 0,
            scale: 0
        });
    }

    // Animate heading
    gsap.to(servicesHeading, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: servicesSection,
            start: 'top 80%',
            toggleActions: 'play none none none'
        }
    });

    // Animate icons first
    gsap.to(serviceIcons, {
        opacity: 1,
        scale: 1,
        duration: 0.6,
        stagger: 0.15,
        ease: 'back.out(1.7)',
        scrollTrigger: {
            trigger: servicesSection,
            start: 'top 75%',
            toggleActions: 'play none none none'
        }
    });

    // Animate cards with stagger
    gsap.to(serviceCards, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: servicesSection,
            start: 'top 70%',
            toggleActions: 'play none none none'
        }
    });
}

/**
 * About Advantages Section: Stagger animation for advantage cards
 */
function initAboutAdvantagesAnimations() {
    const advantagesSection = document.querySelector('[data-gsap="about-advantages-section"]');
    const advantagesHeading = document.querySelector('[data-gsap="about-advantages-heading"]');
    const advantageCards = document.querySelectorAll('[data-gsap="about-advantage-card"]');

    if (!advantagesSection) return;
    
    // Skip jika sudah di-animate
    if (advantagesSection.hasAttribute('data-animated')) return;
    advantagesSection.setAttribute('data-animated', 'true');

    // Set initial states
    if (advantagesHeading) {
        gsap.set(advantagesHeading, {
            opacity: 0,
            y: 30
        });
    }

    if (advantageCards.length > 0) {
        gsap.set(advantageCards, {
            opacity: 0,
            y: 40
        });
    }

    // Animate heading
    gsap.to(advantagesHeading, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: advantagesSection,
            start: 'top 80%',
            toggleActions: 'play none none none'
        }
    });

    // Animate cards with stagger
    gsap.to(advantageCards, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: advantagesSection,
            start: 'top 75%',
            toggleActions: 'play none none none'
        }
    });
}

/**
 * About Contact Section: Fade in on scroll
 */
function initAboutContactAnimations() {
    const contactSection = document.querySelector('[data-gsap="about-contact-section"]');
    const contactHeading = document.querySelector('[data-gsap="about-contact-heading"]');
    const contactItems = document.querySelectorAll('[data-gsap="about-contact-item"]');

    if (!contactSection) return;
    
    // Skip jika sudah di-animate
    if (contactSection.hasAttribute('data-animated')) return;
    contactSection.setAttribute('data-animated', 'true');

    // Set initial states
    if (contactHeading) {
        gsap.set(contactHeading, {
            opacity: 0,
            y: 30
        });
    }

    if (contactItems.length > 0) {
        gsap.set(contactItems, {
            opacity: 0,
            y: 40
        });
    }

    // Animate heading
    if (contactHeading) {
        gsap.to(contactHeading, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: contactSection,
                start: 'top 80%',
                toggleActions: 'play none none none'
            }
        });
    }

    // Animate items with stagger
    if (contactItems.length > 0) {
        gsap.to(contactItems, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            stagger: 0.15,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: contactSection,
                start: 'top 75%',
                toggleActions: 'play none none none'
            }
        });
    }
}

// Auto-initialize when script loads
initLandingAnimations();

