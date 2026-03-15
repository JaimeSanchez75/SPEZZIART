<?php
require_once __DIR__ . '/../model/FeedModel.php';
require_once __DIR__ . '/../view/FeedView.php';
require_once __DIR__ . '/../../../core/auth.php';

class FeedController 
{   
    public function index() 
    {
        $model = new FeedModel();

        $catFiltro = $_GET['cat'] ?? null;
        $posts = $model->getPosts($catFiltro);
        $etiquetas = $model->getEtiquetasDisponibles();

        $config = null; //Tomamos la configuración
        if (Auth::check()) {$config = $model->getUserConfig(Auth::id());}

        (new FeedView())->render($posts, $etiquetas, $catFiltro, $config); //Renderizamos en la vista
    }

    public function filtrar() 
    {
        error_reporting(0); 
        
        $etiquetas = $_POST['etiquetas'] ?? [];
        $busqueda = $_POST['busqueda'] ?? null;
        
        $model = new FeedModel();
        $posts = $model->getPosts($etiquetas, $busqueda);
        
        foreach ($posts as &$post) 
        {
            $post['FechaCreacion'] = date('d M', strtotime($post['FechaCreacion']));
            $post['Descripcion'] = $post['Descripcion'] ?? '';
        }

        header('Content-Type: application/json');
        echo json_encode($posts);
        exit;
    }

    public function crearPost() 
    {
        if (!Auth::check()){header("Location: /App/pages/login"); exit;}
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        if ($titulo === '' || $descripcion === ''){header("Location: /App/pages/feed?error=campos_vacios"); exit;}
        
        $model = new FeedModel();
        if ($model->crearPost(Auth::id(), $titulo, $descripcion)){header("Location: /App/pages/feed?success=1");} 
        else{header("Location: /App/pages/feed?error=db");}
        exit;
    }
}