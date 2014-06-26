<?php

namespace Kladr\Core\Models {

    use \Phalcon\Mvc\Collection;
    
    /**
     * Kladr\Core\Models\Cities
     * 
     * Коллекция всех элементов для осущствления умного поиска одной строкой
     *
     * @property string $Id - Идентификатор объекта
     * @property string $Name - Название объекта
     * @property array(string) $NormalizedName - Нормализованное название
     * @property int $ZipCode - Почтовый индекс
     * @property string $TypeShort - Подпись коротко
     * @property string $Type - Подпись
     * @property string $Okato - Код ОКАТО
     * @property int $CodeRegion - Код области
     * @property int $CodeDistrict - Код района
     * @property int $CodeCity - Код города
     * @property int $CodeStreet - Код улицы
     * @property int $CodeBuilding - Код здания
     * @property string $BuildingId - Id здания
     * @property string $StreetId - Id улицы
     * @property string $CityId - Id города
     * @property string $DistrictId - Id района
     * @property string $RegionId - Id области
     * @property int $Sort - Поле для сортировки. Чем больше значение, тем "меньше" объект
     * @property array(string) $Address - Массив полного адреса объекта
     * @property string $FullName - Полное имя объекта
     * @property string $NormalizedBuldingName - Нормализованное название здания
     * @property string $NormalizedStreetName - Нормализованное название улицы
     * @property string $NormalizedCityName - Нормализованное название города
     * @property string $NormalizedDistrictName - Нормализованное название района
     * @property string $NormalizedRegionName - Нормализованное название области
     * @property string $ContentType - Тип объекта
     * 
     * @author Y. Lichutin
     */
    class Complex extends Collection {        
        
        public function getSource()
        {
            return "complex";
        }
    }
}