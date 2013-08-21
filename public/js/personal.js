$(function(){
    var container = $('.content');
    
    container.find('#permanent-key-reload').click(function(){
        $.get('/personal/keyreload', {}, function(data){
            container.find('#permanent-key').text(data);
        });
        return false;
    });
    
    container.find('#domain-reload').click(function(){
        $.post('/personal/domainload', {
            domain: container.find('#domain').val()
        }, function(data){
            container.find('#domain').val(data);
            alert('Сохранено');
        });
        return false;
    });
    
    container.find('#domain-key-reload').click(function(){
        $.post('/personal/domainkeyreload', {}, function(data){
            container.find('#domain-key').text(data);
        });
        return false;
    });
});