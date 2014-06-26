<?php
/**
 * Генерирует xml-файл для возможности будущего поиска с помощью сфинкса.
 *
 * @author Y. Lichutin
 */

$connectString = 'mongodb://127.0.0.1:27017';
MongoCursor::$timeout = -1;

try {
    $conn = new MongoClient($connectString);
    $db = $conn->kladr;    
    //$db = $conn->test;
    

    xmlGenerate($db);
    
    unset($db);
    $conn->close();
    unset($conn);
} catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
} catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
}

/*
 * Создаёт xml-разметку из таблицы complex передаваемой БД.
 */
function xmlGenerate(MongoDb $db)
{
    $path = "c:/temp/elements.xml";
    $complex = $db->complex;
    
    $elements = $complex->find(array(), array(
        'FullName' => 1,
        'Sort' => 1,
        'ContentType' => 1,
        'Id' => 1,
        'RegionId' => 1,
        'DistrictId' => 1,
        'CityId' => 1
    ));
    
    $xmlWriter = new XMLWriter();
    
    $xmlWriter->openMemory();
    $xmlWriter->setIndent(true);
    $xmlWriter->startDocument('1.0', 'UTF-8');
            
    $xmlWriter->startElement('sphinx:docset');
    $xmlWriter->startElement('sphinx:schema');
    
    $xmlWriter->startElement('sphinx:field');
    $xmlWriter->writeAttribute('name', 'fullname');
    $xmlWriter->endElement();
    
    $xmlWriter->startElement('sphinx:attr');
    $xmlWriter->writeAttribute('name', 'sort');
    $xmlWriter->writeAttribute('type', 'int');
    $xmlWriter->endElement();
    
//    $xmlWriter->startElement('sphinx:field'); //мб поменять на аттр, чтобы оно возвращалось 
//    $xmlWriter->writeAttribute('name', 'contenttype');
//    $xmlWriter->endElement();
    
    $xmlWriter->startElement('sphinx:field');
    $xmlWriter->writeAttribute('name', 'regionid');
//    $xmlWriter->writeAttribute('type', 'string');
    $xmlWriter->endElement();
    
    $xmlWriter->startElement('sphinx:field');
    $xmlWriter->writeAttribute('name', 'districtid');
//    $xmlWriter->writeAttribute('type', 'string');
    $xmlWriter->endElement();
    
    $xmlWriter->startElement('sphinx:field');
    $xmlWriter->writeAttribute('name', 'cityid');
//    $xmlWriter->writeAttribute('type', 'string');
    $xmlWriter->endElement();
    
    $xmlWriter->endElement();
    
    file_put_contents($path, $xmlWriter->flush(true));//перезаписали файл
    
    $i = 0;
    $count = 0;
    foreach ($elements as $element)
    {
        if ($element['Id'] === null || $element['ContentType'] == 'building')
        {
            continue;
        }
        $xmlWriter->startElement('sphinx:document');
        $xmlWriter->writeAttribute('id', $element['Id']);
        
        $xmlWriter->startElement('fullname');
        $xmlWriter->text($element['FullName']);
        $xmlWriter->endElement();
        
//        $xmlWriter->startElement('contenttype');
//        $xmlWriter->text($element['ContentType']);
//        $xmlWriter->endElement();
        
        $xmlWriter->startElement('regionid');
        $xmlWriter->text((string)$element['RegionId']);
        $xmlWriter->endElement();
        
        $xmlWriter->startElement('districtid');
        $xmlWriter->text((string)$element['DistrictId']);
        $xmlWriter->endElement();
        
        $xmlWriter->startElement('cityid');
        $xmlWriter->text((string)$element['CityId']);
        $xmlWriter->endElement();
        
        $xmlWriter->startElement('sort');
        $xmlWriter->text($element['Sort']);
        $xmlWriter->endElement();
        
        $xmlWriter->endElement();//document
        
        if ($i == 50000)
        {
            file_put_contents($path, $xmlWriter->flush(true), FILE_APPEND);
            $i = 0;
            echo ++$count . ";";
        }
        $i++;
    }
    
    $xmlWriter->endElement();//docset
    
    unset($elements);
    
    file_put_contents($path, $xmlWriter->flush(true), FILE_APPEND);
    
    unset($xmlWriter);
    unset ($complex);
    echo 'ok';
    //print $xmlWriter->outputMemory(true);
}

