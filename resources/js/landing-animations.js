import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Register ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

/**
 * Initialize all landing page animations
 */
export function initLandingAnimations() {
    // Wait for DOM and Livewire to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initAnimations();
        });
    } else {
        // If DOM is already loaded, wait for Livewire
        if (window.Livewire) {
            window.Livewire.hook('morph.updated', () => {
                initAnimations();
            });
        }
        initAnimations();
    }
}

function initAnimations() {
    // Hero Section Animations (On Load)
    initHeroAnimations();
    
    // Stats Section Animations (Scroll Triggered)
    initStatsAnimations();
    
    // Features Section Animations (Scroll Triggered)
    initFeaturesAnimations();
    
    // Testimonials Section Animations (Scroll Triggered)
    initTestimonialsAnimations();
}

/**
 * Hero Section: Fade in + slide up on page load
 */
function initHeroAnimations() {
    const heroTitle = document.querySelector('[data-gsap="hero-title"]');
    const heroDescription = document.querySelector('[data-gsap="hero-description"]');
    const heroButtons = document.querySelectorAll('[data-gsap="hero-button"]');
    const heroImage = document.querySelector('[data-gsap="hero-image"]');
    const heroBlur1 = document.querySelector('[data-gsap="hero-blur-1"]');
    const heroBlur2 = document.querySelector('[data-gsap="hero-blur-2"]');

    // Set initial states (only if elements exist)
    const heroElements = [heroTitle, heroDescription, ...heroButtons, heroImage].filter(Boolean);
    if (heroElements.length > 0) {
        gsap.set(heroElements, {
            opacity: 0,
            y: 30
        });
    }

    const blurElements = [heroBlur1, heroBlur2].filter(Boolean);
    if (blurElements.length > 0) {
        gsap.set(blurElements, {
            opacity: 0,
            scale: 0.8
        });
    }

    // Create timeline for hero animations
    const heroTimeline = gsap.timeline();

    // Animate blur backgrounds first
    if (heroBlur1) {
        heroTimeline.to(heroBlur1, {
            opacity: 1,
            scale: 1,
            duration: 1.5,
            ease: 'power2.out'
        });
    }

    if (heroBlur2) {
        heroTimeline.to(heroBlur2, {
            opacity: 1,
            scale: 1,
            duration: 1.5,
            ease: 'power2.out'
        }, '-=1');
    }

    // Animate title
    if (heroTitle) {
        heroTimeline.to(heroTitle, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, '-=0.5');
    }

    // Animate description
    if (heroDescription) {
        heroTimeline.to(heroDescription, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out'
        }, '-=0.6');
    }

    // Animate buttons with stagger
    if (heroButtons.length > 0) {
        heroTimeline.to(heroButtons, {
            opacity: 1,
            y: 0,
            duration: 0.6,
            stagger: 0.1,
            ease: 'power2.out'
        }, '-=0.4');
    }

    // Animate image
    if (heroImage) {
        heroTimeline.to(heroImage, {
            opacity: 1,
            y: 0,
            duration: 1,
            ease: 'power2.out'
        }, '-=0.8');
    }
}

/**
 * Stats Section: Counter animation + fade in on scroll
 */
function initStatsAnimations() {
    const statsSection = document.querySelector('[data-gsap="stats-section"]');
    const statCards = document.querySelectorAll('[data-gsap="stat-card"]');
    const statNumbers = document.querySelectorAll('[data-gsap="stat-number"]');

    if (!statsSection) return;

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

// Auto-initialize when script loads
initLandingAnimations();

