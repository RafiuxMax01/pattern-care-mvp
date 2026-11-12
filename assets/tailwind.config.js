// Configuracion del tema de Tailwind (la lee el CDN cargado mas arriba).

tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Segoe UI', 'system-ui', 'sans-serif'],
                heading: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
            },
            colors: {
                bc: {
                    blue: '#0078d4',       // Dynamics Blue
                    darkBlue: '#005a9e',
                    navy: '#002050',
                    teal: '#008272',       // Dynamics Accent
                    gray: '#f3f2f1',
                    cardBg: '#ffffff',
                    cardBorder: '#e5e7eb',
                    lightBg: '#f9fafb'
                }
            }
        }
    }
}
