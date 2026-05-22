<?php
require_once __DIR__ . '/../model/BusquedaModel.php';
require_once __DIR__ . '/../view/BusquedaView.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../core/logger.php';
class BusquedaController
{
    private $model;
    private $view;
    public function __construct()
    {
        $this->model = new BusquedaModel();
        $this->view = new BusquedaView();
    }
   public function index()
    {
        $user = Auth::user();
        if ($user)
        {
            Auth::user($user);
        }
        $userId = Auth::check() ? Auth::id() : null;
        $etiquetas = $this->model->getEtiquetasDisponibles();
        $esfit = isset($_GET['esfit']) && $_GET['esfit'] == '1';
        // Si viene desde modo fit
        if ($esfit) 
        {
            $recetas = $this->model->buscarRecetas('',[],12,0,$userId,true);
            $this->view->render($recetas, $etiquetas, []);
        } 
        else 
        {
            $recomendadas = $this->model->getRecetasRecomendadas(12, $userId);
            $this->view->render([], $etiquetas, $recomendadas);
        }
    }
    public function recomendaciones()
    {
        try 
        {
            $userId = Auth::check() ? Auth::id() : null;
            $recomendadas = $this->model->getRecetasRecomendadas(12, $userId);
            $html = '';
            foreach ($recomendadas as $receta) {$html .= $this->view->renderRecipeCardGrid($receta);}
            header('Content-Type: application/json');
            echo json_encode(['html' => $html]);
        } 
        catch (\Exception $e) 
        {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno al cargar recomendaciones.']);
        }
        exit;
    }
    public function filtrar()
    {
        csrf_verify();

        $input = json_decode(file_get_contents('php://input'), true);

        $busqueda = trim((string)($input['busqueda'] ?? ''));
        $etiquetas = $input['etiquetas'] ?? [];
        $esfit = !empty($input['esfit']);
        $limit = 12;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $userId = Auth::check() ? Auth::id() : null;

        try {
            $response = [];
            $userHtml = '';
            $username = '';

            if (str_starts_with($busqueda, '@')) {
                $username = trim(substr($busqueda, 1));

                Logger::info(
                    'BusquedaController',
                    'filtrar',
                    'AJAX',
                    "Detectada búsqueda por usuario: @$username"
                );

                if ($username !== '') {
                    $user = $this->model->getUserByUsername($username);

                    Logger::info(
                        'BusquedaController',
                        'filtrar',
                        'AJAX',
                        "Resultado getUserByUsername: " . var_export($user, true)
                    );

                    if ($user) {
                        $userHtml = $this->view->renderUserCard($user);

                        Logger::info(
                            'BusquedaController',
                            'filtrar',
                            'AJAX',
                            "HTML de tarjeta generado: " . (strlen($userHtml) > 0 ? 'OK' : 'vacío')
                        );
                    }

                    $busqueda = $username;
                } else {
                    $busqueda = '';
                }
            }

            $recetas = $this->model->buscarRecetas(
                $busqueda,
                $etiquetas,
                $limit,
                $offset,
                $userId,
                $esfit
            );

            $html = '';

            foreach ($recetas as $receta) {
                $html .= $this->view->renderRecipeCardGrid($receta);
            }

            $response['html'] = $html;
            $response['count'] = count($recetas);
            $response['userHtml'] = $userHtml;
            $response['debug'] = [
                'original' => $input['busqueda'] ?? '',
                'username' => $username,
                'userFound' => !empty($userHtml),
                'userHtmlLength' => strlen($userHtml),
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al filtrar: ' . $e->getMessage()]);
        }

        exit;
    }
}