<?php

/**
 * Выполняет запрос к сервису
 */
class QueryToApi {
    // Url сервиса
    const Url = 'http://kladr-api.ru/api.php';
    
    const RegionType = 'region';
    const DistrictType = 'district';
    const CityType = 'city';
    const StreetType = 'street';
    const BuildingType = 'building';
    
    public $regionId = null;
    public $districtId = null;
    public $cityId = null;
    public $streetId = null;
    public $buildingId = null;
    public $query = null;
    public $contentType = null;
    public $withParent = null;
    public $limit = null;
    
    /**
     * Формирует строку запроса
     * @return string
     */
    public function getQueryUrl(){
        $url = '';
        
        if(isset($this->regionId)){
            if($url) $url .= '&';
            $url .= 'regionId=' . $this->regionId;
        }
        
        if(isset($this->districtId)){
            if($url) $url .= '&';
            $url .= 'districtId=' . $this->districtId;
        }
        
        if(isset($this->cityId)){
            if($url) $url .= '&';
            $url .= 'cityId=' . $this->cityId;
        }
        
        if(isset($this->streetId)){
            if($url) $url .= '&';
            $url .= 'streetId=' . $this->streetId;
        }
        
        if(isset($this->buildingId)){
            if($url) $url .= '&';
            $url .= 'buildingId=' . $this->buildingId;
        }
        
        if(isset($this->query)){
            if($url) $url .= '&';
            $url .= 'query=' . urlencode($this->query);
        }
        
        if(isset($this->contentType)){
            if($url) $url .= '&';
            $url .= 'contentType=' . $this->contentType;
        }
        
        if(isset($this->withParent)){
            if($url) $url .= '&';
            $url .= 'withParent=' . $this->withParent;
        }
        
        if(isset($this->limit)){
            if($url) $url .= '&';
            $url .= 'limit=' . $this->limit;
        }
        
        return self::GetURL() . '?' . $url;
    }
    
    /**
     * Отправляет сервису запрос и декодирует полученный json
     * @return mixed
     */
    public function send(){
        $url = $this->getQueryUrl();
        $result = file_get_contents($url);
        return json_decode($result);
    }
    
        /**
     * Проверяет, определена ли константа URL
     */
	 private function GetURL(){
		if( defined("URL") ){
			return URL; 
		}
		else{
			return self::Url;
		}
        }
}
