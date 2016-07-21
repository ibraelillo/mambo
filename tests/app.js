var console = {
        
        log: function(){
            for(var i = 0; i < arguments.length; i++)
            {
                print(arguments[i]);
            }
        },
        error: function(){
            for(var i = 0; i < arguments.length; i++)
            {
                print(arguments[i]);
            }
        }
    };

exports = console;