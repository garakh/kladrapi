<?php

/**
 * Тест api при работе с улицами
 */
class StreetsTest extends PHPUnit_Framework_TestCase {
    
    
    /* --------------------------- Поиск по ID ------------------------------- */
    
    /**
     * Тест поиска улицы Правдина по Id
     */
    public function testSearchById1(){
        $query = new QueryToApi();
        $query->streetId = '56005000001002900';
        $query->contentType = QueryToApi::StreetType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Правдина');
        $this->assertEquals($res->zip, '461420');
        $this->assertEquals($res->type, 'Улица');
        $this->assertEquals($res->typeShort, 'ул');
    }
    
    /**
     * Тест поиска переулка Кибальчича по Id
     */
    public function testSearchById2(){
        $query = new QueryToApi();
        $query->streetId = '48000001003001051';
        $query->contentType = QueryToApi::StreetType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Кибальчича');
        $this->assertEquals($res->zip, '398901');
        $this->assertEquals($res->type, 'Переулок');
        $this->assertEquals($res->typeShort, 'пер');
    }
    
    /**
     * Тест поиска улицы Ямакова по Id
     */
    public function testSearchById3(){
        $query = new QueryToApi();
        $query->streetId = '02002000108001300';
        $query->contentType = QueryToApi::StreetType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Ямакова');
        $this->assertEquals($res->zip, '452132');
        $this->assertEquals($res->type, 'Улица');
        $this->assertEquals($res->typeShort, 'ул');
    }
    
    
    /* -------------- Поиск по названию и населённому пункту ----------------- */
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Ле" в Гаджиево
     */
    public function testSearchByNameFromCity1(){
        $query = new QueryToApi();
        $query->cityId = '5100001200000';
        $query->query = 'ле';
        $query->contentType = QueryToApi::StreetType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 1, 'Сервис должен был вернуть 1 объект');
        
        $city = $res[0];
        
        $this->assertEquals($city->id, '51000012000000600');
        $this->assertEquals($city->name, 'Ленина');
        $this->assertEquals($city->zip, '184670');
        $this->assertEquals($city->type, 'Улица');
        $this->assertEquals($city->typeShort, 'ул');      
    }
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Аку" в Одинциво
     */
    public function testSearchByNameFromCity2(){
        $query = new QueryToApi();
        $query->cityId = '5002200100000';
        $query->query = 'аку';
        $query->contentType = QueryToApi::StreetType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 1, 'Сервис должен был вернуть 1 объект');
        
        $city = $res[0];
        
        $this->assertEquals($city->id, '50022001000008300');
        $this->assertEquals($city->name, 'Акуловская');
        $this->assertEquals($city->zip, '143002');
        $this->assertEquals($city->type, 'Улица');
        $this->assertEquals($city->typeShort, 'ул');      
    }
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Куд" в Санкт-Петербурге
     */
    public function testSearchByNameFromCity3(){
        $query = new QueryToApi();
        $query->cityId = '7800000000000';
        $query->query = 'КУД';
        $query->contentType = QueryToApi::StreetType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 1, 'Сервис должен был вернуть 1 объект');
        
        $city = $res[0];
        
        $this->assertEquals($city->id, '78000000000272200');
        $this->assertEquals($city->name, 'Кудровский');
        $this->assertEquals($city->zip, '193315');
        $this->assertEquals($city->type, 'Проезд');
        $this->assertEquals($city->typeShort, 'проезд');      
    }
    
    /* -------------------- Получение вместе с родителями --------------------- */
    
    /*
     * Получение вместе с родителями улицы Мулина
     */
    public function testGetWithParent1(){
        $query = new QueryToApi();
        $query->streetId = '34000003000001000';
        $query->contentType = QueryToApi::StreetType;
        $query->withParent = true;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Мулина');
        $this->assertEquals($res->zip, '403882');
        $this->assertEquals($res->type, 'Улица');
        $this->assertEquals($res->typeShort, 'ул');
        
        $parent = $res->parents[0];
        
        $this->assertEquals($parent->id, '3400000000000');
        $this->assertEquals($parent->name, 'Волгоградская');
        $this->assertEquals($parent->zip, null);
        $this->assertEquals($parent->type, 'Область');
        $this->assertEquals($parent->typeShort, 'обл');

        $parent = $res->parents[1];
        
        $this->assertEquals($parent->id, '3400000300000');
        $this->assertEquals($parent->name, 'Камышин');
        $this->assertEquals($parent->zip, null);
        $this->assertEquals($parent->type, 'Город');
        $this->assertEquals($parent->typeShort, 'г');
    }
    
    /* --------------------------- Лимит ------------------------------------- */
    
    /**
     * Тест лимита
     */
    public function testLimit1(){
        $query = new QueryToApi();
        $query->query = 'н';
        $query->cityId = '7700000000000';
        $query->contentType = QueryToApi::StreetType;
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
        $query->query = '3';
        $query->cityId = '7800000000000';
        $query->contentType = QueryToApi::StreetType;
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
        $query->streetId = '';
        $query->contentType = QueryToApi::StreetType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 0);
    }
    
    /*
     * Передача пустого ID населённого пункта
     */
    public function testInvalidQuery2(){
        $query = new QueryToApi();
        $query->cityId = '';
        $query->query = 'КУД';
        $query->contentType = QueryToApi::StreetType;
        
        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(false, 'Сервис должен был вернуть ошибку');
    }
}
