(function($){
    $(function() {
        var container = $('#example5');
        
        var token = '51dfe5d42fb2b43e3300006e';
        var key = '86a2c2a06f1b2451a87d05512cc2c3edfdf41969';        
        
        var region = container.find( '.regionId');
        var district = container.find( '.districtId');
        var city = container.find( '.cityId');
        var street = container.find( '.streetId');
        var building = container.find( '.buildingId');
        var query = container.find( '.query');
        var contentType = container.find( '.contentType');
        var withParent = container.find( '.withParent');
        var limit = container.find( '.limit');

        var result = container.find( '.result');

        container.find( '.show').click(function(){
            var apiQuery = {
                token : token,
                key : key
            };

            if(contentType.val())
                apiQuery.contentType = contentType.val();

            if(query.val())
                apiQuery.query = query.val();

            if(region.val())
                apiQuery.regionId = region.val();

            if(district.val())
                apiQuery.districtId = district.val();

            if(city.val())
                apiQuery.cityId = city.val();

            if(street.val())
                apiQuery.streetId = street.val();

            if(building.val())
                apiQuery.buildingId = building.val();

            if(withParent.is(':checked'))
                apiQuery.withParent = 1;

            if(limit.val())
                apiQuery.limit = limit.val();

            $.getJSON($.kladr.url + "?callback=?",
                apiQuery,
                function(data) {
                    result.text(JSON.stringify(data.result, null, 4));
                }

            );

        });


    });
})(jQuery);