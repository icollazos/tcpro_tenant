<?php

function clausulaValores($idvalor){
	$clausulaValores = '';
	if ($idvalor) {
		$arrayValor = array_map('trim', explode(",", $idvalor));
		$arrayValor = array_map(function($word) {
			return "$word"; 
		}, $arrayValor);
		$clausulaValores = " AND ( v_aaa_textovalor.id_valor_textovalor = ";
		$clausulaValores .= implode(" OR v_aaa_textovalor.id_valor_textovalor = ", $arrayValor) . ") ";
	}
	return $clausulaValores;
}


function clausulaPalabras($x){
    $palabra = isset($x) ? $x : '';
    $clausulaPalabras = '';

    if ($palabra) {
        $palabrasArray = array_map('trim', explode(",", $palabra));
        $palabrasArray = array_map(function($word) {
        return "'%".strtolower($word)."%'"; 
    }, $palabrasArray);
        $clausulaPalabras = " AND ( LOWER(aaa_texto.descriptor) LIKE ";
        $clausulaPalabras .= implode("AND LOWER(aaa_texto.descriptor) LIKE ", $palabrasArray) . ") ";
    }
    return($clausulaPalabras);
}   

function clausulaExcepto($x){
	$excepto = isset($x) ? $x : '';
    $clausulaExcepto = '';
    if ($excepto) {
        $exceptoArray = array_map('trim', explode(",", $excepto));
        $exceptoArray = array_map(function($word) {
        return "'%".strtolower($word)."%'"; 
    }, $exceptoArray);
        $clausulaExcepto = " AND ( LOWER(aaa_texto.descriptor) NOT LIKE ";
        $clausulaExcepto .= implode(" OR LOWER(aaa_texto.descriptor) NOT LIKE ", $exceptoArray) . ") ";
    }
    return($clausulaExcepto);
}    


function clausulaFechaI($x){
    $clausulaFechaI = ' AND aaa_texto.fechahora >= ' .'\'2000/01/01\'';
	if($x!=''){
    $clausulaFechaI = ' AND aaa_texto.fechahora >= \'' . $x . '\'';
	}
    return($clausulaFechaI);
}

function clausulaFechaF($x){
    $clausulaFechaF = ' AND aaa_texto.fechahora <= ' .'\'3000/01/01\'';
    if($x!=''){
    $clausulaFechaF = ' AND aaa_texto.fechahora <= \'' . $x . '\'';
    }
    return($clausulaFechaF);
}

function clausulaFrecuentes($x){
    $clausulaFrecuentes = ' ';
    if($x!=''){
    $clausulaFrecuentes = ' AND LOWER(aaa_texto.descriptor) LIKE \'%'.strtolower($x).'%\' ';
    }
    return($clausulaFrecuentes);
}

?>