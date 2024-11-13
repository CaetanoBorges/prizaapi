<?php
namespace Controladores;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ferramentas\Funcoes;
use Ferramentas\Criptografia;
use Classes\Administrador;

class AdministradorControl
{
    protected $funcoes;
    protected $conexao;
    protected $auth;
    protected $mail;

    public function __construct(){
        $this->funcoes = new Funcoes();
        $this->conexao = Funcoes::conexao();
        $this->auth = new Administrador($this->conexao);
        $this->mail = new PHPMailer(true);
    }
   

    public function entrar(Request $request, Response $response, $args) 
    {
        $body = $request->getParsedBody();
        $passe = $this->funcoes::fazHash($body['passe']);
        $res = $this->auth->login($body["email"], $passe);
        if($res){
            $metadata = $this->auth->getByEmail($body["email"]);
            $credencial = json_encode($metadata);
            $cript = new Criptografia();
            $chave_sms_real = $cript->fazChave();
            $chave_sms = $cript->criptChave($chave_sms_real);

            $sms = $cript->encrypt($credencial,$chave_sms_real);

            $return['token'] = $sms.'.'.$chave_sms;
            $return['payload'] = "Deverá usar o token no header dos requests. EX: token: {token}";
            $return['sms'] = "Acesso garantido";
            $return['ok'] = true;
            $response->getBody()->write(json_encode($return));
        }else{
            $return['sms'] = "Credenciais errados";
            $return['payload'] = "";
            $return['ok'] = false;
            $response->getBody()->write(json_encode($return));
        }
        return $response;
    }
    
    public function recuperarConta(Request $request, Response $response, $args) 
    {
        $body = $request->getParsedBody();

        if($this->auth->verificaEmail($body["email"])){
            $seisDigitos = $this->funcoes::seisDigitos(6);
            $this->auth->setCodigo($body["email"], $seisDigitos);
            $this->funcoes::enviaEmail($this->mail, $body["email"], "Código de recuperação ".$seisDigitos, "O seu número de verificação é: ".$seisDigitos);
            
            $return['sms'] = "Codigo de recuperação enviado no seu email";
            $return['payload'] = "";
            $return['ok'] = true;
            $response->getBody()->write(json_encode($return));
        }else{
            
            $return['sms'] = "Email não reconhecido";
            $return['payload'] = "";
            $return['ok'] = false;
            $response->getBody()->write(json_encode($return));
        }
        return $response;
    }
    public function confirmarCodigo(Request $request, Response $response, $args) 
    {
        $body = $request->getParsedBody();
        if($this->auth->verificaCodigo($body["email"],$body["codigo"])){

            $return['sms'] = "Codigo e email correctos";
            $return['payload'] = "Insira uma nova passe, usando o email e o codigo também";
            $return['ok'] = true;
            $response->getBody()->write(json_encode($return));
        }else{
            
            $return['sms'] = "Codigo ou email errado";
            $return['payload'] = "";
            $return['ok'] = false;
            $response->getBody()->write(json_encode($return));
        }
        return $response;
    }
    public function novaPasse(Request $request, Response $response, $args) 
    {
        $body = $request->getParsedBody();

        if($this->auth->verificaCodigo($body["email"],$body["codigo"])){

        $hashPasse = $this->funcoes::fazHash($body["passe"]);

        $this->auth->alterarPasse($body["email"], $hashPasse);
        $this->auth->setCodigo($body["email"], "");

        $metadata = $this->auth->getByEmail($body["email"]);
            if(true){

                $credencial = json_encode($metadata);
                $cript = new Criptografia();
                $chave_sms_real = $cript->fazChave();
                $chave_sms = $cript->criptChave($chave_sms_real);

                $sms = $cript->encrypt($credencial,$chave_sms_real);

                $return['token'] = $sms.'.'.$chave_sms;
                $return['payload'] = "Deverá usar o token no header dos requests. EX: token: {token}";
                $return['sms'] = "Palavra passe atualizada";
                $return['ok'] = true;
                $response->getBody()->write(json_encode($return));
            }
        }else{
            
                $return['payload'] = "";
                $return['sms'] = "Erro ";
                $return['ok'] = false;
                $response->getBody()->write(json_encode($return));
        }
        return $response;
    }
}