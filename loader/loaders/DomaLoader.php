<?php

/**
 * Загрузчик файла DOMA.csv
 */
class DomaLoader extends Loader {

    public function __construct($db, $strFilePath) {
        parent::__construct($db, $strFilePath);
        
        $this->arCodeMap = array(2,3,6,4,4);
        $this->arCodeConformity = array(
            Loader::CodeRegionField => 0,
            Loader::CodeDistrictField => 1,
            Loader::CodeLocalityField => 2,
            Loader::CodeStreetField => 3,
            Loader::CodeBuildingField => 4
        );
 
        $this->arFieldConformity = array(
            Loader::IdField => 3,
            Loader::NameField => 0,
            Loader::ZipCodeField => 4,
            Loader::TypeShortField => 2,
            Loader::TypeField => 2,
            Loader::OkatoCodeField => 7
        );
    }

    public function Load() {
        parent::Load();

        $altnames = $this->db->altnames;
        $buildings = $this->db->buildings;

        $buildings->ensureIndex(
            array(Loader::NormalizedNameField => 1),
            array('background' => true)
        );
        $buildings->ensureIndex(
            array(Loader::CodeRegionField => 1),
            array('background' => true)
        );
        $buildings->ensureIndex(
            array(Loader::CodeDistrictField => 1),
            array('background' => true)
        );
        $buildings->ensureIndex(
            array(Loader::CodeLocalityField => 1),
            array('background' => true)
        );
        $buildings->ensureIndex(
            array(Loader::CodeStreetField => 1),
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
                
                if($key == Loader::NameField){
                    $arData[Loader::NormalizedNameField] = __normalize($arData[$key]);
                }
                
                if($key == Loader::TypeField){
                    if($arData[$key] == 'ДОМ') $arData[$key] = 'дом';
                }
                
                if($key == Loader::TypeShortField){
                    if($arData[$key] == 'ДОМ') $arData[$key] = 'д';
                }
            }

            $code = $arData[Loader::IdField];
            $arCode = $this->ReadCode($code);
            $arCodeField = $this->GetCodeField($arCode);

            foreach($arCodeField as $field => $value){
                $arData[$field] = $value;
            }

            $buildings->insert($arData);
        }

        $this->Close();
        return true;
    }

}