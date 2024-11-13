<?php
header("Access-Control-Allow-Origin: *");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Ferramentas\Funcoes;
use Ferramentas\Autorizacao;
use Classes\Transacao;
use Classes\Estatistica;
use Classes\Pendente;
use Classes\Recorrente;
use Classes\Configuracao;
use Classes\Perfil;
use Classes\Enviar;
use Classes\Receber;

require 'vendor/autoload.php';

$token = 'FcOXI3a2YG7mmPYAedvGojY+481gQ1jw3vi7tAt1jcUXcPZ1nEVZcwJ6bAT8oGRWvgLZ4tBWZWQOGzfIxWOSSwoE6yLPN0xIElR2OTujJc8Wgw+URjgoyvR+edTt5aFc+nbFoBi2bv1xCBKjtBrt8yz4WV5jSio9nlDZZkj+vsRqyQ==.NTE2NzU5NmE3NTM1NTU2ZTRiNzI2NDQ4NzY3NjM3NDY3MDRjNzc0NjQ4NGY2MTU5NDU2MTZlNGM3MTM1NjczMjYyNjk0YjU4NTAzNDUwNDI=';

$Auth;
try {
    $Auth = new Autorizacao($token,Funcoes::conexao());
} catch (Exception $e) {
    echo $e->getMessage();
    return;
}
$t = new Enviar(Funcoes::conexao(), new Funcoes());
#$t->autoPayParcelado();
$t->autoPayRecorrente();
//var_dump($Auth->getId());
return;

//$ver = $Auth->verificaCodigo("921797626","465168");
//$ver = $Auth->enviaCodigo("921797626","codigo");
//$ver = $Auth->verificaPin("12356",$Auth->getCliente());
//var_dump($ver);
//return;

$body = (array) json_decode('{
	"valor":300000,
	"de": 921797626,
	"descricao": "uma descricao",
	"tipo": "parcelado",
	"onde": "app",
    "opcoes": "{\"periodicidade\": \"diario\",\"parcelas\": \"2\",\"valor_parcelas\": \"150000\"}"
}');

$t = new Receber(Funcoes::conexao(), new Funcoes());
//escreva uma funcao que de fibonacci

/*   try {
    //code...
    $t->nova($body["de"],$Auth->getId(), $body["tipo"], $body["onde"], $body["valor"], $body["descricao"], (array)json_decode($body["opcoes"]));
    $envia = $t->commit();
    echo ($envia);
} catch (\Exception $e) {
    
    echo $e->getMessage();
} */  

/* 

$body = (array) json_decode('{
	"valor":30,
	"para": 921797626,
	"descricao": "uma descricao",
	"tipo": "normal",
	"onde": "app",
    "opcoes": "{\"periodicidade\": \"diario\",\"parcelas\": \"2\",\"valor_parcelas\": \"150000\"}"
}');

$t = new Enviar(Funcoes::conexao(), new Funcoes());

try {
    //code...
    $envio = $t->nova($Auth->getId(), $body["para"], $body["tipo"], $body["onde"], $body["valor"], $body["descricao"], (array)json_decode($body["opcoes"]));
    var_dump($envio);
    if($envio["ok"]){
        $envia = $t->commit();
        var_dump($envia);
    }else{
        var_dump($envio);
    }
        
} catch (\Exception $e) {
    
    echo $e->getMessage();
} */

/* $t = new Enviar(Funcoes::conexao(), new Funcoes());
try {
    //code...
    $t->aceitarPendente("671cb777663de");
    $envia = $t->commit();
    echo ($envia);
} catch (\Exception $e) {
    
    echo $e->getMessage();
}  */

/* 
#transacao normal
$body = (array) json_decode('{
	"valor":1000,
	"para": 921797626,
	"descricao": "uma descricao",
	"tipo": "normal",
	"onde": "app"
}');
try {
    //code...
    $t->nova($Auth->getId(), $body["para"], $body["tipo"], $body["onde"], $body["valor"], $body["descricao"]);
    $envia = $t->commit();
    echo ($envia);
} catch (\Exception $e) {
    
    echo $e->getMessage();
} */


$t = new Transacao(Funcoes::conexao(), new Funcoes());
#$res = $t->verTodosInit("947436662");
#$res = $t->verTodos("921797626","10","2024");
#$res = $t->verDetalhes("1");
#echo json_encode($res);

$e = new Estatistica(Funcoes::conexao(), new Funcoes());
#$res = $e->verTodosInit("6710363e3da27");
#$res = $e->verTodos("6710363e3da27","10","2024");

$p = new Pendente(Funcoes::conexao(), new Funcoes());
#$res = $p->verTodos("921797626");
#$res = $p->verDetalhes("671cb777663de");
#$res = $p->cancelarPendente("671c3f0ce9452");
#echo json_encode($res);

$r = new Recorrente(Funcoes::conexao(), new Funcoes());
#$res = $r->verTodos("921797626");
#$res = $r->verDetalhes("1099985634");
#echo json_encode($res);

$c = new Configuracao(Funcoes::conexao(), new Funcoes());
//$res = $c->verPin("671039056e390");
//echo json_encode($res);

$p = new Perfil(Funcoes::conexao(), new Funcoes());
//$res = $p->verDetalhes("6710363e3da0a");
#$res = $p->init("671039056e390");
#echo json_encode($res);