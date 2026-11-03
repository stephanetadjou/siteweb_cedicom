export default function PageBanner({ eyebrow, title, lead }) {
    return (
        <section className="relative overflow-hidden bg-gradient-to-br from-sky to-[#f5f8fc]">
            <div className="pointer-events-none absolute -right-10 top-0 h-full w-48 bg-[repeating-linear-gradient(45deg,#265ca614_0_2px,transparent_2px_12px)]" aria-hidden="true" />
            <div className="container-site relative py-14 lg:py-20">
                <p className="eyebrow">{eyebrow}</p>
                <h1 className="mt-4 max-w-[20ch] font-display text-[clamp(2rem,4.4vw,3.25rem)] leading-[1.06] font-extrabold tracking-tight text-navy">{title}</h1>
                {lead && <p className="mt-4 max-w-[60ch] text-[clamp(1.05rem,1.4vw,1.15rem)] text-muted">{lead}</p>}
            </div>
        </section>
    );
}
