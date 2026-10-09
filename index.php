<?php
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDeDatos = "banco";

$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

if (!$enlace) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($enlace, "utf8mb4");

function escapar($texto) {
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}

$nombre = "";
$apellido = "";
$mensaje = "";
$exito = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["enviar"])) {
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellido"] ?? "");
    $mensaje = trim($_POST["mensaje"] ?? "");

    if ($nombre == "" || $apellido == "" || $mensaje == "") {
        $error = "Completá todos los campos antes de enviar.";
    } elseif (
        mb_strlen($nombre) > 50 ||
        mb_strlen($apellido) > 50 ||
        mb_strlen($mensaje) > 2000
    ) {
        $error = "Alguno de los campos supera la longitud permitida.";
    } else {
        $consulta = mysqli_prepare(
            $enlace,
            "INSERT INTO mensajes (nombre, apellido, mensaje) VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $consulta,
            "sss",
            $nombre,
            $apellido,
            $mensaje
        );

        if (mysqli_stmt_execute($consulta)) {
            $exito = true;
            $nombre = "";
            $apellido = "";
            $mensaje = "";
        } else {
            $error = "No se pudo guardar el mensaje.";
        }

        mysqli_stmt_close($consulta);
    }
}

$consultaMensajes = mysqli_query(
    $enlace,
    "SELECT nombre, apellido, mensaje FROM mensajes ORDER BY id DESC"
);

if (!$consultaMensajes) {
    die("Error al consultar los mensajes. Revisá la tabla de la base de datos.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#092341">
    <meta name="description" content="Compartí tu mensaje para Lionel Messi y conocé los saludos de otros fanáticos.">
    <title>Un saludo para Messi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Un saludo para Messi, inicio">
            <span class="brand-number">10</span>
            <span>PARA <strong>MESSI</strong></span>
        </a>
        <span class="header-note">ARGENTINA · FÚTBOL · PASIÓN</span>
    </header>

    <main>
        <section class="hero" aria-labelledby="titulo">
            <div class="hero-copy">
                <p class="eyebrow"><span></span> EL ORGULLO DE UN PAÍS</p>
                <h1 id="titulo">Sos eterno,<br><em>Leo.</em></h1>
                <p class="intro">Gracias por tantas emociones, por cada gol y por llevar nuestra bandera a lo más alto. Escribile unas palabras al diez.</p>
                <a class="button button-outline" href="#deja-tu-mensaje">
                    Dejar mi saludo <span aria-hidden="true">↓</span>
                </a>
            </div>

            <div class="hero-art" role="img" aria-label="Homenaje al número 10 de Lionel Messi">
                <div class="glow"></div>
                <span class="star star-one" aria-hidden="true">✳</span>
                <span class="star star-two" aria-hidden="true">✳</span>
                <span class="number" aria-hidden="true">10</span>
                <p class="captain">NUESTRO CAPITÁN <span>·</span> NUESTRO ORGULLO</p>
            </div>

            <span class="hero-caption">SIEMPRE CON LA CELESTE Y BLANCA</span>
        </section>

        <section class="message-section" id="deja-tu-mensaje" aria-labelledby="message-title">
            <div class="section-label"><span>01</span> DEJÁ TU HUELLA</div>

            <div class="message-layout">
                <div class="message-heading">
                    <h2>Escribí tu<br><em>saludo.</em></h2>
                    <p>Contá lo que significa Messi para vos. Puede ser un agradecimiento, un recuerdo inolvidable o unas palabras de admiración.</p>
                </div>

                <div class="form-card">

                    <?php if ($exito): ?>
                        <p class="notice success" role="status">
                            ¡Muchas gracias! Tu saludo ya forma parte de nuestra colección.
                        </p>
                    <?php endif; ?>

                    <?php if ($error != ""): ?>
                        <p class="notice error" role="alert">
                            <?= escapar($error) ?>
                        </p>
                    <?php endif; ?>

                    <form method="post" action="index.php#deja-tu-mensaje">
                        <div class="field-row">
                            <label class="field">
                                <span>Tu nombre</span>
                                <input
                                    type="text"
                                    name="nombre"
                                    maxlength="50"
                                    autocomplete="given-name"
                                    value="<?= escapar($nombre) ?>"
                                    required
                                >
                            </label>

                            <label class="field">
                                <span>Tu apellido</span>
                                <input
                                    type="text"
                                    name="apellido"
                                    maxlength="50"
                                    autocomplete="family-name"
                                    value="<?= escapar($apellido) ?>"
                                    required
                                >
                            </label>
                        </div>

                        <label class="field">
                            <span>Escribí tu mensaje</span>
                            <textarea
                                name="mensaje"
                                rows="5"
                                maxlength="2000"
                                placeholder="Gracias, Leo, por tantas alegrías..."
                                required
                            ><?= escapar($mensaje) ?></textarea>
                        </label>

                        <button class="button button-primary" type="submit" name="enviar" value="1">
                            Enviar saludo <span aria-hidden="true">↗</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="messages-section" id="mensajes" aria-labelledby="messages-title">
            <div class="messages-top">
                <div>
                    <div class="section-label"><span>02</span> VOCES DE LOS HINCHAS</div>
                    <h2>Saludos para <em>el campeón.</em></h2>
                </div>

                <form method="get" action="index.php#mensajes">
                    <button class="button button-dark" type="submit" name="ver" value="mensajes">
                        Mostrar los saludos <span aria-hidden="true">↓</span>
                    </button>
                </form>
            </div>

            <?php if (mysqli_num_rows($consultaMensajes) == 0): ?>
                <p class="empty-state">
                    Todavía no se publicó ningún saludo. ¡Animate a escribir el primero!
                </p>
            <?php else: ?>
                <div class="message-grid">
                    <?php while ($fila = mysqli_fetch_assoc($consultaMensajes)): ?>
                        <article class="message-card">
                            <span class="quote-mark" aria-hidden="true">“</span>
                            <p><?= nl2br(escapar($fila["mensaje"])) ?></p>
                            <footer>
                                <?= escapar($fila["nombre"]) ?>
                                <?= escapar($fila["apellido"]) ?>
                            </footer>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

            <p class="empty-state">
                Cada palabra cuenta.
                <a href="#deja-tu-mensaje">Sumá tu mensaje.</a>
            </p>
        </section>
    </main>

    <footer class="site-footer">
        <a class="brand" href="index.php">
            <span class="brand-number">10</span>
            <span>PARA <strong>MESSI</strong></span>
        </a>

        <p>Desde Argentina, para el mejor. <span>♥</span></p>

        <a class="back-top" href="#">IR AL INICIO ↑</a>
    </footer>
</body>
</html>
<?php
mysqli_close($enlace);
?>