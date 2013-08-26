<?php

/**
 * Тест api при работе с населёнными пунктами
 */
class CitiesTest extends PHPUnit_Framework_TestCase {
    
    
    /* --------------------------- Поиск по ID ------------------------------- */
    
    /**
     * Тест поиска Урюпинска по Id
     */
    public function testSearchById1(){
        $query = new QueryToApi();
        $query->cityId = '3400000500000';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Урюпинск');
        $this->assertEquals($res->zip, null);
        $this->assertEquals($res->type, 'Город');
        $this->assertEquals($res->typeShort, 'г');
    }
    
    /**
     * Тест поиска Шали по Id
     */
    public function testSearchById2(){
        $query = new QueryToApi();
        $query->cityId = '2001200100000';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Шали');
        $this->assertEquals($res->zip, '366300');
        $this->assertEquals($res->type, 'Город');
        $this->assertEquals($res->typeShort, 'г');
    }
    
    /**
     * Тест поиска Вавилона по Id
     */
    public function testSearchById3(){
        $query = new QueryToApi();
        $query->cityId = '2601100003200';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Вавилон');
        $this->assertEquals($res->zip, '356619');
        $this->assertEquals($res->type, 'Хутор');
        $this->assertEquals($res->typeShort, 'х');
    }
    
    /**
     * Тест поиска Москвы по Id
     */
    public function testSearchById4(){
        $query = new QueryToApi();
        $query->cityId = '7700000000000';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Москва');
        $this->assertEquals($res->zip, null);
        $this->assertEquals($res->type, 'Город');
        $this->assertEquals($res->typeShort, 'г');
    }
    
    /**
     * Тест поиска Санкт-Петербурга по Id
     */
    public function testSearchById5(){
        $query = new QueryToApi();
        $query->cityId = '7800000000000';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Санкт-Петербург');
        $this->assertEquals($res->zip, '190000');
        $this->assertEquals($res->type, 'Город');
        $this->assertEquals($res->typeShort, 'г');
    }
    
    /* ----------------------- Поиск по названию ----------------------------- */
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Мим"
     */
    public function testSearchByName1(){
        $query = new QueryToApi();
        $query->query = 'vbv';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $city){
            $this->assertRegExp('/\s?мим/iu', $city->name, $city->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Ар"
     */
    public function testSearchByName2(){
        $query = new QueryToApi();
        $query->query = 'ар';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $city){
            $this->assertRegExp('/\s?ар/iu', $city->name, $city->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Тор"
     */
    public function testSearchByName3(){
        $query = new QueryToApi();
        $query->query = 'njh';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertLessThan(count($res), 0);
        
        foreach($res as $city){
            $this->assertRegExp('/\s?тор/iu', $city->name, $city->name . ' не должно быть в списке объектов');
        }        
    }
    
    /**
     * Тест поиска населённого пункта с названием "Правое Черепаново"
     */
    public function testSearchByName4(){
        $query = new QueryToApi();
        $query->query = 'Правое Черепаново';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->id, '3800100047700');
        $this->assertEquals($res->name, 'Правое Черепаново');
        $this->assertEquals($res->zip, '664528');
        $this->assertEquals($res->type, 'Территория');
        $this->assertEquals($res->typeShort, 'тер');          
    }
    
    /**
     * Тест поиска населённого пункта с названием "Корабельное"
     */
    public function testSearchByName5(){
        $query = new QueryToApi();
        $query->query = 'Корабельное';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->id, '5100000800500');
        $this->assertEquals($res->name, 'Корабельное');
        $this->assertEquals($res->zip, '184640');
        $this->assertEquals($res->type, 'Населенный пункт');
        $this->assertEquals($res->typeShort, 'нп');          
    }
    
    
    /* ------------------- Поиск по названию и региону ----------------------- */
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Голден" в Московской области
     */
    public function testSearchByNameFromRegion1(){
        $query = new QueryToApi();
        $query->regionId = '5000000000000';
        $query->query = 'голдЕН';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 3, 'Сервис должен был вернуть 3 объекта');
        
        $city = $res[0];
        
        $this->assertEquals($city->id, '5000000110851');
        $this->assertEquals($city->name, 'Голден Лайн');
        $this->assertEquals($city->zip, '142000');
        $this->assertEquals($city->type, 'Территория');
        $this->assertEquals($city->typeShort, 'тер');      
        
        $city = $res[1];
        
        $this->assertEquals($city->id, '5000600018651');
        $this->assertEquals($city->name, 'Голден Лайн');
        $this->assertEquals($city->zip, '142030');
        $this->assertEquals($city->type, 'Территория');
        $this->assertEquals($city->typeShort, 'тер');  
    }
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Рубл" в Амурской области
     */
    public function testSearchByNameFromRegion2(){
        $query = new QueryToApi();
        $query->regionId = '2800000000000';
        $query->query = 'he,k';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 1, 'Сервис должен был вернуть 1 объект');
        
        $city = $res[0];
        
        $this->assertEquals($city->id, '2800600002500');
        $this->assertEquals($city->name, 'Рублевка');
        $this->assertEquals($city->zip, '676216');
        $this->assertEquals($city->type, 'Село');
        $this->assertEquals($city->typeShort, 'с');      
    }
    
    
    /* ------------------- Поиск по названию и району ----------------------- */
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "одесс" в Одесском районе
     */
    public function testSearchByNameFromDistrict1(){
        $query = new QueryToApi();
        $query->districtId = '5501900000000';
        $query->query = 'одесс';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 1, 'Сервис должен был вернуть 1 объект');
        
        $city = $res[0];
        
        $this->assertEquals($city->id, '5501900000100');
        $this->assertEquals($city->name, 'Одесское');
        $this->assertEquals($city->zip, '646860');
        $this->assertEquals($city->type, 'Село');
        $this->assertEquals($city->typeShort, 'с');      
    }
    
    /**
     * Тест поиска населённых пунктов с названием начинающимся на "Тарм" в Чановском районе
     */
    public function testSearchByNameFromDistrict2(){
        $query = new QueryToApi();
        $query->districtId = '5402700000000';
        $query->query = 'nfhv';
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 1, 'Сервис должен был вернуть 1 объект');
        
        $city = $res[0];
        
        $this->assertEquals($city->id, '5402700005300');
        $this->assertEquals($city->name, 'Тармакуль');
        $this->assertEquals($city->zip, '632228');
        $this->assertEquals($city->type, 'Деревня');
        $this->assertEquals($city->typeShort, 'д');      
    }
    
    
    /* -------------------- Получение вместе с родителями --------------------- */
    
    /*
     * Получение вместе с родителями хутора Лобакина
     */
    public function testGetWithParent1(){
        $query = new QueryToApi();
        $query->cityId = '3403000001600';
        $query->contentType = QueryToApi::CityType;
        $query->withParent = true;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Лобакин');
        $this->assertEquals($res->zip, '404432');
        $this->assertEquals($res->type, 'Хутор');
        $this->assertEquals($res->typeShort, 'х');
        
        $parent = $res->parents[0];
        
        $this->assertEquals($parent->id, '3400000000000');
        $this->assertEquals($parent->name, 'Волгоградская');
        $this->assertEquals($parent->zip, null);
        $this->assertEquals($parent->type, 'Область');
        $this->assertEquals($parent->typeShort, 'обл');

        $parent = $res->parents[1];
        
        $this->assertEquals($parent->id, '3403000000000');
        $this->assertEquals($parent->name, 'Суровикинский');
        $this->assertEquals($parent->zip, null);
        $this->assertEquals($parent->type, 'Район');
        $this->assertEquals($parent->typeShort, 'р-н');
    }
    
    /*
     * Получение вместе с родителями местечка Нурай
     */
    public function testGetWithParent2(){
        $query = new QueryToApi();
        $query->cityId = '0302000008900';
        $query->contentType = QueryToApi::CityType;
        $query->withParent = true;
        
        $res = $query->send();
        $res = $res->result[0];
        
        $this->assertEquals($res->name, 'Нурай');
        $this->assertEquals($res->zip, '671010');
        $this->assertEquals($res->type, 'Местечко');
        $this->assertEquals($res->typeShort, 'м');
        
        $parent = $res->parents[0];
        
        $this->assertEquals($parent->id, '0300000000000');
        $this->assertEquals($parent->name, 'Бурятия');
        $this->assertEquals($parent->zip, '670000');
        $this->assertEquals($parent->type, 'Республика');
        $this->assertEquals($parent->typeShort, 'Респ');

        $parent = $res->parents[1];
        
        $this->assertEquals($parent->id, '0302000000000');
        $this->assertEquals($parent->name, 'Тункинский');
        $this->assertEquals($parent->zip, null);
        $this->assertEquals($parent->type, 'Район');
        $this->assertEquals($parent->typeShort, 'р-н');
    }
    
    
    /* --------------------------- Лимит ------------------------------------- */
    
    /**
     * Тест лимита
     */
    public function testLimit1(){
        $query = new QueryToApi();
        $query->query = 'о';
        $query->contentType = QueryToApi::CityType;
        $query->limit = 100;
        
        $res = $query->send();
        $this->assertLessThan(count($res->result), 0);
        $this->assertGreaterThanOrEqual(count($res->result), 100);
    }
    
    /**
     * Тест лимита
     */
    public function testLimit2(){
        $query = new QueryToApi();
        $query->query = 'у';
        $query->contentType = QueryToApi::CityType;
        $query->limit = 50;
        
        $res = $query->send();
        $this->assertLessThan(count($res->result), 0);
        $this->assertGreaterThanOrEqual(count($res->result), 50);
    }
    
    
    /* -------------------- Отправка некорректного запроса ------------------- */
    
    /*
     * Передача пустого ID
     */
    public function testInvalidQuery1(){
        $query = new QueryToApi();
        $query->cityId = '';
        $query->contentType = QueryToApi::CityType;
        
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
        $query->contentType = QueryToApi::CityType;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals(count($res), 0);
    }
}
