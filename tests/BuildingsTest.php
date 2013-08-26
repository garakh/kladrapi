<?php

/**
 * Тест api при работе со строениями
 */
class BuildingsTest extends PHPUnit_Framework_TestCase {
    
    
    /* --------------------------- Поиск по ID ------------------------------- */
    
    /**
     * Тест поиска строения дом4кв14 по Id
     */
    public function testSearchById1(){
        $query = new QueryToApi();
        $query->buildingId = '3401600002400410002';
        $query->contentType = QueryToApi::BuildingType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $building){
            if($building->name == 'дом4кв14') return;
        }
        
        $this->assertTrue(false, 'дом4кв14 должен быть в списке результатов');
    }
    
    /**
     * Тест поиска строения 11 по Id
     */
    public function testSearchById2(){
        $query = new QueryToApi();
        $query->buildingId = '1403200001200020001';
        $query->contentType = QueryToApi::BuildingType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $building){
            if($building->name == '11') return;
        }
        
        $this->assertTrue(false, '11 должен быть в списке результатов');
    }
    
    /**
     * Тест поиска строения 16 по Id
     */
    public function testSearchById3(){
        $query = new QueryToApi();
        $query->buildingId = '3700000100000110001';
        $query->contentType = QueryToApi::BuildingType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $building){
            if($building->name == '16') return;
        }
        
        $this->assertTrue(false, '16 должен быть в списке результатов');
    }
    
    /* ------------------- Поиск по названию и улице ------------------------- */
    
    /**
     * Тест поиска строений с номером "101" на проспекте Ломоносова
     */
    public function testSearchByNameFromStreet1(){
        $query = new QueryToApi();
        $query->streetId = '29000001000025800';
        $query->query = '101';
        $query->contentType = QueryToApi::BuildingType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $building){
            $this->assertRegExp('/^101/iu', $building->name, $building->name . ' не должно быть в списке объектов');
        }      
    }
    
    /**
     * Тест поиска строений с номером начинающимся на "дв" на улице Ленина
     */
    public function testSearchByNameFromStreet2(){
        $query = new QueryToApi();
        $query->streetId = '01000001000011000';
        $query->query = 'дв';
        $query->contentType = QueryToApi::BuildingType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $building){
            $this->assertRegExp('/^дв/iu', $building->name, $building->name . ' не должно быть в списке объектов');
        }      
    }
    
    /**
     * Тест поиска строений с номером начинающимся на "двлд" на улице Мунгатская
     */
    public function testSearchByNameFromStreet3(){
        $query = new QueryToApi();
        $query->streetId = '42005001000002900';
        $query->query = 'ldkl';
        $query->contentType = QueryToApi::BuildingType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $building){
            $this->assertRegExp('/^двлд/iu', $building->name, $building->name . ' не должно быть в списке объектов');
        }      
    }
    
    
    
    /* -------------------- Получение вместе с родителями --------------------- */
    
    /*
     * Получение вместе с родителями 100 дома
     */
    public function testGetWithParent1(){
        $query = new QueryToApi();
        $query->buildingId = '1301300100000830001';
        $query->contentType = QueryToApi::BuildingType;
        $query->withParent = true;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $building){
            if($building->name == '100'){
                $this->assertEquals($building->zip, '431350');
                $this->assertEquals($building->type, 'дом');
                $this->assertEquals($building->typeShort, 'д');
                
                $parent = $building->parents[0];
        
                $this->assertEquals($parent->id, '1300000000000');
                $this->assertEquals($parent->name, 'Мордовия');
                $this->assertEquals($parent->zip, null);
                $this->assertEquals($parent->type, 'Республика');
                $this->assertEquals($parent->typeShort, 'Респ');
                
                $parent = $building->parents[1];
        
                $this->assertEquals($parent->id, '1301300000000');
                $this->assertEquals($parent->name, 'Ковылкинский');
                $this->assertEquals($parent->zip, null);
                $this->assertEquals($parent->type, 'Район');
                $this->assertEquals($parent->typeShort, 'р-н');
                
                $parent = $building->parents[2];
        
                $this->assertEquals($parent->id, '1301300100000');
                $this->assertEquals($parent->name, 'Ковылкино');
                $this->assertEquals($parent->zip, '431350');
                $this->assertEquals($parent->type, 'Город');
                $this->assertEquals($parent->typeShort, 'г');
                
                $parent = $building->parents[3];
        
                $this->assertEquals($parent->id, '13013001000008300');
                $this->assertEquals($parent->name, 'Саранская');
                $this->assertEquals($parent->zip, '431350');
                $this->assertEquals($parent->type, 'Улица');
                $this->assertEquals($parent->typeShort, 'ул');
                
                return;
            }
        }
        
        $this->assertTrue(false, '100 должен быть в списке результатов');
    }
    
    /*
     * Получение вместе с родителями 45 дома
     */
    public function testGetWithParent2(){
        $query = new QueryToApi();
        $query->buildingId = '2901800002500110001';
        $query->contentType = QueryToApi::BuildingType;
        $query->withParent = true;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $building){
            if($building->name == '45'){
                $this->assertEquals($building->zip, '164537');
                $this->assertEquals($building->type, 'дом');
                $this->assertEquals($building->typeShort, 'д');
                
                $parent = $building->parents[0];
        
                $this->assertEquals($parent->id, '2900000000000');
                $this->assertEquals($parent->name, 'Архангельская');
                $this->assertEquals($parent->zip, null);
                $this->assertEquals($parent->type, 'Область');
                $this->assertEquals($parent->typeShort, 'обл');
                
                $parent = $building->parents[1];
        
                $this->assertEquals($parent->id, '2901800000000');
                $this->assertEquals($parent->name, 'Холмогорский');
                $this->assertEquals($parent->zip, null);
                $this->assertEquals($parent->type, 'Район');
                $this->assertEquals($parent->typeShort, 'р-н');
                
                $parent = $building->parents[2];
        
                $this->assertEquals($parent->id, '2901800002500');
                $this->assertEquals($parent->name, 'Емецк');
                $this->assertEquals($parent->zip, '164537');
                $this->assertEquals($parent->type, 'Село');
                $this->assertEquals($parent->typeShort, 'с');
                
                $parent = $building->parents[3];
        
                $this->assertEquals($parent->id, '29018000025001100');
                $this->assertEquals($parent->name, 'Октябрьский');
                $this->assertEquals($parent->zip, '164537');
                $this->assertEquals($parent->type, 'Переулок');
                $this->assertEquals($parent->typeShort, 'пер');
                
                return;
            }
        }
        
        $this->assertTrue(false, '45 должен быть в списке результатов');
    }
    
    
    /* --------------------------- Лимит ------------------------------------- */
    
    /**
     * Тест лимита
     */
    public function testLimit1(){
        $query = new QueryToApi();
        $query->query = '1';
        $query->streetId = '29000001000025800';
        $query->contentType = QueryToApi::BuildingType;
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
        $query->query = '10';
        $query->streetId = '29000001000025800';
        $query->contentType = QueryToApi::BuildingType;
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
        $query->buildingId = '';
        $query->contentType = QueryToApi::BuildingType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 0);
    }
    
    /*
     * Передача пустого ID улицы
     */
    public function testInvalidQuery2(){
        $query = new QueryToApi();
        $query->streetId = '';
        $query->query = '10';
        $query->contentType = QueryToApi::BuildingType;
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(false, 'Сервис должен был вернуть ошибку');
    }
}