<?php

require_once __DIR__ . '/../../../core/db.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../model/ModoFitModel.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class ModoFitController
{
    private $model;

    public function __construct()
    {
        $db = Conexion::conectar();

        $this->model =
            new ModoFitModel($db);
    }
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }
        $user =
            $_SESSION['user'] ?? null;
        if (!$user)
        {
            header('Location: /pages/buscar');
            exit;
        }
        if (
            empty($user['ModoFit']) ||
            $user['ModoFit'] != 1
        )
        {
            header('Location: /pages/buscar');
            exit;
        }
        if ($this->faltanDatosFit($user))
        {
            require_once __DIR__
                . '/../../../pages/modofit/view/ModoFitWelcomeView.php';

            return;
        }
        $modoFitData =
            $this->model->obtenerDatosModoFit($user);

        extract($modoFitData);
        require_once __DIR__
            . '/../view/ModoFitView.php';
    }
    // ================= UPDATE EN TIEMPO REAL =================
    public function updateCantidad()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        ini_set('display_errors', 0);

        // ================= DATOS =================

        $meal =
            $_POST['meal'] ?? null;

        $cantidad =
            $_POST['cantidad'] ?? null;

        if (!$meal || !$cantidad)
        {
            echo json_encode([
                'error' => 'Datos inválidos'
            ]);

            exit;
        }

        if (!isset($_SESSION['user']['id']))
        {
            echo json_encode([
                'error' => 'Sesión no válida'
            ]);

            exit;
        }

        $userId =
            $_SESSION['user']['id'];

        $cantidad =
            max(1, (int)$cantidad);

        // ================= PLAN =================

        $plan =
            $this->model->obtenerPlanActual(
                $userId
            );

        if (!$plan)
        {
            echo json_encode([
                'error' => 'Plan no encontrado'
            ]);

            exit;
        }

        $planId =
            $plan['ID_Plan'];

        // ================= ACTUALIZAR =================

        $this->model->actualizarCantidadComida(
            $planId,
            $meal,
            $cantidad
        );

        // ================= TOTALES =================

        $totales =
            $this->model->obtenerTotalesPlan(
                $planId
            );

        // ================= COMIDA ACTUAL =================

        $mealTotals =
            $this->model->obtenerTotalesComida(
                $planId,
                $meal
            );

        // ================= OBJETIVOS =================

        $objetivos =
            $this->model->obtenerObjetivosNutricionales(
                $_SESSION['user'],
                $plan
            );

        // ================= RESPUESTA =================

        echo json_encode([
            'success' => true,

            // TOTALES
            'totalCal' =>
                round($totales['calorias'], 2),

            'totalProt' =>
                round($totales['proteinas'], 2),

            'totalCarb' =>
                round($totales['carbohidratos'], 2),

            'totalFat' =>
                round($totales['grasas'], 2),

            // OBJETIVOS
            'objetivoCalorias' =>
                round($objetivos['calorias'], 2),

            'protObj' =>
                round($objetivos['proteinas'], 2),

            'carbObj' =>
                round($objetivos['carbohidratos'], 2),

            'fatObj' =>
                round($objetivos['grasas'], 2),

            // PORCENTAJES
            'protPerc' =>
                ($totales['proteinas'] /
                    $objetivos['proteinas']) * 100,

            'carbPerc' =>
                ($totales['carbohidratos'] /
                    $objetivos['carbohidratos']) * 100,

            'fatPerc' =>
                ($totales['grasas'] /
                    $objetivos['grasas']) * 100,

            // COMIDA ACTUAL
            'cantidad' =>
                $cantidad,

            'mealCal' =>
                round($mealTotals['calorias'], 2),

            'mealProt' =>
                round($mealTotals['proteinas'], 2),

            'mealCarb' =>
                round($mealTotals['carbohidratos'], 2),

            'mealFat' =>
                round($mealTotals['grasas'], 2)
        ]);

        exit;
    }
    // PRE VISUALIZACION DEL PLAN

    public function previewPlan()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;

        $id = $_GET['id'] ?? null;

        if (!$id || !$userId) {
            exit("Error");
        }

        $preview =
            $this->model->obtenerPreviewPlan(
                $userId,
                $id
            );

        if (!$preview) {
            exit("Plan no encontrado");
        }

        $plan = $preview['plan'];

        $comidas = $preview['comidas'];

        // ================= HTML =================

        echo "
        <h3 style='margin-bottom:15px;'>
            " . htmlspecialchars($plan['Nombre']) . "
        </h3>

        <p>
            <strong>Objetivo:</strong>
            " . ucfirst($plan['Objetivo']) . "
        </p>

        <p>
            <strong>Intensidad:</strong>
            " . ucfirst(
                str_replace('_', ' ', $plan['Intensidad'])
            ) . "
        </p>

        <p>
            <strong>Comidas:</strong>
            " . $plan['NumComidas'] . "
        </p>

        <hr>
    ";

        foreach ($comidas as $c)
        {
            echo "
        <div style='
            padding:12px;
            border:1px solid #eee;
            border-radius:12px;
            margin-bottom:10px;
        '>

            <strong>" . ucfirst($c['Tipo']) . "</strong>

            <p style='margin:5px 0;font-weight:600;'>
                " . htmlspecialchars($c['Titulo']) . "
            </p>

            <p style='font-size:14px;color:#666;margin:0;'>
                {$c['kcal']} kcal ·
                P {$c['prot']}g ·
                C {$c['carb']}g ·
                G {$c['fat']}g
            </p>

        </div>
        ";
        }
    }
    //GUARDAR DATOS FIT DEL USUARIO

    public function saveFitData()
    {
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        $userId =
            $_SESSION['user']['id'] ?? null;

        if (!$userId)
        {
            die("Usuario inválido");
        }

        $sexo =
            $_POST['sexo'] ?? null;

        $edad =
            $_POST['edad'] ?? null;

        $altura =
            $_POST['altura'] ?? null;

        $peso =
            $_POST['peso'] ?? null;

        $actividad =
            $_POST['actividad'] ?? null;

        $resultado =
            $this->model->guardarDatosFitUsuario(
                $userId,
                $sexo,
                $edad,
                $altura,
                $peso,
                $actividad
            );

        if (!$resultado)
        {
            die("Error al guardar datos");
        }

        // actualizar sesión

        $_SESSION['user']['Sexo'] =
            $sexo;

        $_SESSION['user']['Edad'] =
            $edad;

        $_SESSION['user']['Altura'] =
            $altura;

        $_SESSION['user']['Peso'] =
            $peso;

        $_SESSION['user']['NivelActividad'] =
            $actividad;

        header("Location: /pages/modofit");

        exit;
    }
    public function updateFitRealtime()
    {
        header('Content-Type: application/json');

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            echo json_encode([
                'error' => 'Usuario inválido'
            ]);

            exit;
        }

        $peso =
            (float)($_POST['peso'] ?? 0);

        $altura =
            (float)($_POST['altura'] ?? 0);

        $edad =
            (int)($_POST['edad'] ?? 0);

        $sexo =
            $_POST['sexo'] ?? 'hombre';

        $actividad =
            $_POST['actividad'] ?? 'moderado';

        // ================= UPDATE USER =================

        $this->model->actualizarUsuarioFit(
            $userId,
            $peso,
            $altura,
            $edad,
            $sexo,
            $actividad
        );

        // ================= UPDATE SESSION =================

        $_SESSION['user']['Peso'] = $peso;
        $_SESSION['user']['Altura'] = $altura;
        $_SESSION['user']['Edad'] = $edad;
        $_SESSION['user']['Sexo'] = $sexo;
        $_SESSION['user']['NivelActividad'] = $actividad;

        // ================= PLAN =================

        $plan =
            $this->model->obtenerPlanActual(
                $userId
            );

        if (!$plan)
        {
            echo json_encode([
                'error' => 'Plan no encontrado'
            ]);

            exit;
        }

        // ================= DATOS =================

        $totales =
            $this->model->obtenerTotalesPlan(
                $plan['ID_Plan']
            );

        $objetivos =
            $this->model->obtenerObjetivosNutricionales(
                $_SESSION['user'],
                $plan
            );

        $porcentajes =
            $this->model->calcularPorcentajesMacros(
                $totales,
                $objetivos
            );

        // ================= RESPONSE =================

        echo json_encode([
            'success' => true,

            // OBJETIVOS
            'objetivoCalorias' =>
                round($objetivos['calorias']),

            'protObj' =>
                round($objetivos['proteinas']),

            'carbObj' =>
                round($objetivos['carbohidratos']),

            'fatObj' =>
                round($objetivos['grasas']),

            // TOTALES
            'totalCal' =>
                round($totales['calorias'], 2),

            'totalProt' =>
                round($totales['proteinas'], 2),

            'totalCarb' =>
                round($totales['carbohidratos'], 2),

            'totalFat' =>
                round($totales['grasas'], 2),

            // PORCENTAJES
            'calPerc' =>
                round($porcentajes['calorias'], 2),

            'protPerc' =>
                round($porcentajes['proteinas'], 2),

            'carbPerc' =>
                round($porcentajes['carbohidratos'], 2),

            'fatPerc' =>
                round($porcentajes['grasas'], 2)
        ]);

        exit;
    }
    //CAMBIAR OBJETIVO
    // ================= CAMBIAR OBJETIVO + INTENSIDAD =================

    public function changeObjetivo()
    {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $objetivo =
            $_POST['objetivo'] ?? null;
        $intensidad =
            $_POST['intensidad'] ?? 'moderado';
        if (!$objetivo)
        {
            echo json_encode([
                'error' => 'Objetivo no recibido'
            ]);

            exit;
        }
        if (!isset($_SESSION['user']['id']))
        {
            echo json_encode([
                'error' => 'Sesión inválida'
            ]);
            exit;
        }
        $permitidosObjetivo = [
            'mantenimiento',
            'volumen',
            'definicion'
        ];
        $permitidosIntensidad = [
            'muy_leve',
            'leve',
            'moderado',
            'agresivo',
            'muy_agresivo'
        ];
        if (!in_array($objetivo, $permitidosObjetivo)) {
            echo json_encode([
                'error' => 'Objetivo inválido'
            ]);

            exit;
        }
        if (!in_array($intensidad, $permitidosIntensidad))
        {
            $intensidad = 'moderado';
        }
        $resultado =
            $this->model->cambiarObjetivoPlan(
                $_SESSION['user'],
                $objetivo,
                $intensidad
            );

        if (!$resultado)
        {
            echo json_encode([
                'error' => 'Plan no encontrado'
            ]);

            exit;
        }
        echo json_encode($resultado);
        exit;
    }
    //FALTAN DATOS USUARIO
    private function faltanDatosFit($user)
    {
        return 
        (
            empty($user['Sexo']) ||
            empty($user['Edad']) ||
            empty($user['Altura']) ||
            empty($user['Peso']) ||
            empty($user['NivelActividad'])
        );
    }
    //GUARDAR Y CARGAR PLANES
    public function savePlan()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            die("Usuario inválido");
        }

        $nombre = trim($_POST['nombre'] ?? '');

        if (!$nombre) {
            $nombre = "Mi Plan";
        }

        $resultado = $this->model->guardarPlan(
            $userId,
            $nombre
        );

        if (!$resultado) {
            die("Error al guardar plan");
        }

        header("Location: /pages/modofit");
        exit;
    }
    //CARGAR PLAN GUARDADO
    public function loadPlan()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['user']['id'] ?? null;
        $id = $_GET['id'] ?? null;

        if (!$userId) {
            die("Usuario inválido");
        }
        if (!$id) {
            die("Plan inválido");
        }
        $resultado = $this->model->cargarPlanGuardado(
            $userId,
            $id
        );
        if (!$resultado) {
            die("No se pudo cargar el plan");
        }
        header("Location: /pages/modofit");
        exit;
    }
    //BORRAR PLAN GUARDADO
    public function deletePlan()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['user']['id'] ?? null;
        $id = $_GET['id'] ?? null;
        if (!$userId) {
            die("Usuario inválido");
        }
        if (!$id) {
            die("Plan inválido");
        }
        $resultado = $this->model->eliminarPlanGuardado(
            $userId,
            $id
        );
        if (!$resultado) {
            die("No se pudo eliminar el plan");
        }
        header("Location: /pages/modofit");
        exit;
    }
    // ================= CAMBIAR NÚMERO DE COMIDAS =================

    public function setMeals()
    {
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId)
        {
            die("Usuario inválido");
        }

        $num = (int)($_GET['num'] ?? 4);

        $num = max(
            1,
            min(6, $num)
        );

        $this->model->actualizarNumeroComidas(
            $userId,
            $num
        );

        header(
            "Location: /pages/modofit?reload="
            . time()
        );

        exit;
    }

// ================= AÑADIR RECETA =================

    public function addRecipe()
    {
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId)
        {
            die("Usuario inválido");
        }

        $meal = $_GET['meal'] ?? null;

        $recipe = (int)(
            $_GET['recipe'] ?? 0
        );

        if (!$meal || !$recipe)
        {
            die("Datos incompletos");
        }

        $this->model->asignarReceta(
            $userId,
            $meal,
            $recipe
        );

        header(
            "Location: /pages/modofit?meal="
            . urlencode($meal)
            . "&added=1"
        );

        exit;
    }
    public function removeMeal()
    {
        header(
            'Content-Type: application/json'
        );
        if (
            empty(
            $_SESSION['user']['id']
            )
        )
        {
            echo json_encode([
                "error" => true
            ]);

            exit;
        }
        $meal =
            $_POST['meal']
            ?? '';
        if (!$meal)
        {
            echo json_encode([
                "error" => true
            ]);

            exit;
        }
        $plan =
            $this->model->obtenerPlanActual(
                $_SESSION['user']['id']
            );
        if (!$plan)
        {
            echo json_encode([
                "error" => true,
                "msg" => "Plan no encontrado"
            ]);

            exit;
        }
        $ok =
            $this->model->eliminarComida(
                $plan['ID_Plan'],
                $meal
            );
        echo json_encode([
            "success" => $ok
        ]);
    }

}