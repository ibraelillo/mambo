
var React = require('./react');
var server = require('./react-dom-server');
var Hello = require('./HelloWorld');

print(Object.keys(this));

print(Object.keys(server));

print(
    server.renderToString(
        React.createElement(Hello)
    )
);