<?php

/**
 * Базовый класс загрузчика
 * @property-read string $Error Текст возникшей при загрузке ошибки
 */
class Loader
{

    // Идентификаторы объектов в таблице ALTNAMES
    const OldIdField = 'OldId';
    const NewIdField = 'NewId';
    // Поля объектов
    const IdField = 'Id';
    const NameField = 'Name';
    const NormalizedNameField = 'NormalizedName';
    const TypeField = 'Type';
    const TypeShortField = 'TypeShort';
    const ZipCodeField = 'ZipCode';
    const OkatoCodeField = 'Okato';
    const Bad = "Bad";
    const TypeCode = "TypeCode";
    // Коды объектов
    const CodeRegionField = 'CodeRegion';
    const CodeDistrictField = 'CodeDistrict';
    const CodeLocalityField = 'CodeCity';
    const CodeStreetField = 'CodeStreet';
    const CodeBuildingField = 'CodeBuilding';
    // Поле сортировки
    const SortField = 'Sort';

    /**
     * Указатель файла из которого будет производиться чтение
     * @var type
     */
    protected $file;

    /**
     * Карта для разбиения кода на составляющие
     * @var array
     */
    protected $arCodeMap;

    /**
     * Соответствие полей в базе данных ключам в массиве кодов
     * @var array
     */
    protected $arCodeConformity;

    /**
     * Соответствие полей в базе данных ключам в массиве данных
     * @var array
     */
    protected $arFieldConformity;

    /**
     * БД
     * @var type
     */
    protected $db;

    /**
     * Текст ошибки
     * @var string
     */
    protected $error;

    public function __construct($db, $strFilePath)
    {
        $this->error = '';
        $this->arCodeMap = null;
        $this->arCodeConformity = array();
        $this->arFieldConformity = array();

        $this->db = $db;

        $this->Open($strFilePath);
    }

    /**
     * Открывает файл
     * @param string $strFilePath Путь к файлу
     */
    protected function Open($strFilePath)
    {
        $this->file = fopen($strFilePath, 'r');
    }

    /**
     * Считывает информацию из файла
     * @return type
     */
    protected function ReadLine()
    {
        return fgetcsv($this->file, 0, ';');
    }

    /**
     * Расшифровывает код объекта
     * @return код объекта
     */
    protected function ReadCode($strCode)
    {
        $arResult = array();

        $key = 0;
        $count = 1;
        $strlen = strlen($strCode);
        for ($i = 0; $i < $strlen; $i++)
        {
            $arResult[$key] .= $strCode[$i];

            $count++;
            if ($count > $this->arCodeMap[$key])
            {
                $key++;
                if ($key >= count($this->arCodeMap))
                    break;

                $count = 1;
                $arResult[$key] = '';
            }
        }

        return $arResult;
    }

    /**
     * Определяет тип объекта (регион, район)
     * @param array $arCode Массив кодов объекта
     * @return int
     */
    protected function GetType($arCode)
    {
        if (empty($this->arCodeMap))
            return 0;

        $count = 0;
        $type = 0;
        foreach ($arCode as $code)
        {
            $count++;
            $code = intval($code);
            if ($code > 0)
                $type = $count;
        }
        return $type;
    }

    /**
     * Парсит коды объекта для записи в БД
     * @param array $arCode Массив кодов
     * @return array
     */
    protected function GetCodeField($arCode)
    {
        $arCodeField = array();
        foreach ($this->arCodeConformity as $key => $conform)
        {
            $code = intval($arCode[$conform]);
            if ($code)
                $arCodeField[$key] = $code;
            else
                $arCodeField[$key] = 0;
        }
        return $arCodeField;
    }

    /**
     * Закрывает файл
     */
    protected function Close()
    {
        fclose($this->file);
    }

    /**
     * Загружает данные из файла
     * @return true если успешно
     */
    public function Load()
    {
        if (!$this->file)
        {
            $this->error = 'Ошибка при чтении файла';
            return false;
        }
    }

    public function __get($name)
    {
        switch ($name)
        {
            case 'Error': return $this->error;
        }
    }

}
