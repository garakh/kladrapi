<?php

/**
 * Тесты api не привязанные к типу объекта
 */
class CommonTest extends PHPUnit_Framework_TestCase {
    /* -------------------- Отправка некорректного запроса ------------------- */

    /*
     * Передача пустого типа объекта
     */

    public function testInvalidQuery1() {
        $query = new QueryToApi();
        $query->query = 'лом';
        $query->contentType = '';

        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }
        $this->assertTrue(false, 'Сервис должен был вернуть ошибку');
    }

    /*
     * Передача некорректного типа объекта
     */

    public function testInvalidQuery2() {
        $query = new QueryToApi();
        $query->query = 'лом';
        $query->contentType = 'йцук';

        try {
            $res = $query->send();
        } catch (Exception $exc) {
            return;
        }

        $this->assertTrue(false, 'Сервис должен был вернуть ошибку');
    }

}
