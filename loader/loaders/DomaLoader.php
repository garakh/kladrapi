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

        $first = true;
        $i = 0;
        while (($data = $this->ReadLine()) !== FALSE) {
            if($first){
                $first = false;
                continue;
            }
			
            $arData = array();

            if($i++ % 10000 == 0)
                echo $i.'; ';

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

            $arData[Loader::ZipCodeField] = intval($arData[Loader::ZipCodeField]);

			$arData = $this->fixData($arData);
            $buildings->insert($arData);
        }

        echo " Creating indecies ";

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
        $buildings->ensureIndex(
            array(Loader::SortField => 1),
            array('background' => true)
        );
        $buildings->ensureIndex(
            array(Loader::ZipCodeField => 1),
            array('background' => true)
        );

        $buildings->ensureIndex(
            array(Loader::IdField => 1),
            array('background' => true, "unique" => true, "dropDups" => true)
        );
        $buildings->ensureIndex(
            array(Loader::NormalizedNameField => 1, Loader::CodeRegionField => 1, Loader::CodeStreetField => 1, Loader::CodeDistrictField => 1, Loader::CodeLocalityField => 1),
            array('background' => true)
        );
        $buildings->ensureIndex(
            array(Loader::CodeRegionField => 1, Loader::CodeStreetField => 1, Loader::CodeDistrictField => 1, Loader::CodeLocalityField => 1),
            array('background' => true)
        );
        $buildings->ensureIndex(
            array(Loader::ZipCodeField => 1, Loader::IdField => 1),
            array('background' => true)
        );        

        $this->Close();
        return true;
    }
	
	private function checkLetter($arData, $letter)
	{
		foreach($arData as $item)
		{
			if(substr($item, 0, 2) == $letter)
				return true;
		}
		
		return false;
	}
	
	private function fixLetter($arData, $l1, $l2)
	{
		if($this->checkLetter($arData['NormalizedName'], $l1))
		{
			$name = $arData['Name'];
			$parts = explode(",", $name);
			$arNum = array();
		
			$addName = $name;
			foreach($parts as $n)
			{
				if(substr($n, 0, 2) == $l2)
				{
					$n2 = preg_replace("/[^0-9-]/msi", '', $n);
					list($i1, $i2) = explode("-", $n2, 2);
					for($i = (int)$i1; $i <= (int)$i2; $i+=2)
					{
						$arNum[]= $i.' ';
						$addName.=",".$i;
					}
				}
			}
		
			$arNumCur = $arData['NormalizedName'];
			foreach($arNumCur as $k => $v)
			{
				$arNumCur[$k] = (string)$v;
			}
		
		
			$arNumCur = array_unique(array_merge($arNumCur, $arNum));
			$arData['NormalizedName'] = $arNumCur;
			$arData['Name'] = $addName;		
		}
		
		return $arData;
	}
	
	private function fixData($arData)
	{
		$arData = $this->fixLetter($arData, 'ч', 'Ч');
		$arData = $this->fixLetter($arData, 'н', 'Н');
		
		return $arData;
	}

}