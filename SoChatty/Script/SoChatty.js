var socket;
var host  = 'ws://127.0.0.1:8889';
var chat  = document.getElementById('chat');
var text  = document.getElementById('message');
var numb  = document.getElementById('number');
var users = document.getElementById('users');
var user  = '';
var actv  = true;
var ttl   = 'So chatty';
var ttli  = 0;
var about = null;
var autoc = /@(\w+)$/;
var ftr   = document.getElementsByTagName('footer')[0];
var prtcl = {
    normal: /^m;(.*)$/,
    join:   /^a;(.*)$/,
    left:   /^d;(.*)$/,
    list:   /^l;(.*)$/
};

var send = function ( ) {

    var message = text.value;

    if(!message)
        return;

    text.value  = '';
    text.focus();

    try {

        socket.send(user + ': ' + message);
        echo(user + ': ' + message, 'me');
    }
    catch ( e ) {

        echo(e, 'bot');
    }
};

var receive = function ( message ) {

    var handle = null;

    if(null != (handle = prtcl.normal.exec(message.data))) {

        if(null != about.exec(handle[1]))
            from = 'aboutme';
        else
            from = 'other';

        echo(handle[1], from);

        return;
    }
    else if(null != (handle = prtcl.list.exec(message.data))) {

        ftr.setAttribute('aria-busy', 'true');
        var n = 1;
        handle[1].split(';').forEach(function ( u ) {

            if(user == u)
                return;

            var newUser = document.createElement('li');
            newUser.textContent = u;
            users.appendChild(newUser);
            ++n;
        });
        numb.textContent = n;
        ftr.setAttribute('aria-busy', 'false');

        return;
    }
    else if(null != (handle = prtcl.join.exec(message.data))) {

        if(user == handle[1])
            return;

        echo(handle[1] + ' has joined this awesome channel.', 'bot');
        ftr.setAttribute('aria-busy', 'true');
        numb.textContent++;
        var newUser         = document.createElement('li');
        newUser.textContent = handle[1];
        users.appendChild(newUser);
        ftr.setAttribute('aria-busy', 'false');

        return;
    }
    else if(null != (handle = prtcl.left.exec(message.data))) {

        echo(handle[1] + ' has left this awesome channel.', 'bot');
        ftr.setAttribute('aria-busy', 'true');
        numb.textContent--;

        for(var i = 0, m = users.childNodes.length; i < m; ++i)
            if(handle[1] == users.childNodes.item(i).textContent)
                break;

        users.removeChild(users.childNodes.item(i));
        ftr.setAttribute('aria-busy', 'false');

        return;
    }
};

var onkey = function ( e ) {

    if(13 == e.keyCode)
        send();

    if(null == (match = autoc.exec(text.value)))
        return;

    var r = new RegExp('^' + match[1]);
    var u = users.querySelectorAll('li');

    for(var i = 0, j = 0, k, m = u.length; i < m; ++i)
        if(null != r.exec(u.item(i).textContent)) {

            ++j;
            k = i;
        }

    if(1 != j)
        return;

    text.value += u.item(k).textContent.substring(match[1].length) + ' ';

    return;
};

var echo = function ( string, from ) {

    chat.setAttribute('aria-busy', 'true');

    var p            = document.createElement('p');
    var date         = document.createElement('span');
    var msg          = document.createElement('span');

    p.className      = from;
    date.className   = 'date';
    date.textContent = (new Date()).dToString();
    msg.textContent  = string;

    p.appendChild(date);
    p.appendChild(msg);
    chat.appendChild(p);

    chat.scrollTop   = chat.scrollHeight;

    if(false == actv)
        document.title = '(' + ++ttli + ' new message' +
                         (1 == ttli ? '' : 's') + ') ' + ttl;

    chat.setAttribute('aria-busy', 'false');
};

window.onfocus = function ( ) {

    actv           = true;
    document.title = ttl;
    ttli           = 0;
};

window.onblur = function ( ) {

    actv = false;
};

window.onbeforeunload = function ( ) {

    return false;
};

Date.prototype.dGetHours = function ( ) {

    var handle = this.getHours();

    return handle < 10 ? '0' + handle : '' + handle;
};

Date.prototype.dGetMinutes = function ( ) {

    var handle = this.getMinutes();

    return handle < 10 ? '0' + handle : '' + handle;
};

Date.prototype.dToString = function ( ) {

    return this.dGetHours() + ':' + this.dGetMinutes();
};

(function ( ) {

    while('' == (user = window.prompt('Username:')));

    if(null == user)
        return;

    about = new RegExp('@' + user);

    try {

        socket        = new WebSocket(host);
        socket.onopen = function ( message ) {

            echo('~ Connected! ~', 'bot');
            this.send(user);
        };
        socket.onmessage = receive;
        socket.onclose   = function ( message ) {

            echo('~ Disconnected ~', 'bot');
        };
    }
    catch ( e ) {

        echo(e, 'bot');
    }

    var newUser         = document.createElement('li');
    newUser.textContent = user;
    users.appendChild(newUser);

    text.focus();
})();
