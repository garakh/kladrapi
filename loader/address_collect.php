<?php
//exit(); // Деактивация

$connectString = 'mongodb://127.0.0.1:27017';

MongoCursor::$timeout = -1;

try {
    $conn = new MongoClient($connectString);
    $db = $conn->kladr;
    
    AddressCollect($db);
    
    $conn->close();
} catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
} catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
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
        ), array(
            'NormalizedName' => 1,
        ));
        
        if($arRegion) $arAddress = array_merge($arAddress, $arRegion['NormalizedName']);
        
        $arDistrict = $districts->findOne(array(
            'CodeRegion' => $arStreet['CodeRegion'],
            'CodeDistrict' => $arStreet['CodeDistrict'],
        ), array(
            'NormalizedName' => 1,
        ));
        
        if($arDistrict) $arAddress = array_merge($arAddress, $arDistrict['NormalizedName']);
        
        $arCities = $cities->findOne(array(
            'CodeRegion' => $arStreet['CodeRegion'],
            'CodeDistrict' => $arStreet['CodeDistrict'],
            'CodeCity' => $arStreet['CodeCity'],
        ), array(
            'NormalizedName' => 1,
        ));
        
        if($arCities )$arAddress = array_merge($arAddress, $arCities['NormalizedName']);
        
        $arAddress = array_merge($arAddress, $arStreet['NormalizedName']);

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