(function($){
    $(function() {
        var token = '51dfe5d42fb2b43e3300006e';
        var key = '86a2c2a06f1b2451a87d05512cc2c3edfdf41969';        
        
        var city = $( '[name="city"]' );
        var street = $( '[name="street"]' );
        var building = $( '[name="building"]' );
        var buildingAdd = $( '[name="building-add"]' );

        var map = null;
        var placemark = null;
        var map_created = false;

        // Формирует подписи в autocomplete
        var Label = function( obj, query ){
            var label = '';
            
            var name = obj.name.toLowerCase();
            query = query.toLowerCase();
            
            var start = name.indexOf(query);
            start = start > 0 ? start : 0;

            if(obj.typeShort){
                label += '<span class="ac-s2">' + obj.typeShort + '. ' + '</span>';
            }

            if(query.length < obj.name.length){
                label += '<span class="ac-s2">' + obj.name.substr(0, start) + '</span>';
                label += '<span class="ac-s">' + obj.name.substr(start, query.length) + '</span>';
                label += '<span class="ac-s2">' + obj.name.substr(start+query.length, obj.name.length-query.length-start) + '</span>';
            } else {
                label += '<span class="ac-s">' + obj.name + '</span>';
            }

            if(obj.parents){
                for(var k = obj.parents.length-1; k>-1; k--){
                    var parent = obj.parents[k];
                    if(parent.name){
                        if(label) label += '<span class="ac-st">, </span>';
                        label += '<span class="ac-st">' + parent.name + ' ' + parent.typeShort + '.</span>';
                    }
                }
            }

            return label;
        };
        
        // Подключение плагина для поля ввода города
        city.kladr({
            token: token,
            key: key,
            type: $.ui.kladrObjectType.CITY,
            withParents: true,
            label: Label,
            select: function( event, ui ) {
                city.data( "kladr-obj", ui.item.obj );
                city.parent().find( 'label' ).text( ui.item.obj.type );
                street.kladr( 'option', { parentType: $.ui.kladrObjectType.CITY, parentId: ui.item.obj.id } );
                building.kladr( 'option', { parentType: $.ui.kladrObjectType.CITY, parentId: ui.item.obj.id } );
                Log(ui.item.obj);
                AddressUpdate();
                MapUpdate();                
            }
        });

        // Подключение плагина для поля ввода улицы
        street.kladr({
            token: token,
            key: key,
            type: $.ui.kladrObjectType.STREET,
            label: Label,
            select: function( event, ui ) {
                street.data( "kladr-obj", ui.item.obj );
                street.parent().find( 'label' ).text( ui.item.obj.type );
                building.kladr( 'option', { parentType: $.ui.kladrObjectType.STREET, parentId: ui.item.obj.id } );
                Log(ui.item.obj);
                AddressUpdate();
                MapUpdate();
            }
        });

        // Подключение плагина для поля ввода номера дома
        building.kladr({
            token: token,
            key: key,
            type: $.ui.kladrObjectType.BUILDING,
            label: Label,
            select: function( event, ui ) {
                building.data( "kladr-obj", ui.item.obj );
                Log(ui.item.obj);
                AddressUpdate();
                MapUpdate();
            }
        });
        
        // Проверка корректности названия города (если пользователь ввёл сам, а не выбрал в списке)
        city.change(function(){
            $.kladrCheck({
                token: token,
                key: key,
                value: city.val(),
                type: $.ui.kladrObjectType.CITY,
            }, function(obj){
                if(obj) {
                    city.val(obj.name);
                    city.data( "kladr-obj", obj );
                    city.parent().find( 'label' ).text( obj.type );
                    street.kladr( 'option', { parentType: $.ui.kladrObjectType.CITY, parentId: obj.id } );
                    building.kladr( 'option', { parentType: $.ui.kladrObjectType.CITY, parentId: obj.id } );
                    Log(obj);
                } else {
                    city.data( "kladr-obj", null );
                    city.css('color', 'red');
                    Log(null);
                }   
                
                AddressUpdate();
                MapUpdate(); 
            });
        });
        
        // Проверка корректности названия улицы (если пользователь ввёл сам, а не выбрал в списке)
        street.change(function(){
            var query = {
                token: token,
                key: key,
                value: street.val(),
                type: $.ui.kladrObjectType.STREET,
            };
            
            var cityObj = city.data( "kladr-obj" );
            if(cityObj){
                query['parentType'] = $.ui.kladrObjectType.CITY;
                query['parentId'] = cityObj.id;
            }            
            
            $.kladrCheck(query, function(obj){
                if(obj) {
                    street.val(obj.name);
                    street.data( "kladr-obj", obj );
                    street.parent().find( 'label' ).text( obj.type );
                    building.kladr( 'option', { parentType: $.ui.kladrObjectType.STREET, parentId:  obj.id } );
                    Log(obj);
                } else {
                    street.data( "kladr-obj", null );
                    street.css('color', 'red');
                    Log(null);
                }  
                
                AddressUpdate();
                MapUpdate(); 
            });
        });
        
        // Проверка названия строения
        building.change(function(){
            var query = {
                token: token,
                key: key,
                value: building.val(),
                type: $.ui.kladrObjectType.BUILDING,
            };
            
            var cityObj = city.data( "kladr-obj" );
            if(cityObj){
                query['parentType'] = $.ui.kladrObjectType.CITY;
                query['parentId'] = cityObj.id;
            } 
            
            var streetObj = street.data( "kladr-obj" );
            if(streetObj){
                query['parentType'] = $.ui.kladrObjectType.STREET;
                query['parentId'] = streetObj.id;
            }            
            
            $.kladrCheck(query, function(obj){
                if(obj && (obj.name == building.val())){
                    building.val(obj.name);
                    building.data( "kladr-obj", obj );
                    Log(obj);
                } else {
                    Log(null);
                } 
                
                AddressUpdate();
                MapUpdate();
            });
        });
        
        // Проверка названия корпуса
        buildingAdd.change(function(){
            Log(null);
            AddressUpdate();
            MapUpdate(); 
        });

        var fields = city.add(street);
        
        // Отображение крутилки при отправке запроса к сервису
        fields.bind('downloadStart', function(){
            $(this).parent().find('.spinner').show();
        });
        
        // Скрытие крутилки по получении ответа от сервиса
        fields.bind('downloadStop', function(){
            $(this).parent().find('.spinner').hide();
        });
        
        fields.keydown(function(){
            $(this).css('color', 'black');
        });
        
        // Обновляет карту
        var MapUpdate = function(){
            var zoom = 12;
            var address = '';

            var cityObj = city.data( "kladr-obj" );
            if(cityObj){
                if(address) address += ', ';
                address += cityObj.typeShort + ' ' + cityObj.name;
                zoom = 12;
            }

            var streetObj = street.data( "kladr-obj" );
            if(streetObj){
                if(address) address += ', ';
                address += streetObj.typeShort + ' ' + streetObj.name;
                zoom = 14;
            }

            var buildingVal = $.trim(building.val());
            if(buildingVal){
                if(address) address += ', ';
                address += 'д. ' + buildingVal;
                zoom = 16;
            }

            var buildingAddVal = $.trim(buildingAdd.val());
            if(buildingAddVal){
                if(address) address += ', ';
                address += buildingAddVal;
                zoom = 16;
            }

            if(address && map_created){
                var geocode = ymaps.geocode(address);
                geocode.then(function(res){
                    map.geoObjects.each(function (geoObject) {
                            map.geoObjects.remove(geoObject);
                    });

                    var position = res.geoObjects.get(0).geometry.getCoordinates();

                    placemark = new ymaps.Placemark(position, {}, {});

                    map.geoObjects.add(placemark);
                    map.setCenter(position, zoom);
                });
            }
        }
        
        // Обновляет текстовое представление адреса
        var AddressUpdate = function(){
            var address = '';
            var zip = '';

            var cityObj = city.data( "kladr-obj" );
            if(cityObj){
                if(address) address += ', ';
                address += cityObj.typeShort + '. ' + cityObj.name;

                if(cityObj.zip) zip = cityObj.zip;
            }

            var streetObj = street.data( "kladr-obj" );
            if(streetObj){
                if(address) address += ', ';
                address += streetObj.typeShort + '. ' + streetObj.name;

                if(streetObj.zip) zip = streetObj.zip;
            }

            var buildingVal = $.trim(building.val());
            if(buildingVal){
                var buildingObj = building.data( "kladr-obj" );
                
                if(address) address += ', ';
                address += 'д. ' + buildingVal;
                
                if(buildingObj && buildingObj.zip) zip = buildingObj.zip;
            }

            var buildingAddVal = $.trim(buildingAdd.val());
            if(buildingAddVal){
                if(address) address += ', ';
                address += 'к. ' + buildingAddVal;
            }
            
            address = (zip ? zip + ', ' : '') + address;
            $('#address').text(address);
        }
        
        // Обновляет лог текущего выбранного объекта
        var Log = function(obj){
            var logId = $('#id');
            if(obj && obj.id){
                logId.find('.value').text(obj.id);
                logId.show();
            } else {
                logId.hide();
            }
            
            var logName = $('#name');
            if(obj && obj.name){
                logName.find('.value').text(obj.name);
                logName.show();
            } else {
                logName.hide();
            }
            
            var logZip = $('#zip');
            if(obj && obj.zip){
                logZip.find('.value').text(obj.zip);
                logZip.show();
            } else {
                logZip.hide();
            }
            
            var logType = $('#type');
            if(obj && obj.type){
                logType.find('.value').text(obj.type);
                logType.show();
            } else {
                logType.hide();
            }
            
            var logTypeShort = $('#type_short');
            if(obj && obj.typeShort){
                logTypeShort.find('.value').text(obj.typeShort);
                logTypeShort.show();
            } else {
                logTypeShort.hide();
            }
        }

        ymaps.ready(function(){
            if(map_created) return;
            map_created = true;

            map = new ymaps.Map('map', {
                center: [55.76, 37.64],
                zoom: 12
            });

            map.controls.add('smallZoomControl', { top: 5, left: 5 });
        });
    });
})(jQuery);