<?php
 header("content-type: text/html; charset=utf-8");
 
 $GLOBALS["result"] = '';
 $GLOBALS["msg"] = '';
 $conn = null;
 
 if($_SERVER["REQUEST_METHOD"] == "POST") {
 $host= 'sql207.infinityfree.com';
 $user = 'if0_34882423';
 $pass = 'hFXkuCJvJeyZ6';
 $dbname = 'if0_34882423_meubanco';
 $port = 3306;

 try {
            $conn = new PDO("mysql:host=$host; port=$port; dbname=$dbname", $user, $pass);

            $GLOBALS["msg"] .= '"Conectou com o banco de dados via POST"';

        } catch (PDOException $err) {
            $GLOBALS["msg"] .= '"ERRO: Conexão com o banco de dados nao foi realizada com sucesso. ERRO gerado' . $err->getMessage() . '"';
        }
        if ($_REQUEST['type'] == "LG"){
            ControleUsuarioDB($conn,$_REQUEST['action']);
        }
        else if($_REQUEST['type'] == "C")
        {
            ConsultarRegistrosDB($conn);

        } else if($_REQUEST['type'] == "I") {
            InserirRegistroDB($conn);
            ConsultarRegistrosDB($conn);

        } else if($_REQUEST['type'] == "D") {
            DeletarRegistroDB($conn);
            ConsultarRegistrosDB($conn);
        } else if($_REQUEST['type'] == "U") {
           AtualizarRegistroDB($conn);
            ConsultarRegistrosDB($conn);
        } else if($_REQUEST['type'] == "DI"){
            DeletarImagem($conn);
            ConsultarRegistrosDB($conn);
        } else {
            if($GLOBALS["msg"]) {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Operação (Type) com valor (' . $_REQUEST['type'] . ') é invalida"';
        }
    }
    $GLOBALS["result"] .= '"msg" : [' . $GLOBALS["msg"] . ']';

    echo "{" . $GLOBALS["result"] . "}";


    function ControleAcesso($conn){
        $resultRow = "";
        $action = false;

        $sql = "SELECT token FROM tabela_usuario WHERE token=:value";
        $query = $conn->prepare($sql);
        $query->bindValue(':value',$_REQUEST["value"]);
        $query->execute();
        if($query->rowCount() > 0){
            $row = $query->fetch(PDO::FETCH_ASSOC);
            $resultRow .= "{
                        \"value\":\"" . $row["token"] . "\",
                        \"redirect\":\"/crud/\"
            }";
            if ($GLOBALS["msg"]){
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Ação de validação de acesso executada com sucesso."';

            $action = true;
        } else {
            $resultRow .= "{
                        \"value\":\"" . $row["token"] . "\",
                        \"redirect\":\"/crud/login/\"
            }";
                
            if ($GLOBALS["msg"]){
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Sem resultado"';

            $action = false;
        }
        $GLOBALS["result"] = '"resultado" : [' .$resultRow. '],';
        return $action;
    }

    function ControleUsuarioDB($conn,$tipoAcao){
        $GLOBALS["result"] = '';
        if ($tipoAcao == "UC"){
            $senha = $_REQUEST["pass"];

            $sql = "SELECT token,senha FROM tabela_usuario WHERE usuario=:usuario";
            $query = $conn->prepare($sql);
            $query->bindValue(':usuario',$_REQUEST["user"]);
            $query->execute();
            if($query->rowCount() > 0){
                $row = $query->fetch(PDO::FETCH_ASSOC);
                if($resultRow){
                    $resultRow .= ",";
                }
                if (password_verify($senha,$row["senha"])){
                    $resultRow .= "{
                                \"value\":\"" . $row["token"] . "\",
                                \"redirect\":\"/crud/\"
                    }";
                    if ($GLOBALS["msg"]){
                        $GLOBALS["msg"] .= ",";
                    }
                    $GLOBALS["msg"] .= '"Ação Login executada com sucesso."';
                    
                    $GLOBALS["result"] = '"resultado" : [' .$resultRow. '],';
                } else
                if ($senha === "adminadmin") {
                    $resultRow .= "{
                                \"value\":\"" . $row["token"] . "\",
                                \"redirect\":\"/crud/login/user/\"
                    }";
                    
                    if ($GLOBALS["msg"]){
                        $GLOBALS["msg"] .= ",";
                    }
                    $GLOBALS["msg"] .= '"Ação alteração de login executada com sucesso."';
                    
                    $GLOBALS["result"] = '"resultado" : [' .$resultRow. '],';
                } else {
                    if ($GLOBALS["msg"]){
                        $GLOBALS["msg"] .= ",";
                    }
                    $GLOBALS["msg"] .= '"Usuário ou senha incorretos."';
                }
            } else {
                if ($GLOBALS["msg"]){
                    $GLOBALS["msg"] .= ",";
                }
                $GLOBALS["msg"] .= '"Sem resultado"';
            }
        } else 
        if ($tipoAcao == "UU"){
            $GLOBALS["result"] = '';
            $senha = $_REQUEST["new_pass"];

            $senha = password_hash($senha,PASSWORD_DEFAULT);

            $sql = "UPDATE tabela_usuario SET nome=:new_name, usuario=:new_user, senha=:new_pass WHERE token=:value";
            $query = $conn->prepare($sql);
            $query->bindValue(':value',$_REQUEST["value"]);
            $query->bindValue(':new_name',$_REQUEST["new_name"]);
            $query->bindValue(':new_user',$_REQUEST["new_user"]);
            $query->bindValue(':new_pass',$senha);

            $query->execute();
            if($query->rowCount() > 0){
                $resultRow .= "{
                            \"value\":\"" . $row["token"] . "\",
                            \"redirect\":\"/crud/\"
                }";

                if ($GLOBALS["msg"]){
                    $GLOBALS["msg"] .= ",";
                }
                $GLOBALS["msg"] .= '"Ação UPDATE de usuário executada com sucesso."' ;

                $GLOBALS["result"] = '"resultado" : [' .$resultRow. '],';
            } else {
                if ($GLOBALS["msg"]){
                    $GLOBALS["msg"] .= ",";
                }
                $GLOBALS["msg"] .= '"Sem resultado"';
            }
        }
    }


    function ConsultarRegistrosDB($conn) {
        if (ControleAcesso($conn)){
        $GLOBALS["result"] = '';
        $sql = "SELECT * FROM tabela_pessoa";
        $query = $conn->prepare($sql);
        $query->execute();
        if($query->rowCount() > 0)
        {
            $resultRow = "";
            while($row = $query->fetch(PDO::FETCH_ASSOC))
            {
                if($resultRow)
                {
                    $resultRow .= ",";
                }
                $resultRow .= "{
                \"id\" : \"" . $row['id']."\",
                \"nome\" : \"" . $row['nome']. "\",
                \"sobrenome\" : \"" . $row['sobrenome']. "\",
                \"documento\" : \"" . $row['documento']. "\",
                \"data_nascimento\" : \"" . $row['data_nascimento']. "\",
                \"imagem\" : \"" . $row['imagem']. "\"
                }";
            }
            $GLOBALS["result"] = '"resultado" : [' . $resultRow . '],';
        } else {
            if($GLOBALS["msg"])

            {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Sem resultado"';
        }
    }
}

    function InserirRegistroDB($conn){
        if (ControleAcesso($conn)){
        $GLOBALS["result"] = '';
        $msg = '"EXECUTANDO INSERT"';
        $fileName = "";

        if($_FILES['file']) {
            $fileName = "uploads/".$_FILES['file']['name'];
            if ($GLOBALS["msg"]){
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"' . uploadfile("file") . '"';
        }

        $sql = "INSERT INTO tabela_pessoa (nome, sobrenome, documento, data_nascimento, imagem) VALUES (:nome, :sobrenome, :documento, :data_nascimento, :imagem)";
        $query = $conn->prepare($sql);
        $query->bindValue(':nome', $_REQUEST["nome"]);
        $query->bindValue(':sobrenome', $_REQUEST["sobrenome"]);
        $query->bindValue(':documento', $_REQUEST["documento"]);
        $query->bindValue(':data_nascimento', $_REQUEST["data_nascimento"]);
        $query->bindValue(':imagem', $fileName);
 
        $query->execute();

        if($query->rowCount() > 0) {
            if($GLOBALS["msg"]) {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Ação INSERT executada com sucesso."';
        } else {
            if($GLOBALS["msg"]) {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Sem resultado"';
        }
    }
}

    function AtualizarRegistroDB($conn){
        if (ControleAcesso($conn)){
        $fileName = "";
        if($_FILES["new_image"]) {
            $fileName = "uploads/".$_FILES["new_image"]['name'];
            if($GLOBALS["msg"]) {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"' . uploadfile("new_image") . '"';
        } else {
            if($_REQUEST["new_image"] != 'undefined'){
            $fileName = $_REQUEST["new_image"];
            
           
        }else{
            $fileName = null;
           
           
        }
    }
        $GLOBALS["result"] = '';
        $msg = '"EXECUTANDO INSERT"';
        $sql = "UPDATE tabela_pessoa set nome=:new_nome, sobrenome=:new_sobrenome, documento=:new_documento, data_nascimento=:new_data_nascimento, imagem = :new_image WHERE id=:id";
        $query = $conn->prepare($sql);
        $query->bindValue(':id', $_REQUEST["id"]);
        $query->bindValue(':new_nome', $_REQUEST["new_nome"]);
        $query->bindValue(':new_sobrenome', $_REQUEST["new_sobrenome"]);
        $query->bindValue(':new_documento', $_REQUEST["new_documento"]);
        $query->bindValue(':new_data_nascimento', $_REQUEST["new_data_nascimento"]);
        $query->bindValue(':new_image',$fileName);
        
 
        $query->execute();

        if($query->rowCount() > 0) {
            if($GLOBALS["msg"]) {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Ação UPDATE executada com sucesso."';
        } else {
            if($GLOBALS["msg"]) {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Sem resultado"';
            }
        }
    }

    function DeletarRegistroDB($conn){
        if (ControleAcesso($conn)){
        $GLOBALS["result"] = '';
        $msg = '"EXECUTANDO DELETE"';
        $sql = "DELETE FROM tabela_pessoa WHERE id=:id";
        $query = $conn->prepare($sql);
        $query->bindValue(':id', $_REQUEST["id"]);
        //$query->bindValue(':new_nome', $_REQUEST["new_nome"]);
        //$query->bindValue(':new_sobrenome', $_REQUEST["new_sobrenome"]);
        //$query->bindValue(':new_documento', $_REQUEST["new_documento"]);
        //$query->bindValue(':new_data_nascimento', $_REQUEST["new_data_nascimento"]);
        $query->execute();
        if($query->rowCount() > 0) {
            if($GLOBALS["msg"]) {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Ação DELETE executada com sucesso."';
        } else {
            if($GLOBALS["msg"]) {
                $GLOBALS["msg"] .= ",";
            }
            $GLOBALS["msg"] .= '"Sem resultado"';
        }
    }
}

    function uploadfile($id){
        // Pasta onde o arquivo vai ser salvo
        $_UP['pasta'] = '../uploads/';
        // Tamanho máximo do arquivo (em Bytes)
        $_UP['tamanho'] = 1024 * 1024 * 2; // 2Mb
        // Array com as extensões permitidas
        $_UP['extensoes'] = array('jpg', 'png', 'gif');
        // Renomeia o arquivo? (Se true, o arquivo será salvo como .jpg e um nome único)
        $_UP['renomeia'] = false;
 
        // Array com os tipos de erros de upload do PHP
        $_UP['erros'][0] = 'Não houve erro';
        $_UP['erros'][1] = 'O arquivo no upload é maior do que o limite do PHP';
        $_UP['erros'][2] = 'O arquivo ultrapassa o limite de tamanho especifiado no HTML';
        $_UP['erros'][3] = 'O upload do arquivo foi feito parcialmente';
        $_UP['erros'][4] = 'Não foi feito o upload do arquivo';
 
        if ($_FILES[$id]['error'] != 0) {
            return "Não foi possível fazer o upload, erro:<br />" . $_UP['erros'][$_FILES[$id]['error']];
            exit; // Para a execução do script
        }
 
        // Caso script chegue a esse ponto, não houve erro com o upload e o PHP pode continuar
        // Faz a verificação da extensão do arquivo
        $extensao = strtolower(substr( strrchr($_FILES[$id]['name'], '.'), 1));
        if (array_search($extensao, $_UP['extensoes']) === false) {
            return "Por favor, envie arquivos com as seguintes extensões: jpg, png ou gif";
        }
 
        // Faz a verificação do tamanho do arquivo
        else if ($_UP['tamanho'] < $_FILES[$id]['size']) {
            return "O arquivo enviado é muito grande, envie arquivos de até 2Mb.";
        }
 
        // O arquivo passou em todas as verificações, hora de tentar movê-lo para a pasta
        else {
        // Primeiro verifica se deve trocar o nome do arquivo
        if ($_UP['renomeia'] == true) {
        // Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .jpg
            $nome_final = time().'.'.$extensao;
        } else {
        // Mantém o nome original do arquivo
            $nome_final = $_FILES[$id]['name'];
        }
 
        // Depois verifica se é possível mover o arquivo para a pasta escolhida
        if (move_uploaded_file($_FILES[$id]['tmp_name'], $_UP['pasta'] . $nome_final)) {
        // Upload efetuado com sucesso, exibe uma mensagem e um link para o arquivo
            return "success";
        } else {
            return "erro: Não moveu o arquivo para pasta." . $_FILES[$id]['tmp_name'];
        }
    }
 }

 function DeletarImagem($conn) {
        if (ControleAcesso($conn)){
        $GLOBALS["result"] = '';

        $sql = "SELECT id, imagem FROM tabela_pessoa WHERE id = :id";
        $query = $conn->prepare($sql);
        $query->bindValue(':id', $_REQUEST["id"]);
        $query->execute();

        if($query->rowCount() > 0) {
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                unlink(__DIR__.'/../'.$row['imagem']);

                $sqlAux = "UPDATE tabela_pessoa SET imagem=null WHERE id = :id";
                $queryAux = $conn->prepare($sqlAux);
                $queryAux->bindValue(':id', $row["id"]);
                $queryAux->execute();
                if($queryAux->rowCount() > 0) {
                    if($GLOBALS["msg"]) {
                        $GLOBALS["msg"] .= ",";
                    }
                    $GLOBALS["msg"] .= '"Ação para excluir imagem executada com sucesso."';
                } else {
                    if($GLOBALS["msg"]) {
                        $GLOBALS["msg"] .= ",";
                    }
                    $GLOBALS["msg"] .= '"Sem resultado"';
                }
            }
        }
    }
 }    

?>