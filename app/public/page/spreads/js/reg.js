$(document).ready(function () {
        var minSize;
        var maxSize;
    if(navigator.userAgent.match(/mobile/i)) { 
        minSize = 18;    // 飘落物最小尺寸18
        maxSize = 28;   // 飘落物最大尺寸28
    }else {
        minSize = 28;    // 飘落物最小尺寸18
        maxSize = 48;   // 飘落物最大尺寸28
    }  
    var showRate = 2000  // 飘落物出现的时间频率
    var snowLayer = $('.jinpiao img').css({ 'position': 'absolute', 'top': '-50px' });// 飘落物显示的层
    var winHeight = $(document).height();   // 获取页面高度
    var winWidth = $(document).width(); // 获取页面的宽度
    setInterval(function () {
        var a = Math.random();
        var startPositionLeft = Math.random() * winWidth - 100;    // 飘落物下落时随机位置
        var startOpacity = 0.7 + Math.random(); // 获取随机的透明度
        var sizeWidth = minSize + Math.random() * maxSize;  // 获取飘落物随机大小
        var endPositionTop = winHeight - 80;    // 飘落物距离窗体上端距离
        var endPositionLeft = (startPositionLeft + Math.random() * 500) >= winWidth ? winWidth - 50 : (startPositionLeft + Math.random() * 500); // 飘落物距离窗体左端距离
        if (endPositionLeft > winWidth) {
            endPositionLeft = winWidth - 100;
        }
        var durationFall = winHeight * 10 + Math.random() * 3000;   // 获取飘落物下落的随机速度
        var imgSrc = "./img/recommend/jinpiao"+Math.floor(Math.random()*5 + 1)+".png";//获取随机图片
        snowLayer.clone().appendTo('body').css({
            left: startPositionLeft,
            opacity: startOpacity,
            width: sizeWidth
        }).attr('src',imgSrc).animate({
            top: endPositionTop,
            left: endPositionLeft,
            opacity: 0.5
        }, durationFall, function () {
            $(this).remove();
        });
    }, showRate);
}); 

// 注册验证逻辑
var cmpRegs = {
    password: /(?=.*[a-z])((?=.*[0-9])|(?=.*[!@#$%^&*-+]))|((?=.*[0-9])((?=.*[a-z])|(?=.*[!@#$%^&*-+]))).{6,12}/,
    phone:/^1[2345789]\d{9}$/,
    verify: /^\d{6}$/
}




$(function(){
    function sendCode() {
        var captcha = $('#picString').val();
        var phone = $('#phone').val();
        if(phone=='') {
            $('.error').html('请输入手机号');
            $('.error').hide().slideDown();
            return false;
        }
        if(!cmpRegs.phone.test(phone)) {
            $('.error').html('手机号格式错误');
            $('.error').hide().slideDown();
            return false;
        }
        if(captcha=='') {
            $('.error').html('请输入验证码');
            $('.error').hide().slideDown();
            return false;
        }
        $.ajax({
            url: '/common/sendSms',
            type: 'POST',
            dataType: 'json',
            data: {phone:phone, msgType:'register', 'captcha': captcha},
            success: function(result){
              if (result.status==1){
                daojishi();
              } else {
                $('.error').html(result.info)
                $('.error').hide().slideDown();
              }
            }
        })
    }
    function daojishi(){
        var timer =60;
        function Countdown() {
            if (timer >= 1) {
                timer -= 1;
                setTimeout(function() {
                    Countdown();
                }, 1000);
            }
            if (timer<60) {
                 $(".reg-hqdx").text("短信已发送 "+timer+"s")
                 $('.reg-hqdx').attr("disabled",true); 
            }
            if(timer==0){
                $(".reg-hqdx").text("重新获取验证码")
                $('.reg-hqdx').attr("disabled",false); 
            }
        }
        Countdown()
    }
    $(".reg-hqdx").click(sendCode);
    function getSpreadUser() {
        var code = getQueryParam('spreadCode');
        if(!code){
            code = getCookie('spreadCode');
        }
        var spreadUser = '';
        $.ajax({
          url: '/common/getSpreadUser',
          type: 'POST',
          dataType: 'json',
          async: false,
          data: {code:code},
          success: function(result){
            if (result.status==1){
                spreadUser = result.data.spreadUser;
            }
          }
        })
        return spreadUser;
    }



    $('.reg-input-submit').on({
        click: function() {
            var phone = $('#phone').val();
            var smsCode = $('#verify').val();
            var password = $('#password').val();

            if(phone=='') {
                $('.error').html('请输入手机号');
                $('.error').hide().slideDown();
                return false;
            }
            if(!cmpRegs.phone.test(phone)) {
                $('.error').html('手机号格式错误');
                $('.error').hide().slideDown();
                return false;
            }

            if(smsCode=='') {
                $('.error').html('请输入手机验证码');
                $('.error').hide().slideDown();
                return false;
            }
            if(!cmpRegs.verify.test(smsCode)) {
                $('.error').html('手机验证码错误');
                $('.error').hide().slideDown();
                return false;
            }
            if(password=='') {
                $('.error').html('请输入密码');
                $('.error').hide().slideDown();
                return false;
            }
            if(!cmpRegs.password.test(password)) {
                $('.error').html('密码格式错误');
                $('.error').hide().slideDown();
                return false;
            }
            var spreadUser = getSpreadUser();
            $.ajax({
              url: 'https://www.91hc.com/help/register',
              type: 'POST',
              dataType: 'json',
              data: {phone:phone, password:password, smsCode:smsCode, spreadUser:spreadUser},
              success: function(result){
                if (result.status==1){
                    setCookie('spreadCode','');
                    host=document.domain; 
                    var url = host.split('.');
                    url = 'http://user.'+ url[1] +'.com/register/guide';
                    window.opener=null;
                    window.open(url,'_self');
                    window.close();
                } else {
                    alert(result.msg);
                }
              }
            })
        }
    });
})

function getQueryParam(name) { 
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
    var result = window.location.search.substr(1).match(reg); 
    if (result != null) {
        return unescape(result[2]); 
    } else {
        return null;
    }
}

    function iniSpreadCode(){
        var code = getQueryParam('spreadCode');
        if(code){
            setCookie('spreadCode',code);
        }
    }

    iniSpreadCode();

    function getCookie(c_name){
　　　　if (document.cookie.length>0){　　//先查询cookie是否为空，为空就return ""
　　　　　　c_start=document.cookie.indexOf(c_name + "=")　　//通过String对象的indexOf()来检查这个cookie是否存在，不存在就为 -1　　
　　　　　　if (c_start!=-1){ 
　　　　　　　　c_start=c_start + c_name.length+1　　//最后这个+1其实就是表示"="号啦，这样就获取到了cookie值的开始位置
　　　　　　　　c_end=document.cookie.indexOf(";",c_start)　　//其实我刚看见indexOf()第二个参数的时候猛然有点晕，后来想起来表示指定的开始索引的位置...这句是为了得到值的结束位置。因为需要考虑是否是最后一项，所以通过";"号是否存在来判断
　　　　　　　　if (c_end==-1) c_end=document.cookie.length　　
　　　　　　　　return unescape(document.cookie.substring(c_start,c_end))　　//通过substring()得到了值。想了解unescape()得先知道escape()是做什么的，都是很重要的基础，想了解的可以搜索下，在文章结尾处也会进行讲解cookie编码细节
　　　　　　} 
　　　　}
　　　　return "";
　　}

    function setCookie(c_name, value, expiredays){
        expiredays = expiredays ? expiredays : '9999';
 　　　　var exdate=new Date();
　　　　 exdate.setDate(exdate.getDate() + expiredays);
 　　　　document.cookie=c_name+ "=" + escape(value) + ((expiredays==null) ? "" : ";expires="+exdate.toGMTString()+";path=/;domain=.91hc.com");
　　}