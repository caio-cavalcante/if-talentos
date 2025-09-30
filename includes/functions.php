<?php

function validaCampoInformado($valor, $nomeCampo) {
    if (!isset($valor)) {
        return "O campo $nomeCampo não foi informado.";
    }
    return null;
}

function validaCampoVazio($valor, $nomeCampo) {
    if (trim($valor) === '') {
        return "O campo $nomeCampo é obrigatório.";
    }
    return null;
}

function validaEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "O campo Email não contém um endereço de email válido.";
    }
    return null;
}

function validaApenasNumeros($valor, $nomeCampo) {
    $valorSemFormatacao = preg_replace('/[^0-9]/', '', $valor);
    if (!ctype_digit($valorSemFormatacao)) {
        return "O campo $nomeCampo deve conter apenas números.";
    }
    return null;
}

function validaTamanhoMaximo($valor, $nomeCampo, $tamanho) {
    if (strlen($valor) > $tamanho) {
        return "O campo $nomeCampo não pode ter mais de $tamanho caracteres.";
    }
    return null;
}

function comparaSenhas($senha, $confirmaSenha) {
    if ($senha !== $confirmaSenha) {
        return "A senha e a confirmação de senha não coincidem.";
    }
    return null;
}

function validaCPF($cpf) {
    $cpfNumerico = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpfNumerico) != 11) {
        return "O CPF deve conter exatamente 11 dígitos numéricos.";
    }

    if (preg_match('/^(\d)\1{10}$/', $cpfNumerico)) {
        return "CPF inválido: todos os dígitos são iguais.";
    }

    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpfNumerico[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $digitoVerificador1 = ($resto < 2) ? 0 : 11 - $resto;

    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpfNumerico[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $digitoVerificador2 = ($resto < 2) ? 0 : 11 - $resto;

    if ($cpfNumerico[9] != $digitoVerificador1 || $cpfNumerico[10] != $digitoVerificador2) {
        return "CPF inválido.";
    }

    return null;
}

?>