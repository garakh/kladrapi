$(function(){
    var container = $('#example1');
    
    // Подключение автодополнения улиц
    container.find( '[name="street"]' ).kladr({
        token: '51dfe5d42fb2b43e3300006e',
        key: '86a2c2a06f1b2451a87d05512cc2c3edfdf41969',
        type: $.kladr.type.street,
        parentType: $.kladr.type.city,
        parentId: '7700000000000'
    });
});