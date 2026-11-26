// Configuracion del tema de Tailwind (la lee el CDN cargado mas arriba).
//
// Sistema visual de Pattern Care:
// - Una sola familia tipografica: Inter (font-heading queda como alias).
// - Un color de marca, `marca`: azul marino de uniforme quirurgico.
// - Color con significado solo en estados: emerald (bien), amber (atencion),
//   rose (critico). Todo lo demas, en grises.

tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
                heading: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
            },
            colors: {
                // Contraste sobre blanco: 700 ≈ 11:1, 600 ≈ 8:1, 500 ≈ 6:1.
                // 400 y menores solo para iconos, bordes y fondos.
                marca: {
                    50:  '#eef3f9',
                    100: '#dbe5f1',
                    200: '#b8cbe2',
                    300: '#8ba9cc',
                    400: '#5b82b0',
                    500: '#3a6294',
                    600: '#2b4f7e',
                    700: '#1f3a5f',
                    800: '#172c48',
                    900: '#101f33'
                }
            }
        }
    }
}
