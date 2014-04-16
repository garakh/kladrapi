<?php

namespace Kladr\Core\Plugins\General {

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

        const COMMAND = 'GetAllData';
        const PARAM_CITIES = 'City';
        const PARAM_STREETS = 'Street';

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
            if ($user == null || !$user->isPaid())
            {
                $prevResult->error = true;
                $prevResult->errorCode = 403;
                $prevResult->errorMessage = 'Функционал только для платных аккаунтов';

                return $prevResult;
            }

            switch ($request->getQuery('param1'))
            {
                case self::PARAM_CITIES : $this->processCities($prevResult);
                    break;
                case self::PARAM_STREETS : $this->processStreets($request->getQuery('param2'), $prevResult);
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
         * @param \Kladr\Core\Plugins\Base\PluginResult $result
         * @return void
         */
        private function processStreets($cityId, PluginResult $result)
        {
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

            $cacheKey = 'all_cities_' . $cityId;
            if (!$this->checkCache($cacheKey))
            {
                $cities = new Cities();
                $mongo = $cities->getConnection();

                $city = $mongo->cities->findOne(array('Id' => $cityId));

                $fp = fopen($this->getCachePath($cacheKey), 'w');
                fputcsv($fp, $this->streetToArray());

                foreach ($mongo->streets->find(array('Bad' => false, 'CodeCity' => (int) $city['CodeCity'])) as $street)
                {
                    fputcsv($fp, $this->streetToArray($street));
                }

                fclose($fp);
            }

            $result->fileToSend = $this->getCachePath($cacheKey);
        }

        /**
         * Собирает города
         * 
         * @param \Kladr\Core\Plugins\Base\PluginResult $result
         */
        private function processCities(PluginResult $result)
        {
            set_time_limit(600);
            ini_set('max_execution_time', 600);

            $cacheKey = 'all_cities';

            if (!$this->checkCache($cacheKey))
            {
                $cities = new Cities();
                $mongo = $cities->getConnection();
                $fp = fopen($this->getCachePath($cacheKey), 'w');
                fputcsv($fp, $this->cityToArray());
                foreach ($mongo->cities->find(array('Bad' => false)) as $city)
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

                fclose($fp);
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
                    'typeShort' => 'TypeShort'
                );

            return array(
                'id' => $street['Id'],
                'name' => $street['Name'],
                'okato' => $street['Okato'],
                'zipCode' => $street['ZipCode'],
                'type' => $street['Type'],
                'typeShort' => $street['TypeShort']
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