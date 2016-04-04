(function(window){
    
	ps = {},
    ps = window.ps,
    ps.subscriptions = [],
    
	ps.subscribe = function(name, callback)
	{
        ps.subscriptions.push({"name": name, "callback": callback});
        return [name,callback];
    },
    
	ps.unsubscribe = function(args)
	{
        for(x=0;x<ps.subscriptions.length;x++){
            if(ps.subscriptions[x].name == args[0], ps.subscriptions[x].callback == args[1])
                ps.subscriptions.splice(x, 1);
        }
    },
    
	ps.publish = function(name, args)
	{
        var temp = [];
        if(ps.subscriptions.length > 0){
            for(var x=0;x<ps.subscriptions.length;x++) {
                if(ps.subscriptions[x].name == name)
                    temp.push({"fn":ps.subscriptions[x].callback});
            }
            for(x=0;x<temp.length;x++){
                temp[x].fn.apply(this,[args]);
            }
        }
    };

})(window);