import './bootstrap';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

window.flatpickr = flatpickr;

let prelinePromise;
let chartsPromise;

async function initOptionalComponents() {
    const chartIds = ['chartOne', 'chartTwo', 'chartThree', 'chartSix', 'chartEight', 'chartThirteen'];
    const hasCharts = chartIds.some((id) => document.getElementById(id));

    if (hasCharts) {
        chartsPromise ??= import('apexcharts').then(({ default: ApexCharts }) => {
            window.ApexCharts = ApexCharts;
        });

        await chartsPromise;

        const chartInitializers = [
            ['chartOne', () => import('./components/chart/chart-1'), 'initChartOne'],
            ['chartTwo', () => import('./components/chart/chart-2'), 'initChartTwo'],
            ['chartThree', () => import('./components/chart/chart-3'), 'initChartThree'],
            ['chartSix', () => import('./components/chart/chart-6'), 'initChartSix'],
            ['chartEight', () => import('./components/chart/chart-8'), 'initChartEight'],
            ['chartThirteen', () => import('./components/chart/chart-13'), 'initChartThirteen'],
        ];

        await Promise.all(chartInitializers.map(async ([id, loadModule, exportName]) => {
            const chartElement = document.getElementById(id);
            if (!chartElement || chartElement.dataset.chartInitialized) return;

            chartElement.dataset.chartInitialized = 'loading';

            const module = await loadModule();
            if (!chartElement.isConnected) return;

            module[exportName]();
            chartElement.dataset.chartInitialized = 'true';
        }));
    }

    const hasPrelineComponent = document.querySelector('[data-hs-overlay], [data-hs-dropdown], [data-hs-collapse], [data-hs-select]');

    if (hasPrelineComponent) {
        prelinePromise ??= import('preline');
        await prelinePromise;
        initPrelineComponents();
    }
}

// Real-time notification listener via Reverb broadcasting
document.addEventListener('DOMContentLoaded', () => {
    const userId = document.querySelector('meta[name="user-id"]')?.content;
    if (userId && window.Echo) {
        window.Echo.private(`user.${userId}`)
            .listen('.notification.new', (event) => {
                // Update badge count in header
                document.querySelectorAll('[data-notification-count]').forEach((el) => {
                    el.textContent = event.unread_count > 99 ? '99+' : event.unread_count;
                    el.style.display = event.unread_count > 0 ? 'flex' : 'none';
                });

                // Dispatch Livewire event for components that need to refresh
                if (window.Livewire) {
                    window.Livewire.dispatch('notification-sent');
                    window.Livewire.dispatch('notification-updated');
                }
            });
    }
});

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const themeButtons = Array.from(document.querySelectorAll('[data-theme-toggle]'));
    const themeIcons = Array.from(document.querySelectorAll('[data-theme-icon]'));
    const themeLabels = Array.from(document.querySelectorAll('[data-theme-label]'));
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    const applyTheme = (theme, persist = true) => {
        root.dataset.theme = theme;

        if (persist) {
            localStorage.setItem('slau-theme', theme);
        }

        themeIcons.forEach((icon) => {
            icon.textContent = theme === 'dark' ? '☾' : '☀';
        });

        themeLabels.forEach((label) => {
            label.textContent = theme === 'dark' ? 'Dark' : 'Light';
        });
    };

    if (themeButtons.length) {
        const savedTheme = localStorage.getItem('slau-theme');
        applyTheme(savedTheme || root.dataset.theme || (mediaQuery.matches ? 'dark' : 'light'), false);

        themeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                applyTheme(nextTheme);
            });
        });

        mediaQuery.addEventListener('change', (event) => {
            if (!localStorage.getItem('slau-theme')) {
                applyTheme(event.matches ? 'dark' : 'light', false);
            }
        });
    }

    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    initOptionalComponents();

    const revealItems = document.querySelectorAll('.reveal-fade');
    if (revealItems.length && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -40px 0px',
        });

        revealItems.forEach((item) => revealObserver.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    const carousel = document.querySelector('[data-home-carousel]');
    if (carousel) {
        const slides = Array.from(carousel.querySelectorAll('[data-home-slide]'));
        const dots = Array.from(carousel.querySelectorAll('[data-home-dot]'));
        const panels = Array.from(carousel.querySelectorAll('[data-home-panel]'));
        const prev = carousel.querySelector('[data-home-prev]');
        const next = carousel.querySelector('[data-home-next]');
        let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
        activeIndex = activeIndex >= 0 ? activeIndex : 0;
        let intervalId = null;

        const renderSlide = (index) => {
            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('is-active', slideIndex === index);
            });

            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('is-active', dotIndex === index);
                dot.setAttribute('aria-current', dotIndex === index ? 'true' : 'false');
            });

            panels.forEach((panel, panelIndex) => {
                panel.classList.toggle('is-active', panelIndex === index);
            });

            activeIndex = index;
        };

        const nextSlide = () => renderSlide((activeIndex + 1) % slides.length);
        const prevSlide = () => renderSlide((activeIndex - 1 + slides.length) % slides.length);

        const startAutoPlay = () => {
            if (slides.length < 2) return;
            clearInterval(intervalId);
            intervalId = window.setInterval(nextSlide, 5200);
        };

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                renderSlide(index);
                startAutoPlay();
            });
        });

        panels.forEach((panel, index) => {
            panel.addEventListener('click', () => {
                renderSlide(index);
                startAutoPlay();
            });
        });

        prev?.addEventListener('click', () => {
            prevSlide();
            startAutoPlay();
        });

        next?.addEventListener('click', () => {
            nextSlide();
            startAutoPlay();
        });

        carousel.addEventListener('mouseenter', () => clearInterval(intervalId));
        carousel.addEventListener('mouseleave', startAutoPlay);

        renderSlide(activeIndex);
        startAutoPlay();
    }

});



// Initialize Preline UI components
function initPrelineComponents() {
  // Use the recommended HSStaticMethods.autoInit() approach
  if (window.HSStaticMethods && typeof window.HSStaticMethods.autoInit === 'function') {
    window.HSStaticMethods.autoInit();
  }
}

// Listen for Livewire events to re-initialize components
document.addEventListener('livewire:navigated', () => {
  initOptionalComponents();
});

document.addEventListener('livewire:updated', () => {
  initOptionalComponents();
});

document.addEventListener('livewire:load', () => {
  initOptionalComponents();
});

// Initialize on page load
document.addEventListener('livewire:init', () => {
  initOptionalComponents();
});
