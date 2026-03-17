
<div class="notification_bonus popup" style="display: none;">    
    <button class="notification__close"></button>
    <img class="bonus_wintype" src="/frontend/Default/img/gold_jackpot.png"/>
    <div class="bonus_win">
        <p class="bonus_balance"></p>
    </div>
</div>


<script>
    $(function(){
        var landscape = false;
        if(window.innerHeight > window.innerWidth){
            landscape = true;
        }
        // if(landscape)
        {
            var width = $('.notification_bonus').width();
            var height = width * 659 / 1453;
            $('.notification_bonus').css('height', height + 'px');

            width = width * 0.08;
            $('.notification__close').css('height', width + 'px');
        }        
    })

    $('.notification__close').on('click', () => {
        $('.popup').css('display', 'none');
    })    
</script>