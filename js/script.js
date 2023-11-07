ConsultarPessoa();

function InserirPessoa() {
    let fileInput = document.getElementById('input_imagem');
    objPessoa = {

        value: sessionStorage.getItem("value"),

        file: fileInput.files[0],

        nome: document.getElementById('nome').value,

        sobrenome: document.getElementById('sobrenome').value,

        documento: document.getElementById('documento').value,

        data_nascimento: document.getElementById('data_nascimento').value,

        type: 'I'

    }

    exceRecord(objPessoa, listRecord);

}

function ConsultarPessoa() {

    objPessoa = {

        value: sessionStorage.getItem("value"),

        type: 'C'

    }

    exceRecord(objPessoa, listRecord);

}



function DeletarPessoa(idValue) {

    objPessoa = {

        value: sessionStorage.getItem("value"),

        id: idValue,

        type: 'D'

    }

    exceRecord(objPessoa, listRecord);

}

function AtualizarPessoa(idValue) {
    let fileInput = document.getElementById(idValue + "_imagem");
    if(fileInput.files){
        fileInput = fileInput.files[0];
    }else{
        fileInput = fileInput.getAttribute("src");
    }
    objPessoa = {
        value: sessionStorage.getItem("value"),
        id: idValue,
        new_image: fileInput,
        new_nome: document.getElementById(idValue + '_nome').value,
        new_sobrenome: document.getElementById(idValue + '_sobrenome').value,
        new_documento: document.getElementById(idValue + '_documento').value,
        new_data_nascimento: document.getElementById(idValue + '_data_nascimento').value,
        type: 'U'

    }

    exceRecord(objPessoa, listRecord);

}

function exceRecord(obj, functionAux) {

    let fObj = new FormData();

    for (let key in obj) {

        fObj.append(key, obj[key]);

    }

    ajaxReq(fObj, functionAux);

}

function funcaoTest(data) {

    alert(data);

}

function ajaxReq(valueData, functionAux) {

    $.ajax({

        url: '/crud/php/processardados.php',

        data: valueData,

        dataType: 'json',

        mimeType: "ISO-8859-1",

        type: "POST",

        processData: false,

        cache: false,

        contentType: false,

        success: function (dataOut) {

            if (functionAux) {

                functionAux(dataOut);

            }

        },

        beforeSend: function () {

            $('.modal-loader').css({ display: 'flex' });

        },

        complete: function () {

            $('.modal-loader').css({ display: 'none' });

        },

        error: function (XMLHttpRequest, textStatus, errorThrown) {

            alert("erro: " + XMLHttpRequest.responseText + " | errorThrown(" + errorThrown + ")");

        },

    });

}

function DeletarImagem(idValue) {
    objPessoa = {
        value: sessionStorage.getItem("value"),
        id: idValue,
        type: 'DI'
    }
    exceRecord(objPessoa, listRecord);
}

function PreVisualizarImagem(idValue){
    if (!idValue){
        idValue = "input";
    }
    document.getElementById(idValue + "_preview").style.display = "inline";
    document.getElementById(idValue + "_preview").src = window.URL.createObjectURL(document.getElementById(idValue + "_imagem").files[0]);
    
}

function listRecord(data) {

    document.getElementById('conteudo').innerHTML = '';

    if (data.msg) {

        let msgAux = '';

        let obj = data.msg;

        for (let key in obj) {

            msgAux += ' - ' + obj[key] + '<br/>';

        }

        document.getElementById('logs').innerHTML = msgAux;

    }

    if (data.resultado) {
        if (data.resultado[0].redirect) {
            window.location.replace(data.resultado[0].redirect);
        } else {
            let obj = data.resultado;

            let contentTbody = '<tbody>';

            let contentThead = '';

            let countRecords = 0;

            for (let key in obj) {

                countRecords++;

                let objPerson = obj[key]

                let idPessoa = null;

                for (let keyInfo in objPerson) {

                    let campoDesabilitado = '';

                    let tipoCampo = 'text';

                    if (countRecords == 1) {

                        contentThead += '<td>';

                        contentThead += keyInfo[0].toUpperCase()+keyInfo.slice(1);

                        contentThead += '</td>'

                    }

                    if (keyInfo == 'id') {

                        campoDesabilitado = 'disabled';

                        idPessoa = objPerson[keyInfo];

                    }

                    if (keyInfo == 'data_nascimento') {

                        tipoCampo = 'date';

                    }

                    contentTbody += '<td>';

                    if (keyInfo == "imagem") {

                        if (objPerson[keyInfo] != "") {
                            contentTbody += "<div style='text-align:center;'>";
                            contentTbody += "<img id='" + objPerson["id"] + "_" + keyInfo + "' style='width:20%;' src='" + objPerson[keyInfo] + "'>";
                            contentTbody += "<input type='button' id='btnDelImg' style='width:30%' class='btn zoom' value='Excluir' onClick='DeletarImagem(" + idPessoa + ")'>";
                            contentTbody += "</div>";
                        } else {
                            contentTbody += "<div style='text-align:center; class='zoom'>";
                            contentTbody += "<img id='" + objPerson["id"] + "_preview' style='width:20%; display:none;' src='" + objPerson[keyInfo] + "'>";
                            contentTbody += "<label for='" + objPerson["id"] + "_" + keyInfo + "' id='" + objPerson["id"] + "_label' class='btn zoom'> Imagem</label>";
                            contentTbody += "<input type='file' id='" + objPerson["id"] + "_" + keyInfo + "' name='" + objPerson["id"] + "_" + keyInfo + "' accept='image/*' onchange='PreVisualizarImagem(\"" + objPerson["id"] + "\")'>";
                            contentTbody += "</div>";
                        }
                    } else {
                        contentTbody += "<input type='" + tipoCampo + "' id='" + objPerson["id"] + "_" + keyInfo + "' value='" + objPerson[keyInfo] + "' " + campoDesabilitado + ">";
                    }

                    contentTbody += '</td>';

                }

                contentTbody += "<td>";

                contentTbody += '<input type="button" id="btnupd" class="btn zoom" value="SALVAR" onclick="AtualizarPessoa(\'' + idPessoa + '\')">';

                contentTbody += '<input type="button" id="btnDel" class="btn zoom" value="DELETAR" onclick="DeletarPessoa (\'' + idPessoa + '\')">';

                contentTbody += '</td>';

                contentTbody += '</tr>';

            }

            contentThead = '<tr>' + contentThead + '<td> Ações </td></tr>';

            contentTbody += '</tbody>';

            document.getElementById('conteudo').innerHTML += '<table style="width:100%;"><thead>' + contentThead + '</thead>' + contentTbody + '</table>'
        }
    }

}

'use strict'

const openModal = () => document.getElementById('modal')
    .classList.add('active')

const closeModal = () => document.getElementById('modal')
    .classList.remove('active')

document.getElementById('cadastrarCliente')
    .addEventListener('click', openModal)

document.getElementById('modalClose')
    .addEventListener('click', closeModal)

$('#btnIns').click(function () {

    InserirPessoa();

})

$('#btnSel').click(function () {

    ConsultarPessoa();

})




