(new Image()).src = "/images/btn-start2.png";
(new Image()).src = "/images/btn-blue2.png";

$(function(){
    var PageHeightSet = function(){
        var pageHeight = $('body').height();
        var windowHeight = $(window).height();
        
        if(pageHeight >= windowHeight) return;
        
        $('.main').height(windowHeight - $('.header').height() - $('.footer').height() - 20);
    };
    
    PageHeightSet();    
    $(window).resize(function(){
        PageHeightSet();
    });
    
    $('.advers li').each(function() {		
        height = $(this).height();
        $(this).before('<div class="before" style="height:'+height+'px"></div>');
    });
    
    // Валидация email
    var FormDisableSubmit = function(){ return false; };    
    $('[data-email]').bind('change blur', function(){
        var $this = $(this);
        var form = $this.closest('form');   
        
        if(!/^[-._a-z0-9]+@(?:[a-z0-9][-a-z0-9]+\.)+[a-z]{2,6}$/.test($this.val())){
            form.bind('submit', FormDisableSubmit); 
            $this.css('color', 'red');
        } 
        
        return false;
    });
    $('[data-email]').bind('keydown focus', function(){
        var $this = $(this);
        var form = $this.closest('form'); 
        
        $this.css('color', 'black');
        form.unbind('submit', FormDisableSubmit); 
    });
    
    $('.header .btr-reg.sing-in, #bt-reg, #start-use-service').click(function(e){ 
        $('#register-popap').modal({
            maxHeight: 371,
            maxWidth: 570,
            closeHTML: '<b class="close">X</b>',
            overlayClose: true
        });
        return false;
    });
    
    $('.header .btn-enter.login').click(function(e){ 
        $('#login-popap').modal({
            maxHeight: 371,
            maxWidth: 570,
            closeHTML: '<b class="close">X</b>',
            overlayClose: true
        });
        return false;
    });
    
    $('.header .btr-reg.recovery').click(function(e){ 
        $('#recovery-popap').modal({
            maxHeight: 371,
            maxWidth: 570,
            closeHTML: '<b class="close">X</b>',
            overlayClose: true
        });
        return false;
    });
    
    $('.header .btn-enter.user').click(function(e){
        var popap = $('#personal-popap');
        
        popap.find('.reg').load('/personal/get/', function(){
            popap.modal({
                maxHeight: 371,
                maxWidth: 570,
                closeHTML: '<b class="close">X</b>',
                overlayClose: true
            });
        });
        return false;
    });
    
    var recoveryPopap = $('#recovery-popap');
    recoveryPopap.find('.btn').click(function(){
        var oldPass = recoveryPopap.find('#inputOld').val();
        var newPass = recoveryPopap.find('#inputNew').val();
        var repeatPass = recoveryPopap.find('#inputRepeat').val();
        
        if(!oldPass){
            alert('Введите старый пароль');
            return;
        }
        
        if(!newPass){
            alert('Введите новый пароль');
            return;
        }
        
        if(!repeatPass){
            alert('Повторите новый пароль');
            return;
        }
        
        if(newPass != repeatPass){
            alert('Неверно введён повтор нового пароля');
            return;
        }
        
        $.post('/recovery/change',{
            'old': oldPass,
            'new': newPass,
            'repeat': repeatPass
        }, function(res){
            if(res == 'y'){
                alert('Пароль успешно изменён');
                $.modal.close();
            } else {
                alert(res);
            }
        });
        
        return false;
    });
});