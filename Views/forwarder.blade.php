<style>
    .pre-loader{direction:rtl; position:fixed;background:#ecf0f1;width:100%;height:100%;z-index:9999999;display:block}.pre-loader .load-con{margin:20% auto;position:relative;text-align:center;color:#293889;text-shadow:0 1px 2px rgba(0,0,0,.6)}.pre-loader .spinner{margin:20px auto 0;width:70px;text-align:center}.pre-loader .spinner>div{width:18px;height:18px;background-color:#bdc3c7;border-radius:100%;display:inline-block;-webkit-animation:bouncedelay 1.4s infinite ease-in-out;animation:bouncedelay 1.4s infinite ease-in-out;-webkit-animation-fill-mode:both;animation-fill-mode:both}.pre-loader .spinner .bounce1{-webkit-animation-delay:-.32s;animation-delay:-.32s}.pre-loader .spinner .bounce2{-webkit-animation-delay:-.16s;animation-delay:-.16s}@-webkit-keyframes bouncedelay{0%,100%,80%{-webkit-transform:scale(0)}40%{-webkit-transform:scale(1)}}@keyframes bouncedelay{0%,100%,80%{transform:scale(0);-webkit-transform:scale(0)}40%{transform:scale(1);-webkit-transform:scale(1)}}
</style>
<div class='pre-loader'>
    <div class='load-con'>
        <div class='spinner'>
            <div class='bounce1'></div>
            <div class='bounce2'></div>
            <div class='bounce3'></div>
        </div>
    </div>
</div>
در حال انتقال به بانک
<form name='paymentForm' method='POST' action='https://mabna.shaparak.ir' style='display: none'>
    <p><input type='submit' value='GO TO PAYMENT PAGE' name='B1'></p>
    <input type='hidden' name='TOKEN' value='{{$token}}'>
</form>
<script type='text/javascript' language='JavaScript'>
    setTimeout(function(){
        document.paymentForm.submit();
    }, 0);
</script>