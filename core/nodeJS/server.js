
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/


var io = require('socket.io').listen(8070);
var express = require('express');

var internalServer = express.createServer();
internalServer.listen(8334);
internalServer.get('/', function(req, res) {
    var message = {};
    message.datetime = new Date().getTime();
    message.type = req.param('type');
    message.key = req.param('key');
    message.title = req.param('title');
    message.text = req.param('text');
    message.category = req.param('category');
    message.userFromId = req.param('userFromId');
    message.userDestId = req.param('userDestId');
    message.message = req.param('message');
    message.options = req.param('options');
    handleMessage(message);
    addMessage(message);
    res.send('OK', 200);
});

io.configure('development', function(){
  io.set('transports', ['xhr-polling']);
  io.set('log level', 0);
});

var clients = [];
var messages = [];
io.sockets.on('connection', function(socket) {
    socket.on('authentification', function(key, user_id) {
        clients[socket.id] = [];
        clients[socket.id]['key'] = key;
        clients[socket.id]['user_id'] = user_id;
        var connectUserList = [];
        for (var i in clients) {
            connectUserList.push(clients[i].user_id);
        }
        io.sockets.socket(socket.id).emit('refreshUserList', connectUserList);
        addMessage(null);
        for (var i in messages) {
            handleMessage(messages[i]);
        }
    });

    socket.on('disconnect', function(key) {
        delete clients[socket.id];
        var number = 0
        var connectUserList = [];
        for (var i in clients) {
            connectUserList.push(clients[i].user_id);
        }
        for (var i in clients) {
            if (number == 0) {
                io.sockets.socket(i).emit('refreshUserList', connectUserList);
                number++;
            }
        }
    });
});

function addMessage(message) {
    var tmp_message = [];
    var now = new Date().getTime();
    for (var i in messages) {
        if (messages[i].datetime > (now - 5000)) {
            if (message == null || (messages[i].type != message.type || messages[i].options != message.options)) {
                tmp_message.push(messages[i]);
            }
        }
    }
    if (message != null) {
        if (message.type != 'refreshUserList' && message.type != 'notify' && message.type != 'newChatMessage') {
            tmp_message.push(message);
        }
    }
    messages = tmp_message;
}

function handleMessage(message) {
    for (var i in clients) {
        if (clients[i].key == message.key) {
            switch (message.type) {
                case 'notify' :
                    io.sockets.socket(i).emit('notify', message.title, message.text, message.category);
                    break;
                case 'newChatMessage' :
                    if (clients[i].user_id == message.userDestId || message.userDestId == 'broadcast') {
                        io.sockets.socket(i).emit('newChatMessage', message.userFromId, message.userDestId, message.message);
                    }
                    break;
                case 'refreshUserList' :
                    io.sockets.socket(i).emit('refreshUserList', null);
                    break;
                default :
                    io.sockets.socket(i).emit(message.type, message.options);
                    break;
            }
        } else {
            io.sockets.socket(i).emit('authentification_failed');
            io.sockets.socket(i).disconnect(true);
            delete clients[i];
        }
    }
}
