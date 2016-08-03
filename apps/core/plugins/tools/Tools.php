<?php

namespace Kladr\Core\Plugins\Tools {

    /**
     * Kladr\Core\Plugins\Tools\Tools
     * 
     * Набор вспомагательных методов
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class Tools
    {

	/**
	 * Нормализует строку
	 * 
	 * @param string $str
	 * @return string
	 */
	public static function Normalize($str)
	{
	    $str = preg_replace('/[ёЁ]/u', 'е', $str);
            $str = preg_replace('/[^а-яА-Я0-9,\/._ёЁ]+/u', '', $str);
	    $str = mb_strtolower($str, mb_detect_encoding($str));

	    return $str;
	}

	/**
	 * Обертка над mb_strlen c анализом кодировки
	 * 
	 * @param string $str Строка
	 * @return int Длина строки
	 */
	public static function Strlen($str)
	{
	    return mb_strlen($str, mb_detect_encoding($str));
	}

	/**
	 * Конвертирует строку на английском в строку на русском в 
	 * соответвии с windows раскладкой клавиатуры
	 * 
	 * @param string $strMessage
	 * @return string
	 */
	public static function Key($strMessage)
	{
	    //Если латинских символов нет, то раскладка верная — вернем строку, как она к нам пришла
	    if (!preg_match('/[a-zA-Z]/u', $strMessage))
		return $strMessage;

	    $s1 = "qazwsxedcrfvtgbyhnujmik,ol.p;[']-1234567890 ";
	    $s2 = "йфяцычувскамепинртгоьшлбщдюзжхэъ-1234567890 ";

	    $s12 = "QAZWSXEDCRFVTGBYHNUJMIK<OL>P:{\"} ";
	    $s22 = "ЙФЯЦЫЧУВСКАМЕПИНРТГОЬШЛБЩДЮЗЖХЭЪ ";


	    $strNew = '';
	    for ($i = 0; $i < mb_strlen($strMessage, mb_detect_encoding($strMessage)); $i++)
	    {
		$char = mb_substr($strMessage, $i, 1, mb_detect_encoding($strMessage));

		if (strpos($s2, $char) !== false)
		{
		    $strNew .= $char;
		    continue;
		}

		if (strpos($s22, $char) !== false)
		{
		    $strNew .= $char;
		    continue;
		}

		if (strpos($s1, $char) !== false)
		{
		    $p = strpos($s1, $char);
		    $strNew .= mb_substr($s2, $p, 1, mb_detect_encoding($s2));
		    continue;
		}

		if (strpos($s12, $char) !== false)
		{
		    $p = strpos($s12, $char);
		    $strNew .= mb_substr($s22, $p, 1, mb_detect_encoding($s22));
		    continue;
		}
	    }

	    return $strNew;
	}

    }

}