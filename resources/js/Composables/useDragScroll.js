import { ref, onMounted, onUnmounted } from 'vue';

/**
 * Composable for adding click-and-drag scrolling to horizontal containers
 * Usage: const { containerRef } = useDragScroll();
 */
export function useDragScroll() {
    const containerRef = ref(null);
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    const handleMouseDown = (e) => {
        if (!containerRef.value) return;

        isDown = true;
        containerRef.value.classList.add('cursor-grabbing', 'select-none');
        startX = e.pageX - containerRef.value.offsetLeft;
        scrollLeft = containerRef.value.scrollLeft;
    };

    const handleMouseLeave = () => {
        if (!containerRef.value) return;
        isDown = false;
        containerRef.value.classList.remove('cursor-grabbing', 'select-none');
    };

    const handleMouseUp = () => {
        if (!containerRef.value) return;
        isDown = false;
        containerRef.value.classList.remove('cursor-grabbing', 'select-none');
    };

    const handleMouseMove = (e) => {
        if (!containerRef.value || !isDown) return;

        e.preventDefault();
        const x = e.pageX - containerRef.value.offsetLeft;
        const walk = (x - startX) * 2; // Scroll speed multiplier
        containerRef.value.scrollLeft = scrollLeft - walk;
    };

    onMounted(() => {
        const container = containerRef.value;
        if (!container) return;

        container.addEventListener('mousedown', handleMouseDown);
        container.addEventListener('mouseleave', handleMouseLeave);
        container.addEventListener('mouseup', handleMouseUp);
        container.addEventListener('mousemove', handleMouseMove);
    });

    onUnmounted(() => {
        const container = containerRef.value;
        if (!container) return;

        container.removeEventListener('mousedown', handleMouseDown);
        container.removeEventListener('mouseleave', handleMouseLeave);
        container.removeEventListener('mouseup', handleMouseUp);
        container.removeEventListener('mousemove', handleMouseMove);
    });

    return {
        containerRef,
    };
}
