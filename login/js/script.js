function login() {
    objPessoa = {
        user: document.getElementById("usuario").value,
        pass: document.getElementById("senha").value,
        type: 'LG',
        action: 'UC'
    }
    execRecord(objPessoa, listRecords);
}

function atualizarUsuario() {
    objPessoa = {
        value: sessionStorage.getItem("value"),
        new_user: document.getElementById("usuario").value,
        new_pass: document.getElementById("senha").value,
        new_name: document.getElementById("nome").value,
        type: 'LG',
        action: 'UU'
    }
    execRecord(objPessoa, listRecords);
}


function ajaxReq(valueData, functionAux) {
    $.ajax({
        url: '/crud/php/processardados.php',
        data: valueData,
        mimeType: 'ISO-8859-1',
        type: 'post',
        dataType: 'json',
        processData: false,
        cache: false,
        contentType: false,
        success: function (dataOut) {
            if (functionAux) {
                functionAux(dataOut);
            }
        },
        beforeSend: function () {
            $('.modal-loader').css({ display: "flex" });
        },
        complete: function () {
            $('.modal-loader').css({ display: "none" });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("erro: " + XMLHttpRequest.responseText + " | errorThrown(" + errorThrown + ")");
        }
    });
}

function execRecord(obj, functionAux) {
    let fObj = new FormData();
    for (let key in obj) {
        fObj.append(key, obj[key]);
    }
    ajaxReq(fObj, functionAux);
}

function listRecords(data) {
    if (data.msg) {
        let msgAux = "";
        let obj = data.msg;
        for (let key in obj) {
            msgAux += " - " + obj[key] + "<br/>";
        }
        document.getElementById("logs").innerHTML = msgAux;
    }
    if (data.resultado) {
        if ( data.resultado[0].value){
            sessionStorage.setItem("value", data.resultado[0].value);
        }
        if (data.resultado[0].redirect) {
            window.location.replace(data.resultado[0].redirect);
        }
    }
}