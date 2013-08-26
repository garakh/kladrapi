<?php

/**
 * Загрузчик файла KLADR.csv
 */
class KladrLoader extends Loader{

    public function __construct($db, $strFilePath) {
        parent::__construct($db, $strFilePath);

        $this->arCodeMap = array(2,3,6);
        $this->arCodeConformity = array(
            Loader::CodeRegionField => 0,
            Loader::CodeDistrictField => 1,
            Loader::CodeLocalityField => 2,
            Loader::CodeStreetField => -1,
            Loader::CodeBuildingField => -1
        );
        $this->arFieldConformity = array(
            Loader::IdField => 2,
            Loader::NameField => 0,
            Loader::ZipCodeField => 3,
            Loader::TypeShortField => 1,
            Loader::OkatoCodeField => 6
        );
    }

    public function Load() {
        parent::Load();

        $altnames = $this->db->altnames;
        $socrbase = $this->db->socrbase;

        $regions = $this->db->regions;
        $district = $this->db->district;
        $cities = $this->db->cities;

        $regions->ensureIndex(
            array(Loader::IdField => 1),
            array('background' => true)
        );
        $regions->ensureIndex(
            array(Loader::NormalizedNameField => 1),
            array('background' => true)
        );

        $district->ensureIndex(
            array(Loader::IdField => 1),
            array('background' => true)
        );
        $district->ensureIndex(
            array(Loader::NormalizedNameField => 1),
            array('background' => true)
        );
        $district->ensureIndex(
            array(Loader::CodeRegionField => 1),
            array('background' => true)
        );

        $cities->ensureIndex(
            array(Loader::IdField => 1),
            array('background' => true)
        );
        $cities->ensureIndex(
            array(Loader::NormalizedNameField => 1),
            array('background' => true)
        );
        $cities->ensureIndex(
            array(Loader::CodeRegionField => 1),
            array('background' => true)
        );
        $cities->ensureIndex(
            array(Loader::CodeDistrictField => 1),
            array('background' => true)
        );

        $first = true;
        while (($data = $this->ReadLine()) !== FALSE) {
            if($first){
                $first = false;
                continue;
            }

            $arData = array();

            $id_key = $this->arFieldConformity[Loader::IdField];
            $cursor = $altnames->find(array(Loader::OldIdField => $data[$id_key]));
            foreach ($cursor as $el) {
                $data[$id_key] = $el[Loader::NewIdField];
                break;
            }

            foreach($this->arFieldConformity as $key => $conform){
                $arData[$key] = $data[$conform] ? $data[$conform] : null;
                
                if($key == Loader::IdField){
                    $GLOBALS[Loader::SphinxIdField]++;
                    $arData[Loader::SphinxIdField] = $GLOBALS[Loader::SphinxIdField];
                }
                
                if($key == Loader::NameField){
                    $arData[Loader::NormalizedNameField] = __normalize($arData[$key]);
                }
            }

            $typeShortField = Loader::TypeShortField;
            $typeField = Loader::TypeField;
            $cursor = $socrbase->find(array($typeShortField => $arData[$typeShortField]));
            foreach ($cursor as $type) {
                $arData[$typeField] = $type[$typeField];
                break;
            }

            $code = $arData[Loader::IdField];
            $arCode = $this->ReadCode($code);
            $arCodeField = $this->GetCodeField($arCode);

            foreach($arCodeField as $field => $value){
                $arData[$field] = $value;
            }
            
            $sort = 100000;
            switch($arData[Loader::TypeShortField])
            {
                case 'г':
                    $sort = 1000;
                    break;
                case 'городок':
                    $sort = 2000;
                    break;
                case 'пгт':
                    $sort = 3000;
                    break;
                case 'п':
                    $sort = 4000;
                    break;
                case 'дп':
                    $sort = 5000;
                    break;
                case 'кп':
                    $sort = 6000;
                    break;
                case 'рп':
                    $sort = 7000;
                    break;
                case 'с':
                    $sort = 8000;
                    break;
                case 'д':
                    $sort = 9000;
                    break;
                case 'ст':
                    $sort = 10000;
                    break;
                default:
                    $sort = 100000;
                    break;
            }

            $type = $this->GetType($arCode);

            switch($type)
            {
                case 1:
                    switch($arData[Loader::IdField]){
                        case '7700000000000': // Москва
                        case '7800000000000': // Санкт-Петербург                        
                        case '9900000000000': // Байконур    
                            $arData = array_slice($arData, 0, 9);
                            $arData[Loader::CodeDistrictField] = null;
                            $arData[Loader::CodeLocalityField] = null;                            
                            $arData[Loader::SortField] = 100;
                            $cities->insert($arData);
                            break;
                        case '7800000000001': // Ленинград
                            $arData = array_slice($arData, 0, 9);
                            $arData[Loader::CodeDistrictField] = null;
                            $arData[Loader::CodeLocalityField] = null;                            
                            $arData[Loader::SortField] = 500;
                            $cities->insert($arData);
                            break;
                        default:
                            $arData = array_slice($arData, 0, 9);
                            $regions->insert($arData);
                            break; 
                    }
                    break;
                case 2:
                    $arData = array_slice($arData, 0, 10);
                    $district->insert($arData);
                    break;
                default: 
                    switch($arData[Loader::IdField]){
                        case '3403100100051': // Урюпинск (копия)
                            break;
                        default:
                            $arData = array_slice($arData, 0, 11);
                            $arData[Loader::SortField] = $sort;
                            $cities->insert($arData);
                        break; 
                    }
                    break;
            }
        }

        $this->Close();
        return true;
    }
}