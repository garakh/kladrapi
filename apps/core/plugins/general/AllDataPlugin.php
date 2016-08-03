<?php

namespace Kladr\Core\Plugins\General
{

    use \Phalcon\Http\Request,
        \Phalcon\Mvc\User\Plugin,
        \Kladr\Core\Plugins\Base\IPlugin,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Plugins\Tools\Tools,
        \Kladr\Core\Models\Regions,
        \Kladr\Core\Models\Districts,
        \Kladr\Core\Models\Cities,
        \Kladr\Core\Models\Streets,
        \Kladr\Core\Models\Buildings;

    /**
     * Kladr\Core\Plugins\General\AllDataPlugin
     * 
     * Возвращает все города, все улициы города.
     * Доступен только для платных пользователей.
     * 
     * Кеширует данные в файлах
     * 
     * @author I. Garakh Primepix (http://primepix.ru/)
     */
    class AllDataPlugin extends Plugin implements IPlugin
    {

        /**
         * Путь к файловому кешу
         * @var string
         */
        public $cacheDir;

        /**
         * Сервис работы с пользователями
         * @var \Kladr\Core\Services\UserService
         */
        public $userService;

        /**
         * Отключить платные ограничения
         * 
         * @var bool 
         */
        public $disablePaid;

        const COMMAND = 'GetAllData';
        const PARAM_CITIES = 'City';
        const PARAM_STREETS = 'Street';
        const FORMAT_CSV = 'csv';
        const FORMAT_JSON = 'json';

        /**
         * Выполняет обработку запроса
         * 
         * @param \Phalcon\Http\Request $request
         * @param \Kladr\Core\Plugins\Base\PluginResult $prevResult
         * @return \Kladr\Core\Plugins\Base\PluginResult
         */
        public function process(Request $request, PluginResult $prevResult)
        {
            if ($prevResult->error)
            {
                return $prevResult;
            }

            if (!$this->check($request))
                return $prevResult;


            $user = $prevResult->user;
            if (!$this->disablePaid && ($user == null || !$user->isPaid()))
            {
                $prevResult->error = true;
                $prevResult->errorCode = 403;
                $prevResult->errorMessage = 'Функционал только для платных аккаунтов';

                return $prevResult;
            }

            switch ($request->getQuery('param1'))
            {
                case self::PARAM_CITIES : $this->processCities($request->getQuery('typeCode'), $request->getQuery('format'), $prevResult);
                    break;
                case self::PARAM_STREETS : $this->processStreets($request->getQuery('param2'), $request->getQuery('format'), $request->getQuery('direct'), $prevResult);
                    break;
                default :
                    $prevResult->error = true;
                    $prevResult->errorCode = 400;
                    $prevResult->errorMessage = 'Не указан param1';
            }

            $prevResult->terminate = true;
            return $prevResult;
        }

        /**
         * Собирает улицы
         * @param string $cityId ID улицы
         * @param string $format CSV или JSON формат
         * @param bool $direct Вывести данные сразу в поток или как скачиваемый файл
         * @param \Kladr\Core\Plugins\Base\PluginResult $result
         * @return void
         */
        private function processStreets($cityId, $format, $direct, PluginResult $result)
        {
            $format = ($format == self::FORMAT_JSON ? self::FORMAT_JSON : self::FORMAT_CSV);

            $cityId = preg_replace('/[^0-9]/msi', '', $cityId);
            if ($cityId == '' || strlen($cityId) > 25)
            {
                $result->error = true;
                $result->errorCode = 400;
                $result->errorMessage = 'Не указан или не верный param2';
                return;
            }

            set_time_limit(600);
            ini_set('max_execution_time', 600);

            $cacheKey = 'all_cities_' . $cityId . ( $format == self::FORMAT_JSON ? '_json' : '' );
            if (!$this->checkCache($cacheKey))
            {
                $cities = new Cities();
                $mongo = $cities->getConnection();
                $city = $mongo->cities->findOne(array('Id' => $cityId));
                $codeCity = $city['CodeCity'];

                if ($city['CodeCity'] != null && ($city['CodeCity'] % 1000 == 0))
                {
                    $cc = $city['CodeCity'];
                    $codeCity = array('$gte' => $cc, '$lt' => $cc + 1000);
                }

                if ($codeCity == null)
                    $codeCity = 0;


                $streets = $mongo->streets->find(
                        array(
                            'Bad' => false,
                            'CodeCity' => $codeCity,
                            'CodeDistrict' => (int) $city['CodeDistrict'],
                            'CodeRegion' => $city['CodeRegion']));

                $subCityCache = array();

                $tmp = $this->getCachePath($cacheKey) . '_' . rand(10000, 10000000);
                $fp = fopen($tmp, 'w');
                if ($format == self::FORMAT_CSV)
                {
                    fputcsv($fp, $this->streetToArray());
                    foreach ($streets as $street)
                    {

                        $subCity = false;

                        $codeSubCity = (int) $street['CodeCity'];
                        if (isset($subCityCache[$codeSubCity]))
                        {
                            $subCity = $subCityCache[$codeSubCity];
                        } else
                        {
                            $subCity = $mongo->cities->findOne(
                                    array(
                                        'Bad' => false,
                                        'CodeCity' => $codeSubCity,
                                        'CodeDistrict' => (int) $city['CodeDistrict'],
                                        'CodeRegion' => $city['CodeRegion']));

                            $subCityCache[$codeSubCity] = $subCity ? $subCity : false;
                        }

                        $street['SubCity'] = $subCity;


                        fputcsv($fp, $this->streetToArray($street));
                    }
                } else
                {
                    fwrite($fp, '{ "result" : [');
                    $first = true;
                    foreach ($streets as $street)
                    {

                        $subCity = false;

                        $codeSubCity = (int) $street['CodeCity'];
                        if (isset($subCityCache[$codeSubCity]))
                        {
                            $subCity = $subCityCache[$codeSubCity];
                        } else
                        {
                            $subCity = $mongo->cities->findOne(
                                    array(
                                        'Bad' => false,
                                        'CodeCity' => $codeSubCity,
                                        'CodeDistrict' => (int) $city['CodeDistrict'],
                                        'CodeRegion' => $city['CodeRegion']));

                            $subCityCache[$codeSubCity] = $subCity ? $subCity : false;
                        }

                        $street['SubCity'] = $subCity;



                        if ($first)
                        {
                            $first = false;
                        } else
                        {
                            fwrite($fp, ',' . PHP_EOL);
                        }
                        fwrite($fp, $this->streetToJson($street));
                    }

                    fwrite($fp, ']}');
                }

                fclose($fp);
                copy($tmp, $this->getCachePath($cacheKey));
                unlink($tmp);
            }

            if ($direct && $format == self::FORMAT_JSON)
            {
                $data = file_get_contents($this->getCachePath($cacheKey));
                $data = json_decode($data);
                $result->result = $data->result;
                return;
            }
            $result->fileToSend = $this->getCachePath($cacheKey);
        }

        /**
         * Собирает города
         * 
         * @param string $typeCode
         * @param string $format CSV или JSON формат
         * @param \Kladr\Core\Plugins\Base\PluginResult $result
         */
        private function processCities($typeCode, $format, PluginResult $result)
        {
            set_time_limit(600);
            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '600M');

            $format = ($format == self::FORMAT_JSON ? self::FORMAT_JSON : self::FORMAT_CSV);

            $cacheKey = 'all_cities';

            $typeCodes = FindPlugin::ConvertCodeTypeToArray($typeCode);

            if ($typeCodes != null)
                foreach ($typeCodes as $code)
                    $cacheKey .= '_' . $code;

            $cacheKey .= ( $format == self::FORMAT_JSON ? '_json' : '' );

            if (!$this->checkCache($cacheKey))
            {
                $cities = new Cities();
                $mongo = $cities->getConnection();

                $tmp = $this->getCachePath($cacheKey) . '_' . rand(10000, 10000000);
                $cities = $typeCodes == null ?
                        $mongo->cities->find(array('Bad' => false)) :
                        $mongo->cities->find(array(
                            'Bad' => false,
                            'TypeCode' => array('$in' => $typeCodes)));

                $fp = fopen($tmp, 'w');

                if ($format == self::FORMAT_CSV)
                {
                    fputcsv($fp, $this->cityToArray());
                    foreach ($cities as $city)
                    {
                        $districtCode = $city['CodeDistrict'];
                        $regionCode = $city['CodeRegion'];

                        $district = null;
                        $region = null;
                        if ($districtCode != null)
                        {
                            $district = $mongo->district->findOne(array(
                                'CodeDistrict' => (int) $districtCode,
                                'CodeRegion' => (int) $regionCode,
                                'Bad' => false));
                        }

                        $region = $mongo->regions->findOne(array(
                            'CodeRegion' => (int) $regionCode,
                            'Bad' => false));

                        fputcsv($fp, $this->cityToArray($city, $district, $region));
                    }
                } else
                {
                    fwrite($fp, '{ "result" : [');
                    $first = true;
                    foreach ($cities as $city)
                    {
                        if ($first)
                        {
                            $first = false;
                        } else
                        {
                            fwrite($fp, ',' . PHP_EOL);
                        }

                        $districtCode = $city['CodeDistrict'];
                        $regionCode = $city['CodeRegion'];

                        $district = null;
                        $region = null;
                        if ($districtCode != null)
                        {
                            $district = $mongo->district->findOne(array(
                                'CodeDistrict' => (int) $districtCode,
                                'CodeRegion' => (int) $regionCode,
                                'Bad' => false));
                        }

                        $region = $mongo->regions->findOne(array(
                            'CodeRegion' => (int) $regionCode,
                            'Bad' => false));

                        fwrite($fp, $this->cityToJson($city, $district, $region));
                    }

                    fwrite($fp, ']}');
                }
                fclose($fp);

                copy($tmp, $this->getCachePath($cacheKey));
                unlink($tmp);
            }

            $result->fileToSend = $this->getCachePath($cacheKey);
        }

        /**
         * Формирует массив для отдачи клиенту
         * 
         * @param type $city
         * @param type $district
         * @param type $region
         * @return type
         */
        private function cityToArray($city = null, $district = null, $region = null)
        {
            if ($city == null)
                return array(
                    'cityId' => 'CityId',
                    'cityName' => 'CityName',
                    'cityOkato' => 'CityOkato',
                    'cityType' => 'CityType',
                    'cityTypeShort' => 'CityTypeShort',
                    'cityZip' => 'CityZipCode',
                    'districtId' => 'DistrictId',
                    'districtName' => 'DistrictName',
                    'districtOkato' => 'DistrictOkato',
                    'districtType' => 'DistrictType',
                    'districtTypeShort' => 'DistrictTypeShort',
                    'regionId' => 'RegionId',
                    'regionName' => 'RegionName',
                    'regionOkato' => 'RegionOkato',
                    'regionType' => 'RegionType',
                    'regionTypeShort' => 'RegionTypeShort'
                );

            return array(
                'cityId' => $city['Id'],
                'cityName' => $city['Name'],
                'cityOkato' => $city['Okato'],
                'cityType' => $city['Type'],
                'cityTypeShort' => $city['TypeShort'],
                'cityZip' => $city['ZipCode'],
                'districtId' => $district ? $district['Id'] : '',
                'districtName' => $district ? $district['Name'] : '',
                'districtOkato' => $district ? $district['Okato'] : '',
                'districtType' => $district ? $district['Type'] : '',
                'districtTypeShort' => $district ? $district['TypeShort'] : '',
                'regionId' => $region['Id'],
                'regionName' => $region['Name'],
                'regionOkato' => $region['Okato'],
                'regionType' => $region['Type'],
                'regionTypeShort' => $region['TypeShort']
            );
        }

        /**
         * Сохраняет город в json формате
         * 
         * @param type $city
         * @param type $district
         * @param type $region
         * @return type
         */
        private function cityToJson($city = null, $district = null, $region = null)
        {
            $data = array(
                'id' => $city['Id'],
                'name' => $city['Name'],
                'okato' => $city['Okato'],
                'type' => $city['Type'],
                'typeShort' => $city['TypeShort'],
                'zip' => $city['ZipCode'],
                'parents' => array()
            );

            if ($district && $district['Id'] != null)
            {
                $data['parents'][] = array(
                    'id' => $district['Id'],
                    'name' => $district['Name'],
                    'okato' => $district['Okato'],
                    'type' => $district['Type'],
                    'typeShort' => $district['TypeShort'],
                    'contentType' => Districts::ContentType
                );
            }

            if ($region['Id'] != null)
            {
                $data['parents'][] = array(
                    'id' => $region['Id'],
                    'name' => $region['Name'],
                    'okato' => $region['Okato'],
                    'type' => $region['Type'],
                    'typeShort' => $region['TypeShort'],
                    'contentType' => Regions::ContentType
                );
            }
            return json_encode($data);
        }

        /**
         * Сохраняет улицу в json формате
         * 
         * @param type $street
         * @return type
         */
        private function streetToJson($street = null)
        {

            return json_encode(
                    array(
                        'id' => $street['Id'],
                        'name' => $street['Name'],
                        'okato' => $street['Okato'],
                        'zip' => $street['ZipCode'],
                        'type' => $street['Type'],
                        'typeShort' => $street['TypeShort'],
                        'parentName' => $street['SubCity'] ? $street['SubCity']['Name'] : '',
                        'parentType' => $street['SubCity'] ? $street['SubCity']['Type'] : '',
                        'parentShortType' => $street['SubCity'] ? $street['SubCity']['TypeShort'] : ''
            ));
        }

        /**
         * Формирует массив для отдачи клиенту
         * @param type $street
         * @return type
         */
        private function streetToArray($street = null)
        {

            if ($street == null)
                return array(
                    'id' => 'Id',
                    'name' => 'Name',
                    'okato' => 'Okato',
                    'zipCode' => 'ZipCode',
                    'type' => 'Type',
                    'typeShort' => 'TypeShort',
                    'parentName' => 'ParentName',
                    'parentType' => 'ParentType',
                    'parentShortType' => 'ParentShortType'
                );

            return array(
                'id' => $street['Id'],
                'name' => $street['Name'],
                'okato' => $street['Okato'],
                'zipCode' => $street['ZipCode'],
                'type' => $street['Type'],
                'typeShort' => $street['TypeShort'],
                'parentName' => $street['SubCity'] ? $street['SubCity']['Name'] : '',
                'parentType' => $street['SubCity'] ? $street['SubCity']['Type'] : '',
                'parentShortType' => $street['SubCity'] ? $street['SubCity']['TypeShort'] : ''
            );
        }

        /**
         * Возвращает путь до кеша
         * 
         * @param string $cacheKey  Ключ кеша
         * @return string
         */
        private function getCachePath($cacheKey)
        {
            return $this->cacheDir . $cacheKey;
        }

        /**
         * Проверят есть ли кеш
         * 
         * @param string $cacheKey Ключ кеша
         * @return boolean
         */
        private function checkCache($cacheKey)
        {
            $filepath = $this->getCachePath($cacheKey);
            if (!file_exists($filepath))
                return false;

            return filesize($filepath) > 0;
        }

        /**
         * Проверяет, надо ли включать данный плагин
         * @param \Phalcon\Http\Request $request
         * @return bool 
         */
        private function check(Request $request)
        {
            return $request->getQuery('cmd') && $request->getQuery('cmd') == self::COMMAND;
        }

    }

}
