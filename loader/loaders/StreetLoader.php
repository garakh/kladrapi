<?php

/**
 * Загрузчик файла STREET.csv
 */
class StreetLoader extends Loader {

    public function __construct($db, $strFilePath) {
        parent::__construct($db, $strFilePath);

        $this->arCodeMap = array(2,3,6,4);
        $this->arCodeConformity = array(
            Loader::CodeRegionField => 0,
            Loader::CodeDistrictField => 1,
            Loader::CodeLocalityField => 2,
            Loader::CodeStreetField => 3,
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

        $streets = $this->db->streets;

        $streets->ensureIndex(
            array(Loader::IdField => 1),
            array('background' => true)
        );
        $streets->ensureIndex(
            array(Loader::NormalizedNameField => 1),
            array('background' => true)
        );
        $streets->ensureIndex(
            array(Loader::CodeRegionField => 1),
            array('background' => true)
        );
        $streets->ensureIndex(
            array(Loader::CodeDistrictField => 1),
            array('background' => true)
        );
        $streets->ensureIndex(
            array(Loader::CodeLocalityField => 1),
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

            $arData = array_slice($arData, 0, 11);
            $streets->insert($arData);
        }

        $this->Close();
        return true;
    }
}