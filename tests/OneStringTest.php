<?php

/**
 * Тесты для поиска одной строкой
 *
 * @author Y. Lichutin
 */
class OneStringTest extends PHPUnit_Framework_TestCase
{
    
    /*
     * Тест поиска области
     */
    public function testRegionSearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'арханг';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::RegionType);
        $this->assertEquals($res[0]->name, 'Архангельская');
        $this->assertEQuals($res[0]->regionId, 2900000000000);
    }
    /*
     * Тест поиска области с ключевыми словами
     */
    public function testRegionSearch2()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'еврейск ао';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::RegionType);
        $this->assertEquals($res[0]->name, 'Еврейская');
        $this->assertEQuals($res[0]->regionId, 7900000000000);
    }  
    
    /*
     * Тест поиска района
     */
    public function testDistrictSearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'краснодарский брюх';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::DistrictType);
        $this->assertEquals($res[0]->name, 'Брюховецкий');
        $this->assertEquals($res[0]->regionId, 2300000000000);
        $this->assertEquals($res[0]->districtId, 2300700000000);
    }
    
    /*
     * Тест поиска района с использованием ключевых слов
     */
    public function testDistrictSearch2()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'респ карелия кем район';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::DistrictType);
        $this->assertEquals($res[0]->name, 'Кемский');
        $this->assertEquals($res[0]->regionId, 1000000000000);
        $this->assertEquals($res[0]->districtId, 1000400000000); 
    }
    
    /*
     * Тест поиска города
     */
    public function testCitySearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'московская воскресенский белоо';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::CityType);
        $this->assertEquals($res[0]->name, 'Белоозерский');
        $this->assertEquals($res[0]->regionId, 5000000000000);
        $this->assertEquals($res[0]->districtId, 5000400000000); 
        $this->assertEquals($res[0]->cityId, 5000400000800);
    }
    
    /*
     * Тест поиска города с использованием ключевых слов
     */
    public function testCitySearch2()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'г санкт-петербург';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::CityType);
        $this->assertEquals($res[0]->name, 'Санкт-Петербург');
        $this->assertEquals($res[0]->regionId, 7800000000000);
        $this->assertEquals($res[0]->cityId, 7800000000000);
    }
    
    /*
     * Тест поиска улицы
     */
    public function testStreetSearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'мезенский каменка лесна';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::StreetType);
        $this->assertEquals($res[0]->name, 'Лесная');
        $this->assertEquals($res[0]->regionId, 2900000000000);
        $this->assertEquals($res[0]->districtId, 2901200000000);
        $this->assertEquals($res[0]->cityId, 2901200001800);
        $this->assertEquals($res[0]->streetId, 29012000018000600);
    }
    
    /*
     * Тест поиска улицы с использованием ключевых слов
     */
    public function testStreetSearch2()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'архангельская обл г архангельск переулок Физкультурный';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::StreetType);
        $this->assertEquals($res[0]->name, '1-й Физкультурный');
        $this->assertEquals($res[0]->regionId, 2900000000000);
        $this->assertEquals($res[0]->cityId, 2900000100000);
        $this->assertEquals($res[0]->streetId, 29000001000000200);
    }
    
    /*
     * Тест поиска здания
     */
    public function testBuildingSearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'петрозаводск ленина 35а';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::BuildingType);
        $this->assertEquals($res[0]->name, 'д. 35а');
        $this->assertEquals($res[0]->regionId, 1000000000000);
        $this->assertEquals($res[0]->cityId, 1000000100000);
        $this->assertEquals($res[0]->streetId, 10000001000013900); 
        $this->assertEquals($res[0]->buildingId, 1000000100001390004);
    }
    
    /*
     * Тест поиска здания с использованием ключевых слов
     */
    public function testBuildingSearch2()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'г орел ул колхозная дом 6';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::BuildingType);
        $this->assertEquals($res[0]->name, 'д. 6');
        $this->assertEquals($res[0]->regionId, 3500000000000);
        $this->assertEquals($res[0]->cityId, 3502200009800);
        $this->assertEquals($res[0]->streetId, 35022000098000300); 
        $this->assertEquals($res[0]->buildingId, 3502200009800030001);        
    }
    
    /*
     * Проверяет правильность работы лимита
     */
    public function testLimit()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'воронеж московский 1';
        $query->limit = 10;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($query->limit, count($res));
    }
}
