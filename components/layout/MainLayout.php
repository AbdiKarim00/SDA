<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize lazy loading
    const lazyLoad = () => {
        const images = document.querySelectorAll('img[data-src]');
        const iframes = document.querySelectorAll('iframe[data-src]');
        
        const loadElement = (element) => {
            if (element.dataset.src) {
                element.src = element.dataset.src;
                element.removeAttribute('data-src');
            }
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadElement(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });

        images.forEach(img => observer.observe(img));
        iframes.forEach(iframe => observer.observe(iframe));
    };

    // Initialize lazy loading
    lazyLoad();

    // Handle dynamic content loading
    const handleDynamicContent = () => {
        const content = document.querySelector('#main-content');
        if (content) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList') {
                        lazyLoad();
                    }
                });
            });

            observer.observe(content, {
                childList: true,
                subtree: true
            });
        }
    };

    handleDynamicContent();
});
</script> 