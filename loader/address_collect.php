<?php
//exit(); // Деактивация

$connectString = 'mongodb://127.0.0.1:27017';

MongoCursor::$timeout = -1;

try {
    $conn = new MongoClient($connectString);
    $db = $conn->kladr;    
    
    ForOneStringCollect($db);
    
    $conn->close();
} catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
} catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
}

/*
 * Проходит по таблицам созданной БД, создавая ещё одну, предназначенную для работы поиска одной строкой.
 */
function ForOneStringCollect(MongoDB $db) {
    $streets = $db->streets;
    $cities = $db->cities;
    $districts = $db->district;
    $regions = $db->regions;
    $buildings = $db->buildings;
    
    /*
     * Массив всех элементов будущей таблицы
     */
    //$allElements = array();
    
    //удаляем-создаём таблицу
    $db->complex->drop();
    $db->createCollection('complex');
    
    // Здания
    $allBuildings = $buildings->find(array(), array(
        'NormalizedName' => 1,
        'Name' => 1,
        'Id' => 1,
        'ZipCode' => 1,
        'TypeShort' => 1,
        'Type' => 1,
        'Okato' => 1,
        'CodeRegion' => 1,
        'CodeDistrict' => 1,
        'CodeCity' => 1,
        'CodeStreet' => 1,
        'CodeBuilding' => 1
    ));

    foreach ($allBuildings as $arBuilding)
    {
        //здания с одним id
        $building = $arBuilding;
        $building['NormalizedName'] = $arBuilding['NormalizedName'];      
        $building['Sort'] = 50;
        $building['BuildingId'] = $arBuilding['Id'];
        $building['Address'] = array();
        $building['Address'] = array_merge($building['Address'], $arBuilding['NormalizedName']);
        //$building['FullName'] .= $arBuilding['NormalizedName']; //без имени дома
        $building['FullName'] = null;
        $building['StreetId'] = null;
        $building['CityId'] = null;
        $building['DistrictId'] = null;
        $building['RegionId'] = null; 
        $building['NormalizedBuildingName'] = $arBuilding['NormalizedName'];
        $building['NormalizedStreetName'] = null;
        $building['NormalizedCityName'] = null;
        $building['NormalizedDistrictName'] = null;
        $building['NormalizedRegionName'] =  null;
        $building['ContentType'] = 'building';

        //ищем айдишники её городов и заполняем поле address т.п.
        $street = $streets->findOne(array(
            'CodeStreet' => $building['CodeStreet'], 
            'CodeCity' => $building['CodeCity'], 
            'CodeDistrict' => $building['CodeDistrict'], 
            'CodeRegion' => $building['CodeRegion'],
            'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($street)
        {
            $building['StreetId'] = $street['Id'];
            $building['Address'] = array_merge($building['Address'], $street['NormalizedName']);
            $building['NormalizedStreetName'] = $street['NormalizedName'];
        }

        $city = $cities->findOne(array(
            'CodeCity' => $building['CodeCity'], 
            'CodeDistrict' => $building['CodeDistrict'], 
            'CodeRegion' => $building['CodeRegion'],
            'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($city)
        {
            $building['CityId'] = $city['Id'];
            $building['Address'] = array_merge($building['Address'], $city['NormalizedName']);  
            $building['NormalizedCityName'] = $city['NormalizedName'];
        }

        $district = $districts->findOne(array(
            'CodeDistrict' => $building['CodeDistrict'],
            'CodeRegion' => $building['CodeRegion'],
            'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($district)
        {
            $building['DistrictId'] = $district['Id'];
            $building['Address'] = array_merge($building['Address'], $district['NormalizedName']);   
            $building['NormalizedDistrictName'] = $district['NormalizedName'];
        }

        $region = $regions->findOne(array(
            'CodeRegion' => $building['CodeRegion'],
            'Bad' => false), 
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));    
        
        if ($region)
        {
            $building['RegionId'] = $region['Id'];
            $building['Address'] = array_merge($building['Address'], $region['NormalizedName']); 
            $building['NormalizedRegionName'] = $region['NormalizedName'];
        }

        ConstructFullName($building, $region, $district, $city, $street);
        
        unset($building['_id']);//избегаем ненужных конфликтов
        $db->complex->insert($building);        
    }
    
    // Улицы
    $allStreets = $streets->find(array('Bad' => false), array(
        'NormalizedName' => 1,
        'Name' => 1,
        'Id' => 1,
        'ZipCode' => 1,
        'TypeShort' => 1,
        'Type' => 1,
        'Okato' => 1,
        'CodeRegion' => 1,
        'CodeDistrict' => 1,
        'CodeCity' => 1,
        'CodeStreet' => 1
    ));
    
    foreach ($allStreets as $arStreet)
    {
        //сама улица
        $street = $arStreet;
        $street['Sort'] = 40;
        $street['StreetId'] = $arStreet['Id'];
        $street['Address'] = array();
        $street['Address'] = array_merge($street['Address'], $arStreet['NormalizedName']);
        $street['FullName'] .= null;
        $street['CityId'] = null;
        $street['DistrictId'] = null;
        $street['RegionId'] = null;
        $street['NormalizedStreetName'] = $arStreet['NormalizedName'];
        $street['NormalizedCityName'] = null;
        $street['NormalizedDistrictName'] = null;
        $street['NormalizedRegionName'] =  null; 
        $street['ContentType'] = 'street';
        
        //ищем айдишники, заполняем адрес
        $city = $cities->findOne(array(
            'CodeCity' => $street['CodeCity'], 
            'CodeDistrict' => $street['CodeDistrict'],
            'CodeRegion' => $street['CodeRegion'],
            'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($city)
        {
            $street['CityId'] = $city['Id'];
            $street['Address'] = array_merge($street['Address'], $city['NormalizedName']); 
            $street['NormalizedCityName'] = $city['NormalizedName'];
        }     
        
        $district = $districts->findOne(array(
            'CodeDistrict' => $street['CodeDistrict'], 
            'CodeRegion' => $street['CodeRegion'],
            'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($district)
        {
            $street['DistrictId'] = $district['Id'];
            $street['Address'] = array_merge($street['Address'], $district['NormalizedName']);
            $street['NormalizedDistrictName'] = $district['NormalizedName'];
        }
        
        $region = $regions->findOne(array(
            'CodeRegion' => $street['CodeRegion'],
            'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($region)
        {
            $street['RegionId'] = $region['Id'];
            $street['Address'] = array_merge($street['Address'], $region['NormalizedName']);  
            $street['NormalizedRegionName'] = $region['NormalizedName'];
        }
        
        ConstructFullName($street, $region, $district, $city, $street);
        
        unset($street['_id']);
        $db->complex->insert($street);
    }
    
    // Города
    $allCities = $cities->find(array('Bad' => false), array(
        'NormalizedName' => 1,
        'Name' => 1,
        'Id' => 1,
        'ZipCode' => 1,
        'TypeShort' => 1,
        'Type' => 1,
        'Okato' => 1,
        'CodeRegion' => 1,
        'CodeDistrict' => 1,
        'CodeCity' => 1
    ));
    
    foreach ($allCities as $arCity)
    {
        //город
        $city = $arCity;
        $city['Sort'] = 30;
        $city['CityId'] = $arCity['Id'];
        $city['Address'] = array();
        $city['Address'] = array_merge($city['Address'], $arCity['NormalizedName']);
        $city['FullName'] .= null;
        $city['DistrictId'] = null;
        $city['RegionId'] = null; 
        $city['NormalizedCityName'] = $arCity['NormalizedName'];
        $city['NormalizedDistrictName'] = null;
        $city['NormalizedRegionName'] =  null; 
        $city['ContentType'] = 'city';
        
        //айдишники, address
        $district = $districts->findOne(array(
            'CodeDistrict' => $city['CodeDistrict'],
            'CodeRegion' => $city['CodeRegion'],
            'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($district)
        {
            $city['DistrictId'] = $district['Id'];
            $city['Address'] = array_merge($city['Address'], $district['NormalizedName']);   
            $city['NormalizedDistrictName'] = $district['NormalizedName'];
        }
        
        $region = $regions->findOne(array(
            'CodeRegion' => $city['CodeRegion'],
            'Bad' => false), 
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($region)
        {
            $city['RegionId'] = $region['Id'];
            $city['Address'] = array_merge($city['Address'], $region['NormalizedName']); 
            $city['NormalizedRegionName'] = $region['NormalizedName'];
        }
        
        ConstructFullName($city, $region, $district, $city);
        
        unset($city['_id']);
        $db->complex->insert($city);
    }
    
    // Районы
    $allDistricts = $districts->find(array('Bad' => false), array(
        'NormalizedName' => 1,
        'Name' => 1,
        'Id' => 1,
        'ZipCode' => 1,
        'TypeShort' => 1,
        'Type' => 1,
        'Okato' => 1,
        'CodeRegion' => 1,
        'CodeDistrict' => 1      
    ));
    
    foreach ($allDistricts as $arDistrict)
    {
        //район
        $district = $arDistrict;
        $district['Sort'] = 20;
        $district['DistrictId'] = $arDistrict['Id'];
        $district['Address'] = array();
        $district['Address'] = array_merge($district['Address'], $arDistrict['NormalizedName']);
        $district['FullName'] = null;
        $district['RegionId'] = null; 
        $district['NormalizedDistrictName'] = $arDistrict['NormalizedName'];
        $district['NormalizedRegionName'] = null;
        $district['ContentType'] = 'district';
        
        //айдишники, address
        $region = $regions->findOne(array(
            'CodeRegion' => $district['CodeRegion'],
            'Bad' => false), 
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1));
        
        if ($region)
        {
            $district['RegionId'] = $region['Id'];
            $district['Address'] = array_merge($district['Address'], $region['NormalizedName']);
            $district['NormalizedRegionName'] = $region['NormalizedName'];
        }       
        
        ConstructFullName($district, $region, $district);
        
        unset($district['_id']);
        $db->complex->insert($district);
    }
    
    // Области 
    $allRegions = $regions->find(array('Bad' => false), array(
        'NormalizedName' => 1,
        'Name' => 1,
        'Id' => 1,
        'ZipCode' => 1,
        'TypeShort' => 1,
        'Type' => 1,
        'Okato' => 1,
        'CodeRegion' => 1  
    ));
    
    foreach ($allRegions as $arRegion)
    {
        //область
        $region = $arRegion;
        $region['Sort'] = 10;
        $region['RegionId'] = $arRegion['Id'];
        $region['Address'] = array();
        $region['Address'] = array_merge($region['Address'], $arRegion['NormalizedName']);
        $region['NormalizedRegionName'] = $arRegion['NormalizedName'];
        $region['FullName'] = null;
        $region['ContentType'] = 'region';
        
        ConstructFullName($region, $region);
        
        unset($region['_id']);
        $db->complex->insert($region);
    }
    
    echo 'таблица сформирована. идёт постройка индексов';
    
    $complex = $db->complex;
    
    $complex->ensureIndex(
        array('Address' => 1),
        array('background' => true)
    );
    $complex->ensureIndex(
        array('NormalizedRegionName' => 1),
        array('background' => true)
    );
    $complex->ensureIndex(
        array('NormalizedDistrictName' => 1),
        array('background' => true)
    );
    $complex->ensureIndex(
        array('NormalizedCityName' => 1),
        array('background' => true)
    );
    $complex->ensureIndex(
        array('NormalizedStreetName' => 1),
        array('background' => true)
    );
    $complex->ensureIndex(
        array('NormalizedBuildingName' => 1),
        array('background' => true)
    );
    
    echo 'построение индексов завершено. приятной работы!';
}
/*
 * Собирает полное имя для элемента БД object, используя элементы street, city, district, region. Записывает полное имя
 * в поле 'FullName', считывает имена элементов из 'TypeShort' и 'Name'.
 */
function ConstructFullName(&$object, $region, $district = null, $city = null, $street = null)
{
    if ($region)
    {
        if ($region['TypeShort'] == 'Респ' || $region['TypeShort'] == 'респ')
        {
            $object['FullName'] .= $region['TypeShort'] . '. ';
            $object['FullName'] .= $region['Name'] . ' ';
        }
        else 
        {
            $object['FullName'] .= $region['Name'] . ' ';
            $object['FullName'] .= $region['TypeShort'] . '. ';
        }
    }
    
    if ($district)
    { 
        if ($object['FullName'])
        {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);            
        }
        $object['FullName'] .= $district['Name'] . ' ';
        $object['FullName'] .= $district['TypeShort'] . '. ';     
    }
   
    if ($city)
    {
        if ($object['FullName'])
        {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);            
        }
        $object['FullName'] .= $city['TypeShort'] . '. ';
        $object['FullName'] .= $city['Name'] . ' ';
    }
    
    if ($street)
    {
        if ($object['FullName'])
        {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);            
        }
        $object['FullName'] .= $street['TypeShort'] . '. ';
        $object['FullName'] .= $street['Name'];
    }
    
    $object['FullName'] = trim($object['FullName']);
}

function AddressCollect(MongoDB $db) {
    $streets   = $db->streets;
    $cities    = $db->cities;
    $districts = $db->districts;
    $regions   = $db->regions;
    
    $allStreets = $streets->find(array(), array(
        'NormalizedName' => 1,
        'CodeRegion' => 1,
        'CodeDistrict' => 1,
        'CodeCity' => 1
    ));

    $i = 0;
    foreach ($allStreets as $arStreet){

        if($i++ % 10000 == 0)
            echo $i.'; ';

        $arAddress = array();
        
        $arRegion = $regions->findOne(array(
            'CodeRegion' => $arStreet['CodeRegion'],
            'Bad' => false
        ), array(
            'NormalizedName' => 1,
        ));
        
        if($arRegion) $arAddress = array_merge($arAddress, $arRegion['NormalizedName']);
        
        $arDistrict = $districts->findOne(array(
            'CodeRegion' => $arStreet['CodeRegion'],
            'CodeDistrict' => $arStreet['CodeDistrict'],
            'Bad' => false
        ), array(
            'NormalizedName' => 1,
        ));
        
        if($arDistrict) $arAddress = array_merge($arAddress, $arDistrict['NormalizedName']);
        
        $arCities = $cities->findOne(array(
            'CodeRegion' => $arStreet['CodeRegion'],
            'CodeDistrict' => $arStreet['CodeDistrict'],
            'CodeCity' => $arStreet['CodeCity'],
            'Bad' => false
        ), array(
            'NormalizedName' => 1,
        ));
        
        if($arCities )$arAddress = array_merge($arAddress, $arCities['NormalizedName']);
        
        $arAddress = array_merge($arAddress, $arStreet['NormalizedName']);

        $arAddress = array_unique($arAddress);

        $streets->update(array(
            '_id' => $arStreet['_id']
        ), array(
            '$set' => array(
                'Address' => $arAddress
            )
        ));
    }
    
    $streets->ensureIndex(
        array('Address' => 1),
        array('background' => true)
    );
}

print 'Скрипт успешно выполнил свою работу';