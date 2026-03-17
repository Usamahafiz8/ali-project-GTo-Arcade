<div class="popup" id="loading_popup">    
    <div id="gameLoadingBack">
        <div id="gameLoadingProgress"></div>
    </div>
</div>
<script>
    $(function() {
        var div = $('#gameLoadingBack');
        var width = div.width();    
        div.css('height', width * 61 / 673);
    });

    addEventListener('message', function(e){
        try{
            var data = JSON.parse(e.data);            
            if(data.event == 'loadProgress')
            {
                $('#loading_popup').css('display', 'block');
                setGameLoadingProgress(data.value * 100);
                if(data.value >= 0.999)
                {
                    setTimeout(()=>{$('#loading_popup').css('display', 'none');}, 2500);
                }
            }            
        }
        catch(e){}
    })

    function setGameLoadingProgress(progress)
    {
        if(progress > 100)
            progress = 100;
        var progress_div = $('#gameLoadingProgress');        
        progress_div.css('width', progress + '%');
    }
</script>