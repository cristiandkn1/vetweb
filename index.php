<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetWeb - Cuidado Veterinario Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50">

    <?php
    require_once __DIR__ . '/includes/db.php';
    $servicios = [];
    try {
        $stmt = $pdo->query("SELECT nombre, descripcion, icono, precio_min, precio_max FROM servicios WHERE activo = 1 ORDER BY nombre ASC");
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silencio
    }

    ?>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md shadow-sm transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span class="text-2xl font-bold text-brand-900 tracking-tight">VetWeb</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#services" class="text-gray-600 hover:text-brand-600 font-medium transition-colors">Servicios</a>
                    <a href="ticket.php" class="text-gray-600 hover:text-brand-600 font-medium transition-colors">Seguimiento</a>
                    <a href="#location" class="text-gray-600 hover:text-brand-600 font-medium transition-colors">Ubicación</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="assets/img/hero-bg.png" alt="Veterinary Clinic Background" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-brand-900/80 to-brand-600/40 mix-blend-multiply"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full text-center">

            <div class="text-white space-y-6 max-w-3xl mx-auto">
                <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight leading-tight">
                    Cuidado Experto <br>
                    <span class="text-brand-100">Para Tu Mascota</span>
                </h1>
                <p class="text-xl md:text-2xl text-brand-50 font-light">
                    Soluciones veterinarias modernas con la mejor tecnología y atención personalizada.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                    <a href="#services" class="px-8 py-3 bg-white text-brand-600 rounded-lg font-semibold hover:bg-brand-50 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Ver Servicios
                    </a>
                    <button id="btn-solicitar-cita" type="button" class="px-8 py-3 border border-white/30 text-white rounded-lg font-semibold hover:bg-white/10 transition-all backdrop-blur-sm">
                        <i class="fa-solid fa-calendar-plus mr-2"></i>
                        Solicitar Cita
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Nuestros Servicios -->
    <section id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Nuestros Servicios</h2>
                <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">Todo lo que tu mascota necesita para una vida larga y saludable.</p>
            </div>

            <?php if (empty($servicios)): ?>
                <div class="text-center text-gray-400 py-12">
                    <i class="fa-solid fa-paw text-4xl mb-4"></i>
                    <p>Próximamente más servicios disponibles.</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-3 gap-8">
                    <?php foreach ($servicios as $s): 
                        $fa = !empty($s['icono']) ? htmlspecialchars($s['icono']) : 'fa-paw';
                        $precio = $s['precio_min'] > 0 ? '$' . number_format((float)$s['precio_min'], 0, ',', '.') : null;
                    ?>
                    <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition-shadow group">
                        <div class="w-12 h-12 bg-brand-100 rounded-lg flex items-center justify-center mb-4 text-brand-600 text-xl group-hover:bg-brand-600 group-hover:text-white transition-colors">
                            <i class="fa-solid <?= $fa ?>"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($s['nombre']) ?></h3>
                        <?php if (!empty($s['descripcion'])): ?>
                            <p class="text-gray-600"><?= htmlspecialchars($s['descripcion']) ?></p>
                        <?php endif; ?>
                        <?php if ($precio): ?>
                            <p class="text-brand-600 font-semibold mt-3">Desde <?= $precio ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Mapa -->
    <section id="location" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Nuestra Ubicación</h2>
                <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">Visítanos en nuestra clínica en Copiapó, Atacama.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-10 items-start">
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center text-brand-600 shrink-0">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Dirección</h3>
                            <p class="text-gray-600">Av. Los Carrera 1234, Copiapó, Región de Atacama</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center text-brand-600 shrink-0">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Teléfono</h3>
                            <p class="text-gray-600">+56 52 2 123 456</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center text-brand-600 shrink-0">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Horarios</h3>
                            <p class="text-gray-600">Lunes a Viernes: 9:00 – 19:00</p>
                            <p class="text-gray-600">Sábado: 10:00 – 14:00</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl overflow-hidden shadow-lg border border-gray-200 h-[350px]">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d109011.94278755372!2d-70.40172153583334!3d-27.36647446342583!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x96a4780e0a3b0c63%3A0x9e0b1f5c5c5b5c5b!2sCopiap%C3%B3%2C%20Atacama!5e0!3m2!1ses!2scl!4v1"
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <span class="text-2xl font-bold tracking-tight mb-4 block">VetWeb</span>
                    <p class="text-gray-400 max-w-sm">Dedicados al bienestar de tus mascotas. Tecnología y amor en cada consulta.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Enlaces Rápidos</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Inicio</a></li>
                        <li><a href="#services" class="hover:text-brand-500 transition-colors">Servicios</a></li>
                        <li><a href="#location" class="hover:text-brand-500 transition-colors">Ubicación</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Contacto</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>contacto@vetweb.cl</li>
                        <li>+56 52 2 123 456</li>
                        <li>Av. Los Carrera 1234, Copiapó</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500 text-sm">
                &copy; 2024 VetWeb. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <a href="https://wa.me/56912345678?text=Hola%2C%20quisiera%20agendar%20una%20hora%20para%20mi%20mascota"
       target="_blank" rel="noopener"
       class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg hover:shadow-xl flex items-center justify-center transition-all hover:-translate-y-1 active:scale-95"
       title="Contáctanos por WhatsApp">
        <i class="fa-brands fa-whatsapp text-3xl"></i>
    </a>

    <!-- Modal Solicitar Cita -->
    <div id="modal-solicitar-cita" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/50" onclick="cerrarModalCita()"></div>
        <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[95vh]">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between shrink-0">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fa-solid fa-calendar-plus text-brand-600 mr-2"></i>
                    Solicitar Cita
                </h2>
                <button type="button" onclick="cerrarModalCita()" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="px-6 pt-5 pb-2 shrink-0">
                <div class="flex items-center gap-1">
                    <div class="step-item active" data-step="1">
                        <div class="step-circle">1</div>
                        <span class="step-label">Cliente</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" data-step="2">
                        <div class="step-circle">2</div>
                        <span class="step-label">Mascota</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" data-step="3">
                        <div class="step-circle">3</div>
                        <span class="step-label">Cita</span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 overflow-y-auto flex-1">
                <form id="form-solicitar-cita" onsubmit="return false">
                    <!-- STEP 1: Cliente -->
                    <div class="step-panel" data-step="1">
                        <p class="text-sm text-gray-500 mb-4">Ingresa tus datos personales para iniciar la solicitud.</p>
                        <div class="space-y-4">
                            <div>
                                <label for="sc-nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                                <input type="text" id="sc-nombre" name="nombre_completo" required
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label for="sc-email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                                <input type="email" id="sc-email" name="email" required
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label for="sc-rut" class="block text-sm font-medium text-gray-700 mb-1">RUT o Pasaporte</label>
                                <input type="text" id="sc-rut" name="rut"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                                    placeholder="12.345.678-5 o número de pasaporte"
                                    oninput="formatearRut(this)">
                            </div>
                            <div>
                                <label for="sc-telefono" class="block text-sm font-medium text-gray-700 mb-1">Celular <span class="text-red-500">*</span></label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 py-2.5 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 text-gray-500 text-sm font-medium select-none">+569</span>
                                    <input type="tel" id="sc-telefono" name="telefono" required maxlength="8"
                                        class="flex-1 px-3 py-2.5 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                                        placeholder="12345678">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Mascota -->
                    <div class="step-panel hidden" data-step="2">
                        <p class="text-sm text-gray-500 mb-4">Cuéntanos sobre tu mascota.</p>
                        <div class="space-y-4">
                            <div>
                                <label for="sc-mascota-nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Mascota <span class="text-red-500">*</span></label>
                                <input type="text" id="sc-mascota-nombre" name="mascota_nombre" required
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label for="sc-especie" class="block text-sm font-medium text-gray-700 mb-1">Especie <span class="text-red-500">*</span></label>
                                <select id="sc-especie" name="mascota_especie" required
                                    onchange="toggleOtroEspecie()"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                                    <option value="">Seleccionar...</option>
                                    <option>Perro</option>
                                    <option>Gato</option>
                                    <option>Ave</option>
                                    <option>Conejo</option>
                                    <option>Roedor</option>
                                    <option>Reptil</option>
                                    <option value="__otro__">Otro (especificar)</option>
                                </select>
                                <div id="sc-especie-otro-wrapper" class="hidden mt-2">
                                    <input type="text" id="sc-especie-otro" placeholder="Especificar especie..."
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                                </div>
                            </div>
                            <div>
                                <label for="sc-raza" class="block text-sm font-medium text-gray-700 mb-1">Raza</label>
                                <input type="text" id="sc-raza" name="mascota_raza"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label for="sc-sexo" class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                                <select id="sc-sexo" name="mascota_sexo"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                                    <option value="">Seleccionar...</option>
                                    <option>Macho</option>
                                    <option>Hembra</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Cita -->
                    <div class="step-panel hidden" data-step="3">
                        <p class="text-sm text-gray-500 mb-4">Selecciona el servicio y la fecha deseada.</p>
                        <div class="space-y-4">
                            <div>
                                <label for="sc-servicio" class="block text-sm font-medium text-gray-700 mb-1">Servicio <span class="text-red-500">*</span></label>
                                <select id="sc-servicio" name="servicio" required
                                    onchange="toggleOtroServicio()"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                                    <option value="">Seleccionar servicio...</option>
                                    <?php foreach ($servicios as $sv): ?>
                                    <option value="<?= htmlspecialchars($sv['nombre']) ?>"><?= htmlspecialchars($sv['nombre']) ?></option>
                                    <?php endforeach; ?>
                                    <option value="__otro__">Otro (especificar)</option>
                                </select>
                                <div id="sc-servicio-otro-wrapper" class="hidden mt-2">
                                    <input type="text" id="sc-servicio-otro" placeholder="Especificar servicio..."
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                                </div>
                            </div>
                            <div>
                                <label for="sc-fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha Deseada <span class="text-red-500">*</span></label>
                                <input type="date" id="sc-fecha" name="fecha" required
                                    min="<?= date('Y-m-d') ?>"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label for="sc-nota" class="block text-sm font-medium text-gray-700 mb-1">Nota o Comentario</label>
                                <textarea id="sc-nota" name="nota" rows="3"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition resize-none"
                                    placeholder="Describe brevemente el motivo de la consulta..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between shrink-0 bg-gray-50">
                <button type="button" id="btn-step-prev"
                    class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors hidden">
                    <i class="fa-solid fa-chevron-left mr-1"></i>
                    Anterior
                </button>
                <div></div>
                <button type="button" id="btn-step-next"
                    class="px-6 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
                    Siguiente
                    <i class="fa-solid fa-chevron-right ml-1"></i>
                </button>
                <button type="button" id="btn-step-submit"
                    class="px-6 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors shadow-sm hidden">
                    <i class="fa-solid fa-paper-plane mr-1"></i>
                    Enviar Solicitud
                </button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
    // === Modal Solicitar Cita ===
    const modalCita = document.getElementById('modal-solicitar-cita');
    const formCita = document.getElementById('form-solicitar-cita');
    const btnNext = document.getElementById('btn-step-next');
    const btnPrev = document.getElementById('btn-step-prev');
    const btnSubmit = document.getElementById('btn-step-submit');
    const stepPanels = document.querySelectorAll('.step-panel');
    const stepItems = document.querySelectorAll('.step-item');
    let currentStep = 1;
    const totalSteps = 3;

    document.getElementById('btn-solicitar-cita').addEventListener('click', abrirModalCita);

    function toggleOtroEspecie() {
        const sel = document.getElementById('sc-especie');
        const wrapper = document.getElementById('sc-especie-otro-wrapper');
        wrapper.classList.toggle('hidden', sel.value !== '__otro__');
        if (sel.value === '__otro__') {
            document.getElementById('sc-especie-otro').focus();
        }
    }

    function toggleOtroServicio() {
        const sel = document.getElementById('sc-servicio');
        const wrapper = document.getElementById('sc-servicio-otro-wrapper');
        wrapper.classList.toggle('hidden', sel.value !== '__otro__');
        if (sel.value === '__otro__') {
            document.getElementById('sc-servicio-otro').focus();
        }
    }

    function formatearRut(input) {
        let val = input.value.replace(/[^0-9kK\-]/g, '');
        if (!val) { input.value = ''; return; }
        // Si parece RUT (solo dígitos + posible K al final)
        if (/^[\dkK]+$/.test(val.replace(/-/g, ''))) {
            let limpio = val.replace(/[^0-9kK]/g, '').toUpperCase();
            if (limpio.length <= 1) { input.value = limpio; return; }
            let cuerpo = limpio.slice(0, -1);
            let dv = limpio.slice(-1);
            cuerpo = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            input.value = cuerpo + '-' + dv;
        }
        // Si no (pasaporte), dejar como el usuario escribe
    }

    function abrirModalCita() {
        formCita.reset();
        currentStep = 1;
        mostrarStep(1);
        modalCita.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModalCita() {
        modalCita.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function mostrarStep(step) {
        stepPanels.forEach(el => el.classList.add('hidden'));
        document.querySelector(`.step-panel[data-step="${step}"]`).classList.remove('hidden');

        stepItems.forEach(el => el.classList.remove('active', 'completed'));
        for (let i = 1; i <= totalSteps; i++) {
            const item = document.querySelector(`.step-item[data-step="${i}"]`);
            if (i < step) item.classList.add('completed');
            else if (i === step) item.classList.add('active');
        }

        btnPrev.classList.toggle('hidden', step === 1);
        btnNext.classList.toggle('hidden', step === totalSteps);
        btnSubmit.classList.toggle('hidden', step !== totalSteps);
        currentStep = step;
    }

    function validarStep(step) {
        const inputs = document.querySelectorAll(`.step-panel[data-step="${step}"] [required]`);
        for (const input of inputs) {
            if (!input.value.trim()) {
                input.classList.add('border-red-400', 'ring-red-200');
                input.focus();
                input.addEventListener('input', function limpiar() {
                    input.classList.remove('border-red-400', 'ring-red-200');
                    input.removeEventListener('input', limpiar);
                }, { once: true });
                const label = document.querySelector(`label[for="${input.id}"]`);
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: label ? `Por favor completa "${label.textContent.replace('*', '').trim()}"` : 'Completa todos los campos obligatorios.',
                    confirmButtonColor: '#0284c7',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        }
        return true;
    }

    btnNext.addEventListener('click', function () {
        if (!validarStep(currentStep)) return;
        mostrarStep(currentStep + 1);
    });

    btnPrev.addEventListener('click', function () {
        mostrarStep(currentStep - 1);
    });

    btnSubmit.addEventListener('click', enviarSolicitud);

    async function enviarSolicitud() {
        if (!validarStep(3)) return;

        const especieSel = document.getElementById('sc-especie');
        const especie = especieSel.value === '__otro__'
            ? document.getElementById('sc-especie-otro').value.trim()
            : especieSel.value;

        const servicioSel = document.getElementById('sc-servicio');
        const servicio = servicioSel.value === '__otro__'
            ? document.getElementById('sc-servicio-otro').value.trim()
            : servicioSel.value;

        const data = {
            nombre_completo: document.getElementById('sc-nombre').value.trim(),
            email: document.getElementById('sc-email').value.trim(),
            rut: document.getElementById('sc-rut').value.trim(),
            telefono: document.getElementById('sc-telefono').value.trim(),
            mascota_nombre: document.getElementById('sc-mascota-nombre').value.trim(),
            mascota_especie: especie,
            mascota_raza: document.getElementById('sc-raza').value.trim(),
            mascota_sexo: document.getElementById('sc-sexo').value,
            servicio: servicio,
            fecha: document.getElementById('sc-fecha').value,
            nota: document.getElementById('sc-nota').value.trim()
        };

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Enviando...';

        try {
            const res = await fetch('api/solicitar_cita.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();

            if (json.ok) {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Solicitud Enviada!',
                    text: json.msg,
                    confirmButtonColor: '#0284c7',
                    confirmButtonText: 'Perfecto'
                });
                cerrarModalCita();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: json.msg || 'Ocurrió un error al enviar la solicitud.',
                    confirmButtonColor: '#0284c7',
                    confirmButtonText: 'OK'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Intenta nuevamente.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'OK'
            });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane mr-1"></i> Enviar Solicitud';
        }
    }

    // Cerrar con Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modalCita.classList.contains('hidden')) {
            cerrarModalCita();
        }
    });
    </script>

    <style>
    .step-item { display: flex; align-items: center; gap: 0.5rem; flex: 1; }
    .step-circle {
        width: 28px; height: 28px; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700;
        background: #e5e7eb; color: #9ca3af; transition: all 0.2s;
        flex-shrink: 0;
    }
    .step-label { font-size: 0.75rem; font-weight: 500; color: #9ca3af; transition: color 0.2s; white-space: nowrap; }
    .step-item.active .step-circle { background: #0284c7; color: #fff; }
    .step-item.active .step-label { color: #0284c7; font-weight: 600; }
    .step-item.completed .step-circle { background: #16a34a; color: #fff; }
    .step-item.completed .step-label { color: #16a34a; }
    .step-line { flex: 1; height: 2px; background: #e5e7eb; margin: 0 0.25rem; }
    .step-item.completed + .step-line { background: #16a34a; }
    @media (max-width: 480px) {
        .step-label { display: none; }
        .step-item { justify-content: center; flex: 0 1 auto; }
    }
    </style>
</body>
</html>
