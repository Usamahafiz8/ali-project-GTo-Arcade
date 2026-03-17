(function() {
    var b = !1;
    try {
        var d = Object.defineProperty({}, "passive", {
            get: function() {
                b = !0
            }
        });
        window.addEventListener("test", null, d)
    } catch (a) {}
    document.addEventListener("touchstart", function(a) {
        1 < a.touches.length && a.preventDefault()
    }, b ? {
        passive: !1
    } : !1);
    var c = 0;
    document.addEventListener("touchend", function(a) {
        var b = (new Date).getTime();
        300 >= b - c && a.preventDefault();
        c = b
    }, b ? {
        passive: !1
    } : !1);
    document.addEventListener("touchmove", function(a) {
        (1 < a.touches.length || a.scale && 1 !== a.scale) && a.preventDefault()
    }, b ? {
        passive: !1
    } : !1)
})();