<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Mensaje de Bienvenida -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-500 text-3xl">check_circle</span>
                    <span class="text-lg font-medium">¡Bienvenido, {{ Auth::user()->name }}! Has iniciado sesión correctamente.</span>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Tarjeta 1 -->
                <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between border-l-4 border-indigo-500">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Usuarios Totales</p>
                        <h3 class="text-2xl font-bold text-gray-800">1,024</h3>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-full">
                        <span class="material-symbols-outlined text-indigo-500 text-3xl">group</span>
                    </div>
                </div>

                <!-- Tarjeta 2 -->
                <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between border-l-4 border-blue-500">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Roles Activos</p>
                        <h3 class="text-2xl font-bold text-gray-800">4</h3>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <span class="material-symbols-outlined text-blue-500 text-3xl">shield_person</span>
                    </div>
                </div>

                <!-- Tarjeta 3 -->
                <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between border-l-4 border-emerald-500">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Reportes</p>
                        <h3 class="text-2xl font-bold text-gray-800">89</h3>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-full">
                        <span class="material-symbols-outlined text-emerald-500 text-3xl">bar_chart</span>
                    </div>
                </div>
            </div>

            <!-- Sección del Mapa -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-500">location_on</span>
                    <h3 class="text-lg font-semibold text-gray-800">Ubicación de Referencia - Jiutepec</h3>
                </div>
                <!-- Contenedor del mapa. z-0 previene que el mapa cubra los menús desplegables de Breeze -->
                <div id="map" class="w-full h-96 z-0"></div>
            </div>

        </div>
    </div>

    <!-- Script de inicialización de Leaflet -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Coordenadas de Jiutepec, Morelos (Latitud, Longitud)
            const jiutepecCoords = [18.8844, -99.1654];
            
            // Inicializar el mapa
            const map = L.map('map').setView(jiutepecCoords, 13);

            // Agregar la capa de mapa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // Agregar un marcador por defecto
            L.marker(jiutepecCoords).addTo(map)
                .bindPopup('<b>Jiutepec, Morelos</b><br>Centro del municipio.')
                .openPopup();
        });
    </script>
</x-app-layout>