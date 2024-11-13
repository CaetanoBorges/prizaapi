<?php
header("Access-Control-Allow-Origin: *");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;


use Ferramentas\Funcoes;
use Controladores\AdministradorControl;
use Controladores\AutorizacaoControl;

require 'vendor/autoload.php';

$funcoes = new Funcoes;
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType("application/json");
$afterMiddleware = function (Request $request, RequestHandler $handler) {
    // Proceed with the next middleware
    $response = $handler->handle($request);
    
    // Modify the response after the application has processed the request
    $response = $response->withHeader('content-type',"application/json");
    
    return $response;
};

$app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($app): ResponseInterface {
    if ($request->getMethod() === 'OPTIONS') {
        $response = $app->getResponseFactory()->createResponse();
    } else {
        $response = $handler->handle($request);
    }

    $response = $response
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->withHeader('Pragma', 'no-cache');

    if (ob_get_contents()) {
        ob_clean();
    }

    return $response;
});
$app->add($afterMiddleware);
$app->setBasePath("/sysgen");
$app->get('/', function (Request $request, Response $response, $args) {

    $response->getBody()->write("Hello World!");
    return $response;
});
$app->get('/{id}', function (Request $request, Response $response, $args) {

    $conexao = Funcoes::conexao();
    $query = $conexao->prepare("select * from cliente where identificador=?");
    $query->bindValue(1,$args["id"]);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($result));

    return $response->withHeader('content-type',"application/json");
});

$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('/entrar', AdministradorControl::class.":entrar");
    $group->post('/recuperarconta', AdministradorControl::class.":recuperarConta");
    $group->post('/confirmarcodigo', AdministradorControl::class.":confirmarCodigo");
    $group->post('/novapasse', AdministradorControl::class.":novaPasse");
});
$app->post('/pedecodigo', ConfiguracaoControl::class.":pedecodigo");

// Run app
$app->run();


//$app->post('/auth/verificaexistencia', AuthControl::class.":verificaExistencia");
//$app->post('/auth/verificatelefone', AuthControl::class.":verificaTelefone");
//$app->post('/auth/cadastrar', AuthControl::class.":cadastrar");
//$app->post('/auth/entrar', AuthControl::class.":entrar");
//$app->post('/auth/recuperarconta', AuthControl::class.":recuperarConta");
//$app->post('/auth/confirmarcodigo', AuthControl::class.":confirmarCodigo");
//$app->post('/auth/novopin', AuthControl::class.":novoPin");