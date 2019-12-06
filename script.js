
function hide_or_show_block(id, rowtype)
{
    var block = document.getElementById(id).style; 
    var container = document.getElementById(rowtype+id); 

    if(block.display == "none")
    {
        block.display = "block";
        container.className = "chekingTeacher vertical-node";
    }
    else
    {
        block.display = "none";
        container.className = "chekingTeacher horizontal-node";
    }
}


// Teachers tooltips start 
require(['jquery'], function($)
{
    $(document).ready(function() {

        $('div.jqueryTooltip').each(function(i){
            $("body").append("<div class='NeedToCheckTooltip' id='NeedToCheckTooltip"+i+"'><p>"+$(this).attr('title')+"</p></div>");
            var my_tooltip = $("#NeedToCheckTooltip"+i);
            
            $(this).removeAttr("title").mouseover(function(){
                    my_tooltip.css({display:"none"}).fadeIn(100);
            }).mousemove(function(kmouse){
                    my_tooltip.css({left:kmouse.pageX+15, top:kmouse.pageY+15});
            }).mouseout(function(){
                    my_tooltip.fadeOut(100);                  
            });
        });
    });    
});
// Teachers tooltips end

