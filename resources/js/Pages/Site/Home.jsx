import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import ContactCards from '@/Components/Site/ContactCards';
import Icon from '@/Components/Site/Icon';
import Layout from '@/Components/Site/Layout';
import PartnershipForm from '@/Components/Site/PartnershipForm';
import { useLocale, useT } from '@/lib/i18n';
import { CountUp } from '@/lib/motion';

const bigTitle = 'mt-4 font-display text-[clamp(1.9rem,4vw,3rem)] leading-[1.08] font-extrabold tracking-tight text-navy';

// Voiles clairs du hero (en bas sur mobile, à gauche sur grand écran) : léger = photo bien visible, fort = photo estompée.
const HERO_VEILS = {
    light: 'bg-[linear-gradient(0deg,#eef3f9_0%,#eef3f900_30%)] lg:bg-[linear-gradient(90deg,#eef3f9b3_0%,#eef3f9b3_30%,#eef3f94d_40%,#eef3f900_50%),linear-gradient(0deg,#eef3f9_0%,#eef3f900_12%)]',
    strong: 'bg-[linear-gradient(0deg,#eef3f9_0%,#eef3f900_45%)] lg:bg-[linear-gradient(90deg,#eef3f9f5_0%,#eef3f9e0_36%,#eef3f966_62%,#eef3f900_85%),linear-gradient(0deg,#eef3f9_0%,#eef3f900_22%)]',
};

// Diaporama du hero : ajouter une image = ajouter une ligne (src, clé de traduction du texte alternatif, cadrage, voile).
const HERO_SLIDES = [
    { src: '/images/hero-entrepot-2.jpg', alt: 'hero.image2_alt', position: '60% 55%', veil: 'light' },
    { src: '/images/hero-entrepot.jpg', alt: 'hero.image_alt', position: '70% 60%', veil: 'strong' },
    { src: '/images/logistique/entrepot.jpg', alt: 'hero.image3_alt', position: '50% 45%', veil: 'light' },
];
const HERO_DELAY = 6000;

function HeroSlideshow() {
    const t = useT();
    const slides = HERO_SLIDES.map((s) => ({ ...s, alt: t(s.alt) }));
    const [index, setIndex] = useState(0);
    const [paused, setPaused] = useState(false);
    const count = slides.length;
    const go = (step) => setIndex((i) => (i + step + count) % count);

    useEffect(() => {
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (count < 2 || paused || reduced) return;
        const timer = setTimeout(() => go(1), HERO_DELAY);
        return () => clearTimeout(timer);
    }, [index, paused, count]);

    return (
        <div
            className="relative h-56 sm:h-72 lg:absolute lg:inset-0 lg:h-full"
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
            onFocus={() => setPaused(true)}
            onBlur={() => setPaused(false)}
        >
            {/* Chaque diapositive porte son voile : image et voile changent ensemble en fondu */}
            {slides.map((slide, i) => (
                <div key={`${i}-${slide.src}`} aria-hidden={i !== index} className={`absolute inset-0 overflow-hidden transition-opacity duration-1000 ${i === index ? 'opacity-100' : 'opacity-0'}`}>
                    <img
                        src={slide.src}
                        alt={i === index ? slide.alt : ''}
                        fetchPriority={i === 0 ? 'high' : 'low'}
                        style={{ objectPosition: slide.position }}
                        className={`size-full object-cover ${i === index ? 'hero-kenburns' : ''}`}
                    />
                    <div className={`absolute inset-0 ${HERO_VEILS[slide.veil]}`} />
                </div>
            ))}

            {count > 1 && (
                <div className="absolute right-3 bottom-8 z-10 flex items-center gap-2 rounded-full bg-white/80 px-2 py-1.5 shadow-card backdrop-blur-sm sm:right-4 lg:right-8 lg:bottom-10">
                    <button type="button" onClick={() => go(-1)} aria-label={t('hero.prev')} className="grid size-8 place-items-center rounded-full text-navy transition hover:bg-brand hover:text-white">
                        <Icon name="chevron-left" className="size-5" />
                    </button>
                    {slides.map((slide, i) => (
                        <button
                            key={`${i}-${slide.src}`}
                            type="button"
                            onClick={() => setIndex(i)}
                            aria-label={t('hero.goto', { n: i + 1, total: count })}
                            aria-current={i === index}
                            className="grid size-6 place-items-center"
                        >
                            <span className={`block h-2 rounded-full transition-all ${i === index ? 'w-5 bg-brand' : 'w-2 bg-navy/35'}`} />
                        </button>
                    ))}
                    <button type="button" onClick={() => go(1)} aria-label={t('hero.next')} className="grid size-8 place-items-center rounded-full text-navy transition hover:bg-brand hover:text-white">
                        <Icon name="chevron-right" className="size-5" />
                    </button>
                </div>
            )}
        </div>
    );
}

function Hero() {
    const t = useT();
    const { locale } = useLocale();
    const stats = [
        ['warehouse', 30000, ' m²', t('stats.warehouses')],
        ['truck', 35, '', t('stats.trucks')],
        ['calendar', 30, '+', t('stats.years')],
    ];

    return (
        <section id="accueil" className="relative overflow-hidden bg-gradient-to-b from-[#eaf1f9] to-page pb-6 lg:pb-10">
            <HeroSlideshow />

            <div className="container-site relative">
                <div className="pt-4 pb-8 lg:flex lg:min-h-[min(68vh,600px)] lg:flex-col lg:justify-center lg:py-16 lg:[text-shadow:0_0_18px_rgb(238_243_249),0_0_8px_rgb(238_243_249),0_0_2px_rgb(238_243_249)]">
                    <p className="eyebrow">{t('hero.eyebrow')}</p>
                    <h1 className="mt-4 max-w-[24ch] font-display text-[clamp(1.7rem,3.4vw,2.75rem)] leading-[1.12] font-extrabold uppercase tracking-tight text-navy">
                        {t('hero.title')}
                    </h1>
                    <p className="mt-4 max-w-[46ch] text-[17px] text-ink lg:text-lg">{t('hero.lead')}</p>
                    <div className="mt-7 flex flex-col gap-3 sm:flex-row">
                        <a href="#poles" className="btn btn-primary">{t('hero.cta_poles')} <Icon name="arrow-right" /></a>
                        <a href="#partenariat" className="btn btn-outline">{t('hero.cta_partner')}</a>
                    </div>
                </div>

                <dl className="grid max-w-3xl divide-y divide-line rounded-2xl bg-white shadow-card sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    {stats.map(([icon, value, suffix, label]) => (
                        <div key={icon} className="flex items-center gap-4 px-5 py-4 sm:justify-center sm:py-5">
                            <Icon name={icon} className="size-10 text-accent" strokeWidth={1.5} />
                            <div className="flex flex-col-reverse">
                                <dt className="text-[15px] text-muted">{label}</dt>
                                <dd className="font-display text-[clamp(1.4rem,2.2vw,1.75rem)] leading-tight font-extrabold whitespace-nowrap text-navy"><CountUp to={value} suffix={suffix} locale={locale} /></dd>
                            </div>
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    );
}

function Poles({ poles }) {
    const t = useT();

    return (
        <section id="poles" className="pt-14 lg:pt-16">
            <div className="container-site">
                <h2 className="section-title">{t('poles.title')}</h2>
                <div className="mt-9 grid gap-5 md:grid-cols-3">
                    {poles.map((pole, i) => (
                        <a key={pole.id} href={`#${pole.anchor}`} data-reveal style={{ '--reveal-delay': `${i * 120}ms` }} className="group flex flex-col overflow-hidden rounded-xl bg-white text-center shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-lift">
                            <span className="block aspect-[2.4/1] overflow-hidden">
                                <img src={pole.image} alt="" loading="lazy" style={{ objectPosition: pole.imagePosition }} className="size-full object-cover transition duration-500 group-hover:scale-105" />
                            </span>
                            <h3 className="mx-4 mt-5 font-display text-lg font-extrabold text-navy">{pole.title}</h3>
                            <p className="mx-5 mt-2 text-[15px] text-muted">{pole.description}</p>
                            <span className="flex flex-wrap items-center justify-center gap-3 px-4 py-5">
                                {pole.logos.map((logo) => (
                                    <img key={logo.name} src={logo.src} alt={logo.name} loading="lazy" className={`h-8 w-auto max-w-[88px] object-contain ${pole.roundLogos ? 'rounded-full' : ''}`} />
                                ))}
                            </span>
                            <span className="mt-auto flex items-center justify-center gap-2 border-t border-line bg-soft px-4 py-3 text-[13px] font-bold text-brand">{pole.tags}</span>
                        </a>
                    ))}
                </div>
            </div>

            <div className="mt-14 bg-brand text-white">
                <div className="container-site flex flex-col items-start gap-4 py-6 sm:flex-row sm:items-center sm:justify-between">
                    <p className="font-display text-[clamp(1.05rem,1.8vw,1.35rem)] font-extrabold uppercase">{t('band.title')}</p>
                    <Link href="/devenir-partenaire" className="btn btn-accent shrink-0 uppercase tracking-wide">{t('band.cta')}</Link>
                </div>
            </div>
        </section>
    );
}

function About() {
    const t = useT();
    const facts = [
        ['1989', t('about.fact_1989')],
        [t('about.fact_poles_value'), t('about.fact_poles')],
        ['Angola', t('about.fact_angola')],
    ];

    return (
        <section id="a-propos" className="py-16 lg:py-24">
            <div data-reveal className="container-site grid items-center gap-8 lg:grid-cols-[auto_1fr] lg:gap-16">
                <p className="relative w-fit font-display text-[clamp(7rem,18vw,15rem)] leading-[0.8] font-extrabold tracking-tighter text-transparent [-webkit-text-stroke:2px_var(--color-brand)]" aria-hidden="true">
                    30<span className="absolute -right-[0.35em] top-[0.02em] text-[0.38em] text-brand [-webkit-text-stroke:0]">+</span>
                </p>
                <div>
                    <p className="eyebrow">{t('about.eyebrow')}</p>
                    <h2 className={bigTitle}>{t('about.title')}</h2>
                    <p className="mt-5 max-w-[62ch] text-[clamp(1.05rem,1.4vw,1.15rem)] text-muted">{t('about.text')}</p>
                    <dl className="mt-8 flex flex-wrap gap-x-10 gap-y-5">
                        {facts.map(([value, label]) => (
                            <div key={label} className="flex flex-col-reverse border-l-2 border-brand pl-4">
                                <dt className="text-sm text-muted">{label}</dt>
                                <dd className="font-display text-2xl font-extrabold text-navy">{value}</dd>
                            </div>
                        ))}
                    </dl>
                </div>
            </div>
        </section>
    );
}

function Brands({ brands, categories }) {
    const t = useT();
    const [filter, setFilter] = useState('all');
    const visible = brands.filter((b) => filter === 'all' || b.category === filter);

    return (
        <section id="marques" className="bg-white py-16 lg:py-24">
            <div className="container-site">
                <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="eyebrow">{t('brands.eyebrow')}</p>
                        <h2 className={bigTitle}>{t('brands.title')}</h2>
                    </div>
                    <div className="flex flex-wrap gap-2" role="group" aria-label={t('brands.filter_label')}>
                        {['all', ...categories].map((key) => (
                            <button
                                key={key}
                                type="button"
                                aria-pressed={filter === key}
                                onClick={() => setFilter(key)}
                                className="rounded-full border border-line bg-white px-4 py-2 text-sm font-bold text-muted transition hover:border-brand hover:text-brand aria-pressed:border-brand aria-pressed:bg-brand aria-pressed:text-white"
                            >
                                {t(`brands.category.${key}`)}
                            </button>
                        ))}
                    </div>
                </div>

                <ul data-reveal className="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    {visible.map((brand) => (
                        <li key={brand.id} title={brand.name} className="group grid min-h-[104px] place-items-center rounded-2xl border border-line bg-white p-3 transition hover:-translate-y-0.5 hover:border-brand">
                            <img src={brand.logo} alt={brand.name} loading="lazy" className="max-h-14 max-w-full object-contain transition duration-300 group-hover:scale-105 sm:max-h-[68px]" />
                        </li>
                    ))}
                </ul>
                <p className="mt-5 text-sm text-muted" aria-live="polite">{t('brands.count', { count: visible.length })}</p>
            </div>
        </section>
    );
}

function EnseigneList({ title, subtitle, items, border, bg }) {
    return (
        <div className="min-w-0">
            <div className={`mb-3 border-l-4 pl-3.5 ${border}`}>
                <h4 className="text-[15px] font-extrabold uppercase tracking-wide text-navy">{title}</h4>
                <p className="text-[11px] uppercase tracking-[0.14em] text-muted">{subtitle}</p>
            </div>
            <ul className="grid gap-2.5">
                {items.map((e) => (
                    <li key={e.id} className={`flex items-center gap-3.5 rounded-xl border border-line px-3.5 py-2.5 font-bold text-navy ${bg}`}>
                        <span className="grid size-11 shrink-0 place-items-center overflow-hidden rounded-full bg-white shadow-[0_2px_8px_rgb(11_42_91/0.14)]">
                            <img src={e.logo} alt="" loading="lazy" className="max-h-[30px] max-w-[34px] object-contain" />
                        </span>
                        {e.name}
                    </li>
                ))}
            </ul>
        </div>
    );
}

// Photo principale seule, ou composition : grande photo + vignettes (une colonne à droite dès 640 px, une rangée dessous sur mobile).
function DivisionMedia({ division }) {
    const tile = 'overflow-hidden rounded-2xl shadow-lift';
    const img = 'size-full object-cover transition duration-500 hover:scale-105';

    if (!division.gallery?.length) {
        return (
            <div className="overflow-hidden rounded-2xl shadow-lift">
                <img src={division.image} alt={division.imageAlt} loading="lazy" className="aspect-[4/3] w-full object-cover" />
            </div>
        );
    }

    return (
        <div className="grid grid-cols-2 gap-3 sm:aspect-[4/3] sm:grid-cols-3 sm:grid-rows-2">
            <div className={`${tile} col-span-2 aspect-[16/10] sm:row-span-2 sm:aspect-auto`}>
                <img src={division.image} alt={division.imageAlt} loading="lazy" className={img} />
            </div>
            {division.gallery.slice(0, 2).map((photo) => (
                <div key={photo.image} className={`${tile} aspect-square sm:aspect-auto`}>
                    <img src={photo.image} alt={photo.alt} loading="lazy" className={`${img} object-[center_40%]`} />
                </div>
            ))}
        </div>
    );
}

function Divisions({ divisions }) {
    const t = useT();

    return (
        <section id="divisions" className="py-16 lg:py-24">
            <div className="container-site">
                <h2 className="section-title">{t('divisions.title')}</h2>
                <p className="section-lead">{t('divisions.lead')}</p>

                {divisions.map((division, i) => (
                    <article key={division.id} data-reveal className="mt-14 grid items-center gap-8 lg:mt-20 lg:grid-cols-2 lg:gap-14">
                        <div className={`relative ${i % 2 ? 'lg:order-2' : ''}`}>
                            <DivisionMedia division={division} />
                            <span className="absolute left-4 top-4 bg-white px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-brand">Retail Group</span>
                        </div>

                        <div className="min-w-0">
                            <h3 className="font-display text-[clamp(1.75rem,3.2vw,2.6rem)] leading-tight font-extrabold tracking-tight text-navy">{division.title}</h3>
                            <p className="mt-4 text-[17px] leading-relaxed text-muted">{division.description}</p>

                            <p className="mt-6 mb-2.5 text-[11px] font-bold uppercase tracking-[0.2em] text-muted">{t('divisions.categories')}</p>
                            <ul className="flex flex-wrap gap-2">
                                {division.categories.map((c) => (
                                    <li key={c} className="rounded-full border border-[#bcd9ef] bg-[#e3f0fa] px-3.5 py-1 text-[13px] text-[#1f5f8f]">{c}</li>
                                ))}
                            </ul>

                            <div className="mt-7 grid gap-6 sm:grid-cols-2">
                                <EnseigneList title={t('divisions.franchises')} subtitle={t('divisions.franchises_sub')} items={division.franchises} border="border-[#f2c94c]" bg="bg-soft" />
                                <EnseigneList title={t('divisions.concepts')} subtitle={t('divisions.concepts_sub')} items={division.concepts} border="border-navy" bg="bg-white" />
                            </div>
                        </div>
                    </article>
                ))}

                <div className="mt-14 text-center">
                    <a href="https://www.retailgroup-brands.com/" target="_blank" rel="noopener" className="btn btn-outline">
                        {t('divisions.cta')} <Icon name="external" />
                    </a>
                </div>
            </div>
        </section>
    );
}

function Logistics() {
    const t = useT();
    const figures = [
        ['30 000 m²', t('stats.warehouses')],
        ['35', t('logistics.fig_trucks')],
        ['24 h', t('logistics.fig_delivery')],
    ];
    const features = [
        ['warehouse', 'f1'],
        ['shield', 'f2'],
        ['truck', 'f3'],
        ['handshake', 'f4'],
    ];

    return (
        <section id="logistique" className="bg-white py-16 lg:py-24">
            <div className="container-site">
                <h2 className="section-title">{t('logistics.title')}</h2>
                <p className="section-lead">{t('logistics.lead')}</p>

                <div className="mt-10 grid items-center gap-8 lg:grid-cols-2 lg:gap-10">
                    <figure data-reveal className="relative overflow-hidden rounded-2xl shadow-card">
                        <img src="/images/logistique/equipe.jpg" alt={t('logistics.image_alt')} loading="lazy" className="aspect-[4/3] w-full object-cover" />
                        <dl className="absolute inset-x-0 bottom-0 grid grid-cols-3 bg-gradient-to-t from-navy/95 to-navy/70 text-center text-white">
                            {figures.map(([value, label]) => (
                                <div key={value} className="flex flex-col-reverse px-2 py-3 sm:py-4">
                                    <dt className="text-xs leading-snug text-white/90 sm:text-[13px]">{label}</dt>
                                    <dd className="font-display text-lg font-extrabold text-[#ffb37a] sm:text-2xl">{value}</dd>
                                </div>
                            ))}
                        </dl>
                    </figure>

                    <ul className="grid gap-4">
                        {features.map(([icon, key], i) => (
                            <li key={key} data-reveal style={{ '--reveal-delay': `${i * 100}ms` }} className="flex gap-4 rounded-lg border-l-[5px] border-accent bg-soft px-5 py-4">
                                <span className="grid size-11 shrink-0 place-items-center rounded-lg bg-white text-brand"><Icon name={icon} className="size-6" /></span>
                                <div>
                                    <h3 className="font-display text-[17px] font-extrabold text-navy">{t(`logistics.${key}_title`)}</h3>
                                    <p className="mt-0.5 text-[15px] text-muted">{t(`logistics.${key}_text`)}</p>
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </section>
    );
}

export default function Home({ poles, brands, brandCategories, divisions, sectors }) {
    const t = useT();

    return (
        <Layout>
            <Hero />
            <Poles poles={poles} />
            <About />
            <Brands brands={brands} categories={brandCategories} />
            <Divisions divisions={divisions} />
            <Logistics />

            <section id="partenariat" className="py-16 lg:py-20">
                <div className="container-site">
                    <h2 className="section-title">{t('partner.title')}</h2>
                    <p className="section-lead">{t('partner.lead')}</p>
                    <div data-reveal className="mx-auto mt-8 max-w-3xl rounded-xl bg-white p-5 shadow-card sm:p-9">
                        <PartnershipForm sectors={sectors} />
                    </div>
                </div>
            </section>

            <section className="pb-20">
                <div className="container-site">
                    <div className="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="eyebrow">{t('contact.eyebrow')}</p>
                            <h2 className={bigTitle}>{t('contact.title')}</h2>
                        </div>
                        <Link href="/contact" className="btn btn-primary self-start sm:self-auto">{t('contact.cta')} <Icon name="arrow-right" /></Link>
                    </div>
                    <ContactCards className="mt-8" />
                </div>
            </section>
        </Layout>
    );
}
