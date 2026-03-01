<?php
$config = require __DIR__ . '/config.php';
//Conexión a la base de datos usando PDO 
//La función es a prueba de fallos y devuelve un mensaje de error genérico en caso de problemas de conexión, mientras que los detalles se registran en el log de errores del servidor.
//Se usa patrón singleton y solo establece una conexión a la base de datos durante la ejecución del script.
function db() 
{
    static $pdo;
    global $config; //Uso de config para no poner la información de la DB directamente.

    if (!$pdo) 
    {
        try 
        {
            $pdo = new PDO(
                "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4",
                $config['DB_USER'],
                $config['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } 
        catch (PDOException $e) 
        {
            error_log($e->getMessage());
            die("Error de conexión.");
        }
    }
    return $pdo;
}
//============================Función de Login============================
function login_usuario($correo, $contra) 
{
    $st = db()->prepare("SELECT * FROM Usuario WHERE Email = :e");
    $st->execute([':e' => $correo]);
    $u = $st->fetch();
    //Utiliza método de comprobación segura de PHP para verificar la contraseña.
    //Devuelve el usuario si la comprobación es exitosa, o false si falla.
    //Se encarga solo el método de descifrar el passwordhash.
    if ($u && password_verify($contra, $u['Contrasena'])) 
    {
        return $u;
    }
    return false;
}
//============================Funciones sobre usuarios======================
function buscar_usuarios($q) 
{
    $st = db()->prepare("SELECT * FROM Usuario WHERE Nombre LIKE :q ");
    $st->execute([':q' => "%$q%"]);
    return $st->fetchAll();
}
function recetas_por_usuario($id_usuario) 
{
    $sql = "SELECT r.*, u.Nombre 
            FROM Receta r
            JOIN Usuario u ON u.ID_Usuario = r.ID_Creador
            WHERE r.ID_Creador = :id
            ORDER BY r.ID_Receta DESC";

    $st = db()->prepare($sql);
    $st->execute([':id' => $id_usuario]);
    
    return $st->fetchAll();
}
function crear_usuario($nombre, $email, $contra, $telefono = null)
{
    $sql = "INSERT INTO Usuario 
            (Nombre, Email, Contrasena, Telefono) 
            VALUES (:n, :e, :p, :t)";

    try 
    {
        return db()->prepare($sql)->execute([
            ':n' => $nombre,
            ':e' => $email,
            ':p' => password_hash($contra, PASSWORD_DEFAULT),
            ':t' => $telefono
        ]);
    } 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
function actualizar_usuario($id_usuario, $nombre = null, $email = null, $telefono = null, $modo_oscuro = null, $modo_fit = null, $notificacion_on = null, $cuenta_publica = null)
{
    $campos = [];
    $params = [':id' => $id_usuario];

    if ($nombre !== null) { $campos[] = "Nombre = :n"; $params[':n'] = $nombre; }
    if ($email !== null) { $campos[] = "Email = :e"; $params[':e'] = $email; }
    if ($telefono !== null) { $campos[] = "Telefono = :t"; $params[':t'] = $telefono; }
    if ($modo_oscuro !== null) { $campos[] = "ModoOscuro = :mo"; $params[':mo'] = $modo_oscuro; }
    if ($modo_fit !== null) { $campos[] = "ModoFit = :mf"; $params[':mf'] = $modo_fit; }
    if ($notificacion_on !== null) { $campos[] = "NotificacionOn = :no"; $params[':no'] = $notificacion_on; }
    if ($cuenta_publica !== null) { $campos[] = "CuentaPublica = :cp"; $params[':cp'] = $cuenta_publica; }

    if (empty($campos)) return false;

    $sql = "UPDATE Usuario SET " . implode(', ', $campos) . " WHERE ID_Usuario = :id";

    try {
        return db()->prepare($sql)->execute($params);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
function borrar_usuario($id_usuario)
{
    try {
        return db()->prepare("DELETE FROM Usuario WHERE ID_Usuario = :id")->execute([':id' => $id_usuario]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
//============================Funciones sobre recetas======================
function crear_receta($id_creador, $titulo, $descripcion = null, $imagen = null, $tiempo = null, $porciones = null, $es_fit = false, $es_publica = true)
{
    $sql = "INSERT INTO Receta 
            (ID_Creador, Titulo, Descripcion, Imagen, Tiempo, Porciones, EsFit, EsPublica) 
            VALUES (:idc, :t, :d, :i, :ti, :po, :fit, :pub)";

    try {
        return db()->prepare($sql)->execute([
            ':idc' => $id_creador,
            ':t'   => $titulo,
            ':d'   => $descripcion,
            ':i'   => $imagen,
            ':ti'  => $tiempo,
            ':po'  => $porciones,
            ':fit' => $es_fit,
            ':pub' => $es_publica
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
function actualizar_receta($id_receta, $titulo = null, $descripcion = null, $imagen = null, $tiempo = null, $porciones = null, $es_fit = null, $es_publica = null)
{
    $campos = [];
    $params = [':id' => $id_receta];

    if ($titulo !== null) $campos[] = "Titulo = :t"; $params[':t'] = $titulo;
    if ($descripcion !== null) $campos[] = "Descripcion = :d"; $params[':d'] = $descripcion;
    if ($imagen !== null) $campos[] = "Imagen = :i"; $params[':i'] = $imagen;
    if ($tiempo !== null) $campos[] = "Tiempo = :ti"; $params[':ti'] = $tiempo;
    if ($porciones !== null) $campos[] = "Porciones = :po"; $params[':po'] = $porciones;
    if ($es_fit !== null) $campos[] = "EsFit = :fit"; $params[':fit'] = $es_fit;
    if ($es_publica !== null) $campos[] = "EsPublica = :pub"; $params[':pub'] = $es_publica;

    if (empty($campos)) return false;

    $sql = "UPDATE Receta SET " . implode(', ', $campos) . " WHERE ID_Receta = :id";

    try {
        return db()->prepare($sql)->execute($params);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

function borrar_receta($id_receta)
{
    try {
        return db()->prepare("DELETE FROM Receta WHERE ID_Receta = :id")->execute([':id' => $id_receta]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
function todas_las_recetas()
{
    $sql = "
    SELECT 
        r.ID_Receta,
        r.Titulo,
        r.Descripcion,
        r.Imagen,
        r.Tiempo,
        r.Porciones,
        r.Megustas,
        r.Valoracion,
        r.EsFit,
        r.EsPublica,
        u.ID_Usuario AS ID_Creador,
        u.Nombre AS Nombre_Creador,
        u.Email AS Email_Creador,
        --Usamos GROUP_CONCAT para obtener los ingredientes, etiquetas y pasos relacionados con cada receta en una sola fila.
        --Así no tenemos que hacer múltiples consultas o joins adicionales, lo tenemos todo del tirón.
        GROUP_CONCAT(DISTINCT CONCAT(i.Nombre, ' (', ri.Cantidad, ')') SEPARATOR '|') AS Ingredientes,
        GROUP_CONCAT(DISTINCT t.Nombre SEPARATOR '|') AS Etiquetas,
        GROUP_CONCAT(DISTINCT CONCAT(p.ID_Paso, '::', p.Nombre, '::', p.Descripcion) ORDER BY p.ID_Paso ASC SEPARATOR '|') AS Pasos
    FROM Receta r
    JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
    LEFT JOIN Receta_Ingrediente ri ON r.ID_Receta = ri.ID_Receta
    LEFT JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente
    LEFT JOIN Etiqueta_Receta er ON r.ID_Receta = er.ID_Receta
    LEFT JOIN Etiqueta t ON er.ID_Etiqueta = t.ID_Etiqueta
    LEFT JOIN Paso p ON r.ID_Receta = p.ID_Receta
    GROUP BY r.ID_Receta
    ORDER BY r.ID_Receta DESC
    ";

    $st = db()->prepare($sql);
    $st->execute();

    return $st->fetchAll();
}
function crear_ingrediente($id_creador,$nombre,$grasas = null,$calorias = null,$proteina = null,$carbohidratos = null,$verificada = false) 
{
    $sql = "INSERT INTO Ingrediente (ID_Creador, Nombre, Grasas, Calorias, Proteina, Carbohidratos, Verificada) VALUES (:idc, :n, :g, :c, :p, :ca, :v)";
    try 
    {
        return db()->prepare($sql)->execute(
        [
            ':idc' => $id_creador,
            ':n'   => $nombre,
            ':g'   => $grasas,
            ':c'   => $calorias,
            ':p'   => $proteina,
            ':ca'  => $carbohidratos,
            ':v'   => $verificada
        ]);
    } 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}

function asociar_ingrediente_receta($id_receta, $id_ingrediente, $cantidad)
{
    $sql = "INSERT INTO Receta_Ingrediente (ID_Receta, ID_Ingrediente, Cantidad) VALUES (:idr, :idi, :cant)";
    try 
    {
        return db()->prepare($sql)->execute(
        [
            ':idr'  => $id_receta,
            ':idi'  => $id_ingrediente,
            ':cant' => $cantidad
        ]);
    } 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
function actualizar_ingrediente($id_ingrediente, $nombre = null, $grasas = null, $calorias = null, $proteina = null, $carbohidratos = null, $verificada = null)
{
    $campos = [];
    $params = [':id' => $id_ingrediente];

    if ($nombre !== null) $campos[] = "Nombre = :n"; $params[':n'] = $nombre;
    if ($grasas !== null) $campos[] = "Grasas = :g"; $params[':g'] = $grasas;
    if ($calorias !== null) $campos[] = "Calorias = :c"; $params[':c'] = $calorias;
    if ($proteina !== null) $campos[] = "Proteina = :p"; $params[':p'] = $proteina;
    if ($carbohidratos !== null) $campos[] = "Carbohidratos = :ca"; $params[':ca'] = $carbohidratos;
    if ($verificada !== null) $campos[] = "Verificada = :v"; $params[':v'] = $verificada;

    if (empty($campos)) return false;

    $sql = "UPDATE Ingrediente SET " . implode(', ', $campos) . " WHERE ID_Ingrediente = :id";

    try {return db()->prepare($sql)->execute($params);} 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
function borrar_ingrediente($id_ingrediente)
{
    try 
    {
        return db()->prepare("DELETE FROM Ingrediente WHERE ID_Ingrediente = :id")->execute([':id' => $id_ingrediente]);
    } catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
//============================Funciones sobre etiquetas======================
function crear_etiqueta($admin, $nombre) 
{
    if (!$admin['EsAdmin']) return false;
    $sql = "INSERT INTO Etiqueta (Nombre) VALUES (:n)";
    return db()->prepare($sql)->execute([':n' => $nombre]);
}
function borrar_etiqueta($id_etiqueta)
{
    try {return db()->prepare("DELETE FROM Etiqueta WHERE ID_Etiqueta = :id")->execute([':id' => $id_etiqueta]);} 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
//============================Funciones sobre colecciones======================
function crear_coleccion($id_creador, $nombre, $es_publica = true)
{
    $sql = "INSERT INTO Coleccion (ID_Creador, Nombre, EsPublica) VALUES (:idc, :n, :pub)";
    try 
    {
        return db()->prepare($sql)->execute(
        [
            ':idc' => $id_creador,
            ':n'   => $nombre,
            ':pub' => $es_publica
        ]);
    } 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
function agregar_receta_a_coleccion($id_receta, $id_coleccion)
{
    $sql = "INSERT INTO Coleccion_Receta (ID_Receta, ID_Coleccion) VALUES (:idr, :idc)";
    try 
    {
        return db()->prepare($sql)->execute(
        [
            ':idr' => $id_receta,
            ':idc' => $id_coleccion
        ]);
    } 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
function actualizar_coleccion($id_coleccion, $nombre = null, $es_publica = null)
{
    $campos = [];
    $params = [':id' => $id_coleccion];

    if ($nombre !== null) $campos[] = "Nombre = :n"; $params[':n'] = $nombre;
    if ($es_publica !== null) $campos[] = "EsPublica = :pub"; $params[':pub'] = $es_publica;

    if (empty($campos)) return false;

    $sql = "UPDATE Coleccion SET " . implode(', ', $campos) . " WHERE ID_Coleccion = :id";

    try {return db()->prepare($sql)->execute($params);} 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
function borrar_coleccion($id_coleccion)
{
    try {return db()->prepare("DELETE FROM Coleccion WHERE ID_Coleccion = :id")->execute([':id' => $id_coleccion]);} 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
function quitar_receta_de_coleccion($id_receta, $id_coleccion)
{
    try {return db()->prepare("DELETE FROM Coleccion_Receta WHERE ID_Receta = :idr AND ID_Coleccion = :idc")->execute([':idr' => $id_receta, ':idc' => $id_coleccion]);} 
    catch (PDOException $e) 
    {
        error_log($e->getMessage());
        return false;
    }
}
function listar_recetas_de_coleccion($id_coleccion)
{
    $sql = "SELECT r.*
            FROM Receta r
            JOIN Coleccion_Receta cr ON cr.ID_Receta = r.ID_Receta
            WHERE cr.ID_Coleccion = :idc";

    $st = db()->prepare($sql);
    $st->execute([':idc' => $id_coleccion]);
    return $st->fetchAll();
}