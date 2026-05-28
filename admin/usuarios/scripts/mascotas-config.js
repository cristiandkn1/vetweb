// admin/usuarios/scripts/mascotas-config.js
// Constantes, estado global, SVGs, colores y helpers compartidos

const BASE_MASCOTA = '/admin/usuarios/api';

window.MascotaState = {
    clienteId:     null,
    clienteNombre: null,
    eliminarId:    null,
};

// ── SVGs de especie ────────────────────────────────────────────────────────────
const ESPECIE_SVG = {
    Perro:   `<i data-lucide="dog" class="w-full h-full"></i>`,
    Gato:    `<i data-lucide="cat" class="w-full h-full"></i>`,
    Ave:     `<i data-lucide="bird" class="w-full h-full"></i>`,
    Conejo:  `<i data-lucide="rabbit" class="w-full h-full"></i>`,
    Roedor:  `<i data-lucide="squirrel" class="w-full h-full"></i>`,
    Hamster: `<i data-lucide="squirrel" class="w-full h-full"></i>`,
    Cobaya:  `<i data-lucide="squirrel" class="w-full h-full"></i>`,
    Reptil:  `<i data-lucide="turtle" class="w-full h-full"></i>`,
    default: `<i data-lucide="paw-print" class="w-full h-full"></i>`,
};

// ── Colores por especie ────────────────────────────────────────────────────────
const ESPECIE_COLOR = {
    Perro:   'text-amber-500 bg-amber-50',
    Gato:    'text-purple-500 bg-purple-50',
    Ave:     'text-sky-500 bg-sky-50',
    Conejo:  'text-pink-400 bg-pink-50',
    Roedor:  'text-orange-500 bg-orange-50',
    Hamster: 'text-orange-500 bg-orange-50',
    Cobaya:  'text-orange-500 bg-orange-50',
    Reptil:  'text-green-500 bg-green-50',
    default: 'text-gray-400 bg-gray-50',
};

function getEspecieSvg(especie)   { return ESPECIE_SVG[especie]   ?? ESPECIE_SVG.default;   }
function getEspecieColor(especie) { return ESPECIE_COLOR[especie] ?? ESPECIE_COLOR.default; }

// ── Helpers ────────────────────────────────────────────────────────────────────
function escM(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function calcularEdad(fechaNac) {
    if (!fechaNac) return null;
    const hoy   = new Date();
    const nac   = new Date(fechaNac);
    const años  = hoy.getFullYear() - nac.getFullYear();
    const m     = hoy.getMonth() - nac.getMonth();
    const total = m < 0 ? años - 1 : años;
    if (total < 1) {
        const meses = ((hoy.getFullYear() - nac.getFullYear()) * 12) + hoy.getMonth() - nac.getMonth();
        return `${meses} mes${meses !== 1 ? 'es' : ''}`;
    }
    return `${total} año${total !== 1 ? 's' : ''}`;
}