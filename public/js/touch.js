(function(){
    scrollers = {};
    function showScreen(screenId){
        try{
            var activeScreen = document.querySelector("#container .scroller#" + screenId);}catch(e){
            return;
        }

        if(!activeScreen){ return; }

        Array.prototype.forEach.call(document.querySelectorAll("#container .scroller"), function(screen){
            screen.style.display = "none";
        });
        activeScreen.style.display = "block";

        scrollers[screenId].setupScroller(true);
    }

    Array.prototype.forEach.call(document.querySelectorAll("#container .scroller"), function(scroller){
        scrollers[scroller.id] = new TouchScroll(scroller, {elastic: true});
    });

    document.querySelector("#container .tabs").addEventListener("click", function(event){
        var screenId = event.target.getAttribute("href");
        if(screenId){
            showScreen(screenId.slice(1));

            Array.prototype.forEach.call(this.children, function(tab){
                tab.className = "";
            });
            event.target.className = "current";
        }
    }, false);

    var hash = location.hash.slice(1);
    showScreen(hash || "kc");
    tab = document.querySelector('#container .tabs [href="' +  location.hash + '"]');
    if(tab){
        Array.prototype.forEach.call(tab.parentNode.children, function(tab){
            tab.className = "";
        });
        tab.className = "current";
    }
}())