<?php

function __normalize($str){
    $str = preg_replace('/\./u', ' ', $str);
	$str = preg_replace('/[Ёё]/u', 'е', $str);
    $str = preg_replace('/[^а-яА-Я0-9,\s\/]+/u', '', $str);
    $str = mb_strtolower($str, 'UTF-8');
    
    $arRes = array();
    $arStr = explode(',', $str);       
    foreach($arStr as $el){
        $arRes = array_merge($arRes, explode(' ', $el));
    }
    
    $arRes[0] = preg_replace('/\s+/u', '', $str);    
    return $arRes;
}

function __key($strMessage)
{
    $s1 = "qazwsxedcrfvtgbyhnujmik,ol.p;[']-1234567890 ";
    $s2 = "йфяцычувскамепинртгоьшлбщдюзжхэъ-1234567890 ";

    $s12 = "QAZWSXEDCRFVTGBYHNUJMIK<OL>P:{\"} ";
    $s22 = "ЙФЯЦЫЧУВСКАМЕПИНРТГОЬШЛБЩДЮЗЖХЭЪ ";


    $strNew = '';
    for($i = 0; $i < strlen($strMessage); $i++)
    {
	$char = substr($strMessage, $i, 1);
	if(strpos($s2, $char) !== false)
	{
	    $strNew .= $char;
	    continue;
	}

	if(strpos($s22, $char) !== false)
	{
	    $strNew .= $char;
	    continue;
	}

	if(strpos($s1, $char) !== false)
	{
	    $p = strpos($s1, $char);
	    $strNew .= substr($s2, $p, 1);
	    continue;
	}

	if(strpos($s12, $char) !== false)
	{
	    $p = strpos($s12, $char);
	    $strNew .= substr($s22, $p, 1);
	    continue;
	}

    }

    return $strNew;
}

function print_var($var, $internal_call=false)
{
    $is_obj = false;
    if(is_object($var)){$var =  (array)$var; $is_obj = true;}

    if(!$internal_call) echo '<div style="position: absolute; top: 0px; left 0px; z-index: 10000000000000; padding: 20px; background-color: white; border: 2px solid black;">';
    if(is_array($var))
    {
        echo $is_obj?'object':'array';
        if(count($var) > 0)
        {
            echo '<br/>(<ul style="list-style-type: none; display: block;">';
            foreach($var as $key => $value)
            {
                echo '<li style="margin: 5px 0 5px 20px;"><span style=" font-style: italic;">'.$key.'</span>'.($is_obj?' = ':' => ');
                print_var($value, true);
                echo '</li>';
            }
            echo '</ul>)';
        }else echo '()';
    }
    else
    {
        if(is_null($var)) echo 'null';
        else
        {
            if($var) echo $var;
            else
            {
                if(is_bool($var)) echo 'false';
                else if(is_float($var)) echo '0.00';
                else if(is_int($var)) echo '0';
                else if(is_string($var)) echo '""';
            }
        }
    }
    if(!$internal_call) echo '</div>';
}

function hardlog($text)
{
    $path = $_SERVER["DOCUMENT_ROOT"]."/tmp/log.txt";
    $f = fopen($path, 'a');
    fwrite($f, $text."\n");
    fclose($f);
}


