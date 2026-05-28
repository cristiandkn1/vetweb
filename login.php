<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetWeb - Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 900: '#0c4a6e' }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="relative min-h-screen w-full flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="assets/img/hero-bg.png" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-brand-900/80 to-brand-600/40 mix-blend-multiply"></div>
        </div>

        <div class="relative z-10 w-full max-w-md mx-auto px-4">
            <div class="text-center mb-8">
                <a href="index.php" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors mb-6">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Volver al inicio</span>
                </a>
                <div class="flex items-center justify-center gap-2 mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span class="text-2xl font-bold text-white tracking-tight">VetWeb</span>
                </div>
            </div>

            <div class="glass-panel p-8 rounded-2xl shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Iniciar Sesión</h2>
                    <p class="text-gray-500 mt-2">Accede a tu portal de VetWeb</p>
                </div>

                <form class="space-y-5" action="login/auth.php" method="POST">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" id="email" name="email" autocomplete="email"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-shadow"
                                placeholder="ejemplo@correo.com" required autofocus>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" autocomplete="current-password"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-shadow"
                                placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember_me" type="checkbox"
                                class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-600">Recordarme</label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-brand-600 hover:text-brand-500 hover:underline">¿Olvidaste tu contraseña?</a>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>
                        Ingresar
                    </button>


                </form>
            </div>
        </div>
    </div>

</body>
</html>
