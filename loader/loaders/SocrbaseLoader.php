<?php

/**
 * Загрузчик файла SOCRBASE.csv
 */
class SocrbaseLoader extends Loader {

    public function Load() {
        parent::Load();

        $socrbase = $this->db->socrbase;

        $socrbase->ensureIndex(
            array(Loader::TypeShortField => 1),
            array('background' => true)
        );

        $first = true;
        while (($data = $this->ReadLine()) !== FALSE) {
            if($first){
                $first = false;
                continue;
            }

            $item = array(
                Loader::TypeShortField => $data[1],
                Loader::TypeField => $data[2],
            );

            $socrbase->insert($item);
        }

        $this->Close();
        return true;
    }

}