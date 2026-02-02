<div id="sidebar-overlay" class="fixed inset-0 bg-gray-900/50 z-40 hidden md:hidden"></div>

<aside id="sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 flex flex-col z-50 transform -translate-x-full transition-transform duration-300 md:translate-x-0 md:static md:h-full">

    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 flex-shrink-0">
        <div class="flex items-center gap-2">
            <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                </path>
            </svg>
            <span class="text-xl font-bold text-gray-800">VetWeb</span>
        </div>
        <button id="btn-close-sidebar" class="md:hidden text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                </path>
            </svg>
        </button>
    </div>

    <?php include 'nav.php'; ?>

    <div class="p-4 border-t border-gray-200 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold">
                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-700 truncate">
                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>
                </p>
                <p class="text-xs text-gray-500">Conectado</p>
            </div>
            <a href="../logout.php" class="text-gray-400 hover:text-red-500 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
            </a>
        </div>
    </div>
</aside>