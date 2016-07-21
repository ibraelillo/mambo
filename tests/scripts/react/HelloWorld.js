/**
 * Created by ibra on 21/07/2016.
 */
var React = require ('./react');
var ReactDOM = require ('./reactdom');

var Hello = React.createClass({
    render: function(){
        return React.DOM.div({}, [
            React.DOM.h1({}, 'Hello'),
            React.DOM.h1({}, 'World!')
        ])
    }
})

exports = Hello;