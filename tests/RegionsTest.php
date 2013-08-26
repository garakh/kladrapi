<?php

/**
 * Тест api при работе с регионами
 */
class RegionsTest extends PHPUnit_Framework_TestCase {
    
    
    /* --------------------------- Поиск по ID ------------------------------- */
        
    /**
     * Тест поиска Ненецкого автономного округа по Id
     */
    public function testSearchById1(){
        $query = new QueryToApi();
        $query->regionId = '8300000000000';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Ненецкий');
        $this->assertEquals($res->zip, '166000');
        $this->assertEquals($res->type, 'Автономный округ');
        $this->assertEquals($res->typeShort, 'АО');
    }
    
    /**
     * Тест поиска Адыгеи по Id
     */
    public function testSearchById2(){
        $query = new QueryToApi();
        $query->regionId = '0100000000000';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Адыгея');
        $this->assertEquals($res->zip, '385000');
        $this->assertEquals($res->type, 'Республика');
        $this->assertEquals($res->typeShort, 'Респ');
    }
    
    /**
     * Тест поиска Байконура по Id
     */
    public function testSearchById3(){
        $query = new QueryToApi();
        $query->regionId = '8900000000000';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Ямало-Ненецкий');
        $this->assertEquals($res->zip, '629000');
        $this->assertEquals($res->type, 'Автономный округ');
        $this->assertEquals($res->typeShort, 'АО');
    }
    
    
    /* ------------------------- Поиск по названию --------------------------- */
    
    /**
     * Тест поиска регионов с названием начинающимся на "А"
     */
    public function testSearchByName1(){
        $query = new QueryToApi();
        $query->query = 'а';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result;
        
        foreach($res as $region){
            $this->assertRegExp('/\s?а/iu', $region->name, $region->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска регионов с названием начинающимся на "К"
     */
    public function testSearchByName2(){
        $query = new QueryToApi();
        $query->query = 'r';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result;
        
        foreach($res as $region){
            $this->assertRegExp('/\s?к/iu', $region->name, $region->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска регионов с названием начинающимся на "Пе"
     */
    public function testSearchByName3(){
        $query = new QueryToApi();
        $query->query = 'пЕ';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result;
        
        foreach($res as $region){
            $this->assertRegExp('/\s?пе/iu', $region->name, $region->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска региона с названием "Хакасия"
     */
    public function testSearchByName4(){
        $query = new QueryToApi();
        $query->query = '[FRFCBZ';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->id, '1900000000000');
        $this->assertEquals($res->name, 'Хакасия');
        $this->assertEquals($res->zip, '655000');
        $this->assertEquals($res->type, 'Республика');
        $this->assertEquals($res->typeShort, 'Респ');      
    }
    
    
    /* --------------------------- Лимит ------------------------------------- */
    
    /**
     * Тест лимита
     */
    public function testLimit1(){
        $query = new QueryToApi();
        $query->query = 'к';
        $query->contentType = QueryToApi::RegionType;
        $query->limit = 10;
        
        $res = $query->send();
        $this->assertLessThan(count($res->result), 0);
        $this->assertGreaterThanOrEqual(count($res->result), 10);
    }
    
    /**
     * Тест лимита
     */
    public function testLimit2(){
        $query = new QueryToApi();
        $query->query = 'н';
        $query->contentType = QueryToApi::RegionType;
        $query->limit = 5;
        
        $res = $query->send();
        $this->assertLessThan(count($res->result), 0);
        $this->assertGreaterThanOrEqual(count($res->result), 5);
    }
    
    
    /* -------------------- Отправка некорректного запроса ------------------- */
    
    /*
     * Передача пустого ID
     */
    public function testInvalidQuery1(){
        $query = new QueryToApi();
        $query->regionId = '';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 0);
    }
    
    /*
     * Передача пустого названия
     */
    public function testInvalidQuery2(){
        $query = new QueryToApi();
        $query->query = '';
        $query->contentType = QueryToApi::RegionType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 0);
    }
}
