<?php

namespace Ferramentas;
use PDO;
class Funcoes
{
    public static $conexao = null;

    static function Tokeniza($str)
    {
        $res = self::valid($str);
        if (gettype($res) == "array") {
            return true;
        } else {
            return false;
        }
    }
    static function valid($token)
    {
        $token = explode(".", $token);

        $sms = $token[0];
        $chave = $token[1];
        $cript = new Criptografia();
        $chave = $cript->decriptChave($chave);
        $res = $cript->decrypt($sms, $chave);

        $r = (array) json_decode($res);

        if (count($r) > 1) {
            return $r;
        } else {
            return "Erro, token inválido!";
        }
    }

    static function seisDigitos()
    {
        return mt_rand(100000, 999999);
    }

    static function HTTPpost($url, $opt)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opt));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    static function enviaSMS($numero, $mensagem)
    {
        // get token
        $enviar = self::HTTPpost("https://app.smshub.ao/api/authentication", ["authId" => "223849641474031576", "secretKey" => 'Rju26xnMYNNt5qTXrzPz6ziVv7q841pxx8Kys1A85gaXx9LJkMTJb8jvxYPQOQQoZW28E6mHpMSVkNMH7B0YG8NWwbjpgPRkxUwS']);
        $res = (array) json_decode($enviar);
        $data = (array) $res['data'];

        // set post fields
        $post['contactNo'] = [$numero];
        $post['message'] = $mensagem;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://app.smshub.ao/api/sendsms");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            'accessToken:' . $data['authToken']
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $res = curl_exec($ch);
        curl_close($ch);


        return $res;
    }
    static function setRemetente($nome)
    {
        // get token
        $enviar = self::HTTPpost("https://app.smshub.ao/api/authentication", ["authId" => "223849641474031576", "secretKey" => 'Rju26xnMYNNt5qTXrzPz6ziVv7q841pxx8Kys1A85gaXx9LJkMTJb8jvxYPQOQQoZW28E6mHpMSVkNMH7B0YG8NWwbjpgPRkxUwS']);
        $res = (array) json_decode($enviar);
        $data = (array) $res['data'];

        // set post fields
        $post['name'] = $nome;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://app.smshub.ao/api/sender");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'accessToken:' . $data['authToken']
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    static function enviaEmail($mail, $email, $titulo, $corpo, $confPath = "email.conf.json")
    {
       
        // Passing `true` enables exceptions
        //Server settings
        $mail->SMTPDebug = 2;         // Enable verbose debug output
        $mail->isSMTP();  // Set mailer to use SMTP
        $mail->Host = "mail.mintel.ao";  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;   // Enable SMTP authentication
        $mail->Username = "naoresponder@mintel.ao";         // SMTP username
        $mail->Password = "J(AJeXS*I(%C";           // SMTP password
        $mail->SMTPSecure = "ssl"; // Enable TLS encryption, `ssl` also accepted
        $mail->SMTPOptions = array(
        'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->Port = 465; // TCP port to connect to

        //Recipients
        $mail->setFrom("naoresponder@mintel.ao", "PRIZA");
        //$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
        $mail->addAddress($email);   // Name is optional
        //$mail->addReplyTo('info@example.com', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        //Content
        $mail->isHTML(true);          // Set email format to HTML
        $mail->Subject = mb_convert_encoding($titulo, 'ISO-8859-1');
        $mail->Body    = $corpo;
        $mail->AltBody = $corpo;


        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }

    static function chaveDB()
    {
        return uniqid();
    }

    static function data()
    {
        $data['dia'] = date('d');
        $data['mes'] = date('m');
        $data['ano'] = date('y');
        return $data;
    }

    static function conexao()
    {

        if (isset(self::$conexao)) {
            return self::$conexao;
        }
        
        $dsn = "mysql:host=localhost;dbname=priza";

        // make a database connection
        self::$conexao = new \PDO($dsn, 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        //self::$conexao = pg_connect("host=localhost port=5432 dbname=fetafacil user=postgres password=root");
        return self::$conexao;

    }

    static function substituiEspacoPorMais($variavel)
    {
        return str_replace(" ", "+", $variavel);
    }
    static function fazHash($valor)
    {
        return hash("sha512", $valor);
    }
    static function quando($quando)
    {
        date_default_timezone_set("Africa/Luanda");
        return date("d-m-Y H:i:s A", $quando);
    }
    static function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
    static function rm_special_chars($string) {

        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

            $chars = array(
                chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
                chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
                chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
                chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
                chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
                chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
                chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
                chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
                chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
                chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
                chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
                chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
                chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
                chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
                chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
                chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
                chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
                chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
                chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
                chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
                chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
                chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
                chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
                chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
                chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
                chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
                chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
                chr(195).chr(191) => 'y',
                // Decompositions for Latin Extended-A
                chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
                chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
                chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
                chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
                chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
                chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
                chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
                chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
                chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
                chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
                chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
                chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
                chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
                chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
                chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
                chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
                chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
                chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
                chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
                chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
                chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
                chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
                chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
                chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
                chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
                chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
                chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
                chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
                chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
                chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
                chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
                chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
                chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
                chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
                chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
                chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
                chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
                chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
                chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
                chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
                chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
                chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
                chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
                chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
                chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
                chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
                chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
                chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
                chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
                chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
                chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
                chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
                chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
                chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
                chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
                chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
                chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
                chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
                chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
                chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
                chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
                chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
                chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
                chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
            );

        $string = strtr($string, $chars);
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        return str_replace('-', ' ', $string);
    }
}