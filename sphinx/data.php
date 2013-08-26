<?

try {
    $conn = new MongoClient();
    $db = $conn->kladr;
    $collection = $db->selectCollection($argv[1]);

    $cursor = $collection->find();
    $cursor->limit(500);

    print('<?xml version="1.0" encoding="utf-8"?>' . "\r");
    print('<sphinx:docset xmlns:sphinx="http://sphinxsearch.com/">' . "\r");
    print("\r");

    print('  <sphinx:schema>' . "\r");
    print('    <sphinx:field name="name" attr="string" />' . "\r");
    print('  </sphinx:schema>' . "\r");
    print("\r");

    foreach($cursor as $document){
        print('  <sphinx:document id="' . $document['Id'] . '">' . "\r");
        print('    <name>' . $document['Name'] . '</name>' . "\r");
        print('  </sphinx:document>' . "\r");
    }

    print("\r");
    print('</sphinx:docset>');

    $conn->close();
} catch (Exception $e) {
    exit();
}