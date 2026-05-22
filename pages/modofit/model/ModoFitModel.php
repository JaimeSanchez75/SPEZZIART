<?php
require_once __DIR__ . '/../../../core/db.php';
class ModoFitModel
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }
    public function esModoFitActivo($userId)
    {
        $stmt = $this->db->prepare("
        SELECT ModoFit
        FROM Usuario
        WHERE ID_Usuario = ?
        LIMIT 1
    ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (
            $row &&
            $row['ModoFit'] == 1
        );
    }
    public function obtenerDatosModoFit($user)
    {
        $userId =
            $user['id'];

        $plan =
            $this->obtenerOCrearPlanActual(
                $userId
            );

        $meals =
            $this->obtenerMealsPlan(
                $plan['ID_Plan']
            );

        $total =
            $this->calcularTotalesMeals(
                $meals
            );

        $objetivos =
            $this->obtenerObjetivosNutricionales(
                $user,
                $plan
            );

        $planes =
            $this->obtenerPlanesGuardados(
                $userId
            );

        return [
            'plan' => $plan,
            'meals' => $meals,
            'total' => $total,

            'objetivoCalorias' =>
                $objetivos['calorias'],

            'proteinasObjetivo' =>
                $objetivos['proteinas'],

            'carbsObjetivo' =>
                $objetivos['carbohidratos'],

            'grasasObjetivo' =>
                $objetivos['grasas'],

            'planes' => $planes
        ];
    }
    public function obtenerPlanesGuardados(
        $userId
    )
    {
        $stmt =
            $this->db->prepare(
                "
            SELECT *
            FROM Planes_Guardados
            WHERE ID_Usuario = ?
            ORDER BY ID_Guardado DESC
            LIMIT 10
            "
            );

        $stmt->execute([
            $userId
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
    public function obtenerOCrearPlanActual(
        $userId
    )
    {
        $today =
            date('Y-m-d');

        $stmt =
            $this->db->prepare(
                "
            SELECT *
            FROM Planes
            WHERE ID_Usuario = ?
            AND Fecha = ?
            LIMIT 1
            "
            );

        $stmt->execute([
            $userId,
            $today
        ]);

        $plan =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if ($plan)
        {
            return $plan;
        }

        $stmt =
            $this->db->prepare(
                "
            INSERT INTO Planes
            (
                ID_Usuario,
                Fecha,
                Objetivo,
                Intensidad,
                NumComidas
            )
            VALUES (?, ?, ?, ?, ?)
            "
            );

        $stmt->execute([
            $userId,
            $today,
            'mantenimiento',
            'moderado',
            4
        ]);

        $planId =
            $this->db->lastInsertId();

        $this->crearComidasBase(
            $planId,
            4
        );

        return [
            'ID_Plan' => $planId,
            'ID_Usuario' => $userId,
            'Fecha' => $today,
            'Objetivo' => 'mantenimiento',
            'Intensidad' => 'moderado',
            'NumComidas' => 4
        ];
    }
    public function obtenerMealsPlan($planId)
    {
        $stmt = $this->db->prepare("
        SELECT 
            pc.Tipo,
            pc.NombrePersonalizado,
            pc.ID_Receta,
            pc.Cantidad,
            r.Titulo,
            r.Imagen
        FROM Plan_Comidas pc
        LEFT JOIN Receta r 
            ON pc.ID_Receta = r.ID_Receta
        WHERE pc.ID_Plan = ?
        ORDER BY pc.Orden ASC
    ");

        $stmt->execute([$planId]);

        $rawMeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $meals = [];

        foreach ($rawMeals as $m)
        {
            if (!empty($m['ID_Receta']))
            {
                $nutricion =
                    $this->calcularNutricionReceta(
                        $m['ID_Receta']
                    );

                $cantidad =
                    (int)($m['Cantidad'] ?? 100);

                $factor = $cantidad / 100;

                $meals[$m['Tipo']] =
                    [
                        'Imagen' => $m['Imagen'],

                        'Titulo' =>
                            $m['NombrePersonalizado']
                            ?? $m['Titulo'],

                        'Calorias' =>
                            $nutricion['Calorias'] * $factor,

                        'Proteinas' =>
                            $nutricion['Proteinas'] * $factor,

                        'Carbohidratos' =>
                            $nutricion['Carbohidratos'] * $factor,

                        'Grasas' =>
                            $nutricion['Grasas'] * $factor,

                        'Cantidad' => $cantidad
                    ];
            }
            else
            {
                $meals[$m['Tipo']] = null;
            }
        }

        return $meals;
    }
    public function cargarPlanGuardado(
        int $userId,
        int $guardadoId
    )
    {
        $today = date('Y-m-d');

        // ================= PLAN GUARDADO =================

        $stmt = $this->db->prepare("
        SELECT *
        FROM Planes_Guardados
        WHERE ID_Guardado = ?
        AND ID_Usuario = ?
        LIMIT 1
    ");

        $stmt->execute([
            $guardadoId,
            $userId
        ]);

        $saved = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$saved) {
            return false;
        }

        // ================= PLAN ACTIVO =================

        $stmt = $this->db->prepare("
        SELECT ID_Plan
        FROM Planes
        WHERE ID_Usuario = ?
        AND Fecha = ?
        LIMIT 1
    ");

        $stmt->execute([
            $userId,
            $today
        ]);

        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            return false;
        }

        $planId = $plan['ID_Plan'];

        // ================= UPDATE PLAN =================

        $stmt = $this->db->prepare("
        UPDATE Planes
        SET Objetivo = ?,
            Intensidad = ?,
            NumComidas = ?
        WHERE ID_Plan = ?
    ");

        $stmt->execute([
            $saved['Objetivo'],
            $saved['Intensidad'],
            $saved['NumComidas'],
            $planId
        ]);

        // ================= LIMPIAR COMIDAS =================

        $stmt = $this->db->prepare("
        DELETE FROM Plan_Comidas
        WHERE ID_Plan = ?
    ");

        $stmt->execute([$planId]);

        // ================= COMIDAS GUARDADAS =================

        $stmt = $this->db->prepare("
        SELECT *
        FROM Planes_Guardados_Comidas
        WHERE ID_Guardado = ?
        ORDER BY OrdenComida ASC
    ");

        $stmt->execute([$guardadoId]);

        $comidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ================= INSERTAR =================

        $insert = $this->db->prepare("
        INSERT INTO Plan_Comidas
        (
            ID_Plan,
            Tipo,
            ID_Receta,
            Cantidad,
            Orden
        )
        VALUES (?, ?, ?, ?, ?)
    ");

        foreach ($comidas as $c)
        {
            $insert->execute([
                $planId,
                $c['Tipo'],
                $c['ID_Receta'],
                $c['Cantidad'],
                $c['OrdenComida']
            ]);
        }

        return true;
    }
    public function eliminarPlanGuardado(
        $userId,
        $guardadoId
    )
    {
        // borrar comidas
        $stmt = $this->db->prepare(
            "
        DELETE FROM Planes_Guardados_Comidas
        WHERE ID_Guardado = ?
        "
        );

        $stmt->execute([$guardadoId]);

        // borrar plan
        $stmt = $this->db->prepare(
            "
        DELETE FROM Planes_Guardados
        WHERE ID_Guardado = ?
        AND ID_Usuario = ?
        "
        );

        return $stmt->execute([
            $guardadoId,
            $userId
        ]);
    }
    public function obtenerPreviewPlan(
        $userId,
        $guardadoId
    )
    {
        // ================= PLAN =================

        $stmt = $this->db->prepare(
            "
        SELECT *
        FROM Planes_Guardados
        WHERE ID_Guardado = ?
        AND ID_Usuario = ?
        LIMIT 1
        "
        );

        $stmt->execute([
            $guardadoId,
            $userId
        ]);

        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            return false;
        }

        // ================= COMIDAS =================

        $stmt = $this->db->prepare(
            "
        SELECT 
            pgc.Tipo,
            pgc.Cantidad,
            pgc.ID_Receta,
            r.Titulo
        FROM Planes_Guardados_Comidas pgc
        LEFT JOIN Receta r
            ON pgc.ID_Receta = r.ID_Receta
        WHERE pgc.ID_Guardado = ?
        ORDER BY pgc.OrdenComida ASC
        "
        );

        $stmt->execute([$guardadoId]);

        $rawComidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $comidas = [];

        foreach ($rawComidas as $c)
        {
            if (!$c['ID_Receta']) {
                continue;
            }

            $nutricion =
                $this->calcularNutricionReceta(
                    $c['ID_Receta']
                );

            $factor =
                ((float)($c['Cantidad'] ?? 100)) / 100;

            $comidas[] = [
                'Tipo' =>
                    $c['Tipo'],

                'Titulo' =>
                    $c['Titulo'],

                'kcal' =>
                    round(
                        $nutricion['Calorias'] * $factor
                    ),

                'prot' =>
                    round(
                        $nutricion['Proteinas'] * $factor
                    ),

                'carb' =>
                    round(
                        $nutricion['Carbohidratos'] * $factor
                    ),

                'fat' =>
                    round(
                        $nutricion['Grasas'] * $factor
                    )
            ];
        }

        return [
            'plan' => $plan,
            'comidas' => $comidas
        ];
    }
    public function cambiarObjetivoPlan(
        $user,
        $objetivo,
        $intensidad
    )
    {
        $userId = $user['id'];

        $today = date('Y-m-d');

        // ================= PLAN =================

        $stmt = $this->db->prepare(
            "
        SELECT ID_Plan
        FROM Planes
        WHERE ID_Usuario = ?
        AND Fecha = ?
        LIMIT 1
        "
        );

        $stmt->execute([
            $userId,
            $today
        ]);

        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            return false;
        }

        $planId = $plan['ID_Plan'];

        // ================= UPDATE =================

        $stmt = $this->db->prepare(
            "
        UPDATE Planes
        SET Objetivo = ?,
            Intensidad = ?
        WHERE ID_Plan = ?
        "
        );

        $stmt->execute([
            $objetivo,
            $intensidad,
            $planId
        ]);

        // ================= OBJETIVOS =================

        $objetivoCalorias =
            $this->calcularCalorias(
                $user,
                $objetivo,
                $intensidad
            );

        $peso =
            (float)($user['Peso'] ?? 70);

        $macros =
            $this->calcularMacrosObjetivo(
                $peso,
                $objetivo,
                $intensidad
            );

        $protObj =
            $macros['proteinas'];

        $fatObj =
            $macros['grasas'];

        $caloriasRestantes =
            $objetivoCalorias -
            (($protObj * 4) + ($fatObj * 9));

        $carbObj =
            max(30, $caloriasRestantes / 4);

        // ================= TOTALES =================

        $totales =
            $this->calcularTotalesPlan(
                $planId
            );

        // ================= RESPONSE =================

        return [
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
                round($objetivoCalorias, 2),

            'protObj' =>
                round($protObj, 2),

            'carbObj' =>
                round($carbObj, 2),

            'fatObj' =>
                round($fatObj, 2),

            // PORCENTAJES
            'calPerc' => min(
                100,
                ($totales['calorias'] / max(1, $objetivoCalorias)) * 100
            ),

            'protPerc' => min(
                100,
                ($totales['proteinas'] / max(1, $protObj)) * 100
            ),

            'carbPerc' => min(
                100,
                ($totales['carbohidratos'] / max(1, $carbObj)) * 100
            ),

            'fatPerc' => min(
                100,
                ($totales['grasas'] / max(1, $fatObj)) * 100
            )
        ];
    }
    //GUARDAR DATOS FIT USUARIO

    public function guardarDatosFitUsuario(
        $userId,
        $sexo,
        $edad,
        $altura,
        $peso,
        $actividad
    )
    {
        $stmt =
            $this->db->prepare(
                "
            UPDATE Usuario
            SET Sexo = ?,
                Edad = ?,
                Altura = ?,
                Peso = ?,
                NivelActividad = ?
            WHERE ID_Usuario = ?
            "
            );

        return $stmt->execute([
            $sexo,
            $edad,
            $altura,
            $peso,
            $actividad,
            $userId
        ]);
    }
    public function calcularTotalesMeals($meals)
    {
        $total =
            [
                'calorias' => 0,
                'proteinas' => 0,
                'carbohidratos' => 0,
                'grasas' => 0
            ];

        foreach ($meals as $meal)
        {
            if ($meal)
            {
                $total['calorias'] += $meal['Calorias'];

                $total['proteinas'] += $meal['Proteinas'];

                $total['carbohidratos'] += $meal['Carbohidratos'];

                $total['grasas'] += $meal['Grasas'];
            }
        }

        return $total;
    }
    public function guardarPlan(
        int $userId,
        string $nombre
    )
    {
        date_default_timezone_set('Europe/Madrid');

        $today = date('Y-m-d');

        $fechaGuardado = date('Y-m-d H:i:s');

        // máximo 30
        $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM Planes_Guardados
        WHERE ID_Usuario = ?
    ");

        $stmt->execute([$userId]);

        if ($stmt->fetchColumn() >= 30) {
            return false;
        }

        // plan actual
        $stmt = $this->db->prepare("
        SELECT *
        FROM Planes
        WHERE ID_Usuario = ?
        AND Fecha = ?
        LIMIT 1
    ");

        $stmt->execute([$userId, $today]);

        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            return false;
        }

        // crear guardado
        $stmt = $this->db->prepare("
        INSERT INTO Planes_Guardados
        (
            ID_Usuario,
            Nombre,
            Objetivo,
            Intensidad,
            NumComidas,
            Fecha
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

        $stmt->execute([
            $userId,
            $nombre,
            $plan['Objetivo'],
            $plan['Intensidad'],
            $plan['NumComidas'],
            $fechaGuardado
        ]);

        $guardadoId = $this->db->lastInsertId();

        // comidas
        $stmt = $this->db->prepare("
        SELECT *
        FROM Plan_Comidas
        WHERE ID_Plan = ?
        ORDER BY Orden ASC
    ");

        $stmt->execute([$plan['ID_Plan']]);

        $comidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $insert = $this->db->prepare("
        INSERT INTO Planes_Guardados_Comidas
        (
            ID_Guardado,
            Tipo,
            ID_Receta,
            Cantidad,
            OrdenComida
        )
        VALUES (?, ?, ?, ?, ?)
    ");

        foreach ($comidas as $c)
        {
            $insert->execute([
                $guardadoId,
                $c['Tipo'],
                $c['ID_Receta'],
                $c['Cantidad'],
                $c['Orden'] ?? 0
            ]);
        }

        return true;
    }
    public function asignarRecetaAComida(
        $planId,
        $meal,
        $idReceta
    )
    {
        $sql = "
            UPDATE Plan_Comidas
            SET ID_Receta = ?
            WHERE ID_Plan = ?
            AND Tipo = ?
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $idReceta,
            $planId,
            $meal
        ]);
    }

    public function calcularNutricionReceta($idReceta)
    {
        $stmt = $this->db->prepare("
            SELECT
                ri.Cantidad,
                i.Calorias,
                i.Proteina,
                i.Carbohidratos,
                i.Grasas

            FROM Receta_Ingrediente ri

            INNER JOIN Ingrediente i
                ON ri.ID_Ingrediente = i.ID_Ingrediente

            WHERE ri.ID_Receta = ?
        ");

        $stmt->execute([$idReceta]);

        $ingredientes =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totales = [
            'Calorias' => 0,
            'Proteinas' => 0,
            'Carbohidratos' => 0,
            'Grasas' => 0
        ];

        foreach ($ingredientes as $ing)
        {
            $cantidad =
                (float)($ing['Cantidad'] ?? 0);

            $factor =
                $cantidad / 100;

            $totales['Calorias'] +=
                ((float)$ing['Calorias'])
                * $factor;

            $totales['Proteinas'] +=
                ((float)$ing['Proteina'])
                * $factor;

            $totales['Carbohidratos'] +=
                ((float)$ing['Carbohidratos'])
                * $factor;

            $totales['Grasas'] +=
                ((float)$ing['Grasas'])
                * $factor;
        }

        return $totales;
    }
    public function calcularTotalesPlan($planId)
    {
        $stmt = $this->db->prepare("
        SELECT
            pc.Tipo,
            pc.Cantidad,
            pc.ID_Receta

        FROM Plan_Comidas pc

        WHERE pc.ID_Plan = ?
    ");

        $stmt->execute([$planId]);

        $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalCal = 0;

        $totalProt = 0;

        $totalCarb = 0;

        $totalFat = 0;

        foreach ($meals as $m)
        {
            if (empty($m['ID_Receta']))
            {
                continue;
            }

            $nutricion =
                $this->calcularNutricionReceta(
                    $m['ID_Receta']
                );

            $factor =
                ((float)($m['Cantidad'] ?? 100))
                / 100;

            $totalCal +=
                $nutricion['Calorias']
                * $factor;

            $totalProt +=
                $nutricion['Proteinas']
                * $factor;

            $totalCarb +=
                $nutricion['Carbohidratos']
                * $factor;

            $totalFat +=
                $nutricion['Grasas']
                * $factor;
        }

        return [
            'calorias' => round(
                $totalCal,
                2
            ),

            'proteinas' => round(
                $totalProt,
                2
            ),

            'carbohidratos' => round(
                $totalCarb,
                2
            ),

            'grasas' => round(
                $totalFat,
                2
            )
        ];
    }
    public function crearComidasBase(
        $planId,
        $numComidas
    )
    {
        $tiposBase = [
            'desayuno',
            'comida',
            'cena',
            'snack',
            'extra1',
            'extra2'
        ];

        $tipos =
            array_slice(
                $tiposBase,
                0,
                $numComidas
            );

        $stmt = $this->db->prepare("
        INSERT INTO Plan_Comidas
        (
            ID_Plan,
            Tipo
        )
        VALUES
        (
            ?,
            ?
        )
    ");

        foreach ($tipos as $tipo)
        {
            $stmt->execute([
                $planId,
                $tipo
            ]);
        }

        return true;
    }
    public function calcularMacrosObjetivo(
        $peso,
        $objetivo,
        $intensidad = 'moderado'
    )
    {
        switch($objetivo)
        {
            case 'volumen':

                $proteinas = $peso * 2.2;

                $grasas = match($intensidad)
                {
                    'muy_leve' => $peso * 0.8,
                    'leve' => $peso * 0.9,
                    'moderado' => $peso * 1,
                    'agresivo' => $peso * 1.1,
                    'muy_agresivo' => $peso * 1.2,
                    default => $peso * 1
                };

                break;

            case 'definicion':

                $proteinas = match($intensidad)
                {
                    'muy_leve' => $peso * 2.2,
                    'leve' => $peso * 2.3,
                    'moderado' => $peso * 2.5,
                    'agresivo' => $peso * 2.7,
                    'muy_agresivo' => $peso * 3,
                    default => $peso * 2.5
                };

                $grasas = match($intensidad)
                {
                    'muy_leve' => $peso * 0.8,
                    'leve' => $peso * 0.7,
                    'moderado' => $peso * 0.6,
                    'agresivo' => $peso * 0.5,
                    'muy_agresivo' => $peso * 0.45,
                    default => $peso * 0.6
                };

                break;

            default: // mantenimiento

                $proteinas = $peso * 2;

                $grasas = match($intensidad)
                {
                    'muy_leve' => $peso * 0.7,
                    'leve' => $peso * 0.75,
                    'moderado' => $peso * 0.8,
                    'agresivo' => $peso * 0.9,
                    'muy_agresivo' => $peso * 1,
                    default => $peso * 0.8
                };

                break;
        }

        return [
            'proteinas' => $proteinas,
            'grasas' => $grasas
        ];
    }
    public function calcularCalorias(
        $user,
        $objetivo,
        $intensidad = 'moderado'
    )
    {
        if
        (
            empty($user['Peso']) ||
            empty($user['Altura']) ||
            empty($user['Edad']) ||
            empty($user['Sexo'])
        )
        {
            return 2000;
        }

        $peso = (float)$user['Peso'];

        $altura = (float)$user['Altura'];

        $edad = (int)$user['Edad'];

        $sexo = strtolower($user['Sexo']);

        $actividad =
            strtolower(
                $user['NivelActividad']
                ?? 'moderado'
            );

        // ================= TMB =================

        if ($sexo === 'hombre')
        {
            $tmb =
                88.362 +
                (13.397 * $peso) +
                (4.799 * $altura) -
                (5.677 * $edad);
        }
        else
        {
            $tmb =
                447.593 +
                (9.247 * $peso) +
                (3.098 * $altura) -
                (4.330 * $edad);
        }

        // ================= ACTIVIDAD =================

        $factorActividad = match($actividad)
        {
            'sedentario' => 1.2,
            'ligero' => 1.375,
            'moderado' => 1.55,
            'activo' => 1.725,
            'muy_activo' => 1.9,
            'extremo' => 1.9,

            default => 1.55
        };

        $mantenimiento =
            $tmb * $factorActividad;

        // ================= AJUSTE =================

        $ajuste = 0;

        if ($objetivo === 'volumen')
        {
            $ajuste = match($intensidad)
            {
                'muy_leve' => 150,
                'leve' => 250,
                'moderado' => 350,
                'agresivo' => 500,
                'muy_agresivo' => 700,

                default => 350
            };
        }

        if ($objetivo === 'definicion')
        {
            $ajuste = match($intensidad)
            {
                'muy_leve' => -150,
                'leve' => -250,
                'moderado' => -400,
                'agresivo' => -550,
                'muy_agresivo' => -700,

                default => -400
            };
        }

        return round(
            $mantenimiento + $ajuste
        );
    }
    public function actualizarNumeroComidas(
        $userId,
        $num
    )
    {
        $today = date('Y-m-d');

        $stmt = $this->db->prepare("
        SELECT ID_Plan
        FROM Planes
        WHERE ID_Usuario = ?
        AND Fecha = ?
        LIMIT 1
    ");

        $stmt->execute([
            $userId,
            $today
        ]);

        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan)
        {
            throw new Exception(
                "Plan no encontrado"
            );
        }

        $planId = (int)$plan['ID_Plan'];

        $stmt = $this->db->prepare("
        UPDATE Planes
        SET NumComidas = ?
        WHERE ID_Plan = ?
    ");

        $stmt->execute([
            $num,
            $planId
        ]);
        $tiposBase = [
            'desayuno',
            'comida',
            'cena',
            'snack',
            'extra1',
            'extra2'
        ];
        $tipos = array_slice(
            $tiposBase,
            0,
            $num
        );
        $stmtDelete = $this->db->prepare("
        DELETE FROM Plan_Comidas
        WHERE ID_Plan = ?
    ");
        $stmtDelete->execute([
            $planId
        ]);
        $stmtInsert = $this->db->prepare("
        INSERT INTO Plan_Comidas
        (
            ID_Plan,
            Tipo,
            ID_Receta,
            Cantidad,
            Orden
        )
        VALUES
        (
            ?,
            ?,
            NULL,
            100,
            ?
        )
    ");
        foreach ($tipos as $index => $tipo)
        {
            $stmtInsert->execute([
                $planId,
                $tipo,
                $index
            ]);
        }
        return true;
    }
    public function asignarReceta(
        $userId,
        $meal,
        $recipe
    )
    {
        $stmt = $this->db->prepare("
        SELECT ID_Receta
        FROM Receta
        WHERE ID_Receta = ?
        LIMIT 1
    ");
        $stmt->execute([
            $recipe
        ]);
        if (!$stmt->fetch()) {
            throw new Exception(
                "Receta inválida"
            );
        }
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
        SELECT ID_Plan
        FROM Planes
        WHERE ID_Usuario = ?
        AND Fecha = ?
        LIMIT 1
    ");
        $stmt->execute([
            $userId,
            $today
        ]);
        $plan = $stmt->fetch(
            PDO::FETCH_ASSOC
        );
        if (!$plan)
        {
            throw new Exception(
                "Plan no encontrado"
            );
        }
        $planId = (int)$plan['ID_Plan'];
        $stmt = $this->db->prepare("
        SELECT ID_PlanComida
        FROM Plan_Comidas
        WHERE ID_Plan = ?
        AND Tipo = ?
        LIMIT 1
    ");

        $stmt->execute([
            $planId,
            $meal
        ]);

        $exists = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if ($exists)
        {
            $stmt = $this->db->prepare("
            UPDATE Plan_Comidas
            SET ID_Receta = ?
            WHERE ID_Plan = ?
            AND Tipo = ?
        ");

            $stmt->execute([
                $recipe,
                $planId,
                $meal
            ]);
        }
        else
        {
            $stmt = $this->db->prepare("
            INSERT INTO Plan_Comidas
            (
                ID_Plan,
                Tipo,
                ID_Receta,
                Cantidad,
                Orden
            )
            VALUES
            (
                ?,
                ?,
                ?,
                100,
                0
            )
        ");
            $stmt->execute([
                $planId,
                $meal,
                $recipe
            ]);
        }
        return true;
    }
    public function obtenerPlanActual($userId)
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
        SELECT *
        FROM Planes
        WHERE ID_Usuario = ?
        AND Fecha = ?
        LIMIT 1
    ");
        $stmt->execute([
            $userId,
            $today
        ]);
        return $stmt->fetch(
            PDO::FETCH_ASSOC
        );
    }
    public function actualizarCantidadComida(
        $planId,
        $meal,
        $cantidad
    )
    {
        $stmt = $this->db->prepare("
        UPDATE Plan_Comidas
        SET Cantidad = ?
        WHERE ID_Plan = ?
        AND Tipo = ?
    ");
        return $stmt->execute([
            $cantidad,
            $planId,
            $meal
        ]);
    }
    public function obtenerComidasPlan($planId)
    {
        $stmt = $this->db->prepare("
        SELECT
            Tipo,
            Cantidad,
            ID_Receta
        FROM Plan_Comidas
        WHERE ID_Plan = ?
    ");
        $stmt->execute([
            $planId
        ]);
        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
    public function obtenerComida(
        $planId,
        $meal
    )
    {
        $stmt = $this->db->prepare("
        SELECT
            Cantidad,
            ID_Receta
        FROM Plan_Comidas
        WHERE ID_Plan = ?
        AND Tipo = ?
        LIMIT 1
    ");
        $stmt->execute([
            $planId,
            $meal
        ]);
        return $stmt->fetch(
            PDO::FETCH_ASSOC
        );
    }
    public function eliminarComida(
        $planId,
        $meal
    )
    {
        $sql = "
        UPDATE Plan_Comidas
        SET ID_Receta = NULL,
            Cantidad = 100
        WHERE ID_Plan = ?
        AND Tipo = ?
    ";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            $planId,
            $meal
        ]);

        if (!$result)
        {
            var_dump(
                $stmt->errorInfo()
            );
        }

        return $result;
    }
    public function obtenerTotalesPlan($planId)
    {
        return $this->calcularTotalesPlan(
            $planId
        );
    }
    public function obtenerTotalesComida(
        $planId,
        $meal
    )
    {
        $comida = $this->obtenerComida(
            $planId,
            $meal
        );
        $totales =
            [
                'calorias'      => 0,
                'proteinas'     => 0,
                'carbohidratos' => 0,
                'grasas'        => 0
            ];
        if (
            !$comida ||
            empty($comida['ID_Receta'])
        ) {
            return $totales;
        }

        $nutricion =
            $this->calcularNutricionReceta(
                $comida['ID_Receta']
            );

        $factor =
            ((int)($comida['Cantidad'] ?? 100)) / 100;

        $totales['calorias'] =
            $nutricion['Calorias'] * $factor;

        $totales['proteinas'] =
            $nutricion['Proteinas'] * $factor;

        $totales['carbohidratos'] =
            $nutricion['Carbohidratos'] * $factor;

        $totales['grasas'] =
            $nutricion['Grasas'] * $factor;

        return $totales;
    }
    public function obtenerObjetivosNutricionales(
        $user,
        $plan
    )
    {
        $objetivo =
            $plan['Objetivo'] ?? 'mantenimiento';
        $intensidad =
            $plan['Intensidad'] ?? 'moderado';
        $objetivoCalorias =
            $this->calcularCalorias(
                $user,
                $objetivo,
                $intensidad
            );
        $peso =
            $user['Peso'] ?? 70;
        $macros =
            $this->calcularMacrosObjetivo(
                $peso,
                $objetivo,
                $intensidad
            );
        $protObj = $macros['proteinas'];
        $fatObj = $macros['grasas'];
        $caloriasRestantes =
            $objetivoCalorias -
            (($protObj * 4) + ($fatObj * 9));
        $carbObj =
            max(30, $caloriasRestantes / 4);
        return
            [
                'calorias'      => $objetivoCalorias,
                'proteinas'     => $protObj,
                'carbohidratos' => $carbObj,
                'grasas'        => $fatObj
            ];
    }
    public function actualizarUsuarioFit(
        $userId,
        $peso,
        $altura,
        $edad,
        $sexo,
        $actividad
    )
    {
        $stmt = $this->db->prepare("
        UPDATE Usuario
        SET Peso = ?,
            Altura = ?,
            Edad = ?,
            Sexo = ?,
            NivelActividad = ?
        WHERE ID_Usuario = ?
    ");
        return $stmt->execute([
            $peso,
            $altura,
            $edad,
            $sexo,
            $actividad,
            $userId
        ]);
    }
    public function calcularPorcentajesMacros(
        $totales,
        $objetivos
    )
    {
        return
            [
                'calorias' => min(
                    100,
                    ($totales['calorias'] / max(1, $objetivos['calorias'])) * 100
                ),
                'proteinas' => min(
                    100,
                    ($totales['proteinas'] / max(1, $objetivos['proteinas'])) * 100
                ),
                'carbohidratos' => min(
                    100,
                    ($totales['carbohidratos'] / max(1, $objetivos['carbohidratos'])) * 100
                ),
                'grasas' => min(
                    100,
                    ($totales['grasas'] / max(1, $objetivos['grasas'])) * 100
                )
            ];
    }
}