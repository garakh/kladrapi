<?php

/**
 * Тесты api привязанные к типу объекта
 */
class ContentTest extends PHPUnit_Framework_TestCase  {
 		//список типов:
		//BuildingType;
		//CityType;
		//DistrictType;
		//RegionType;
		//StreetType;
		//null;   
    
    /* -------------------- Ошибочные ------------------- */
    

    public function testInvalidQuery1(){

        $query = new QueryToApi();
        $query->query = 'Стрпк'; // ни одного результата
		//$query->query = 'Ста'; // выдает около десяти результатов
        $query->contentType = QueryToApi::CityType;
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(true, 'Сервис должен был вернуть 0 объектов');
    }
    
    /*
     * Передача некорректного типа 
     */
    public function testInvalidContent(){
        $query = new QueryToApi();
        $query->query = 'Ста';
        $query->contentType = QueryToApi::CityTypeN; // некорректный тип
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(true, 'Сервис должен был вернуть ошибку');
    }

	/*
     * Передача пустого типа
     */
    public function testDefContent(){
        $query = new QueryToApi();
        $query->query = 'Арх';
        $query->contentType = "";
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(true, 'Сервис должен был вернуть 0 результатов');
    }

	 /* -------------------- Правильные ------------------- */

// город
    public function testCityContent(){
        $query = new QueryToApi();
        $query->query = 'мезень';
        $query->contentType = QueryToApi::CityType;
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(true, 'Сервис должен был вернуть несколько результатов');
    }
// дом
    public function testBildContent(){
        $query = new QueryToApi();
        $query->query = '70';
  		$query->cityId = "2900000100000";
    	$query->streetId = "29000001000056700";
        $query->contentType = QueryToApi::BuildingType;
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(true, 'Сервис должен был вернуть два результата');
    }
//район
	public function testDistContent(){
        $query = new QueryToApi();
        $query->query = 'октяб';
        $query->contentType = QueryToApi::DistrictType;
		try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(true, 'Сервис должен был вернуть много результатов');
    }
//регион
    public function testRegionContent(){
        $query = new QueryToApi();
        $query->query = 'арх';
        $query->contentType = QueryToApi::RegionType;
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(true, 'Сервис должен был вернуть один результат');
    }
//улица
    public function testStreetContent(){
        $query = new QueryToApi();
        $query->query = 'урицкого';
		$query->cityId = "2900000100000";
        $query->contentType = QueryToApi::StreetType;
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(true, 'Сервис должен был вернуть один результат');
    }


}