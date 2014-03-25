<?php

//use Kladr\Core\Models\Regions;
//use Kladr\Core\Models\Districts;
//use Kladr\Core\Models\Cities;
//use Kladr\Core\Models\Streets;
//use Kladr\Core\Models\Buildings;
//
//include '../apps/core/models/Cities.php';
//include '../apps/core/models/Buildings.php';
//include '../apps/core/models/Regions.php';
//include '../apps/core/models/Districts.php';
//include '../apps/core/models/Streets.php';


/**
 * Тесты api, привязанные к типу объекта
 */
class ContentTest extends PHPUnit_Framework_TestCase  {

    
/**
 * Тест города
 */
    public function testCityContent(){
        $query = new QueryToApi();
        $query->query = 'мезень';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
	$res = $res->result[0];
        $this->assertEquals($res->contentType,"city" );
	}
	
/**
 * Тест здания
 */
    public function testBuildingContent(){
        $query = new QueryToApi();
        $query->query = '70';
  	$query->cityId = "2900000100000";
    	$query->streetId = "29000001000056700";
        $query->contentType = QueryToApi::BuildingType;
        
        $res = $query->send();
	$res = $res->result[0];
			
        $this->assertEquals($res->contentType,"building" );	
	}

/**
 * Тест района
 */
    public function testDistrictContent(){
        $query = new QueryToApi();
        $query->query = 'октяб';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
	$res = $res->result[0];
			
        $this->assertEquals($res->contentType, "district" );		
	}

/**
 * Тест региона
 */
    public function testRegionContent(){
        $query = new QueryToApi();
        $query->query = 'арх';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
	$res = $res->result[0];
			
        $this->assertEquals($res->contentType,"region" );	
	}

/**
 * Тест улицы
 */
    public function testStreetContent(){
        $query = new QueryToApi();
        $query->query = 'урицкого';
	$query->cityId = "2900000100000";
        $query->contentType = QueryToApi::StreetType;
        
        $res = $query->send();
	$res = $res->result[0];
			
        $this->assertEquals($res->contentType,"street" );		
	}
}

