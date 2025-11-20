// GSAP Setup
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger);

// Export for use in other files
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

