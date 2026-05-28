<?php // admin/usuarios/componentes/modal-mascotas-form.php ?>

<!-- z-[60] para quedar encima del modal de listado -->
<div id="modal-form-mascota" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                <h3 id="modal-form-mascota-titulo" class="text-lg font-semibold text-gray-800">Nueva Mascota</h3>
                <button id="btn-cerrar-form-mascota" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="form-mascota" class="p-6 space-y-5 overflow-y-auto max-h-[75vh]">
                <input type="hidden" id="mascota_id" name="id" value="">
                <input type="hidden" id="mascota_cliente_id" name="cliente_id" value="">

                <!-- Avatar especie -->
                <div class="flex justify-center">
                    <div id="mascota-avatar-preview"
                        class="w-20 h-20 rounded-2xl bg-amber-50 border-2 border-amber-100 flex items-center justify-center text-amber-500 p-3">
                        <i data-lucide="paw-print" class="w-10 h-10"></i>
                    </div>
                </div>

                <!-- ── Información básica ──────────────────────────────── -->
                <div>
                    <h4
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i> Información básica
                    </h4>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="m_nombre" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Especie</label>
                            <div class="grid grid-cols-3 gap-2" id="especie-selector">

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Perro" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <svg class="w-7 h-7 text-gray-500" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M3.15452 1.01195C5.11987 1.32041 7.17569 2.2474 8.72607 3.49603C9.75381 3.17407 10.8558 2.99995 12 2.99995C13.1519 2.99995 14.261 3.17641 15.2946 3.5025C16.882 2.27488 18.8427 1.31337 20.8354 1.01339C21.2596 0.95092 21.7008 1.16534 21.8945 1.55273C22.6719 3.38958 22.6983 5.57987 22.2202 7.49248L22.2128 7.52213C22.0847 8.03536 21.9191 8.69868 21.3876 8.92182C21.7827 9.89315 22 10.9466 22 12.0526C22 14.825 20.8618 17.6774 19.8412 20.2348L19.7379 20.4936C19.1182 22.0486 17.7316 23.1196 16.125 23.418L13.8549 23.8397C13.1549 23.9697 12.4562 23.7172 12 23.2082C11.5438 23.7172 10.8452 23.9697 10.1452 23.8397L7.87506 23.418C6.26852 23.1196 4.88189 22.0486 4.26214 20.4936L4.15891 20.2348C3.13833 17.6774 2.00004 14.825 2.00004 12.0526C2.00004 10.9466 2.21737 9.89315 2.6125 8.92182C2.08046 8.69845 1.91916 8.05124 1.7909 7.53658L1.7799 7.49248C1.32311 5.66527 1.23531 3.2968 2.10561 1.55273C2.29827 1.16741 2.72906 0.945855 3.15452 1.01195ZM6.58478 4.44052C5.45516 5.10067 4.47474 5.9652 3.71373 6.98132C3.41572 5.76461 3.41236 4.41153 3.67496 3.18754C4.68842 3.48029 5.68018 3.89536 6.58478 4.44052ZM20.2863 6.98133C19.5303 5.97184 18.5577 5.11195 17.4374 4.45347C18.3364 3.9005 19.3043 3.45749 20.3223 3.17455C20.5884 4.40199 20.5853 5.76068 20.2863 6.98133ZM8.85364 5.56694C9.81678 5.20285 10.8797 4.99995 12 4.99995C13.1204 4.99995 14.1833 5.20285 15.1464 5.56694C18.0554 6.66661 20 9.1982 20 12.0526C20 14.4676 18.9891 16.9876 18.0863 19.238C18.0167 19.4115 17.9478 19.5832 17.8801 19.7531C17.5291 20.6338 16.731 21.2712 15.7597 21.4516L13.4896 21.8733L12.912 20.5896C12.7505 20.2307 12.3935 19.9999 12 19.9999C11.6065 19.9999 11.2496 20.2307 11.0881 20.5896L10.5104 21.8733L8.24033 21.4516C7.26908 21.2712 6.471 20.6338 6.12001 19.7531C6.05237 19.5834 5.98357 19.4119 5.91414 19.2388L5.91381 19.238C5.01102 16.9876 4.00004 14.4676 4.00004 12.0526C4.00004 9.1982 5.94472 6.66661 8.85364 5.56694ZM10.5 15.9999C10.1212 15.9999 9.77497 16.2139 9.60557 16.5527C9.43618 16.8915 9.47274 17.2969 9.7 17.5999L11.2 19.5999C11.3889 19.8517 11.6852 19.9999 12 19.9999C12.3148 19.9999 12.6111 19.8517 12.8 19.5999L14.3 17.5999C14.5273 17.2969 14.5638 16.8915 14.3944 16.5527C14.225 16.2139 13.8788 15.9999 13.5 15.9999H10.5ZM9.62134 11.1212C9.62134 11.9497 8.94977 12.6212 8.12134 12.6212C7.29291 12.6212 6.62134 11.9497 6.62134 11.1212C6.62134 10.2928 7.29291 9.62125 8.12134 9.62125C8.94977 9.62125 9.62134 10.2928 9.62134 11.1212ZM16 12.4999C16.8284 12.4999 17.5 11.8284 17.5 10.9999C17.5 10.1715 16.8284 9.49994 16 9.49994C15.1716 9.49994 14.5 10.1715 14.5 10.9999C14.5 11.8284 15.1716 12.4999 16 12.4999Z"
                                                fill="currentColor" />
                                        </svg>
                                        <span class="text-xs font-medium text-gray-600">Perro</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Gato" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <svg class="w-7 h-7 text-gray-500" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M19.9801 9.0625L20.7301 9.06545V9.0625H19.9801ZM4.01995 9.0625H3.26994L3.26995 9.06545L4.01995 9.0625ZM19.0993 10.6602L18.5268 11.1447L18.6114 11.2447L18.725 11.3101L19.0993 10.6602ZM18.8279 9.39546C18.494 9.15031 18.0246 9.22224 17.7795 9.55611C17.5343 9.88999 17.6063 10.3594 17.9401 10.6045L18.8279 9.39546ZM4.01994 15L3.26994 15V15H4.01994ZM6.05987 10.6045C6.39375 10.3594 6.46568 9.88999 6.22053 9.55612C5.97538 9.22224 5.50598 9.15031 5.1721 9.39546L6.05987 10.6045ZM12 5.65636C11.2279 5.65636 10.7904 5.69743 10.4437 5.74003C10.1041 5.78176 9.93161 5.8125 9.60601 5.8125V7.3125C10.0465 7.3125 10.3308 7.26518 10.6266 7.22883C10.9153 7.19336 11.2918 7.15636 12 7.15636V5.65636ZM12 7.15636C12.7083 7.15636 13.0847 7.19336 13.3734 7.22883C13.6692 7.26518 13.9536 7.3125 14.394 7.3125V5.8125C14.0684 5.8125 13.896 5.78176 13.5563 5.74003C13.2097 5.69743 12.7721 5.65636 12 5.65636V7.15636ZM14.394 7.3125C14.6069 7.3125 14.8057 7.25192 14.9494 7.19867C15.1051 7.14099 15.2662 7.06473 15.4208 6.98509C15.7257 6.82803 16.0797 6.61814 16.4042 6.43125C16.7431 6.23612 17.064 6.0575 17.3512 5.92771C17.6589 5.78868 17.8349 5.75011 17.9053 5.75011V4.25011C17.4968 4.25011 17.0743 4.40685 16.7336 4.56076C16.3725 4.72392 15.9951 4.9359 15.6557 5.13136C15.3019 5.33508 14.9976 5.51578 14.7338 5.65167C14.6041 5.7185 14.5034 5.7643 14.4284 5.79206C14.3415 5.82426 14.3408 5.8125 14.394 5.8125V7.3125ZM17.9053 5.75011C18.2495 5.75011 18.58 5.85266 18.8122 6.0527C19.0237 6.23486 19.2301 6.56231 19.2301 7.18761H20.7301C20.7301 6.18792 20.3778 5.42162 19.7913 4.91628C19.2255 4.42882 18.5186 4.25011 17.9053 4.25011V5.75011ZM19.2301 7.18761V9.0625H20.7301V7.18761H19.2301ZM9.60601 5.8125C9.65925 5.8125 9.65855 5.82426 9.57164 5.79206C9.49668 5.7643 9.39595 5.71849 9.26624 5.65166C9.00249 5.51576 8.69813 5.33504 8.34437 5.13132C8.00493 4.93584 7.62754 4.72384 7.26643 4.56067C6.92577 4.40675 6.5032 4.25 6.09476 4.25V5.75C6.16512 5.75 6.34105 5.78856 6.64878 5.92761C6.93605 6.05741 7.25693 6.23603 7.5958 6.43118C7.92035 6.61808 8.27434 6.82799 8.57919 6.98506C8.73377 7.06471 8.89488 7.14098 9.05059 7.19866C9.19436 7.25191 9.39317 7.3125 9.60601 7.3125V5.8125ZM6.09476 4.25C5.48139 4.25 4.77453 4.42871 4.20872 4.91616C3.62216 5.4215 3.26995 6.18781 3.26995 7.1875H4.76995C4.76995 6.56219 4.97634 6.23475 5.18778 6.05259C5.41998 5.85254 5.75053 5.75 6.09476 5.75V4.25ZM3.26995 7.1875V9.0625H4.76995V7.1875H3.26995ZM12 20.75C13.431 20.75 15.5401 20.4654 17.3209 19.6462C19.1035 18.8262 20.7301 17.3734 20.7301 15H19.2301C19.2301 16.5328 18.2232 17.58 16.694 18.2835C15.1631 18.9877 13.2822 19.25 12 19.25V20.75ZM19.6719 10.1758C19.437 9.89818 19.1575 9.63749 18.8279 9.39546L17.9401 10.6045C18.1808 10.7813 18.3726 10.9625 18.5268 11.1447L19.6719 10.1758ZM19.2301 9.05955C19.2293 9.25778 19.1888 9.67007 19.0916 9.95501C19.0374 10.1139 19.0062 10.1101 19.0627 10.0649C19.1075 10.0289 19.1902 9.98403 19.3002 9.97847C19.4051 9.97317 19.468 10.007 19.4737 10.0103L18.725 11.3101C18.9057 11.4142 19.1272 11.4891 19.3759 11.4766C19.6297 11.4637 19.8412 11.3633 20.0013 11.2349C20.2881 11.0048 20.4331 10.6686 20.5113 10.4392C20.679 9.94758 20.7289 9.35941 20.7301 9.06545L19.2301 9.05955ZM12 19.25C10.7178 19.25 8.83685 18.9877 7.30594 18.2835C5.7768 17.5801 4.76994 16.5328 4.76994 15H3.26994C3.26994 17.3734 4.89649 18.8262 6.67907 19.6462C8.45988 20.4654 10.5689 20.75 12 20.75V19.25ZM4.76994 15C4.76994 14.2119 4.71349 13.5629 4.7889 12.8724C4.85939 12.227 5.04214 11.6541 5.47321 11.1447L4.32811 10.1758C3.64728 10.9804 3.38966 11.8682 3.29777 12.7095C3.2108 13.5058 3.26994 14.3696 3.26994 15L4.76994 15ZM5.47321 11.1447C5.62738 10.9625 5.81916 10.7813 6.05987 10.6045L5.1721 9.39546C4.84248 9.63749 4.56299 9.89818 4.32811 10.1758L5.47321 11.1447ZM3.26995 9.06545C3.27111 9.35941 3.32101 9.94757 3.48871 10.4392C3.56694 10.6686 3.71186 11.0048 3.99873 11.2349C4.15878 11.3633 4.3703 11.4637 4.62412 11.4766C4.87277 11.4891 5.0943 11.4142 5.27501 11.3101L4.52631 10.0103C4.53204 10.007 4.59487 9.97317 4.69976 9.97847C4.80981 9.98403 4.89252 10.0289 4.93734 10.0649C4.99376 10.1101 4.96261 10.1139 4.9084 9.95501C4.81121 9.67007 4.77072 9.25778 4.76994 9.05955L3.26995 9.06545Z"
                                                fill="currentColor" />
                                            <path
                                                d="M12.826 16C12.826 16.1726 12.465 16.3125 12.0196 16.3125C11.5742 16.3125 11.2131 16.1726 11.2131 16C11.2131 15.8274 11.5742 15.6875 12.0196 15.6875C12.465 15.6875 12.826 15.8274 12.826 16Z"
                                                stroke="currentColor" stroke-width="1.5" />
                                            <path
                                                d="M15.5 13.5938C15.5 14.0252 15.2834 14.375 15.0161 14.375C14.7489 14.375 14.5323 14.0252 14.5323 13.5938C14.5323 13.1623 14.7489 12.8125 15.0161 12.8125C15.2834 12.8125 15.5 13.1623 15.5 13.5938Z"
                                                stroke="currentColor" stroke-width="1.5" />
                                            <path
                                                d="M9.5 13.5938C9.5 14.0252 9.28336 14.375 9.01613 14.375C8.74889 14.375 8.53226 14.0252 8.53226 13.5938C8.53226 13.1623 8.74889 12.8125 9.01613 12.8125C9.28336 12.8125 9.5 13.1623 9.5 13.5938Z"
                                                stroke="currentColor" stroke-width="1.5" />
                                            <path d="M22.0004 15.4688C21.5165 15.1562 19.4197 14.375 18.6133 14.375"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                            <path d="M20.3871 17.9688C19.9033 17.6562 18.7742 16.875 17.9678 16.875"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                            <path d="M2 15.4688C2.48387 15.1562 4.58065 14.375 5.3871 14.375"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                            <path d="M3.61279 17.9688C4.09667 17.6562 5.2257 16.875 6.03215 16.875"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                        <span class="text-xs font-medium text-gray-600">Gato</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Ave" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <svg class="w-7 h-7 text-gray-500" viewBox="0 0 400 400" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M191.179 273.824C240.235 297.511 305.516 282.723 327.848 235.07C343.653 201.345 294.142 174.478 268.869 180.597C249.795 185.215 238.443 210.424 226.139 201.065C216.677 165.605 199.9 119.51 135.192 107.37C114.091 103.412 83.5311 110.64 102.336 135.815C116.496 154.766 137.36 163.983 158.442 173.765C164.792 176.714 169.78 183.842 176.581 185.72C178.199 186.166 181.717 185.007 181.525 186.671C181.105 190.238 113.899 155.977 108.125 179.498C103.955 196.484 152.426 206.208 162.693 208.177C163.338 208.3 167.696 208.583 167.631 209.126C167.291 212.044 128.996 205.366 122.548 219.126C113.925 237.519 146.169 239.099 156.097 238.053C164.394 237.176 172.809 236.947 180.889 235.438C181.156 235.389 194.153 233.997 169.23 238.769C147.16 242.995 90.4779 253.756 88.9641 262.487C87.4503 271.218 95.0462 273.682 99.275 281.556C103.504 289.429 106.52 291.001 110.939 295.051C115.357 299.1 142.753 259.14 172.051 268.915M323.418 199.974C348.126 209.727 352.589 199.404 329.977 213.31M291.997 210.007C291.781 209.411 291.563 208.813 291.345 208.215"
                                                stroke="currentColor" stroke-opacity="0.9" stroke-width="16"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <span class="text-xs font-medium text-gray-600">Ave</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Conejo" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <svg class="w-7 h-7 text-gray-500" viewBox="0 0 470.203 470.203"
                                            fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M379.422,401.305h-4.668c23.087-26.458,36.848-59.621,36.848-95.585c0-25.873-7.065-49.815-21.518-72.971c12.401-11.967,20.138-28.739,20.138-47.293c0-32.563-23.967-60.027-55.565-64.957c-5.58-16.418-14.612-31.631-26.294-44.499c7.823-7.057,14.278-16.261,17.174-24.93c6.773-20.274-4.209-42.279-24.483-49.053c-9.821-3.281-20.332-2.541-29.597,2.084c-9.265,4.625-16.174,12.581-19.455,22.403c-1.278,3.827-2.066,8.264-2.366,12.948c-11.182-3.184-22.849-4.828-34.786-4.828c-11.739,0-23.215,1.597-34.227,4.677c-0.832-6.464-2.554-12.583-5.007-17.498c-9.548-19.125-32.873-26.917-51.999-17.368c-9.265,4.625-16.174,12.582-19.454,22.403c-3.28,9.822-2.54,20.333,2.086,29.598c3.314,6.64,9.015,13.337,15.799,18.805c-12.021,13.028-21.311,28.513-27.002,45.258c-31.6,4.93-55.566,32.395-55.566,64.957c0,18.333,7.549,34.929,19.69,46.867C65.7,254.809,58.601,280.077,58.601,305.72c0,35.964,13.761,69.127,36.848,95.585h-5.171c-16.367,0-29.683,13.315-29.683,29.683v9.532c0,16.367,13.315,29.683,29.683,29.683h289.143c16.367,0,29.683-13.316,29.683-29.683v-9.532C409.104,414.62,395.789,401.305,379.422,401.305z M288.126,31.89c1.842-5.515,5.721-9.981,10.923-12.579c5.203-2.597,11.102-3.012,16.617-1.17c11.383,3.803,17.55,16.159,13.746,27.542c-2.072,6.203-7.307,13.307-13.348,18.486c-1.484-1.248-2.988-2.473-4.532-3.652c-7.833-5.981-16.216-10.945-25.003-14.892C286.333,40.526,286.883,35.612,288.126,31.89z M140.286,32.224c1.842-5.515,5.722-9.981,10.924-12.579c10.738-5.361,23.835-0.986,29.195,9.752c2.057,4.119,3.372,9.867,3.701,15.801c-9.131,4.011-17.827,9.126-25.939,15.32c-1.229,0.938-2.438,1.9-3.63,2.882c-5.654-4.169-10.602-9.594-13.08-14.558C138.86,43.64,138.444,37.738,140.286,32.224z M76.478,185.456c0-25.624,20.013-46.99,45.562-48.642c3.577-0.231,6.624-2.682,7.618-6.126c13.433-46.552,56.689-79.064,105.191-79.064c48.504,0,91.761,32.513,105.193,79.064c0.994,3.444,4.041,5.895,7.618,6.126c25.549,1.652,45.561,23.018,45.561,48.642c0,26.886-21.874,48.76-48.76,48.76c-6.098,0-12.067-1.126-17.743-3.345c-3.337-1.304-7.131-0.378-9.49,2.316c-20.808,23.757-50.834,37.383-82.38,37.383c-31.545,0-61.57-13.625-82.38-37.383c-1.646-1.881-3.994-2.9-6.396-2.9c-1.038,0-2.087,0.19-3.094,0.585c-5.675,2.219-11.643,3.344-17.741,3.344C98.352,234.216,76.478,212.342,76.478,185.456z M92.837,242.645c5.202,2.959,10.841,5.229,16.799,6.686v68.087c0,17.03,13.854,30.885,30.885,30.885h12.313c17.03,0,30.886-13.855,30.886-30.885v-23.192c0-4.694-3.806-8.5-8.5-8.5s-8.5,3.806-8.5,8.5v23.192c0,7.656-6.23,13.885-13.886,13.885H140.52c-7.656,0-13.885-6.228-13.885-13.885v-66.226c5.756-0.121,11.423-0.978,16.92-2.577c23.811,24.825,56.765,38.955,91.293,38.955c34.53,0,67.484-14.13,91.294-38.955c5.498,1.599,11.165,2.456,16.922,2.577v66.226c0,7.656-6.229,13.885-13.885,13.885h-12.313c-7.656,0-13.886-6.228-13.886-13.885v-23.192c0-4.694-3.806-8.5-8.5-8.5c-4.694,0-8.5,3.806-8.5,8.5v23.192c0,17.03,13.855,30.885,30.886,30.885h12.313c17.03,0,30.885-13.855,30.885-30.885V249.33c5.775-1.411,11.253-3.583,16.32-6.411c12.232,20.036,18.217,40.655,18.217,62.8c0,76.92-71.552,139.5-159.5,139.5s-159.5-62.58-159.5-139.5C75.601,283.761,81.553,262.088,92.837,242.645z M77.595,440.52v-9.532c0-6.993,5.689-12.683,12.683-12.683h20.661c0.518,0,1.023-0.063,1.52-0.153c17.962,15.409,39.59,27.472,63.6,35.051h-85.78C83.285,453.203,77.595,447.513,77.595,440.52z M392.104,440.52c0,6.994-5.689,12.683-12.683,12.683h-85.277c23.954-7.562,45.539-19.586,63.477-34.945c0.217,0.017,0.432,0.047,0.652,0.047h21.148c6.993,0,12.683,5.689,12.683,12.683V440.52z" />
                                            <path
                                                d="M225.599,247.22h18.5c15.715,0,28.5-12.785,28.5-28.5v-33.5c0-4.694-3.806-8.5-8.5-8.5h-20.751v-27.642h13.422c4.694,0,8.5-3.806,8.5-8.5c0-4.694-3.806-8.5-8.5-8.5h-43.842c-4.694,0-8.5,3.806-8.5,8.5c0,4.694,3.806,8.5,8.5,8.5h13.42v27.642h-20.749c-4.694,0-8.5,3.806-8.5,8.5v33.5C197.099,234.435,209.885,247.22,225.599,247.22z M255.599,193.72v25c0,6.341-5.159,11.5-11.5,11.5h-0.751v-36.5H255.599z M214.099,193.72h12.249v36.5h-0.749c-6.341,0-11.5-5.159-11.5-11.5V193.72z" />
                                        </svg>
                                        <span class="text-xs font-medium text-gray-600">Roedor</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Reptil" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <svg class="w-7 h-7 text-gray-500" viewBox="0 0 511.999 511.999"
                                            fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M444.939,283.019h-2.754c-5.851,0-10.593,4.742-10.593,10.593s4.743,10.593,10.593,10.593h2.754c5.851,0,10.593-4.743,10.593-10.593S450.79,283.019,444.939,283.019z" />
                                            <path
                                                d="M510.698,258.464c-7.012-25.582-24.997-46.691-48.827-57.783c-1.148-19.915-17.707-35.767-37.905-35.767c-17.266,0-31.867,11.587-36.463,27.391c-7.141,0.023-14.393,0.479-21.571,1.372c-43.632,5.437-86.671,15.965-127.919,31.29c-40.229,14.946-78.761,34.477-114.593,58.053H10.593C4.743,283.02,0,287.762,0,293.613s4.743,10.593,10.593,10.593h115.99h95.661v32.286c0,5.851,4.743,10.593,10.593,10.593h16.499c5.851,0,10.593-4.743,10.593-10.593s-4.743-10.593-10.593-10.593h-5.906v-21.693h129.179v32.286c0,5.851,4.742,10.593,10.593,10.593h16.5c5.851,0,10.593-4.743,10.593-10.593s-4.743-10.593-10.593-10.593h-5.907v-21.693h17.091c5.851,0,10.593-4.743,10.593-10.593s-4.742-10.593-10.593-10.593h-27.684h-5.584H232.838h-68.996c26.073-14.933,53.381-27.727,81.551-38.193c7.811-2.903,15.693-5.62,23.629-8.162v13.949c0,5.851,4.743,10.593,10.593,10.593c5.851,0,10.593-4.743,10.593-10.593V230.34c5.945-1.644,11.918-3.18,17.914-4.623v15.49c0,5.851,4.743,10.593,10.593,10.593s10.593-4.743,10.593-10.593v-20.159c6.146-1.225,12.314-2.332,18.498-3.344v16.343c0,5.851,4.742,10.593,10.593,10.593s10.593-4.743,10.593-10.593v-19.395c6.357-0.772,12.773-1.171,19.079-1.171h8.515c5.851,0,10.593-4.743,10.593-10.593c0-9.257,7.531-16.788,16.788-16.788c9.257,0,16.788,7.531,16.788,16.788c0,0.972-0.084,1.933-0.255,2.935c-0.956,5.776,2.966,11.259,8.742,12.215c20.986,7.595,37.034,24.975,42.93,46.488c0.364,1.328,0.549,2.678,0.549,4.008c0,8.242-6.735,14.949-15.015,14.949c-5.851,0-10.593,4.743-10.593,10.593s4.743,10.593,10.593,10.593c19.962,0,36.201-16.211,36.201-36.135C512,264.848,511.563,261.617,510.698,258.464z" />
                                            <path
                                                d="M423.967,205.357c-5.851,0-10.593,4.743-10.593,10.593v9.087c0,5.851,4.742,10.593,10.593,10.593s10.593-4.743,10.593-10.593v-9.087C434.56,210.1,429.818,205.357,423.967,205.357z" />
                                        </svg>
                                        <span class="text-xs font-medium text-gray-600">Reptil</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Otro" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <i data-lucide="help-circle" class="w-7 h-7 text-gray-400"></i>
                                        <span class="text-xs font-medium text-gray-600">Otro</span>
                                    </div>
                                </label>

                            </div><!-- /especie-selector -->

                            <div id="especie-otro-wrapper" class="hidden mt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Especificar especie</label>
                                <input type="text" name="especie_otro" id="m_especie_otro"
                                    placeholder="Ej: Hurón, Cerdo vietnamita, Equino..."
                                    class="w-full border border-brand-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 bg-brand-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Raza</label>
                            <input type="text" name="raza" id="m_raza"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="m_fecha_nac"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                            <select name="sexo" id="m_sexo"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                                <option value="">-- Seleccionar --</option>
                                <option value="Macho">Macho</option>
                                <option value="Hembra">Hembra</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="text" name="color" id="m_color"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>
                </div>

                <!-- ── Datos físicos ───────────────────────────────────── -->
                <div class="border-t border-gray-100 pt-4">
                    <h4
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <i data-lucide="weight" class="w-3.5 h-3.5"></i> Datos físicos
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Peso <span
                                    class="text-gray-400 font-normal">(kg)</span></label>
                            <input type="number" name="peso" id="m_peso" step="0.01" min="0" placeholder="0.00"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" name="esterilizado" id="m_esterilizado" value="1"
                                        class="sr-only peer">
                                    <div
                                        class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-brand-600 transition-colors">
                                    </div>
                                    <div
                                        class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4">
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Esterilizado/a</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ── Identificación ─────────────────────────────────── -->
                <div class="border-t border-gray-100 pt-4">
                    <h4
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <i data-lucide="fingerprint" class="w-3.5 h-3.5"></i> Identificación
                    </h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nº de microchip</label>
                        <input type="text" name="numero_chip" id="m_chip"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>

                <!-- ── Salud ──────────────────────────────────────────── -->
                <div class="border-t border-gray-100 pt-4">
                    <h4
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <i data-lucide="stethoscope" class="w-3.5 h-3.5"></i> Salud
                    </h4>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Última revisión</label>
                            <input type="date" name="ultima_revision" id="m_ultima_revision"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alergias conocidas</label>
                            <input type="text" name="alergias" id="m_alergias" placeholder="Ej: Polen, Pollo"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea name="observaciones" id="m_observaciones" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none"></textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Notas internas
                                <span class="text-xs font-normal text-gray-400 ml-1">Solo visible para el equipo</span>
                            </label>
                            <textarea name="notas_internas" id="m_notas_internas" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none bg-yellow-50"></textarea>
                        </div>
                    </div>
                </div>

                <!-- ── Vacunas ─────────────────────────────────────────── -->
                <div class="border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wide flex items-center gap-2">
                            <i data-lucide="syringe" class="w-3.5 h-3.5"></i> Vacunas
                        </h4>
                        <button type="button" id="btn-agregar-vacuna"
                            class="text-xs font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Agregar
                        </button>
                    </div>
                    <div id="lista-vacunas" class="space-y-2"></div>
                    <p id="vacunas-vacio" class="text-xs text-gray-400 text-center py-2">Sin vacunas registradas</p>
                </div>

                <div id="mascota-error"
                    class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3"></div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" id="btn-cancelar-form-mascota"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-submit-mascota"
                        class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition">
                        Guardar Mascota
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>