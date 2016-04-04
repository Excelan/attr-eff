
/**
 HASH ROUTE
 Navigation.Intent('edit/urn')
 disable current screen (cache in mem for 3 min to allow use vs rebuild)

 SCREEN (like html static page controller) - layout with placeholders + main app widget + other widgets
 main app widget = new/edit form, show some

 "OS" - main wrap ui - "start", actionbar, menu etc, left, right sidebars etc. Universal UI. Layout is included INSIDE OS/UI
 allow have OS like (w8, osx) bahave - best from all, not to be like host sys OS.
 it not layout parenting. OS UI != LAYOUT

 LAYOUT - html with DOM placeholders by #ID.
 Non main widgets are independent but notified on 1/list has changed
 APP/WIDGET (any is ONEENTITYVIEW/LISTENTITYVIEW/CUSTOMVIEW))
 - DATA BIND MONITOR - ONE (1 urn), LIST (urn, predicate)

 GC.SCREEN['edit']([urn])
 GC.APP['edit']([urn])

 */
var uriRoute = function () {
    var h = window.location.hash.slice(1);
    var uric = h.split('/');
    //console.log('route',h,uric);
    if (h == "") return;
    if (GC.ROUTER)
    {
//        console.log(GC.READY);
        if (GC.READY !== true)
        {
//            console.log('DB & XML NOT READY', GC.READY);
            GC.READY.then(function(dbready){ GC.ROUTER(uric) });
        }
        else
        {
//            console.log('DB & XML READY');
            GC.ROUTER(uric)
        }
    }
    else
    {
        throw new Error('NO GC.ROUTER');
    }
}
window.addEventListener('hashchange', uriRoute);
window.addEventListener('load', uriRoute);