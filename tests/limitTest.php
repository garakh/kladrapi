<?php 

class LimitTest extends PHPUnit_Framework_TestCase {
/*
 * Тест лимита вывода
 */
	public function testLimit1(){
		$query = new QueryToApi();
		$query->query = '';
		$query->contentType = QueryToApi::BuildingType;
		$query->cityId = '7700000000000';
		
		$res = $query->send();
		$res = $res->result;
		$cnt = count($res);
		
        	$this->assertTrue($cnt<=400, 'Лимит превысил 400');
	}
        
        public function testLimit2(){
		$query = new QueryToApi();
		//$query->query = '';
		$query->contentType = QueryToApi::BuildingType;
		$query->streetId = '29000001000023400'; 
                $query->limit = 500;
		
		$res = $query->send();
		$res = $res->result;
		$cnt = count($res);
		echo "Count: ".$cnt;
                $this->assertTrue($cnt>1, 'Count: '.$cnt);
                
	}
}