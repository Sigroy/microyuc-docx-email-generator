<?php
require './config/db_connect.php';
require './lib/phpword/vendor/autoload.php';
require './includes/functions.php';

check_login();

$fmt = set_date_format();

$carta = [
    'numero_expediente' => '',
    'nombre_cliente' => '',
    'calle' => '',
    'cruzamientos' => '',
    'numero_direccion' => '',
    'colonia_fraccionamiento' => '',
    'localidad' => '',
    'municipio' => '',
    'fecha_firma' => '',
    'documentacion' => '',
    'comprobacion_monto' => '',
    'comprobacion_tipo' => '',
    'pagos_fecha_inicial' => '',
    'pagos_fecha_final' => '',
    'tipo_credito' => '',
    'fecha_otorgamiento' => '',
    'monto_inicial' => '',
    'mensualidades_vencidas' => '',
    'adeudo_total' => '',
];

$errores = [
    'numero_expediente' => '',
    'nombre_cliente' => '',
    'calle' => '',
    'cruzamientos' => '',
    'numero_direccion' => '',
    'colonia_fraccionamiento' => '',
    'localidad' => '',
    'municipio' => '',
    'fecha_firma' => '',
    'documentacion' => '',
    'comprobacion_monto' => '',
    'comprobacion_tipo' => '',
    'tipo_credito' => '',
    'fecha_otorgamiento' => '',
    'monto_inicial' => '',
    'mensualidades_vencidas' => '',
    'adeudo_total' => '',
];

$tipos_comprobacion = ['Capital de trabajo', 'Activo fijo', 'Adecuaciones', 'Insumos', 'Certificaciones',];

$filtros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Setting filter settings
    $filtros['numero_expediente']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['numero_expediente']['options']['regexp'] = '/(^IYE{1,1})([\d\-]+$)/';
    $filtros['nombre_cliente']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['nombre_cliente']['options']['regexp'] = '/^[A-zÀ-ÿ ]+$/';
    $filtros['calle']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['calle']['options']['regexp'] = '/[\s\S]+/';
    $filtros['calle']['options']['default'] = '';
    $filtros['cruzamientos']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['cruzamientos']['options']['regexp'] = '/[\s\S]+/';
    $filtros['cruzamientos']['options']['default'] = '';
    $filtros['numero_direccion']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['numero_direccion']['options']['regexp'] = '/[\s\S]+/';
    $filtros['numero_direccion']['options']['default'] = '';
    $filtros['colonia_fraccionamiento']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['colonia_fraccionamiento']['options']['regexp'] = '/[\s\S]+/';
    $filtros['colonia_fraccionamiento']['options']['default'] = '';
    $filtros['localidad']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['localidad']['options']['regexp'] = '/[\s\S]+/';
    $filtros['municipio']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['municipio']['options']['regexp'] = '/[\s\S]+/';
    $filtros['fecha_firma']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['fecha_firma']['options']['regexp'] = '/^[\d\-]+$/';
    $filtros['documentacion']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['documentacion']['options']['regexp'] = '/[\s\S]+/';
    $filtros['documentacion']['options']['default'] = '';
    $filtros['comprobacion_monto']['filter'] = FILTER_VALIDATE_FLOAT;
    $filtros['comprobacion_monto']['options']['min_range'] = 1;
    $filtros['comprobacion_tipo']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['comprobacion_tipo']['options']['regexp'] = '/^(Capital de trabajo|Activo fijo|Adecuaciones|Insumos|Certificaciones)+$/';
    $filtros['pagos_fecha_inicial']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['pagos_fecha_inicial']['options']['regexp'] = '/^[\d\-]+$/';
    $filtros['pagos_fecha_final']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['pagos_fecha_final']['options']['regexp'] = '/^[\d\-]+$/';
    $filtros['tipo_credito']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['tipo_credito']['options']['regexp'] = '/[\s\S]+/';
    $filtros['fecha_otorgamiento']['filter'] = FILTER_VALIDATE_REGEXP;
    $filtros['fecha_otorgamiento']['options']['regexp'] = '/^[\d\-]+$/';
    $filtros['monto_inicial']['filter'] = FILTER_VALIDATE_FLOAT;
    $filtros['monto_inicial']['options']['min_range'] = 1;
    $filtros['adeudo_total']['filter'] = FILTER_VALIDATE_FLOAT;
    $filtros['adeudo_total']['options']['min_range'] = 1;

    $carta = filter_input_array(INPUT_POST, $filtros);

    // Create error messages
    $errores['numero_expediente'] = $carta['numero_expediente'] ? '' : 'El número de expediente debe comenzar con «IYE» y contener números y guiones.';
    $errores['nombre_cliente'] = $carta['nombre_cliente'] ? '' : 'El nombre solo debe contener letras y espacios.';
    $errores['localidad'] = $carta['localidad'] ? '' : 'Este campo es requerido.';
    $errores['municipio'] = $carta['municipio'] ? '' : 'Este campo es requerido.';
    $errores['fecha_firma'] = $carta['fecha_firma'] ? '' : 'Por favor, introduzca un formato de fecha válido.';
    $errores['comprobacion_monto'] = $carta['comprobacion_monto'] ? '' : 'El monto debe ser mayor a 0.';
    $errores['comprobacion_tipo'] = in_array($carta['comprobacion_tipo'], $tipos_comprobacion) ? '' : 'Por favor, seleccione una opción correcta.';
    $errores['pagos_fecha_inicial'] = $carta['pagos_fecha_inicial'] ? '' : 'Por favor, introduzca un formato de fecha válido.';
    $errores['pagos_fecha_final'] = $carta['pagos_fecha_final'] ? '' : 'Por favor, introduzca un formato de fecha válido.';
    $errores['tipo_credito'] = $carta['tipo_credito'] ? '' : 'Este campo es requerido.';
    $errores['fecha_otorgamiento'] = $carta['fecha_otorgamiento'] ? '' : 'Por favor, introduzca un formato de fecha válido.';
    $errores['monto_inicial'] = $carta['monto_inicial'] ? '' : 'El monto debe ser mayor a 0.';
    $errores['adeudo_total'] = $carta['adeudo_total'] ? '' : 'El monto debe ser mayor a 0.';

    if (!$errores['pagos_fecha_inicial'] && !$errores['pagos_fecha_final']) {
// Create a date using the dates recieved by post
        $pagos_fecha_inicial_conv = date_create($carta['pagos_fecha_inicial']);
        $pagos_fecha_final_conv = date_create($carta['pagos_fecha_final']);

// Add 1 day to the created days, so it's easier to calculate the difference between dates
        date_add($pagos_fecha_inicial_conv, date_interval_create_from_date_string('1 day'));
        date_add($pagos_fecha_final_conv, date_interval_create_from_date_string('1 day'));

// Calculate the month interval diff
        $intervalo_meses = $pagos_fecha_inicial_conv->diff($pagos_fecha_final_conv);
        if ($intervalo_meses->format('%r') != '-') {
            // Calculation so that it only gives the total in months
            $total_meses = 12 * $intervalo_meses->y + $intervalo_meses->m;

// Assign the total months to variable to set the value in the template
            $carta['mensualidades_vencidas'] = $total_meses + 1;

            if ($carta['mensualidades_vencidas'] > 1) {
                $pagos = 'Correspondientes a los meses de ' . datefmt_format($fmt, $pagos_fecha_inicial_conv) . ' a ' . datefmt_format($fmt, $pagos_fecha_final_conv);
            } elseif ($carta['mensualidades_vencidas'] === 1) {
                $pagos = 'Correspondientes al mes de ' . datefmt_format($fmt, $pagos_fecha_inicial_conv);
            } else {
                $errores['mensualidades_vencidas'] = 'Los meses escogidos dan un número de mensualidades vencidas negativo o incorrecto.';
            }
        } else {
            $errores['mensualidades_vencidas'] = 'Los meses escogidos dan un número de mensualidades vencidas negativo o incorrecto.';
        }
    }

    $generacion_invalida = implode($errores);

    if (!$generacion_invalida) {

        // Create variable with filename
        $nombre_archivo = $carta['numero_expediente'] . ' ' . $carta['nombre_cliente'] . '.docx';

        // Encode filename so that UTF-8 characters work
        $nombre_archivo_decodificado = rawurlencode($nombre_archivo);


// Create new instance of PHPWord template processor using the required template file
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('./plantillas/plantilla-carta.docx');

// Set values in template with post received inputs and calculated variables
        $templateProcessor->setValue('numero_expediente', $carta['numero_expediente']);
        $templateProcessor->setValue('nombre_cliente', $carta['nombre_cliente']);
        $templateProcessor->setValue('calle', $carta['calle']);
        $templateProcessor->setValue('cruzamientos', $carta['cruzamientos']);
        $templateProcessor->setValue('numero_direccion', $carta['numero_direccion']);
        $templateProcessor->setValue('colonia_fraccionamiento', $carta['colonia_fraccionamiento']);
        $templateProcessor->setValue('localidad', $carta['localidad']);
        $templateProcessor->setValue('municipio', $carta['municipio']);
        $templateProcessor->setValue('fecha_firma', date("d-m-Y", strtotime($carta['fecha_firma'])));
        $templateProcessor->setValue('documentacion', $carta['documentacion']);
        $templateProcessor->setValue('comprobacion_monto', number_format($carta['comprobacion_monto'], 2));
        $templateProcessor->setValue('comprobacion_tipo', strtolower($carta['comprobacion_tipo']));
        $templateProcessor->setValue('pagos', $pagos);
        $templateProcessor->setValue('tipo_credito', $carta['tipo_credito']);
        $templateProcessor->setValue('fecha_otorgamiento', date("d-m-Y", strtotime($carta['fecha_otorgamiento'])));
        $templateProcessor->setValue('monto_inicial', number_format($carta['monto_inicial'], 2));
        $templateProcessor->setValue('mensualidades_vencidas', $carta['mensualidades_vencidas']);
        $templateProcessor->setValue('adeudo_total', number_format($carta['adeudo_total'], 2));

// Escape strings to insert into the database table
        $numero_expediente = mysqli_real_escape_string($conn, $carta['numero_expediente']);
        $nombre_cliente = mysqli_real_escape_string($conn, $carta['nombre_cliente']);
        $calle = mysqli_real_escape_string($conn, $carta['calle']);
        $cruzamientos = mysqli_real_escape_string($conn, $carta['cruzamientos']);
        $numero_direccion = mysqli_real_escape_string($conn, $carta['numero_direccion']);
        $colonia_fraccionamiento = mysqli_real_escape_string($conn, $carta['colonia_fraccionamiento']);
        $localidad = mysqli_real_escape_string($conn, $carta['localidad']);
        $municipio = mysqli_real_escape_string($conn, $carta['municipio']);
        $fecha_firma = mysqli_real_escape_string($conn, $carta['fecha_firma']);
        $documentacion = mysqli_real_escape_string($conn, $carta['documentacion']);
        $comprobacion_monto = floatval(mysqli_real_escape_string($conn, $carta['comprobacion_monto']));
        $comprobacion_tipo = mysqli_real_escape_string($conn, $carta['comprobacion_tipo']);
        $pagos_fecha_inicial = mysqli_real_escape_string($conn, $carta['pagos_fecha_inicial']);
        $pagos_fecha_final = mysqli_real_escape_string($conn, $carta['pagos_fecha_final']);
        $tipo_credito = mysqli_real_escape_string($conn, $carta['tipo_credito']);
        $fecha_otorgamiento = mysqli_real_escape_string($conn, $carta['fecha_otorgamiento']);
        $monto_inicial = floatval(mysqli_real_escape_string($conn, $carta['monto_inicial']));
        $mensualidades_vencidas = intval(mysqli_real_escape_string($conn, $carta['mensualidades_vencidas']));
        $adeudo_total = floatval(mysqli_real_escape_string($conn, $carta['adeudo_total']));

// Query
        $sql = "INSERT INTO carta(numero_expediente, nombre_cliente, calle, cruzamientos, numero_direccion, colonia_fraccionamiento, localidad, municipio, fecha_firma,
                  documentacion, comprobacion_monto, comprobacion_tipo, pagos_fecha_inicial, pagos_fecha_final, tipo_credito, fecha_otorgamiento, monto_inicial,
                  mensualidades_vencidas, adeudo_total, nombre_archivo) VALUES('$numero_expediente', '$nombre_cliente', '$calle', '$cruzamientos', '$numero_direccion', '$colonia_fraccionamiento', '$localidad', '$municipio', '$fecha_firma',
                                                               '$documentacion', '$comprobacion_monto', '$comprobacion_tipo', '$pagos_fecha_inicial', '$pagos_fecha_final', '$tipo_credito', '$fecha_otorgamiento', '$monto_inicial',
                                                               '$mensualidades_vencidas', '$adeudo_total', '$nombre_archivo')";

// Validation of query
        if (mysqli_query($conn, $sql)) {

            if (!is_dir('./files/')) {
                mkdir('./files/');
            }

            if (!is_dir('./files/cartas/')) {
                mkdir('./files/cartas/');
            }

            // Path where generated file is saved
            $ruta_guardado = './files/cartas/' . $nombre_archivo;
            $templateProcessor->saveAs($ruta_guardado);

            if (file_exists($ruta_guardado)) {
                header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                header('Content-Disposition: attachment; filename="' . "$nombre_archivo_decodificado" . '"');
                header('Content-Length: ' . filesize($ruta_guardado));
                ob_clean();
                flush();
                // Send generated file stored in the server to the browser
                readfile($ruta_guardado);
                exit;
            }
        } else {
            echo 'Error de consulta: ' . mysqli_error($conn);
        }
    }
}

?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../dist/css/styles.css">
    <title>Microyuc | Generador de cartas</title>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <a href="inicio.php"><img src="../img/microyucfondo.png" alt="Logo de microyuc" class="sidebar__image"></a>
        <nav class="sidebar__nav">
            <div class="sidebar__dashboard">
                <h2 class="sidebar__title">Tablero</h2>
                <ul class="sidebar__list">
                    <li><a href="inicio.php" class="sidebar__link">
                            <svg xmlns="http://www.w3.org/2000/svg" class="sidebar__icon" fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Inicio</span></a></li>
                </ul>
            </div>
            <div class="sidebar__apps">
                <h2 class="sidebar__title">Apps</h2>
                <ul class="sidebar__list">
                    <li><a href="generador-carta.php" class="sidebar__link sidebar__link--active">
                            <svg xmlns="http://www.w3.org/2000/svg" class="sidebar__icon" fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Cartas</span></a></li>
                    <li><a href="generador-bitacora.php" class="sidebar__link">
                            <svg xmlns="http://www.w3.org/2000/svg" class="sidebar__icon" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Bitácoras</span></a></li>
                </ul>
            </div>
        </nav>
    </aside>
    <main class="main">
        <div class="main__app">
            <div class="main__header">
                <h1 class="main__title">Generador de cartas</h1>
                <a href="cartas.php" class="main__btn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="main__icon" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Gestionar cartas
                </a>
            </div>
            <div>
                <form class="form" action="generador-carta.php" method="post">
                    <fieldset class="form__fieldset form__fieldset--accredited">
                        <legend class="form__legend">Información del acreditado</legend>
                        <div class="form__division">
                            <label class="form__label" for="numero_expediente">Número de expediente<span
                                        class="asterisk">*</span>:</label>
                            <input class="form__input" type="text" id="numero_expediente"
                                   name="numero_expediente" pattern="(^IYE{1,1})([\d\-]+$)"
                                   value="<?= $carta['numero_expediente'] === '' ? 'IYE' : htmlspecialchars($carta['numero_expediente']) ?>"
                                   required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="nombre_cliente">Nombre del cliente<span
                                        class="asterisk">*</span>: </label>
                            <input class="form__input" type="text" id="nombre_cliente"
                                   name="nombre_cliente" value="<?= htmlspecialchars($carta['nombre_cliente']) ?>"
                                   required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="calle">Calle: </label>
                            <input class="form__input" type="text" id="calle" name="calle"
                                   value="<?= htmlspecialchars($carta['calle']) ?>">
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="cruzamientos">Cruzamientos: </label>
                            <input class="form__input" type="text" id="cruzamientos" name="cruzamientos"
                                   value="<?= htmlspecialchars($carta['cruzamientos']) ?>">
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="numero_direccion">Número: </label>
                            <input class="form__input" type="text" id="numero_direccion"
                                   name="numero_direccion" value="<?= htmlspecialchars($carta['numero_direccion']) ?>">
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="colonia_fraccionamiento">Colonia/fraccionamiento: </label>
                            <input class="form__input" type="text" id="colonia_fraccionamiento"
                                   name="colonia_fraccionamiento"
                                   value="<?= htmlspecialchars($carta['colonia_fraccionamiento']) ?>">
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="localidad">Localidad<span class="asterisk">*</span>:
                            </label>
                            <input class="form__input" type="text" id="localidad" name="localidad"
                                   value="<?= htmlspecialchars($carta['localidad']) ?>"
                                   required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="municipio">Municipio<span class="asterisk">*</span>:
                            </label>
                            <input class="form__input" type="text" id="municipio" name="municipio"
                                   value="<?= htmlspecialchars($carta['municipio']) ?>"
                                   required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="fecha_firma">Fecha de firma de anexos<span class="asterisk">*</span>:
                            </label>
                            <input class="form__input" type="date" id="fecha_firma" name="fecha_firma"
                                   value="<?= htmlspecialchars($carta['fecha_firma']) ?>"
                                   required>
                        </div>
                    </fieldset>
                    <fieldset class="form__fieldset">
                        <legend class="form__legend">Documentación</legend>
                        <div class="form__division">
                            <label class="form__label" for="documentacion"></label>
                            <textarea class="form__input" id="documentacion"
                                      name="documentacion"><?= htmlspecialchars($carta['documentacion']) ?></textarea>
                        </div>
                    </fieldset>
                    <fieldset class="form__fieldset form__fieldset--verification">
                        <legend class="form__legend">Comprobación</legend>
                        <div class="form__division">
                            <label class="form__label" for="comprobacion_monto">Monto de comprobación<span
                                        class="asterisk">*</span>:
                            </label>
                            <input class="form__input" type="number" id="comprobacion_monto"
                                   name="comprobacion_monto" step="0.01" min="0"
                                   value="<?= htmlspecialchars($carta['comprobacion_monto']) ?>" required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="comprobacion_tipo">Tipo de comprobación<span
                                        class="asterisk">*</span>: </label>
                            <select class="form__input" id="comprobacion_tipo" name="comprobacion_tipo" required>
                                <?php foreach ($tipos_comprobacion as $tipos) : ?>
                                    <option value="<?= htmlspecialchars($tipos) ?>" <?= $carta['comprobacion_tipo'] === $tipos ? 'selected' : '' ?>><?= htmlspecialchars($tipos) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </fieldset>
                    <fieldset class="form__fieldset form__fieldset--payment">
                        <legend class="form__legend">Pagos</legend>
                        <div class="form__division">
                            <label class="form__label" for="pagos_fecha_inicial">Fecha inicial<span
                                        class="asterisk">*</span>: </label>
                            <input class="form__input" type="month" id="pagos_fecha_inicial"
                                   name="pagos_fecha_inicial"
                                   value="<?= htmlspecialchars($carta['pagos_fecha_inicial']) ?>" required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="pagos_fecha_final">Fecha final<span
                                        class="asterisk">*</span>: </label>
                            <input class="form__input" type="month" id="pagos_fecha_final"
                                   name="pagos_fecha_final"
                                   value="<?= htmlspecialchars($carta['pagos_fecha_final']) ?>"
                                   required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="tipo_credito">Tipo de crédito<span class="asterisk">*</span>:
                            </label>
                            <input class="form__input" type="text" id="tipo_credito" name="tipo_credito"
                                   value="<?= htmlspecialchars($carta['tipo_credito']) ?>"
                                   required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="fecha_otorgamiento">Fecha de otorgamiento del crédito<span
                                        class="asterisk">*</span>:
                            </label>
                            <input class="form__input" type="date" id="fecha_otorgamiento"
                                   name="fecha_otorgamiento"
                                   value="<?= htmlspecialchars($carta['fecha_otorgamiento']) ?>" required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="monto_inicial">Monto inicial<span class="asterisk">*</span>:
                            </label>
                            <input class="form__input" type="number" id="monto_inicial" name="monto_inicial" step="0.01"
                                   min="0" value="<?= htmlspecialchars($carta['monto_inicial']) ?>"
                                   required>
                        </div>
                        <div class="form__division">
                            <label class="form__label" for="adeudo_total">Adeudo total<span class="asterisk">*</span>:
                            </label>
                            <input class="form__input" type="number" id="adeudo_total" name="adeudo_total" step="0.01"
                                   min="0" value="<?= htmlspecialchars($carta['adeudo_total']) ?>"
                                   required>
                        </div>
                    </fieldset>
                    <div class="form__container--btn">
                        <button class="container__btn--reset" type="reset">Limpiar</button>
                        <input class="container__btn--submit" type="submit" value="Generar archivo">
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>