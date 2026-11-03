import { useEffect, useRef, useState } from 'react';

const reducedMotion = () => typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Un IntersectionObserver répond toujours une première fois juste après observe().
// Sans réponse dans ce délai (onglet en arrière-plan, robot, navigateur restreint),
// on considère les animations indisponibles et on affiche tout : le contenu ne reste jamais caché.
const OBSERVER_TIMEOUT = 1000;

const revealAll = () => {
    document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-visible'));
};

/**
 * Révèle en fondu les éléments [data-reveal] quand ils entrent à l'écran.
 * Sans IntersectionObserver ou avec « réduire les animations », tout est visible d'emblée.
 */
export function useReveal(deps = []) {
    useEffect(() => {
        const items = [...document.querySelectorAll('[data-reveal]:not(.is-visible)')];
        if (reducedMotion() || !('IntersectionObserver' in window)) {
            revealAll();
            return;
        }

        let alive = false;
        const observer = new IntersectionObserver(
            (entries) => {
                alive = true;
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
        );
        items.forEach((el) => observer.observe(el));
        const fallback = setTimeout(() => !alive && (observer.disconnect(), revealAll()), OBSERVER_TIMEOUT);

        return () => {
            clearTimeout(fallback);
            observer.disconnect();
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, deps);
}

/** Nombre qui compte de 0 à `to` à sa première apparition, formaté selon la langue. */
export function CountUp({ to, locale = 'fr', suffix = '', duration = 1400 }) {
    const ref = useRef(null);
    const [value, setValue] = useState(to);

    useEffect(() => {
        const el = ref.current;
        if (!el || reducedMotion() || !('IntersectionObserver' in window)) return;

        let frame;
        let alive = false;
        const timers = [];
        const finish = () => {
            cancelAnimationFrame(frame);
            setValue(to);
        };

        setValue(0);
        const observer = new IntersectionObserver(([entry]) => {
            alive = true;
            if (!entry.isIntersecting) return;
            observer.disconnect();
            const start = performance.now();
            const tick = (now) => {
                const p = Math.min(1, Math.max(0, (now - start) / duration)); // now (horodatage de la frame) peut précéder start
                setValue(Math.round(to * (1 - Math.pow(1 - p, 3)))); // easeOutCubic
                if (p < 1) frame = requestAnimationFrame(tick);
            };
            frame = requestAnimationFrame(tick);
            timers.push(setTimeout(finish, duration + 300)); // si les frames sont suspendues
        });
        observer.observe(el);
        timers.push(setTimeout(() => !alive && (observer.disconnect(), finish()), OBSERVER_TIMEOUT));

        return () => {
            observer.disconnect();
            cancelAnimationFrame(frame);
            timers.forEach(clearTimeout);
        };
    }, [to, duration]);

    return (
        <span ref={ref} className="tabular-nums">
            <span className="sr-only">
                {to.toLocaleString(locale)}
                {suffix}
            </span>
            <span aria-hidden="true">
                {value.toLocaleString(locale)}
                {suffix}
            </span>
        </span>
    );
}
