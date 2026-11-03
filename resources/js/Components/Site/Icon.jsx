const PATHS = {
    warehouse: (
        <>
            <path d="M2 10 12 4l10 6v11H2z" />
            <path d="M6 21v-8h12v8M6 16h12M6 19h12" />
        </>
    ),
    truck: (
        <>
            <path d="M2 6h11v10H2zM13 10h5l3 3v3h-8z" />
            <circle cx="6" cy="18" r="2" />
            <circle cx="17" cy="18" r="2" />
        </>
    ),
    calendar: (
        <>
            <rect x="3" y="5" width="18" height="16" rx="2" />
            <path d="M3 10h18M8 3v4M16 3v4" />
            <path d="m15 13 1 2 2 .3-1.5 1.4.4 2.1-1.9-1-1.9 1 .4-2.1L12 15.3l2-.3z" />
        </>
    ),
    shield: (
        <>
            <path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z" />
            <path d="m8.5 12 2.5 2.5 4.5-5" />
        </>
    ),
    handshake: (
        <>
            <path d="m2 12 4-4 4 2 3-3 4 1 5 5-5 5-3-1-2 2-3-1-3-3-2-1z" />
            <path d="m10 10 3 3M13 9l3 3" />
        </>
    ),
    phone: <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" />,
    mail: (
        <>
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 7 9 6 9-6" />
        </>
    ),
    pin: (
        <>
            <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" />
            <circle cx="12" cy="9.5" r="2.5" />
        </>
    ),
    globe: (
        <>
            <circle cx="12" cy="12" r="9" />
            <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
        </>
    ),
    'arrow-right': <path d="M5 12h14M13 6l6 6-6 6" />,
    'chevron-left': <path d="M15 6l-6 6 6 6" />,
    'chevron-right': <path d="M9 6l6 6-6 6" />,
    external: <path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" />,
    menu: <path d="M4 7h16M4 12h16M4 17h16" />,
    close: <path d="M6 6l12 12M18 6 6 18" />,
    check: <path d="m5 12 4.5 4.5L19 7" />,
    // Icônes du back office
    home: <path d="M3 11 12 4l9 7M5 10v10h5v-6h4v6h5V10" />,
    download: <path d="M12 4v11M7 10l5 5 5-5M5 20h14" />,
    search: (
        <>
            <circle cx="11" cy="11" r="6.5" />
            <path d="m16 16 4.5 4.5" />
        </>
    ),
    logout: <path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 8l-4 4 4 4M6 12h11" />,
    alert: (
        <>
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7.5v5.5M12 16.5v.01" />
        </>
    ),
};

export default function Icon({ name, className = '', strokeWidth = 1.8 }) {
    return (
        <svg
            className={`shrink-0 ${className}`}
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth={strokeWidth}
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
        >
            {PATHS[name]}
        </svg>
    );
}
