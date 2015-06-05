var React = require('react');

var state = {};
state['title'] = '';
state['ip'] = '';
state['geo'] = '';

var getState = function () {
    return state;
};

var AppComponent = React.createClass({
    getInitialState: function () {
        return getState();
    },
    onChange: function(e) {
        if (e.target.value == '') {
            return;
        }

        var request = new XMLHttpRequest();
        request.onreadystatechange = function () {};
        request.open('GET', '/lookup.json?host=' + e.target.value, true);
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.send(null);
    },
    render: function () {
        return <div className="grid-container">
            <input onChange={this.onChange} />
            <div>
                <div className="grid-50">
                    <h1>IP</h1>
                    <h2>{this.state.dns}</h2>
                </div>
                <div className="grid-50">
                    <h1>Title</h1>
                    <h2>{this.state.title}</h2>
                </div>
            </div>
            <div>
                <div className="grid-100">
                    <h1>Location</h1>
                    <h2>{this.state.geo}</h2>
                </div>
            </div>
        </div>;
    },
    componentDidMount: function () {
        var es = new EventSource('sse');
        es.addEventListener('message', function (event) {
            var message = JSON.parse(event.data);
            console.log(message);

            state[message.type] = message.payload;
            this.setState(getState());
        }.bind(this));
    }
});

module.exports = AppComponent;
