<?php

/**
 * Загрузчик для файла ALTNAMES.csv
 */
class AltnamesLoader extends Loader {

    public function Load() {
        parent::Load();

        $altnames = $this->db->altnames;

        $altnames->ensureIndex(
            array(Loader::OldIdField => 1),
            array('background' => true)
        );

        $first = true;
        while (($data = $this->ReadLine()) !== FALSE) {
            if($first){
                $first = false;
                continue;
            }

            $item = array(
                Loader::OldIdField => $data[0],
                Loader::NewIdField => $data[1],
            );

            $altnames->insert($item);
        }

        $this->Close();
        return true;
    }

}
