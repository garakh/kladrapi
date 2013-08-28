(function($){
    $(function() {
        var token = '51dfe5d42fb2b43e3300006e';
        var key = '86a2c2a06f1b2451a87d05512cc2c3edfdf41969';   
        
        var container = $( '#example4' );
        
        var city = container.find( '[name="city"]' );
        var street = container.find( '[name="street"]' );
        var building = container.find( '[name="building"]' );
        var buildingAdd = container.find( '[name="building-add"]' );

        var map = null;
        var placemark = null;
        var map_created = false;

        // Формирует подписи в autocomplete
        var Label = function( obj, query ){
            var label = '';

            if(obj.name){
                if(obj.typeShort){
                    label += '<span class="ac-s2">' + obj.typeShort + '. ' + '</span>';
                }
                
                query = query.toLowerCase();
                var name = obj.name.toLowerCase();
                var start = name.indexOf(query);
                start = start < 0 ? 0 : start;

                if(query.length < obj.name.length){
                    label += '<span class="ac-s2">' + obj.name.substr(0, start) + '</span>';
                    label += '<span class="ac-s">' + obj.name.substr(start, query.length) + '</span>';
                    label += '<span class="ac-s2">' + obj.name.substr(query.length, obj.name.length - query.length - start) + '</span>';
                } else {
                    label += '<span class="ac-s">' + obj.name + '</span>';
                }
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

        // Обновляет карту
        var MapUpdate = function(){
            var zoom = 12;
            var address = '';

            var cityVal = $.trim(city.val());
            if(cityVal){
                var cityObj = city.data( "kladr-obj" );
                if(address) address += ', ';
                address += ( cityObj ? (cityObj.typeShort + ' ') : '' ) + cityVal;
                zoom = 12;
            }

            var streetVal = $.trim(street.val());
            if(streetVal){
                var streetObj = street.data( "kladr-obj" );
                if(address) address += ', ';
                address += ( streetObj ? (streetObj.typeShort + ' ') : '' ) + streetVal;
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

            var cityVal = $.trim(city.val());
            if($.trim(city.val())){
                var cityObj = city.data( "kladr-obj" );
                
                if(address) address += ', ';
                address += ( cityObj ? (cityObj.typeShort + '. ') : 'г. ' ) + cityVal;
                
                if(cityObj && cityObj.zip) zip = cityObj.zip;
            }

            var streetVal = $.trim(street.val());
            if(streetVal){
                var streetObj = street.data( "kladr-obj" );
                
                if(address) address += ', ';
                address += ( streetObj ? (streetObj.typeShort + '. ') : 'ул. ' ) + streetVal;
                
                if(streetObj && streetObj.zip) zip = streetObj.zip;
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
            container.find('#address').text(address);
        }
        
        // Обновляет лог текущего выбранного объекта
        var Log = function(obj){
            var logId = container.find('#id');
            if(obj.id){
                logId.find('.value').text(obj.id);
                logId.show();
            } else {
                logId.hide();
            }
            
            var logName = container.find('#name');
            if(obj.name){
                logName.find('.value').text(obj.name);
                logName.show();
            } else {
                logName.hide();
            }
            
            var logZip = container.find('#zip');
            if(obj.zip){
                logZip.find('.value').text(obj.zip);
                logZip.show();
            } else {
                logZip.hide();
            }
            
            var logType = container.find('#type');
            if(obj.type){
                logType.find('.value').text(obj.type);
                logType.show();
            } else {
                logType.hide();
            }
            
            var logTypeShort = container.find('#type_short');
            if(obj.typeShort){
                logTypeShort.find('.value').text(obj.typeShort);
                logTypeShort.show();
            } else {
                logTypeShort.hide();
            }
        }

        city.kladr({
            token: token,
            key: key,
            type: $.ui.kladrObjectType.CITY,
            withParents: true,
            label: Label,
            select: function( event, ui ) {
                city.val(ui.item.obj.name);
                city.data( "kladr-obj", ui.item.obj );
                city.parent().find( 'label' ).text( ui.item.obj.type );
                street.kladr( 'option', { parentType: $.ui.kladrObjectType.CITY, parentId: ui.item.obj.id } );
                building.kladr( 'option', { parentType: $.ui.kladrObjectType.CITY, parentId: ui.item.obj.id } );
                Log(ui.item.obj);
                AddressUpdate();
                MapUpdate();                
            }
        });

        street.kladr({
            token: token,
            key: key,
            type: $.ui.kladrObjectType.STREET,
            label: Label,
            select: function( event, ui ) {
                street.val(ui.item.obj.name);
                street.data( "kladr-obj", ui.item.obj );
                street.parent().find( 'label' ).text( ui.item.obj.type );
                building.kladr( 'option', { parentType: $.ui.kladrObjectType.STREET, parentId: ui.item.obj.id } );
                Log(ui.item.obj);
                AddressUpdate();
                MapUpdate();
            }
        });

        building.kladr({
            token: token,
            key: key,
            type: $.ui.kladrObjectType.BUILDING,
            label: Label,
            select: function( event, ui ) {
                building.val(ui.item.obj.name);
                building.data( "kladr-obj", ui.item.obj );
                Log(ui.item.obj);
                AddressUpdate();
                MapUpdate();
            }
        });
        
        city.change(function(){
            $.kladrCheck({
                token: token,
                key: key,
                value: city.val(),
                type: $.ui.kladrObjectType.CITY,
            }, function(obj){
                if(!obj) {
                    city.css('color', 'red');
                } else {
                    city.data( "kladr-obj", obj );
                    city.parent().find( 'label' ).text( obj.type );
                    street.kladr( 'option', { parentType: $.ui.kladrObjectType.CITY, parentId: obj.id } );
                    building.kladr( 'option', { parentType: $.ui.kladrObjectType.CITY, parentId: obj.id } );
                    Log(obj);
                    AddressUpdate();
                    MapUpdate(); 
                }         
            });
        });
        
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
                if(!obj) {
                    street.css('color', 'red');
                } else {
                    street.val(obj.name);
                    street.data( "kladr-obj", obj );
                    street.parent().find( 'label' ).text( obj.type );
                    building.kladr( 'option', { parentType: $.ui.kladrObjectType.STREET, parentId:  obj.id } );
                    Log(obj);
                    AddressUpdate();
                    MapUpdate(); 
                }              
            });
        });
        
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
                if(!obj) {
                    building.css('color', 'red');
                } else {
                    building.val(obj.name);
                    building.data( "kladr-obj", obj );
                    Log(obj);
                    AddressUpdate();
                    MapUpdate(); 
                }              
            });
        });
        
        container.find('[name="building-add"]').change(function(){
            AddressUpdate();
            MapUpdate(); 
        });

        var fields = city.add(street).add(building).add(buildingAdd);
        
        fields.keydown(function(){
            $(this).css('color', 'black');
        });
        
        fields.bind('downloadStart', function(){
            $(this).parent().find('.spinner').show();
        });
        
        fields.bind('downloadStop', function(){
            $(this).parent().find('.spinner').hide();
        });

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