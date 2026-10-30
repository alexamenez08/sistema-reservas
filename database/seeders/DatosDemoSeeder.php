<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Anticipo;
use App\Models\AsignacionStaff;
use App\Models\CatalogoPlatillo;
use App\Models\CategoriaMobiliario;
use App\Models\Cliente;
use App\Models\Evento;
use App\Models\Locacion;
use App\Models\Mobiliario;
use App\Models\Paquete;
use App\Models\Reservacion;
use App\Models\User;
use App\Services\CotizadorReservacion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Datos de demostración: staff, clientes, mobiliario, platillos, locaciones reales de Morelos,
 * paquetes y reservaciones de agosto a diciembre de 2026 (con anticipos, eventos, actividades y staff).
 *
 * Uso:   php artisan db:seed --class=DatosDemoSeeder
 *
 *  - No define IDs: todo se inserta con Eloquent, así que no choca con los registros que ya tienes.
 *  - Catálogos (mobiliario, platillos, locaciones, paquetes, clientes, usuarios) se buscan por nombre
 *    o correo antes de crearse, así que no se duplican si ya existen.
 *  - Las reservaciones se cargan una sola vez: si ya corriste este seeder, no vuelve a crearlas.
 *  - Todo corre en una transacción: si algo falla, no queda nada a medias.
 *
 * IMPORTANTE: los nombres de los lugares son reales, pero capacidades, precios y coordenadas son
 * aproximados / de ejemplo. Revisa dirección y coordenadas antes de usarlos como datos reales.
 */
class DatosDemoSeeder extends Seeder
{
    private const ANIO = 2026;

    /** @var array<string, CategoriaMobiliario> */
    private array $categorias = [];
    /** @var array<string, Mobiliario> */
    private array $mobiliario = [];
    /** @var array<string, CatalogoPlatillo> */
    private array $platillos = [];
    /** @var array<string, Locacion> */
    private array $locaciones = [];
    /** @var array<int, array{modelo: Paquete, tipo: string, peso: int}> */
    private array $paquetes = [];
    /** @var array<int, int> */
    private array $clienteIds = [];
    /** @var array<int, User> */
    private array $coordinadores = [];
    /** @var array<int, User> */
    private array $personal = [];
    /** @var array<string, array<int, bool>> fecha => [id_usuario => true] */
    private array $ocupados = [];
    private int $adminId = 0;
    private int $clientesNuevos = 0;

    public function run(): void
    {
        mt_srand(2026); // mismos datos cada vez que se corre en una base vacía

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function () {
            $this->usuarios();
            $this->mobiliario();
            $this->platillos();
            $this->clientes();
            $this->locaciones();
            $this->paquetes();

            if ($this->clientesNuevos === 0) {
                $this->command?->warn('Los clientes de demostración ya existían: no se crearon reservaciones nuevas para no duplicarlas.');

                return;
            }

            $this->reservaciones();
        });
    }

    /* ===================================================================
     *  Usuarios (coordinadores y staff)
     * =================================================================== */

    private function usuarios(): void
    {
        foreach (['administrador', 'coordinador', 'staff'] as $rol) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        }

        $this->adminId = (int) (User::role('administrador')->value('id') ?? User::query()->value('id'));

        $coordinadores = [
            ['Daniela', 'Ocampo Villalobos', 'daniela.ocampo@sotytech.com', '777 310 4421'],
            ['Ricardo', 'Salgado Meza', 'ricardo.salgado@sotytech.com', '777 315 9082'],
        ];
        $personal = [
            ['Luis Ángel', 'Barrera Domínguez', 'luis.barrera@sotytech.com', '777 244 1190'],
            ['Karen', 'Figueroa Lara', 'karen.figueroa@sotytech.com', '777 261 7745'],
            ['Jesús', 'Tapia Rendón', 'jesus.tapia@sotytech.com', '735 352 0618'],
            ['Brenda', 'Ayala Cuevas', 'brenda.ayala@sotytech.com', '777 189 3354'],
            ['Omar', 'Castañeda Vélez', 'omar.castaneda@sotytech.com', '777 402 7716'],
            ['Paola', 'Nájera Solís', 'paola.najera@sotytech.com', '777 233 8801'],
            ['Iván', 'Mendoza Arce', 'ivan.mendoza@sotytech.com', '734 342 5527'],
            ['Fernanda', 'Rosales Pineda', 'fernanda.rosales@sotytech.com', '777 276 6093'],
            ['Héctor', 'Bahena Ortega', 'hector.bahena@sotytech.com', '777 118 4472'],
            ['Alejandra', 'Quintero Gil', 'alejandra.quintero@sotytech.com', '739 395 2260'],
            ['Diego', 'Lagunas Rivas', 'diego.lagunas@sotytech.com', '777 357 1938'],
            ['Mónica', 'Espinoza Tello', 'monica.espinoza@sotytech.com', '777 298 5516'],
        ];

        foreach ($coordinadores as [$nombre, $apellidos, $correo, $tel]) {
            $this->coordinadores[] = $this->crearUsuario($nombre, $apellidos, $correo, $tel, 'coordinador');
        }
        foreach ($personal as [$nombre, $apellidos, $correo, $tel]) {
            $this->personal[] = $this->crearUsuario($nombre, $apellidos, $correo, $tel, 'staff');
        }
    }

    private function crearUsuario(string $nombre, string $apellidos, string $correo, string $tel, string $rol): User
    {
        $usuario = User::firstOrCreate(
            ['email' => $correo],
            [
                'name'      => $nombre,
                'last_name' => $apellidos,
                'phone'     => $tel,
                'password'  => 'password123', // el modelo la cifra (cast "hashed")
                'is_active' => true,
            ]
        );

        if (! $usuario->hasRole($rol)) {
            $usuario->assignRole($rol);
        }

        return $usuario;
    }

    /* ===================================================================
     *  Mobiliario
     * =================================================================== */

    private function mobiliario(): void
    {
        $catalogo = [
            'Mesas' => [
                ['Mesa redonda 10 personas', 'Mesa redonda de madera plegable, diámetro 1.50 m.', 60, 180, 1800],
                ['Mesa rectangular 8 personas', 'Mesa rectangular de 2.40 x 0.80 m.', 40, 150, 1500],
                ['Mesa para pastel y regalos', 'Mesa cuadrada de 1.00 m con cubierta lisa.', 10, 120, 1200],
                ['Mesa infantil', 'Mesa baja de colores para 6 niños.', 15, 90, 800],
                ['Mesa alta de coctel', 'Mesa alta tipo periquera para recepción.', 30, 140, 1600],
            ],
            'Sillas' => [
                ['Silla Tiffany blanca', 'Silla Tiffany de resina color blanco.', 400, 25, 450],
                ['Silla Crossback de madera', 'Silla de madera natural con respaldo cruzado, estilo campestre.', 300, 35, 600],
                ['Silla plegable acojinada', 'Silla plegable con asiento acojinado.', 300, 12, 250],
                ['Silla infantil', 'Silla de plástico de colores para niños.', 60, 10, 180],
            ],
            'Mantelería' => [
                ['Mantel redondo blanco', 'Mantel de tela para mesa redonda de 10 personas.', 80, 60, 350],
                ['Camino de mesa', 'Camino de tela decorativo, varios colores.', 80, 25, 120],
                ['Funda para silla con moño', 'Funda blanca con moño para silla Tiffany.', 400, 12, 90],
            ],
            'Iluminación' => [
                ['Serie de luces cálidas 20 m', 'Cordón de focos cálidos para jardín y techos.', 30, 300, 1500],
                ['Candil de cristal', 'Candil de cristal para carpa o salón.', 8, 1200, 9000],
                ['Reflector LED decorativo', 'Reflector LED de colores para ambientación.', 24, 250, 3200],
                ['Farol de vela', 'Farol decorativo con vela LED.', 80, 40, 220],
            ],
            'Audio y video' => [
                ['Sistema de audio profesional', 'Bocinas, consola y cableado para 300 personas.', 6, 3500, 28000],
                ['Micrófono inalámbrico', 'Micrófono de mano inalámbrico.', 10, 300, 2500],
                ['Pantalla LED 3x2 m', 'Pantalla LED para presentaciones y video.', 2, 5500, 60000],
                ['Pista de baile iluminada 4x4', 'Pista modular con luces LED, 4 x 4 m.', 3, 6500, 48000],
            ],
            'Decoración' => [
                ['Arco floral', 'Arco para ceremonia con flores naturales de temporada.', 6, 2800, 9000],
                ['Centro de mesa floral', 'Arreglo floral para mesa redonda.', 80, 350, 700],
                ['Letras gigantes iluminadas', 'Letras de 1.20 m con luces integradas.', 4, 1800, 9500],
                ['Backdrop de flores', 'Fondo decorativo para fotografías.', 8, 1500, 5000],
            ],
            'Lounge' => [
                ['Sala lounge blanca', 'Sala de 3 piezas en color blanco para zona de descanso.', 10, 2200, 15000],
                ['Puff', 'Puff acojinado de colores.', 40, 150, 900],
            ],
            'Carpas y estructuras' => [
                ['Carpa 10x10 m', 'Carpa blanca con estructura de aluminio.', 8, 5200, 40000],
            ],
            'Vajilla y cristalería' => [
                ['Vajilla completa por persona', 'Plato base, plato fuerte, plato postre y cubiertos.', 400, 55, 480],
                ['Copa de agua y vino', 'Juego de copa de agua y copa de vino.', 800, 8, 90],
            ],
        ];

        foreach ($catalogo as $categoria => $articulos) {
            $this->categorias[$categoria] = CategoriaMobiliario::firstOrCreate(
                ['nombre' => $categoria],
                ['descripcion' => 'Categoría ' . Str::lower($categoria)]
            );

            foreach ($articulos as [$nombre, $descripcion, $stock, $renta, $reposicion]) {
                $this->mobiliario[$nombre] = Mobiliario::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'descripcion'           => $descripcion,
                        'stock_total'           => $stock,
                        'stock_disponible'      => $stock,
                        'precio_renta_unitario' => $renta,
                        'costo_reposicion'      => $reposicion,
                        'activo'                => true,
                        'id_categoriamob'       => $this->categorias[$categoria]->id,
                    ]
                );
            }
        }
    }

    /* ===================================================================
     *  Platillos
     * =================================================================== */

    private function platillos(): void
    {
        $catalogo = [
            'entrada' => [
                ['Crema de flor de calabaza', 'Crema suave con flor de calabaza y queso fresco.', 38, 95],
                ['Tostadas de ceviche de camarón', 'Tostadas con ceviche de camarón, aguacate y limón.', 52, 130],
                ['Ensalada de nopal y queso panela', 'Nopales asados, jitomate, cebolla y queso panela.', 30, 80],
                ['Sopa azteca', 'Caldo de jitomate con tiras de tortilla, aguacate y chicharrón.', 34, 85],
                ['Tlacoyitos surtidos', 'Tlacoyitos de haba, frijol y requesón con salsa.', 28, 75],
                ['Carpaccio de res con arúgula', 'Láminas de res con arúgula, parmesano y aceite de oliva.', 65, 160],
                ['Crema de elote con rajas', 'Crema de elote tierno con rajas poblanas.', 32, 82],
                ['Coctel de camarones', 'Camarones en salsa coctelera con aguacate.', 70, 170],
            ],
            'plato fuerte' => [
                ['Cecina de Yecapixtla con guacamole', 'Cecina estilo Yecapixtla con guacamole, frijoles y tortillas.', 110, 260],
                ['Mole verde con pollo', 'Pierna y muslo en mole verde de pepita con arroz rojo.', 85, 210],
                ['Mole poblano con pollo', 'Pollo en mole poblano con arroz y ajonjolí.', 90, 220],
                ['Filete de res en salsa de chipotle', 'Filete de res con salsa de chipotle y puré de papa.', 150, 360],
                ['Pechuga rellena de espinacas y queso', 'Pechuga rellena con salsa de queso y vegetales al vapor.', 95, 230],
                ['Pollo en pipián verde', 'Pollo en pipián verde con arroz blanco.', 80, 200],
                ['Cochinita pibil con arroz y frijol', 'Cochinita pibil con cebolla morada encurtida.', 75, 190],
                ['Lasaña de verduras', 'Lasaña de calabacita, espinaca y queso gratinado.', 70, 175],
                ['Salmón al limón con vegetales', 'Filete de salmón al limón con vegetales salteados.', 160, 380],
                ['Costillas de cerdo en salsa BBQ', 'Costillas horneadas con salsa BBQ y papas gajo.', 120, 290],
            ],
            'postre' => [
                ['Pastel de tres leches', 'Pastel clásico de tres leches con fresas.', 28, 70],
                ['Pay de queso con frutos rojos', 'Pay de queso horneado con salsa de frutos rojos.', 32, 80],
                ['Flan napolitano', 'Flan casero con caramelo.', 20, 55],
                ['Mousse de chocolate', 'Mousse de chocolate semiamargo.', 26, 68],
                ['Nieve artesanal de sabores', 'Nieves artesanales de limón, mamey y rosas.', 22, 60],
                ['Churros con cajeta', 'Churros recién hechos con cajeta.', 24, 62],
            ],
            'bebida' => [
                ['Agua fresca de jamaica', 'Agua de jamaica natural, servicio ilimitado.', 6, 18],
                ['Agua de horchata', 'Agua de horchata con canela.', 7, 20],
                ['Limonada con chía', 'Limonada natural con semillas de chía.', 7, 20],
                ['Café y té', 'Servicio de café americano y tés.', 9, 28],
                ['Barra de refrescos y agua', 'Refrescos, agua natural y mineral.', 14, 40],
                ['Barra libre nacional (4 horas)', 'Tequila, ron, whisky, vodka, cerveza y mezcladores.', 150, 320],
                ['Coctel margarita de bienvenida', 'Margarita en copa para el brindis de recepción.', 35, 90],
            ],
            'menu infantil' => [
                ['Nuggets de pollo con papas', 'Nuggets de pechuga con papas a la francesa.', 40, 95],
                ['Mini hamburguesas', 'Dos mini hamburguesas con papas.', 45, 110],
                ['Espagueti con salsa de tomate', 'Espagueti con salsa suave de tomate y queso.', 30, 80],
                ['Quesadillas de jamón y queso', 'Quesadillas pequeñas con fruta.', 25, 70],
                ['Gelatina y fruta', 'Gelatina de colores con fruta picada.', 14, 40],
            ],
        ];

        foreach ($catalogo as $categoria => $items) {
            foreach ($items as [$nombre, $descripcion, $costo, $precio]) {
                $this->platillos[$nombre] = CatalogoPlatillo::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'categoria'         => $categoria,
                        'descripcion'       => $descripcion,
                        'costo_preparacion' => $costo,
                        'precio_venta'      => $precio,
                    ]
                );
            }
        }
    }

    /* ===================================================================
     *  Clientes
     * =================================================================== */

    private function clientes(): void
    {
        $personas = [
            ['Mariana', 'Rojas', 'Villegas'], ['Carlos', 'Jiménez', 'Bahena'], ['Gabriela', 'Salazar', 'Ortiz'],
            ['Jorge', 'Hernández', 'Cuevas'], ['Valeria', 'Nájera', 'Torres'], ['Eduardo', 'Mendoza', 'Ramírez'],
            ['Sofía', 'Aguilar', 'Domínguez'], ['Miguel Ángel', 'Pacheco', 'Luna'], ['Andrea', 'Calderón', 'Soto'],
            ['Fernando', 'Montes', 'Ibarra'], ['Daniela', 'Guerrero', 'Acosta'], ['Alejandro', 'Barrios', 'Mejía'],
            ['Paulina', 'Ocampo', 'Rivera'], ['Roberto', 'Villanueva', 'Cruz'], ['Natalia', 'Espinosa', 'Paredes'],
            ['Javier', 'Lara', 'Montoya'], ['Camila', 'Reyes', 'Sandoval'], ['Héctor', 'Domínguez', 'Peña'],
            ['Lucía', 'Fuentes', 'Arroyo'], ['Ricardo', 'Zamora', 'Beltrán'], ['Ximena', 'Cabrera', 'Orozco'],
            ['Arturo', 'Maldonado', 'Servín'], ['Isabel', 'Tapia', 'Cervantes'], ['Pablo', 'Rangel', 'Mora'],
            ['Karla', 'Estrada', 'Valdez'], ['Rodrigo', 'Palacios', 'Gutiérrez'], ['Verónica', 'Santana', 'Olvera'],
            ['Luis', 'Carrillo', 'Meza'], ['Patricia', 'Figueroa', 'Duarte'], ['Emiliano', 'Solís', 'Ávila'],
            ['Adriana', 'Quiroz', 'Navarro'], ['Raúl', 'Bustamante', 'León'], ['Mónica', 'Galindo', 'Pineda'],
            ['Sergio', 'Alarcón', 'Tello'], ['Elena', 'Cortés', 'Mondragón'], ['Óscar', 'Vargas', 'Nava'],
        ];
        $ladas = ['777', '777', '777', '777', '735', '734', '739'];

        foreach ($personas as [$nombre, $paterno, $materno]) {
            $correo = Str::slug($nombre . ' ' . $paterno, '.') . '@example.com';

            $cliente = Cliente::firstOrCreate(
                ['correo' => $correo],
                [
                    'nombre' => $nombre,
                    'apepat' => $paterno,
                    'apemat' => $materno,
                    'tel'    => $ladas[array_rand($ladas)] . ' ' . mt_rand(200, 499) . ' ' . mt_rand(1000, 9999),
                ]
            );

            if ($cliente->wasRecentlyCreated) {
                $this->clientesNuevos++;
            }
            $this->clienteIds[] = $cliente->id;
        }
    }

    /* ===================================================================
     *  Locaciones (lugares reales en Morelos)
     * =================================================================== */

    private function locaciones(): void
    {
        $lugares = [
            [
                'nombre' => 'Hacienda de Chiconcuac',
                'descripcion' => 'Hacienda del siglo XVI con capilla de piedra, arcos y jardines. Ideal para ceremonia y recepción.',
                'capacidad' => 300, 'precio_base' => 85000,
                'calle' => 'Calzada de los Pinos', 'numero_exterior' => '1', 'colonia' => 'Centro, Xochitepec',
                'codigo_postal' => '62790', 'latitud' => 18.7800000, 'longitud' => -99.2350000,
            ],
            [
                'nombre' => 'Hotel & Spa Hacienda de Cortés',
                'descripcion' => 'Antigua hacienda cañera de 1530 convertida en hotel, con jardines y salones para eventos sociales.',
                'capacidad' => 250, 'precio_base' => 95000,
                'calle' => 'Plaza Kennedy', 'numero_exterior' => '90', 'colonia' => 'Atlacomulco, Jiutepec',
                'codigo_postal' => '62660', 'latitud' => 18.8870000, 'longitud' => -99.1755000,
            ],
            [
                'nombre' => 'Hotel Las Mañanitas',
                'descripcion' => 'Hotel boutique en el centro de Cuernavaca con jardín, terraza y vista para bodas íntimas y cenas privadas.',
                'capacidad' => 120, 'precio_base' => 60000,
                'calle' => 'Ricardo Linares', 'numero_exterior' => '107', 'colonia' => 'Centro, Cuernavaca',
                'codigo_postal' => '62000', 'latitud' => 18.9300000, 'longitud' => -99.2325000,
            ],
            [
                'nombre' => 'Hacienda Cocoyoc',
                'descripcion' => 'Gran hacienda con acueducto histórico, jardines amplios y salones para eventos grandes.',
                'capacidad' => 400, 'precio_base' => 120000,
                'calle' => 'Hacienda de Cocoyoc', 'numero_exterior' => 'S/N', 'colonia' => 'Cocoyoc, Yautepec',
                'codigo_postal' => '62738', 'latitud' => 18.8860000, 'longitud' => -98.9870000,
            ],
            [
                'nombre' => 'Hacienda San Gabriel de las Palmas',
                'descripcion' => 'Hacienda del sur de Morelos rodeada de jardines y áreas verdes, con clima cálido todo el año.',
                'capacidad' => 250, 'precio_base' => 78000,
                'calle' => 'Hacienda San Gabriel de las Palmas', 'numero_exterior' => 'S/N', 'colonia' => 'San Gabriel de las Palmas, Amacuzac',
                'codigo_postal' => '62640', 'latitud' => 18.6030000, 'longitud' => -99.3680000,
            ],
            [
                'nombre' => 'Hacienda Vista Hermosa',
                'descripcion' => 'Hacienda campestre en Tlaltizapán con jardines, capilla y espacios abiertos para bodas y fiestas.',
                'capacidad' => 200, 'precio_base' => 65000,
                'calle' => 'Hacienda Vista Hermosa', 'numero_exterior' => 'S/N', 'colonia' => 'Vista Hermosa, Tlaltizapán',
                'codigo_postal' => '62770', 'latitud' => 18.7340000, 'longitud' => -99.1100000,
            ],
            [
                'nombre' => 'Posada del Tepozteco',
                'descripcion' => 'Hotel con terraza y vista al cerro del Tepozteco, para bodas y retiros pequeños en Tepoztlán.',
                'capacidad' => 100, 'precio_base' => 48000,
                'calle' => 'Paraíso', 'numero_exterior' => '3', 'colonia' => 'Santísima, Tepoztlán',
                'codigo_postal' => '62520', 'latitud' => 18.9840000, 'longitud' => -99.0970000,
            ],
        ];

        foreach ($lugares as $lugar) {
            $this->locaciones[$lugar['nombre']] = Locacion::firstOrCreate(
                ['nombre' => $lugar['nombre']],
                $lugar + ['entidad_feder' => 'Morelos', 'activo' => true]
            );
        }
    }

    /* ===================================================================
     *  Paquetes
     * =================================================================== */

    private function paquetes(): void
    {
        // nombre, locación, tipo, capacidad, precio, extra por invitado, menú, silla, peso (frecuencia)
        $definiciones = [
            ['Boda Clásica Chiconcuac', 'Hacienda de Chiconcuac', 'boda', 150, 168000, 950, 'clasico', 'Silla Tiffany blanca', 10],
            ['XV Años en la Hacienda', 'Hacienda de Chiconcuac', 'xv', 200, 142000, 780, 'morelense', 'Silla Tiffany blanca', 8],
            ['Boda Premium Hacienda de Cortés', 'Hotel & Spa Hacienda de Cortés', 'boda', 180, 215000, 1150, 'gala', 'Silla Tiffany blanca', 8],
            ['Evento Corporativo Cortés', 'Hotel & Spa Hacienda de Cortés', 'corporativo', 120, 96000, 620, 'corporativo', 'Silla plegable acojinada', 5],
            ['Boda Íntima en el Jardín', 'Hotel Las Mañanitas', 'boda', 80, 118000, 1200, 'gala', 'Silla Crossback de madera', 6],
            ['Cena de Aniversario', 'Hotel Las Mañanitas', 'social', 40, 42000, 900, 'clasico', 'Silla Crossback de madera', 3],
            ['Boda Gran Hacienda Cocoyoc', 'Hacienda Cocoyoc', 'boda', 300, 285000, 890, 'morelense', 'Silla Tiffany blanca', 7],
            ['Graduación Cocoyoc', 'Hacienda Cocoyoc', 'graduacion', 250, 138000, 520, 'social', 'Silla plegable acojinada', 4],
            ['Fiesta de XV Años San Gabriel', 'Hacienda San Gabriel de las Palmas', 'xv', 180, 125000, 690, 'morelense', 'Silla Tiffany blanca', 6],
            ['Bautizo y Primera Comunión', 'Hacienda San Gabriel de las Palmas', 'social', 100, 58000, 480, 'social', 'Silla plegable acojinada', 5],
            ['Boda Campestre Vista Hermosa', 'Hacienda Vista Hermosa', 'boda', 140, 132000, 780, 'clasico', 'Silla Crossback de madera', 7],
            ['Fiesta Infantil Vista Hermosa', 'Hacienda Vista Hermosa', 'infantil', 80, 36000, 350, 'infantil', 'Silla plegable acojinada', 5],
            ['Boda Mística Tepoztlán', 'Posada del Tepozteco', 'boda', 80, 98000, 1100, 'morelense', 'Silla Crossback de madera', 5],
            ['Retiro Corporativo Tepoztlán', 'Posada del Tepozteco', 'corporativo', 50, 38000, 600, 'corporativo', 'Silla plegable acojinada', 3],
        ];

        foreach ($definiciones as [$nombre, $lugar, $tipo, $capacidad, $precio, $extra, $menu, $silla, $peso]) {
            $locacion = $this->locaciones[$lugar];

            $paquete = Paquete::firstOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion'          => $this->descripcionPaquete($tipo, $lugar, $capacidad),
                    'capacidad'            => min($capacidad, $locacion->capacidad),
                    'precio_venta'         => $precio,
                    'precio_persona_extra' => $extra,
                    'activo'               => true,
                    'id_locacion'          => $locacion->id,
                ]
            );

            // Solo se arman los componentes si el paquete es nuevo (no se pisan los que ya editaste)
            if ($paquete->wasRecentlyCreated) {
                $sync = [];
                foreach ($this->contenidoMobiliario($tipo, $paquete->capacidad, $silla) as $articulo => $cantidad) {
                    $sync[$this->articulo($articulo)->id] = ['cantidad' => $cantidad];
                }
                $paquete->mobiliario()->sync($sync);

                $paquete->platillos()->sync(
                    collect($this->menu($menu))->map(fn ($p) => $this->platillo($p)->id)->all()
                );

                foreach ($this->servicios($tipo) as [$concepto, $costo]) {
                    $paquete->servicios()->create(['concepto' => $concepto, 'precio' => $costo]);
                }
            }

            $this->paquetes[] = ['modelo' => $paquete, 'tipo' => $tipo, 'peso' => $peso];
        }
    }

    private function descripcionPaquete(string $tipo, string $lugar, int $capacidad): string
    {
        $que = match ($tipo) {
            'boda'        => 'Paquete de boda',
            'xv'          => 'Paquete de XV años',
            'social'      => 'Paquete para evento social',
            'graduacion'  => 'Paquete de graduación',
            'corporativo' => 'Paquete corporativo',
            'infantil'    => 'Paquete de fiesta infantil',
        };

        return "{$que} en {$lugar} para hasta {$capacidad} invitados. Incluye renta del lugar, mobiliario, menú y servicios.";
    }

    private function articulo(string $nombre): Mobiliario
    {
        return $this->mobiliario[$nombre] ?? throw new \RuntimeException("Falta el mobiliario \"{$nombre}\" en el catálogo del seeder.");
    }

    private function platillo(string $nombre): CatalogoPlatillo
    {
        return $this->platillos[$nombre] ?? throw new \RuntimeException("Falta el platillo \"{$nombre}\" en el catálogo del seeder.");
    }

    /** @return array<string, int> artículo => cantidad */
    private function contenidoMobiliario(string $tipo, int $capacidad, string $silla): array
    {
        $mesas = (int) ceil($capacidad / 10);

        $base = [
            'Vajilla completa por persona' => $capacidad,
            'Copa de agua y vino'          => $capacidad,
            'Sistema de audio profesional' => 1,
            'Micrófono inalámbrico'        => 2,
        ];

        return match ($tipo) {
            'boda' => $base + [
                'Mesa redonda 10 personas'      => $mesas,
                $silla                          => $capacidad,
                'Mantel redondo blanco'         => $mesas,
                'Camino de mesa'                => $mesas,
                'Centro de mesa floral'         => $mesas,
                'Mesa para pastel y regalos'    => 2,
                'Serie de luces cálidas 20 m'   => (int) ceil($capacidad / 25) + 2,
                'Arco floral'                   => 1,
                'Sala lounge blanca'            => 2,
                'Pista de baile iluminada 4x4'  => 1,
                'Candil de cristal'             => 2,
            ] + ($silla === 'Silla Tiffany blanca' ? ['Funda para silla con moño' => $capacidad] : []),

            'xv' => $base + [
                'Mesa redonda 10 personas'      => $mesas,
                $silla                          => $capacidad,
                'Mantel redondo blanco'         => $mesas,
                'Centro de mesa floral'         => $mesas,
                'Mesa para pastel y regalos'    => 2,
                'Serie de luces cálidas 20 m'   => (int) ceil($capacidad / 25) + 2,
                'Letras gigantes iluminadas'    => 1,
                'Backdrop de flores'            => 1,
                'Pista de baile iluminada 4x4'  => 1,
                'Puff'                          => 10,
            ],

            'social', 'graduacion' => $base + [
                'Mesa redonda 10 personas'      => $mesas,
                $silla                          => $capacidad,
                'Mantel redondo blanco'         => $mesas,
                'Camino de mesa'                => $mesas,
                'Mesa para pastel y regalos'    => 1,
                'Serie de luces cálidas 20 m'   => (int) ceil($capacidad / 25) + 1,
                'Backdrop de flores'            => 1,
            ] + ($tipo === 'graduacion' ? ['Pantalla LED 3x2 m' => 1] : []),

            'corporativo' => $base + [
                'Mesa rectangular 8 personas'   => (int) ceil($capacidad / 8),
                $silla                          => $capacidad,
                'Pantalla LED 3x2 m'            => 1,
                'Mesa alta de coctel'           => 6,
                'Micrófono inalámbrico'         => 3,
            ],

            'infantil' => $base + [
                'Mesa infantil'                 => (int) ceil(($capacidad * 0.5) / 6),
                'Silla infantil'                => (int) ceil($capacidad * 0.5),
                'Mesa redonda 10 personas'      => (int) ceil(($capacidad * 0.5) / 10),
                $silla                          => (int) ceil($capacidad * 0.5),
                'Mantel redondo blanco'         => (int) ceil(($capacidad * 0.5) / 10),
                'Backdrop de flores'            => 1,
                'Puff'                          => 10,
            ],
        };
    }

    /** @return array<int, string> */
    private function menu(string $clave): array
    {
        return match ($clave) {
            'gala' => [
                'Carpaccio de res con arúgula', 'Tostadas de ceviche de camarón',
                'Filete de res en salsa de chipotle', 'Salmón al limón con vegetales',
                'Mousse de chocolate', 'Pastel de tres leches',
                'Agua fresca de jamaica', 'Coctel margarita de bienvenida',
                'Barra libre nacional (4 horas)', 'Café y té',
            ],
            'morelense' => [
                'Crema de flor de calabaza', 'Tlacoyitos surtidos',
                'Cecina de Yecapixtla con guacamole', 'Mole verde con pollo',
                'Nieve artesanal de sabores', 'Pastel de tres leches',
                'Agua de horchata', 'Limonada con chía', 'Coctel margarita de bienvenida', 'Café y té',
            ],
            'clasico' => [
                'Crema de elote con rajas', 'Ensalada de nopal y queso panela',
                'Pechuga rellena de espinacas y queso', 'Mole poblano con pollo',
                'Pastel de tres leches', 'Flan napolitano',
                'Agua fresca de jamaica', 'Limonada con chía', 'Barra de refrescos y agua', 'Café y té',
            ],
            'social' => [
                'Sopa azteca', 'Cochinita pibil con arroz y frijol', 'Pollo en pipián verde',
                'Pay de queso con frutos rojos', 'Churros con cajeta',
                'Agua de horchata', 'Barra de refrescos y agua',
            ],
            'corporativo' => [
                'Ensalada de nopal y queso panela', 'Lasaña de verduras', 'Pechuga rellena de espinacas y queso',
                'Flan napolitano', 'Café y té', 'Barra de refrescos y agua',
            ],
            'infantil' => [
                'Nuggets de pollo con papas', 'Mini hamburguesas', 'Espagueti con salsa de tomate',
                'Quesadillas de jamón y queso', 'Gelatina y fruta', 'Cochinita pibil con arroz y frijol',
                'Pastel de tres leches', 'Agua fresca de jamaica', 'Barra de refrescos y agua',
            ],
        };
    }

    /** @return array<int, array{0: string, 1: int}> */
    private function servicios(string $tipo): array
    {
        $montaje = ['Montaje y desmontaje', 8000];

        return match ($tipo) {
            'boda' => [$montaje, ['Coordinación del evento', 12000], ['DJ y sonido', 9500],
                       ['Fotografía y video', 18000], ['Valet parking', 5500], ['Pastel de boda (3 pisos)', 7500]],
            'xv' => [$montaje, ['Coordinación del evento', 10000], ['DJ y sonido', 9500],
                     ['Maestro de ceremonias', 3500], ['Pastel de XV años', 6500], ['Show de luces', 6000]],
            'social', 'graduacion' => [$montaje, ['Coordinación del evento', 6000], ['Música ambiental', 4500], ['Pastel', 3500]],
            'corporativo' => [$montaje, ['Equipo audiovisual y técnico', 7500], ['Coffee break', 4800], ['Registro de asistentes', 2500]],
            'infantil' => [$montaje, ['Show infantil (payaso o magia)', 3800], ['Inflable y juegos', 4200],
                           ['Piñata y dulces', 1800], ['Pastel', 2200]],
        };
    }

    /* ===================================================================
     *  Reservaciones de agosto a diciembre
     * =================================================================== */

    private function reservaciones(): void
    {
        $hoy = Carbon::today();
        // mes => cantidad de reservaciones a intentar
        $meses = [8 => 7, 9 => 9, 10 => 10, 11 => 10, 12 => 13];

        $creadas = 0;
        $omitidas = 0;

        foreach ($meses as $mes => $cantidad) {
            for ($i = 0; $i < $cantidad; $i++) {
                $ok = false;
                for ($intento = 0; $intento < 15 && ! $ok; $intento++) {
                    $ok = $this->crearReservacion($mes, $hoy);
                }
                $ok ? $creadas++ : $omitidas++;
            }
        }

        $this->command?->info("Reservaciones creadas: {$creadas}" . ($omitidas ? " (omitidas por choque de fechas: {$omitidas})" : ''));
    }

    private function crearReservacion(int $mes, Carbon $hoy): bool
    {
        $paquete = $this->elegirPaquete($mes);
        /** @var Paquete $modelo */
        $modelo   = $paquete['modelo'];
        $tipo     = $paquete['tipo'];
        $locacion = $modelo->locacion ?? Locacion::find($modelo->id_locacion);

        $dia   = $this->elegirDia($mes, $tipo === 'corporativo');
        $fecha = Carbon::create(self::ANIO, $mes, $dia)->startOfDay();

        // Una locación no puede tener dos reservaciones el mismo día (misma regla que el sistema)
        $choque = Reservacion::where('locacion_id', $locacion->id)
            ->where('dia_evento', $fecha->toDateString())
            ->whereNotIn('status', ['cancelada'])
            ->exists();
        if ($choque) {
            return false;
        }

        // Horario y plantilla de actividades según el tipo de evento
        [$entrada, $salida, $plantilla] = match ($tipo) {
            'boda'        => ['16:00', '23:30', 'boda'],
            'xv'          => ['17:00', '23:30', 'xv'],
            'social'      => ['13:00', '19:00', 'social'],
            'graduacion'  => ['18:00', '23:00', 'social'],
            'corporativo' => ['09:00', '17:00', 'corporativo'],
            'infantil'    => ['12:00', '18:00', 'infantil'],
        };
        if ($tipo === 'corporativo' && $mes === 12 && mt_rand(1, 100) <= 60) {
            [$entrada, $salida, $plantilla] = ['19:00', '23:00', 'posada'];
        }

        // Invitados: entre 60 % y 112 % de la capacidad del paquete, sin pasar la capacidad de la locación
        $invitados = (int) round($modelo->capacidad * (mt_rand(60, 112) / 100));
        $invitados = max(10, min($invitados, $locacion->capacidad));

        // Descuento ocasional
        $descuento = 0.0;
        $motivo    = null;
        if (mt_rand(1, 100) <= 15) {
            $descuento = (float) round($modelo->precio_venta * [0.03, 0.05, 0.08, 0.10][array_rand([0, 1, 2, 3])], -2);
            $motivo    = ['Cliente frecuente', 'Promoción de temporada', 'Pago de contado', 'Recomendación de cliente anterior'][array_rand([0, 1, 2, 3])];
        }

        $cot = app(CotizadorReservacion::class)->calcular($locacion, $modelo, $invitados, $descuento);
        if ($cot['problema'] || $cot['total'] <= 0) {
            return false;
        }

        // Estado según la fecha (hoy = en curso, pasado = finalizada, futuro = confirmada o pendiente)
        $status = $this->elegirEstado($fecha, $hoy);

        // Fecha en que se hizo la reservación
        $creada = $fecha->copy()->subDays(mt_rand(40, 150));
        $tope   = $hoy->copy()->subDays(mt_rand(1, 10));
        if ($creada->gt($tope)) {
            $creada = $tope->copy();
        }
        if ($creada->lt(Carbon::create(self::ANIO, 6, 1))) {
            $creada = Carbon::create(self::ANIO, 6, 1)->addDays(mt_rand(0, 20));
        }
        $creada->setTime(mt_rand(9, 19), mt_rand(0, 59));

        $reservacion = new Reservacion([
            'dia_evento'         => $fecha->toDateString(),
            'hora_inicio'        => $fecha->copy()->setTimeFromTimeString($entrada)->format('Y-m-d H:i:s'),
            'hora_fin'           => $fecha->copy()->setTimeFromTimeString($salida)->format('Y-m-d H:i:s'),
            'contador_invitados' => $invitados,
            'costo_total'        => $cot['total'],
            'descuento'          => $cot['descuento'],
            'motivo_descuento'   => $cot['descuento'] > 0 ? $motivo : null,
            'saldo_pendiente'    => $cot['total'],
            'status'             => 'pendiente',
            'cliente_id'         => $this->clienteIds[array_rand($this->clienteIds)],
            'locacion_id'        => $locacion->id,
            'paquete_id'         => $modelo->id,
        ]);
        $reservacion->created_at = $creada;
        $reservacion->updated_at = $creada;
        $reservacion->save();

        $this->crearAnticipos($reservacion, $status, (float) $cot['total'], $creada, $fecha, $hoy);

        $reservacion->refresh();
        $reservacion->status = $status;
        $reservacion->save();

        // Montaje, actividades y personal solo cuando hay evento
        if (in_array($status, ['confirmada', 'en curso', 'finalizada'], true)) {
            $this->crearEvento($reservacion, $status, $tipo, $plantilla, $invitados, $fecha, $creada, $hoy);
        }

        // Igual que el sistema: una reservación cancelada queda como "cancelada" y se borra lógicamente
        if ($status === 'cancelada') {
            $reservacion->delete();
        }

        return true;
    }

    /** @return array{modelo: Paquete, tipo: string, peso: int} */
    private function elegirPaquete(int $mes): array
    {
        $pesos = [];
        foreach ($this->paquetes as $i => $p) {
            $peso = $p['peso'];
            // En diciembre abundan las posadas y eventos de fin de año
            if ($mes === 12 && $p['tipo'] === 'corporativo') {
                $peso *= 3;
            }
            $pesos[$i] = $peso;
        }

        $tirada = mt_rand(1, array_sum($pesos));
        foreach ($pesos as $i => $peso) {
            $tirada -= $peso;
            if ($tirada <= 0) {
                return $this->paquetes[$i];
            }
        }

        return $this->paquetes[array_key_first($this->paquetes)];
    }

    /** Día del mes: los eventos sociales caen sobre todo en fin de semana; los corporativos entre semana. */
    private function elegirDia(int $mes, bool $entreSemana): int
    {
        $total = Carbon::create(self::ANIO, $mes, 1)->daysInMonth;
        $pesos = [];

        for ($d = 1; $d <= $total; $d++) {
            $dow = Carbon::create(self::ANIO, $mes, $d)->dayOfWeekIso; // 1 = lunes ... 7 = domingo

            if ($entreSemana) {
                $pesos[$d] = $dow <= 5 ? 10 : 0;
            } else {
                $pesos[$d] = match (true) {
                    $dow === 6 => 60,
                    $dow === 5 => 30,
                    $dow === 7 => 25,
                    default    => 4,
                };
            }
        }

        $tirada = mt_rand(1, array_sum($pesos));
        foreach ($pesos as $d => $peso) {
            $tirada -= $peso;
            if ($tirada <= 0) {
                return $d;
            }
        }

        return 1;
    }

    private function elegirEstado(Carbon $fecha, Carbon $hoy): string
    {
        $r = mt_rand(1, 100);

        if ($fecha->lt($hoy)) {
            return $r <= 93 ? 'finalizada' : 'cancelada';
        }
        if ($fecha->eq($hoy)) {
            return 'en curso';
        }

        $dias = (int) $hoy->diffInDays($fecha);

        return $dias <= 60
            ? ($r <= 72 ? 'confirmada' : ($r <= 95 ? 'pendiente' : 'cancelada'))
            : ($r <= 45 ? 'confirmada' : ($r <= 95 ? 'pendiente' : 'cancelada'));
    }

    /* ===================================================================
     *  Anticipos
     * =================================================================== */

    private function crearAnticipos(Reservacion $reservacion, string $status, float $total, Carbon $creada, Carbon $fecha, Carbon $hoy): void
    {
        $apartado = $creada->copy()->startOfDay();
        $segundo  = $fecha->copy()->subDays(30);
        $final    = $fecha->copy()->subDays(7);
        if ($segundo->lt($apartado)) {
            $segundo = $apartado->copy()->addDays(5);
        }
        if ($final->lt($segundo)) {
            $final = $segundo->copy()->addDays(5);
        }

        $pagos = [];

        switch ($status) {
            case 'finalizada':
            case 'en curso':
                $a = round($total * 0.30, 2);
                $b = round($total * 0.40, 2);
                $pagos = [
                    [$a, $apartado, 'Anticipo para apartar la fecha'],
                    [$b, $segundo, 'Segundo pago'],
                    [round($total - $a - $b, 2), $final, 'Liquidación del evento'],
                ];
                break;

            case 'confirmada':
                if (mt_rand(1, 100) <= 20) {
                    $pagos = [[round($total * 0.50, 2), $apartado, 'Anticipo del 50 %']];
                } else {
                    $a = round($total * 0.30, 2);
                    $b = round($total * 0.40, 2);
                    $pagos = [[$a, $apartado, 'Anticipo para apartar la fecha']];
                    if ($segundo->lte($hoy)) {
                        $pagos[] = [$b, $segundo, 'Segundo pago'];
                    }
                    if ($final->lte($hoy)) {
                        $pagos[] = [round($total - $a - $b, 2), $final, 'Liquidación del evento'];
                    }
                }
                break;

            case 'cancelada':
                $pagos = [[round($total * 0.30, 2), $apartado, 'Anticipo (cancelación posterior)']];
                break;

            default: // pendiente: todavía no paga nada
                break;
        }

        foreach ($pagos as [$monto, $cuando, $nota]) {
            if ($cuando->gt($hoy) || $monto <= 0) {
                continue; // nunca se registran pagos con fecha futura
            }

            $anticipo = new Anticipo([
                'monto'          => $monto,
                'fecha_pago'     => $cuando->toDateString(),
                'notas'          => $nota,
                'reservacion_id' => $reservacion->id,
            ]);
            $anticipo->created_at = $cuando->copy()->setTime(mt_rand(10, 18), mt_rand(0, 59));
            $anticipo->updated_at = $anticipo->created_at;
            $anticipo->save(); // el modelo recalcula solo el saldo pendiente
        }
    }

    /* ===================================================================
     *  Evento, staff y actividades
     * =================================================================== */

    private function crearEvento(Reservacion $reservacion, string $status, string $tipo, string $plantilla, int $invitados, Carbon $fecha, Carbon $creada, Carbon $hoy): void
    {
        $dias = $fecha->gt($hoy) ? (int) $hoy->diffInDays($fecha) : 0;

        $montaje = match (true) {
            $status === 'finalizada'                          => 'terminado',
            $status === 'en curso'                            => mt_rand(1, 100) <= 60 ? 'terminado' : 'en proceso',
            $dias <= 3                                        => mt_rand(1, 100) <= 40 ? 'en proceso' : 'pendiente',
            default                                           => 'pendiente',
        };

        $evento = new Evento([
            'estado_montaje' => $montaje,
            'reservacion_id' => $reservacion->id,
        ]);
        $momento = $creada->copy()->addDays(2);
        if ($momento->gt(now())) {
            $momento = now()->copy();
        }
        $evento->created_at = $momento;
        $evento->updated_at = $momento;
        $evento->save();

        // Los eventos cercanos siempre tienen personal; los lejanos, solo la mitad
        if ($status === 'confirmada' && $dias > 45 && mt_rand(1, 100) <= 50) {
            return;
        }

        $equipo = $this->asignarPersonal($evento, $tipo, $invitados, $fecha);
        $this->crearActividades($evento, $plantilla, $reservacion, $equipo);
    }

    /**
     * @return array{coordinador: ?int, montaje: ?int, audio: ?int}
     */
    private function asignarPersonal(Evento $evento, string $tipo, int $invitados, Carbon $fecha): array
    {
        $clave = $fecha->toDateString();
        $this->ocupados[$clave] ??= [];

        $equipo = ['coordinador' => null, 'montaje' => null, 'audio' => null];

        $libre = function (array $usuarios) use ($clave): ?User {
            $candidatos = array_values(array_filter($usuarios, fn (User $u) => empty($this->ocupados[$clave][$u->id])));
            if (! $candidatos) {
                return null;
            }
            $u = $candidatos[array_rand($candidatos)];
            $this->ocupados[$clave][$u->id] = true;

            return $u;
        };

        $asignar = function (?User $u, string $rol, int $min, int $max) use ($evento): void {
            if (! $u) {
                return;
            }
            AsignacionStaff::create([
                'rol_asignado' => $rol,
                'pago_evento'  => round(mt_rand($min, $max) / 50) * 50,
                'evento_id'    => $evento->id,
                'empleado_id'  => $u->id,
            ]);
        };

        // Coordinador
        $coordinador = $libre($this->coordinadores);
        $asignar($coordinador, 'Coordinador de evento', 1800, 2600);
        $equipo['coordinador'] = $coordinador?->id;

        // Meseros
        $meseros = max(2, min(6, (int) ceil($invitados / 45)));
        for ($i = 0; $i < $meseros; $i++) {
            $asignar($libre($this->personal), 'Mesero', 600, 750);
        }

        // Bartender (no en eventos infantiles ni corporativos de día)
        if (! in_array($tipo, ['infantil', 'corporativo'], true)) {
            $barras = $invitados > 180 ? 2 : 1;
            for ($i = 0; $i < $barras; $i++) {
                $asignar($libre($this->personal), 'Bartender', 750, 900);
            }
        }

        // Montaje y logística
        for ($i = 0; $i < 2; $i++) {
            $u = $libre($this->personal);
            $asignar($u, 'Montaje y logística', 650, 800);
            $equipo['montaje'] ??= $u?->id;
        }

        // Audio e iluminación
        if (in_array($tipo, ['boda', 'xv', 'corporativo', 'graduacion'], true)) {
            $u = $libre($this->personal);
            $asignar($u, 'Técnico de audio e iluminación', 1100, 1400);
            $equipo['audio'] = $u?->id;
        }

        return $equipo;
    }

    /**
     * @param array{coordinador: ?int, montaje: ?int, audio: ?int} $equipo
     */
    private function crearActividades(Evento $evento, string $plantilla, Reservacion $reservacion, array $equipo): void
    {
        $inicio  = Carbon::parse($reservacion->hora_inicio);
        $fin     = Carbon::parse($reservacion->hora_fin);
        $minutos = (int) $inicio->diffInMinutes($fin);

        // [nombre, descripción, minutos desde el inicio ("fin" = al terminar), duración (null = hasta el final), responsable]
        $plantillas = [
            'boda' => [
                ['Montaje y decoración', 'Instalación de mesas, sillas, mantelería, arco floral y luces.', -240, 200, 'montaje'],
                ['Prueba de audio e iluminación', 'Revisión de sonido, micrófonos y luces antes de recibir invitados.', -40, 30, 'audio'],
                ['Recepción de invitados', 'Registro y acomodo de invitados con coctel de bienvenida.', 0, 45, 'coordinador'],
                ['Ceremonia', 'Ceremonia en el arco floral.', 45, 45, 'coordinador'],
                ['Cóctel de bienvenida', 'Canapés y bebida mientras se toman las fotografías.', 90, 60, 'coordinador'],
                ['Banquete', 'Servicio de entrada, plato fuerte y postre.', 150, 90, 'coordinador'],
                ['Brindis y primer baile', 'Brindis de los padres y primer baile de los novios.', 245, 30, 'audio'],
                ['Corte de pastel', 'Corte y servicio de pastel.', 280, 20, 'coordinador'],
                ['Baile y barra libre', 'Fiesta con DJ y barra libre.', 305, null, 'audio'],
                ['Desmontaje', 'Retiro de mobiliario, decoración y equipo.', 'fin', 90, 'montaje'],
            ],
            'xv' => [
                ['Montaje y decoración', 'Instalación de mesas, letras, backdrop y pista de baile.', -240, 200, 'montaje'],
                ['Recepción de invitados', 'Registro y acomodo de invitados.', 0, 40, 'coordinador'],
                ['Ceremonia y vals', 'Entrada de la festejada y vals con chambelanes.', 40, 50, 'audio'],
                ['Cena', 'Servicio del menú.', 100, 80, 'coordinador'],
                ['Baile sorpresa', 'Presentación de baile sorpresa.', 190, 30, 'audio'],
                ['Pastel y brindis', 'Corte de pastel y brindis.', 225, 20, 'coordinador'],
                ['Fiesta', 'Baile con DJ y show de luces.', 250, null, 'audio'],
                ['Desmontaje', 'Retiro de mobiliario, decoración y equipo.', 'fin', 90, 'montaje'],
            ],
            'social' => [
                ['Montaje', 'Instalación de mesas, sillas y decoración.', -180, 150, 'montaje'],
                ['Recepción de invitados', 'Registro y acomodo de invitados.', 0, 45, 'coordinador'],
                ['Bienvenida y brindis', 'Palabras de los anfitriones y brindis.', 45, 20, 'coordinador'],
                ['Comida', 'Servicio del menú.', 70, 90, 'coordinador'],
                ['Pastel y convivio', 'Corte de pastel y convivencia.', 170, 40, 'coordinador'],
                ['Música y convivencia', 'Música ambiental y convivencia.', 215, null, 'audio'],
                ['Desmontaje', 'Retiro de mobiliario y decoración.', 'fin', 75, 'montaje'],
            ],
            'corporativo' => [
                ['Montaje del salón', 'Mesas, sillas, pantalla y equipo audiovisual.', -120, 90, 'montaje'],
                ['Registro de asistentes', 'Registro y entrega de gafetes.', 0, 30, 'coordinador'],
                ['Sesión principal', 'Presentaciones y conferencias.', 30, 90, 'audio'],
                ['Coffee break', 'Servicio de café y bocadillos.', 120, 30, 'coordinador'],
                ['Comida', 'Servicio del menú.', 150, 75, 'coordinador'],
                ['Actividad de integración', 'Dinámica de equipo.', 225, 90, 'coordinador'],
                ['Cierre', 'Conclusiones y despedida.', 315, 30, 'coordinador'],
                ['Desmontaje', 'Retiro de mobiliario y equipo.', 'fin', 60, 'montaje'],
            ],
            'posada' => [
                ['Montaje y decoración navideña', 'Mesas, sillas, luces y ambientación.', -180, 150, 'montaje'],
                ['Recepción y coctel', 'Bienvenida con bebida.', 0, 45, 'coordinador'],
                ['Cena', 'Servicio del menú.', 45, 75, 'coordinador'],
                ['Intercambio y brindis', 'Intercambio de regalos y brindis de fin de año.', 120, 40, 'coordinador'],
                ['Baile', 'Música y baile.', 160, null, 'audio'],
                ['Desmontaje', 'Retiro de mobiliario y equipo.', 'fin', 60, 'montaje'],
            ],
            'infantil' => [
                ['Montaje y decoración', 'Mesas infantiles, globos y backdrop.', -150, 120, 'montaje'],
                ['Recepción de invitados', 'Registro y bienvenida a los niños.', 0, 30, 'coordinador'],
                ['Show infantil', 'Show de payaso o magia.', 30, 60, 'coordinador'],
                ['Comida', 'Menú infantil y menú de adultos.', 90, 60, 'coordinador'],
                ['Pastel y piñata', 'Canto de cumpleaños, pastel y piñata.', 150, 45, 'coordinador'],
                ['Juegos', 'Juegos e inflables.', 195, null, 'coordinador'],
                ['Desmontaje', 'Retiro de mobiliario y decoración.', 'fin', 60, 'montaje'],
            ],
        ];

        foreach ($plantillas[$plantilla] as [$nombre, $descripcion, $desde, $duracion, $quien]) {
            $inicioMin = $desde === 'fin' ? $minutos : $desde;

            if ($inicioMin >= $minutos && $desde !== 'fin') {
                continue; // la actividad empezaría cuando el evento ya terminó
            }

            $duracion ??= $minutos - $inicioMin;
            if ($desde !== 'fin' && $inicioMin >= 0 && $inicioMin + $duracion > $minutos) {
                $duracion = $minutos - $inicioMin;
            }
            if ($duracion < 20) {
                continue;
            }

            $ini = $inicio->copy()->addMinutes($inicioMin);

            Actividad::create([
                'actividad'   => $nombre,
                'descripcion' => $descripcion,
                'hora_inicio' => $ini->format('Y-m-d H:i:s'),
                'hora_fin'    => $ini->copy()->addMinutes($duracion)->format('Y-m-d H:i:s'),
                'evento_id'   => $evento->id,
                'responsable' => $equipo[$quien] ?? $equipo['coordinador'] ?? $this->adminId,
            ]);
        }
    }
}