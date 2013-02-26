<?php

require_once '/usr/local/lib/Hoa/Core/Core.php';

from('Hoa')
-> import('Websocket.Server')
-> import('Websocket.Node')
-> import('Socket.Server')
-> import('File.Write');

/**
 * Chat's node.
 */
class Node extends \Hoa\Websocket\Node {

    protected $_nick = null;

    public function setNick ( $nick ) {

        $old         = $this->_nick;
        $this->_nick = $nick;

        return $old;
    }

    public function getNick ( ) {

        return $this->_nick;
    }
}

/**
 * Start server.
 */
$ws = new \Hoa\Websocket\Server(
    new \Hoa\Socket\Server('tcp://127.0.0.1:8889')
);

/**
 * Set node.
 */
$ws->getServer()->setNodeName('Node');

/**
 * onmessage.
 */
$ws->on('message', function ( \Hoa\Core\Event\Bucket $bucket ) {

    $self    = $bucket->getSource();
    $data    = $bucket->getData();

    $message = $data['message'];
    $nodes   = $self->getServer()->getNodes();
    $cNode   = $self->getServer()->getCurrentNode();
    $id      = $cNode->getId();

    if(true === $cNode->isFirstMessage()) {

        $cNode->setNick($message);
        $message = 'a;' . $message;

        echo "\r",
             '+ ', $cNode->getNick(), ' has joined.', "\n",
             ($c = count($nodes)), ' geek', (1 < $c ? 's' : ''), '.';
    }
    else
        $message = 'm;' . $message;

    foreach($nodes as $node)
        if(true === $node->isFirstMessage()) {

            $self->send($message, $node);
            $self->send('l;' . implode(';', array_map(function ( $h ) {

                return $h->getNick();
            }, $nodes)), $node);
            $node->setFirst(false);
        }
        elseif($id != $node->getId())
            $self->send($message, $node);

    return;
});

/**
 * onclose.
 */
$ws->on('close', function ( \Hoa\Core\Event\Bucket $bucket ) {

    $self    = $bucket->getSource();
    $nodes   = $self->getServer()->getNodes();
    $node    = $self->getServer()->getCurrentNode();
    $id      = $node->getId();
    $message = 'd;' . $node->getNick();

    echo "\r",
         '- ', $node->getNick(), ' has left.', "\n",
         ($c = count($nodes) -1), ' geek', (1 < $c ? 's' : ''), '.';

    foreach($nodes as $node)
        if($id != $node->getId())
            $self->send($message, $node);

    return;
});

/**
 * onerror.
 */
$ws->on('error', function ( \Hoa\Core\Event\Bucket $bucket ) {

    $data = $bucket->getData();
    $file = new \Hoa\File\Write(__DIR__ . DS . 'Exceptions.log');
    $file->writeAll($data['exception']->raise() . "\n");

    return;
});

/**
 * Same with exceptions.
 */
event('hoa://Event/Exception')->attach(
    new \Hoa\File\Write('Exceptions.log')
);

echo 'Now, open file://', __DIR__, DS, 'index.html', "\n";

/**
 * And here we go :-).
 */
$ws->run();
