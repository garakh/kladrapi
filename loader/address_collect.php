<?php
//exit(); // Деактивация

$connectString = 'mongodb://127.0.0.1:27017';

MongoCursor::$timeout = -1;

try {
    $conn = new MongoClient($connectString);
    $db = $conn->kladr;    
    
//    AddressCollect($db);
//    
//    $conn->close();
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
    $districts = $db->distrct;
    $regions = $db->regions;
    $buildings = $db->buildings;
    
    /*
     * Массив всех элементов будущей таблицы
     */
    $allElements = array();
    
    // Здания
    $allBuildings = $buildings->find(array(), array(
        'NormalizedName' => 1,
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
        foreach ($arBuilding['NormalizedName'] as $name)
        {
            //создаём по элементу в будущей БД для каждого здания
            $building = $arBuilding;
            $building['NormalizedName'] = array($name);
            $building['Sort'] = 50;
            $building['BuildingId'] = $arBuilding['Id'];
            $building['Address'] = array();
            $building['Address'] = array_merge($building['Address'], (array)$name);
            $building['FullName'] .= $name; //пока такое поле, потом -- посмотрим
            $building['StreetId'] = null;
            $building['CityId'] = null;
            $building['DistrictId'] = null;
            $building['RegionId'] = null;         
            
            //ищем айдишники её городов и заполняем поле address т.п.
            $street = $streets->findOne(array('CodeStreet' => $building['CodeStreet']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
            if ($street)
            {
            $building['StreetId'] = $street['Id'];
            $building['FullName'] .= ' ' . $street['Name'];
            $building['Address'] = array_merge($building['Address'], $street['NormalizedName']);
            }
            
            $city = $cities->findOne(array('CodeCity' => $building['CodeCity']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
            if ($city)
            {
            $building['CityId'] = $city['Id'];
            $building['FullName'] .= ' ' . $city['Name'];
            $building['Address'] = array_merge($building['Address'], $city['NormalizedName']);  
            }
            
            $district = $districts->findOne(array('CodeDistrict' => $building['CodeDistrict']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
            if ($district)
            {
            $building['DistrictId'] = $district['Id'];
            $building['FullName'] .= ' ' . $district['Name'];
            $building['Address'] = array_merge($building['Address'], $district['NormalizedName']);   
            }
            
            $region = $regions->findOne(array('CodeRegion' => $building['CodeRegion']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1)) ;       
            if ($region)
            {
            $building['RegionId'] = $region['Id'];
            $building['FullName'] .= ' ' . $region['Name'];
            $building['Address'] = array_merge($building['Address'], $region['NormalizedName']); 
            }
            
            $allElements[] = $building;
        }
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
        $street['FullName'] .= $arStreet['Name'];
        $street['CityId'] = null;
        $street['DistrictId'] = null;
        $street['RegionId'] = null; 
        
        //ищем айдишники, заполняем адрес
        $city = $cities->findOne(array('CodeCity' => $street['CodeCity']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
        if ($city)
        {
        $street['CityId'] = $city['Id'];
        $street['FullName'] .= ' ' . $city['Name'];
        $street['Address'] = array_merge($street['Address'], $city['NormalizedName']); 
        }     
        
        $district = $districts->findOne(array('CodeDistrict' => $street['CodeDistrict']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
        if ($district)
        {
        $street['DistrictId'] = $district['Id'];
        $street['FullName'] .= ' ' . $district['Name'];
        $street['Address'] = array_merge($street['Address'], $district['NormalizedName']);
        }
        
        $region = $regions->findOne(array('CodeRegion' => $street['CodeRegion']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
        if ($region)
        {
        $street['RegionId'] = $region['Id'];
        $street['FullName'] .= ' ' . $region['Name'];
        $street['Address'] = array_merge($street['Address'], $region['NormalizedName']);    
        }
              
        $allElements[] = $street;
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
        $city['FullName'] .= $arCity['Name'];
        $city['DistrictId'] = null;
        $city['RegionId'] = null; 
        
        //айдишники, address
        $district = $districts->findOne(array('CodeDistrict' => $city['CodeDistrict']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
        if ($district)
        {
        $city['DistrictId'] = $district['Id'];
        $city['FullName'] .= ' ' . $district['Name'];
        $city['Address'] = array_merge($city['Address'], $district['NormalizedName']);   
        }
        
        $region = $regions->findOne(array('CodeRegion' => $city['CodeRegion']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
        if ($region)
        {
        $city['RegionId'] = $region['Id'];
        $city['FullName'] .= ' ' . $region['Name'];
        $city['Address'] = array_merge($city['Address'], $region['NormalizedName']);   
        }
        
        $allElements[] = $city;
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
        $district['FullName'] .= $arDistrict['Name'];
        $city['RegionId'] = null; 
        
        //айдишники, address
        $region = $regions->findOne(array('CodeRegion' => $region['CodeRegion']), array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1));
        if ($region)
        {
        $district['RegionId'] = $region['Id'];
        $district['FullName'] .= ' ' . $region['Name'];
        $district['Address'] = array_merge($district['Address'], $region['NormalizedName']);   
        }       
        
        $allElements[] = $district;
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
        $region['FullName'] .= $arRegion['Name'];
        
        $allElements[] = $region;
    }
    
    //удаляем-создаём таблицу
    $db->complex->drop();
    $db->createCollection('complex');
    //вставляем все элементы в бд   
    foreach ($allElements as $elem)
    {
        unset($elem['_id']);//избегаем ненужных конфликтов
        $db->complex->insert($elem);        
    }
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