<?php
//exit(); // Деактивация

$connectString = 'mongodb://127.0.0.1:27017';

MongoCursor::$timeout = -1;

try {
    $conn = new MongoClient($connectString);
    //$db = $conn->kladr;    
    $db = $conn->kladr;

    forOneStringCollect($db);

    $conn->close();
} catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
} catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
}


function getCityOwnerId($id)
{
    /*
       01 234 567 89A BC
       16 031 001 001 00
    */

    if (strlen($id) < 13)
        return null;

    $id = substr($id, 0, 13);

    $id[8] = '0';
    $id[9] = '0';
    $id[10] = '0';
    $id[11] = '0';
    $id[12] = '0';

    return $id;
}

/*
 * Проходит по таблицам созданной БД, создавая ещё одну, предназначенную для работы поиска одной строкой.
 */
function forOneStringCollect(MongoDB $db)
{
    $streets = $db->streets;
    $cities = $db->cities;
    $districts = $db->district;
    $regions = $db->regions;
    $buildings = $db->buildings;

    /*
    * Массив всех элементов будущей таблицы
    */

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

    echo 'buildings';
    $i = 0;

    foreach ($allBuildings as $arBuilding) {
        if ($i++ % 10000 == 0)
            echo $i . '; ';

        //здания с одним id
        $building = $arBuilding;
        $building['NormalizedName'] = $arBuilding['NormalizedName'];
        $building['Sort'] = 50;
        $building['BuildingId'] = $arBuilding['Id'];
//        $building['Address'] = array();
//        $building['Address'] = array_merge($building['Address'], $arBuilding['NormalizedName']);
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
        $building['NormalizedRegionName'] = null;
        $building['ContentType'] = 'building';

        //ищем айдишники её городов и заполняем поле address т.п.
        $street = $streets->findOne(array(
                'CodeStreet' => $building['CodeStreet'],
                'CodeCity' => $building['CodeCity'],
                'CodeDistrict' => $building['CodeDistrict'],
                'CodeRegion' => $building['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($street) {
            $building['StreetId'] = $street['Id'];
//            $building['Address'] = array_merge($building['Address'], $street['NormalizedName']);
            $building['NormalizedStreetName'] = $street['NormalizedName'];
        }

        $city = $cities->findOne(array(
                'CodeCity' => $building['CodeCity'],
                'CodeDistrict' => $building['CodeDistrict'],
                'CodeRegion' => $building['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($city) {
            $building['CityId'] = $city['Id'];
            $building['NormalizedCityName'] = $city['NormalizedName'];
        }
		$cityOwner = null;
		$ownerId = getCityOwnerId($building['BuildingId']);
		if($ownerId && $ownerId != $city['Id'])
		{
			$cityOwner = $cities->findOne(array(
                'Id' => $ownerId,
                'Bad' => false),
			
			array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

			if ($cityOwner) {
				$building['CityOwnerId'] = $cityOwner['Id'];
				$building['NormalizedCityOwnerName'] = $cityOwner['NormalizedName'];
			}
		}
        $district = $districts->findOne(array(
                'CodeDistrict' => $building['CodeDistrict'],
                'CodeRegion' => $building['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($district) {
            $building['DistrictId'] = $district['Id'];
//            $building['Address'] = array_merge($building['Address'], $district['NormalizedName']);   
            $building['NormalizedDistrictName'] = $district['NormalizedName'];
        }

        $region = $regions->findOne(array(
                'CodeRegion' => $building['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($region) {
            $building['RegionId'] = $region['Id'];
//            $building['Address'] = array_merge($building['Address'], $region['NormalizedName']); 
            $building['NormalizedRegionName'] = $region['NormalizedName'];
        }

        //собираем все тайпы
        //typesCollect($building, $region, $district, $city, $street, $building);

        //и имя
        constructFullName($building, $region, $district, $city, $street, $cityOwner);

        unset($building['_id']); //избегаем ненужных конфликтов
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

    echo 'streets';
    $i = 0;

    foreach ($allStreets as $arStreet) {
        if ($i++ % 10000 == 0)
            echo $i . '; ';

        //сама улица
        $street = $arStreet;
        $street['Sort'] = 40;
        $street['StreetId'] = $arStreet['Id'];
//        $street['Address'] = array();
//        $street['Address'] = array_merge($street['Address'], $arStreet['NormalizedName']);
        $street['FullName'] = null;
        $street['CityId'] = null;
        $street['DistrictId'] = null;
        $street['RegionId'] = null;
        $street['NormalizedStreetName'] = $arStreet['NormalizedName'];
        $street['NormalizedCityName'] = null;
        $street['NormalizedDistrictName'] = null;
        $street['NormalizedRegionName'] = null;
        $street['ContentType'] = 'street';

        //ищем айдишники, заполняем адрес
        $city = $cities->findOne(array(
                'CodeCity' => $street['CodeCity'],
                'CodeDistrict' => $street['CodeDistrict'],
                'CodeRegion' => $street['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($city) {
            $street['CityId'] = $city['Id'];
//            $street['Address'] = array_merge($street['Address'], $city['NormalizedName']); 
            $street['NormalizedCityName'] = $city['NormalizedName'];
        }

		
		$cityOwner = null;
		$ownerId = getCityOwnerId($street['StreetId']);
		if($ownerId && $ownerId != $city['Id'])
		{
		
			$cityOwner = $cities->findOne(array(
                'Id' => $ownerId,
                'Bad' => false),
				array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

			if ($cityOwner) {
				$street['CityOwnerId'] = $cityOwner['Id'];
				$street['NormalizedCityOwnerName'] = $cityOwner['NormalizedName'];
			}
		}
        $district = $districts->findOne(array(
                'CodeDistrict' => $street['CodeDistrict'],
                'CodeRegion' => $street['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($district) {
            $street['DistrictId'] = $district['Id'];
//            $street['Address'] = array_merge($street['Address'], $district['NormalizedName']);
            $street['NormalizedDistrictName'] = $district['NormalizedName'];
        }

        $region = $regions->findOne(array(
                'CodeRegion' => $street['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($region) {
            $street['RegionId'] = $region['Id'];
//            $street['Address'] = array_merge($street['Address'], $region['NormalizedName']);  
            $street['NormalizedRegionName'] = $region['NormalizedName'];
        }

        //typesCollect($street, $region, $district, $city, $street);

        constructFullName($street, $region, $district, $city, $street, $cityOwner);

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
        'CodeCity' => 1,
        'Sort' => 1
    ));

    echo 'cities';
    $i = 0;

    foreach ($allCities as $arCity) {
        if ($i++ % 10000 == 0)
            echo $i . '; ';

        //город
        $city = $arCity;
        //$city['Sort'] = 30;
        $city['CityId'] = $arCity['Id'];
//        $city['Address'] = array();
//        $city['Address'] = array_merge($city['Address'], $arCity['NormalizedName']);
        $city['FullName'] = null;
        $city['DistrictId'] = null;
        $city['RegionId'] = null;
        $city['NormalizedCityName'] = $arCity['NormalizedName'];
        $city['NormalizedDistrictName'] = null;
        $city['NormalizedRegionName'] = null;
        $city['ContentType'] = 'city';

        //сортировка городов
        $sort = $city['Sort'];
        $sort /= 1000;
        if ($sort > 9) {
            $sort = 9;
        }
        $city['Sort'] = 30 + $sort;
        //айдишники, address
        $district = $districts->findOne(array(
                'CodeDistrict' => $city['CodeDistrict'],
                'CodeRegion' => $city['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($district) {
            $city['DistrictId'] = $district['Id'];
//            $city['Address'] = array_merge($city['Address'], $district['NormalizedName']);   
            $city['NormalizedDistrictName'] = $district['NormalizedName'];
        }

        $region = $regions->findOne(array(
                'CodeRegion' => $city['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($region) {
            $city['RegionId'] = $region['Id'];
//            $city['Address'] = array_merge($city['Address'], $region['NormalizedName']); 
            $city['NormalizedRegionName'] = $region['NormalizedName'];
        }

		$cityOwner = null;
		$ownerId = getCityOwnerId($city['CityId']);
		if($ownerId && $ownerId != $city['CityId'])
		{		
			$cityOwner = $cities->findOne(array(
					'Id' => $ownerId,
					'Bad' => false),
				array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

			if ($cityOwner) {
				$city['CityOwnerId'] = $cityOwner['Id'];
				$city['NormalizedCityOwnerName'] = $cityOwner['NormalizedName'];
			}
		}
        //typesCollect($city, $region, $district, $city);

        constructFullName($city, $region, $district, $city, null, $cityOwner);

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

    echo 'districts';
    $i = 0;

    foreach ($allDistricts as $arDistrict) {
        if ($i++ % 10000 == 0)
            echo $i . '; ';

        //район
        $district = $arDistrict;
        $district['Sort'] = 20;
        $district['DistrictId'] = $arDistrict['Id'];
//        $district['Address'] = array();
//        $district['Address'] = array_merge($district['Address'], $arDistrict['NormalizedName']);
        $district['FullName'] = null;
        $district['RegionId'] = null;
        $district['NormalizedDistrictName'] = $arDistrict['NormalizedName'];
        $district['NormalizedRegionName'] = null;
        $district['ContentType'] = 'district';

        //айдишники, address
        $region = $regions->findOne(array(
                'CodeRegion' => $district['CodeRegion'],
                'Bad' => false),
            array('Name' => 1, 'NormalizedName' => 1, 'Id' => 1, 'TypeShort' => 1, 'Type' => 1));

        if ($region) {
            $district['RegionId'] = $region['Id'];
//            $district['Address'] = array_merge($district['Address'], $region['NormalizedName']);
            $district['NormalizedRegionName'] = $region['NormalizedName'];
        }

        //typesCollect($district, $region, $district);

        constructFullName($district, $region, $district);

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

    echo 'regions';
    $i = 0;

    foreach ($allRegions as $arRegion) {
        if ($i++ % 10000 == 0)
            echo $i . '; ';

        //область
        $region = $arRegion;
        $region['Sort'] = 10;
        $region['RegionId'] = $arRegion['Id'];
//        $region['Address'] = array();
//        $region['Address'] = array_merge($region['Address'], $arRegion['NormalizedName']);
        $region['NormalizedRegionName'] = $arRegion['NormalizedName'];
        $region['FullName'] = null;
        $region['ContentType'] = 'region';

        //typesCollect($region, $region);

        constructFullName($region, $region);

        unset($region['_id']);
        $db->complex->insert($region);
    }

    echo 'table has done. Indexes...';

    $complex = $db->complex;

//    echo 'address;';
//    $complex->ensureIndex(
//        array('Address' => 1),
//        array('background' => true)
//    );

//    echo 'rn;';
//    $complex->ensureIndex(
//        array('NormalizedRegionName' => 1),
//        array('background' => true)
//    );

//    echo 'dn;';
//    $complex->ensureIndex(
//        array('NormalizedDistrictName' => 1),
//        array('background' => true)
//    );
//    
//    echo 'cn;';
//    $complex->ensureIndex(
//        array('NormalizedCityName' => 1),
//        array('background' => true)
//    );

//    echo 'sn;';
//    $complex->ensureIndex(
//        array('NormalizedStreetName' => 1),
//        array('background' => true)
//    );

    echo 'contentType;';
    $complex->ensureIndex(
        array('ContentType' => 1),
        array('background' => true)
    );

    echo 'bn;';
    $complex->ensureIndex(
        array('NormalizedBuildingName' => 1),
        array('background' => true)
    );

    echo 'sort;';
    $complex->ensureIndex(
        array('Sort' => 1),
        array('background' => true)
    );

    echo 'Id;';
    $complex->ensureIndex(
        array('Id' => 1),
        array('background' => true)
    );

    echo 'StreetId;';
    $complex->ensureIndex(
        array('StreetId' => 1),
        array('background' => true)
    );

    echo 'FullName';
    $complex->ensureIndex(
        array('FullName' => 1),
        array('background' => true)
    );

    echo 'indexes has done';
}

/*
* Собирает полное имя для элемента БД object, используя элементы street, city, district, region. Записывает полное имя
* в поле 'FullName', считывает имена элементов из 'Type' и 'Name'.
*/
function constructFullName(&$object, $region, $district = null, $city = null, $street = null, $cityOwner = null)
{
    if ($region) {
        if ($region['TypeShort'] == 'Респ' || $region['TypeShort'] == 'респ') {
            $object['FullName'] .= $region['Type'] . ' ';
            $object['FullName'] .= $region['Name'] . ' ';
        } else {
            $object['FullName'] .= $region['Name'] . ' ';
            $object['FullName'] .= $region['Type'] . ' ';
        }
    }

    if ($district) {
        if ($object['FullName']) {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);
        }
        $object['FullName'] .= $district['Name'] . ' ';
        $object['FullName'] .= $district['Type'] . ' ';
    }

    if ($cityOwner) {
        if ($object['FullName']) {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);
        }
        $object['FullName'] .= $cityOwner['Type'] . ' ';
        $object['FullName'] .= $cityOwner['Name'] . ' ';
    }

    if ($city) {
        if ($object['FullName']) {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);
        }
        $object['FullName'] .= $city['Type'] . ' ';
        $object['FullName'] .= $city['Name'] . ' ';
    }

    if ($street) {
        if ($object['FullName']) {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);
        }
        $object['FullName'] .= $street['Type'] . ' ';
        $object['FullName'] .= $street['Name'];
    }

    $object['FullName'] = trim($object['FullName']);
}

/*
 * Собирает полное имя с сокращёнными типа населённых пунктов
 *  для элемента БД object, используя элементы street, city, district, region. 
 * Записывает полное имя в поле 'FullName', считывает имена элементов из 'TypeShort' и 'Name'.
 */
function constructFullNameWithShorts(&$object, $region, $district = null, $city = null, $street = null)
{
    if ($region) {
        if ($region['TypeShort'] == 'Респ' || $region['TypeShort'] == 'респ') {
            $object['FullName'] .= $region['TypeShort'] . '. ';
            $object['FullName'] .= $region['Name'] . ' ';
        } else {
            $object['FullName'] .= $region['Name'] . ' ';
            $object['FullName'] .= $region['TypeShort'] . '. ';
        }
    }

    if ($district) {
        if ($object['FullName']) {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);
        }
        $object['FullName'] .= $district['Name'] . ' ';
        $object['FullName'] .= $district['TypeShort'] . '. ';
    }

    if ($city) {
        if ($object['FullName']) {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);
        }
        $object['FullName'] .= $city['TypeShort'] . '. ';
        $object['FullName'] .= $city['Name'] . ' ';
    }

    if ($street) {
        if ($object['FullName']) {
            $object['FullName'] = preg_replace('/\ $/', ', ', $object['FullName']);
        }
        $object['FullName'] .= $street['TypeShort'] . '. ';
        $object['FullName'] .= $street['Name'];
    }

    $object['FullName'] = trim($object['FullName']);
}

/*
 * Добавляет в массив Address объекта типы и сокращённые типы этого объекта и его родителей
 */
function typesCollect(&$object, $region, $district = null, $city = null, $street = null, $building = null)
{
    $types = array();

    if ($region) {
        $types[] = $region['Type'];

        $newShort = true;
        foreach ($types as $type) {
            if (preg_match('/^' . $region['TypeShort'] . '/', $type)) {
                $newShort = false;
                break;
            }
        }

        if ($newShort) {
            $types[] = $region['TypeShort'];
        }
    }

    if ($district) {
        $newType = true;
        $newShort = true;
        foreach ($types as $type) {
            if (preg_match('/^' . $district['TypeShort'] . '/', $type)) {
                $newShort = false;
                break;
            }
            if (preg_match('/^' . $district['Type'] . '/', $type)) {
                $newType = false;
                break;
            }
        }

        if (preg_match('/^' . $district['TypeShort'] . '/', $district['Type'])) {
            $newShort = false;
        }

        if ($newShort) {
            $types[] = $district['TypeShort'];
        }

        if ($newType) {
            $types[] = $district['Type'];
        }
    }

    if ($city) {
        $newType = true;
        $newShort = true;
        foreach ($types as $type) {
            if (preg_match('/^' . $city['TypeShort'] . '/', $type)) {
                $newShort = false;
                break;
            }
            if (preg_match('/^' . $city['Type'] . '/', $type)) {
                $newType = false;
                break;
            }
        }

        if ($newShort) {
            $types[] = $city['TypeShort'];
        }

        if ($newType) {
            $types[] = $city['Type'];
        }

        if (preg_match('/^' . $city['TypeShort'] . '/', $city['Type'])) {
            $newShort = false;
        }
    }

    if ($street) {
        $newType = true;
        $newShort = true;
        foreach ($types as $type) {
            if (preg_match('/^' . $street['TypeShort'] . '/', $type)) {
                $newShort = false;
                break;
            }
            if (preg_match('/^' . $street['Type'] . '/', $type)) {
                $newType = false;
                break;
            }
        }

        if (preg_match('/^' . $street['TypeShort'] . '/', $street['Type'])) {
            $newShort = false;
        }

        if ($newShort) {
            $types[] = $street['TypeShort'];
        }

        if ($newType) {
            $types[] = $street['Type'];
        }
    }

    if ($building) {
        $newType = true;
        $newShort = true;
        foreach ($types as $type) {
            if (preg_match('/^' . $building['TypeShort'] . '/', $type)) {
                $newShort = false;
                break;
            }
            if (preg_match('/^' . $building['Type'] . '/', $type)) {
                $newType = false;
                break;
            }
        }

        if (preg_match('/^' . $building['TypeShort'] . '/', $building['Type'])) {
            $newShort = false;
        }

        if ($newShort) {
            $types[] = $building['TypeShort'];
        }

        if ($newType) {
            $types[] = $building['Type'];
        }
    }

    //мерджим все найденные адреса
    $object['Address'] = array_merge($object['Address'], $types);
}


function addressCollect(MongoDB $db)
{
    $streets = $db->streets;
    $cities = $db->cities;
    $districts = $db->districts;
    $regions = $db->regions;

    $allStreets = $streets->find(array(), array(
        'NormalizedName' => 1,
        'CodeRegion' => 1,
        'CodeDistrict' => 1,
        'CodeCity' => 1
    ));

    $i = 0;
    foreach ($allStreets as $arStreet) {

        if ($i++ % 10000 == 0)
            echo $i . '; ';

        $arAddress = array();

        $arRegion = $regions->findOne(array(
            'CodeRegion' => $arStreet['CodeRegion'],
            'Bad' => false
        ), array(
            'NormalizedName' => 1,
        ));

        if ($arRegion) $arAddress = array_merge($arAddress, $arRegion['NormalizedName']);

        $arDistrict = $districts->findOne(array(
            'CodeRegion' => $arStreet['CodeRegion'],
            'CodeDistrict' => $arStreet['CodeDistrict'],
            'Bad' => false
        ), array(
            'NormalizedName' => 1,
        ));

        if ($arDistrict) $arAddress = array_merge($arAddress, $arDistrict['NormalizedName']);

        $arCities = $cities->findOne(array(
            'CodeRegion' => $arStreet['CodeRegion'],
            'CodeDistrict' => $arStreet['CodeDistrict'],
            'CodeCity' => $arStreet['CodeCity'],
            'Bad' => false
        ), array(
            'NormalizedName' => 1,
        ));

        if ($arCities) $arAddress = array_merge($arAddress, $arCities['NormalizedName']);

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

print 'Script is successful';