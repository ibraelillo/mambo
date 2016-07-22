var global = global || {};

var process = function(){};

process.cwd = function(){
    return global.process.cwd();
}

