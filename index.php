<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

session_set_save_handler (new \Echosong\RedisSession\RedisSessionHandler ([
   'host' => '127.0.0.1'
  ,'port' => 6379
  ,'timeout' => 2
  ,'database' => 0
  ,'prefix' => 'redis_session'
]), true);

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$app->add(new \Slim\Middleware\Session([
  'name' => 'dummy_session',
  'autorefresh' => true,
  'lifetime' => '1 hour'
]));

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('views', []);
    return $view;
};

$container['session'] = function ($container) {
  return new \SlimSession\Helper;
};

$app->get('/logar', function (Request $request, Response $response, array $args) {
	return $this->view->render ($response, 'logar.html', []);
});

$app->post('/efetuarlogon', function (Request $request, Response $response, array $args) {
  $parsedBody = $request->getParsedBody ();
  $this->session['nome'] = $parsedBody['nome'];
  return $response->withRedirect ('dadoslogin', 301);
});

$app->get('/dadoslogin', function (Request $request, Response $response, array $args) {
  return $this->view->render ($response, 'dados_login.html', [ 'nome' => $this->session['nome']]);
});

$app->get ('/sair', function (Request $request, Response $response, array $args) {
  $this->session::destroy();
  return $this->view->render ($response, 'mensagem_de_logoff.html', []);
});

$app->run();
?>