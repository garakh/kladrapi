$(function(){
   var container = $('#feedback');
   container.find('.btn').click(function(){
      var name = $.trim(container.find('#inputName').val()); 
      var email = $.trim(container.find('#inputEmail').val()); 
      var comment = $.trim(container.find('#inputComment').val()); 
      
      if(!name){
          alert('Введите имя');
          return false;
      }
      
      if(!email){
          alert('Введите e-mail');
          return false;
      }
      
      if(!email.match(/^[-._a-z0-9]+@(?:[a-z0-9][-a-z0-9]+\.)+[a-z]{2,6}$/g)){
          alert('Некорректно введён e-mail');
          return false;
      }
      
      if(!comment){
          alert('Введите сообщение');
          return false;
      }
      
      $.post('/contacts/feedback', {
          'name': name,
          'email': email,
          'comment': comment
      }, function(res){
         if(res == 'y'){
             alert('Ваше сообщение принято');
         }
      });
      
      return false;
   });
});

ymaps.ready(init);
var myMap;

function init(){
    myMap = new ymaps.Map ("map", {
        center: [55.76, 37.64],
        zoom: 15
    });

    var myGeocoder = ymaps.geocode("Россия, г. Архангельск, пр. Ломоносова, 81");
    myGeocoder.then(
        function (res) {
            myMap.geoObjects.add(
                new ymaps.Placemark(res.geoObjects.get(0).geometry.getCoordinates(),
                    {iconContent: 'Праймпикс'},
                    {preset: 'twirl#greenStretchyIcon'}
                )
            );

            myMap.setCenter(res.geoObjects.get(0).geometry.getCoordinates());
        },
        function (err) {

        }
    );
}