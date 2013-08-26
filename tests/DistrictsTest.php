<?php

/**
 * Тест api при работе с районами
 */
class DistrictsTest extends PHPUnit_Framework_TestCase {
    
    
    /* --------------------------- Поиск по ID ------------------------------- */
    
    /**
     * Тест поиска Кигинского района по Id
     */
    public function testSearchById1(){
        $query = new QueryToApi();
        $query->districtId = '0203000000000';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Кигинский');
        $this->assertEquals($res->zip, '452500');
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');
    }
    
    /**
     * Тест поиска Кошехабльского района по Id
     */
    public function testSearchById2(){
        $query = new QueryToApi();
        $query->districtId = '0100200000000';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Кошехабльский');
        $this->assertEquals($res->zip, '385400');
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');
    }
    
    /**
     * Тест поиска Надымского района по Id
     */
    public function testSearchById3(){
        $query = new QueryToApi();
        $query->districtId = '8900800000000';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Надымский');
        $this->assertEquals($res->zip, null);
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');
    }
    
    
    /* ----------------------- Поиск по названию ----------------------------- */
    
    /**
     * Тест поиска районов с названием начинающимся на "О"
     */
    public function testSearchByName1(){
        $query = new QueryToApi();
        $query->query = 'о';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $district){
            $this->assertRegExp('/\s?о/iu', $district->name, $district->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска районов с названием начинающимся на "Ра"
     */
    public function testSearchByName2(){
        $query = new QueryToApi();
        $query->query = 'hf';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $district){
            $this->assertRegExp('/\s?ра/iu', $district->name, $district->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска районов с названием начинающимся на "Пуш"
     */
    public function testSearchByName3(){
        $query = new QueryToApi();
        $query->query = 'пуш';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $district){
            $this->assertRegExp('/\s?пуш/iu', $district->name, $district->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска района с названием "Кагальницкий"
     */
    public function testSearchByName4(){
        $query = new QueryToApi();
        $query->query = 'Кагальницкий';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->id, '6101500000000');
        $this->assertEquals($res->name, 'Кагальницкий');
        $this->assertEquals($res->zip, null);
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');          
    }
    
    /**
     * Тест поиска района с названием "Упоровский"
     */
    public function testSearchByName5(){
        $query = new QueryToApi();
        $query->query = 'Упоровский';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->id, '7201900000000');
        $this->assertEquals($res->name, 'Упоровский');
        $this->assertEquals($res->zip, null);
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');          
    }
    
    
    /* ------------------- Поиск по названию и региону ----------------------- */
    
    /**
     * Тест поиска районов с названием начинающимся на "Нян" в Архангельской области
     */
    public function testSearchByNameFromRegion1(){
        $query = new QueryToApi();
        $query->regionId = '2900000000000';
        $query->query = 'нян';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 1, 'Сервис должен был вернуть 1 объект');
        
        $res = $res[0];
        
        $this->assertEquals($res->id, '2901300000000');
        $this->assertEquals($res->name, 'Няндомский');
        $this->assertEquals($res->zip, null);
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');       
    }
    
    /**
     * Тест поиска районов с названием начинающимся на "Хал" в Кировской области
     */
    public function testSearchByNameFromRegion2(){
        $query = new QueryToApi();
        $query->regionId = '4300000000000';
        $query->query = 'хал';
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 1, 'Сервис должен был вернуть 1 объект');
        
        $res = $res[0];
        
        $this->assertEquals($res->id, '4302600000001');
        $this->assertEquals($res->name, 'Халтуринский');
        $this->assertEquals($res->zip, '612275');
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');       
    }
    
    
    /* -------------------- Получение вместе с родителем --------------------- */
    
    /*
     * Получение вместе с родителем Моздокского района
     */
    public function testGetWithParent1(){
        $query = new QueryToApi();
        $query->districtId = '1500700000000';
        $query->contentType = QueryToApi::DistrictType;
        $query->withParent = true;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Моздокский');
        $this->assertEquals($res->zip, null);
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');
        
        $parent = $res->parents[0];
        
        $this->assertEquals($parent->id, '1500000000000');
        $this->assertEquals($parent->name, 'Северная Осетия - Алания');
        $this->assertEquals($parent->zip, null);
        $this->assertEquals($parent->type, 'Республика');
        $this->assertEquals($parent->typeShort, 'Респ');
    }
    
    /*
     * Получение вместе с родителем Лебяжского района
     */
    public function testGetWithParent2(){
        $query = new QueryToApi();
        $query->districtId = '4301600000000';
        $query->contentType = QueryToApi::DistrictType;
        $query->withParent = true;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Лебяжский');
        $this->assertEquals($res->zip, '613500');
        $this->assertEquals($res->type, 'Район');
        $this->assertEquals($res->typeShort, 'р-н');
        
        $parent = $res->parents[0];
        
        $this->assertEquals($parent->id, '4300000000000');
        $this->assertEquals($parent->name, 'Кировская');
        $this->assertEquals($parent->zip, '610000');
        $this->assertEquals($parent->type, 'Область');
        $this->assertEquals($parent->typeShort, 'обл');
    }
    
    /* --------------------------- Лимит ------------------------------------- */
    
    /**
     * Тест лимита
     */
    public function testLimit1(){
        $query = new QueryToApi();
        $query->query = 'а';
        $query->contentType = QueryToApi::DistrictType;
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
        $query->query = 'я';
        $query->contentType = QueryToApi::DistrictType;
        $query->limit = 10;
        
        $res = $query->send();
        $this->assertLessThan(count($res->result), 0);
        $this->assertGreaterThanOrEqual(count($res->result), 10);
    }
    
    
    /* -------------------- Отправка некорректного запроса ------------------- */
    
    /*
     * Передача пустого ID
     */
    public function testInvalidQuery1(){
        $query = new QueryToApi();
        $query->districtId = '';
        $query->contentType = QueryToApi::DistrictType;
        
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
        $query->contentType = QueryToApi::DistrictType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 0);
    }
}