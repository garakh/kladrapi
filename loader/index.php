<?
exit(); // Деактивация

// Папка с файлами БД КЛАДР сконвертированными в формат csv
define('UPLOAD_DIR', $_SERVER["DOCUMENT_ROOT"].'/files/');

require $_SERVER["DOCUMENT_ROOT"] . '/loader/tools.php';
require $_SERVER["DOCUMENT_ROOT"] . '/loader/include.php';

// Фунцкия для запуска загрузчика файла
function LoadFile($db, $arLoaders, $file){
    $info = pathinfo($file);
    $loader = $arLoaders[$info['filename']];

    if($loader){
        print $info['basename'] . ': ';
        $loader = new $loader($db, $file);
        if($loader->Load()) print 'Файл загружен успешно';
        else print $loader->Error;
        print '<br/>';
    }
}

// Соотношение загрузчика имени файла
$arLoaders = array(
    'ALTNAMES' => 'AltnamesLoader',
    'SOCRBASE' => 'SocrbaseLoader',    
    'KLADR' => 'KladrLoader',    
    'STREET' => 'StreetLoader',
    'DOMA' => 'DomaLoader',
    'FLAT' => 'FlatLoader',
);

$file_list = glob(UPLOAD_DIR . "*.csv");

try {
    $conn = new MongoClient('mongodb://10.62.206.183:27017');
    $db = $conn->kladr;
    $db->drop();

    foreach($file_list as $key => $file){
        $info = pathinfo($file);
        if($info['filename'] == 'SOCRBASE'){
            LoadFile($db, $arLoaders, $file);
            unset($file_list[$key]);
            break;
        }
    }

    foreach($file_list as $key => $file){
        $info = pathinfo($file);
        if($info['filename'] == 'ALTNAMES'){
            LoadFile($db, $arLoaders, $file);
            unset($file_list[$key]);
            break;
        }
    }

    foreach($file_list as $file){
        LoadFile($db, $arLoaders, $file);
    }

    $db->altnames->drop();
    $db->socrbase->drop();

    $conn->close();
} catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
} catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
}