<?php
namespace Ferramentas;

use Exception;
use Ferramentas\Funcoes;

class Autorizacao extends Funcoes{
    private $acesso;
    public $conn;
    public $expirou = false;
    function __construct($token,$conec){
        $this->conn = $conec;
        $tk=self::substituiEspacoPorMais($token);
        $eToken = self::Tokeniza($tk);
        if($eToken){
            $this->acesso = self::valid($tk);
            $agora = time();
            $quando = $this->acesso["quando"];

            $diff = $agora-$quando;
            $tempo = floor($diff/60);
            if($tempo >= 5){
                //$this->expirou = true;
                //return ;
            }
            $this->acesso = self::valid($token);
        }
    }
    public function getConta(){
        return $this->acesso["conta"];
    }
    public function getCliente(){
        return $this->acesso["identificador"];
    }
    public function getId(){
        return $this->acesso["telefone"];
    }
    public function eEmpresa(){
        return (bool) $this->acesso["empresa"];
    }

    public function enviaCodigo($telefone, $acao){
        $codigo = self::seisDigitos();
        self::setRemetente('FETA-FACIL');
        $mensagem = "$codigo, é o número para confirmar a sua operação. \n $acao \n Expira em 5 minutos.";
        self::enviaSMS($telefone, $mensagem);

        $query=$this->conn->prepare("INSERT INTO confirmar (cliente_identificador, acao, codigo_enviado, quando, confirmou) VALUES (:cliente, :acao, :codigo, :quando, :confirmou)");
        $query->bindValue(':cliente', $telefone);
        $query->bindValue(':acao', $acao);
        $query->bindValue(':codigo', $codigo);
        $query->bindValue(':quando', time());
        $query->bindValue(':confirmou', 0);
        $query->execute();

        return ["ok"=>true, "payload"=>""];
    }

    /**
     * Verifica se o código de confirmação recebido é o mesmo que
     * foi enviado para um determinado telefone
     * @param string $telefone
     * @param string $codigo
     * @return array
     */
    public function verificaCodigo($id, $codigo){
        $query=$this->conn->prepare("SELECT * FROM confirmar WHERE cliente_identificador = :cliente AND codigo_enviado = :codigo AND confirmou = :confirmou");
        $query->bindValue(':cliente', $id);
        $query->bindValue(':codigo', $codigo);
        $query->bindValue(':confirmou', '0');
        $query->execute();
        if($query->rowCount() > 0){

            $res=$query->fetch(\PDO::FETCH_ASSOC);
           
            $agora = time();
            $quando =  $res["quando"];


            $diff = $agora-$quando;
            $tempo = floor($diff/60);
            if($tempo >= 5){
                $query=$this->conn->prepare("DELETE FROM confirmar WHERE cliente_identificador = :cliente AND codigo_enviado = :codigo");
                $query->bindValue(':cliente', $id);
                $query->bindValue(':codigo', $codigo);
                $query->execute();
                return ["payload"=>"Nao verificado","ok"=>false];
            }

            $query=$this->conn->prepare("UPDATE confirmar SET confirmou = :confirmou WHERE cliente_identificador = :cliente AND codigo_enviado = :codigo");
            $query->bindValue(':cliente', $id);
            $query->bindValue(':codigo', $codigo);
            $query->bindValue(':confirmou', 1);
            $query->execute();
            return ["payload"=>"Verificacao completa","ok"=>true];

        }else{
            return ["payload"=>"Nao verificado","ok"=>false];
        }
    }
    public function verificaPin($pin, $identificador_cliente){
        $query=$this->conn->prepare("SELECT * FROM configuracao WHERE pin = :pin AND cliente_identificador = :identificador");
        $query->bindValue(':pin', self::fazHash($pin));
        $query->bindValue(':identificador', $identificador_cliente);
        $query->execute();
        if($query->rowCount() > 0){
            return ["ok"=>true, "payload"=>''];
        }
        return ["ok"=>false, "payload"=>''];
    }
}
