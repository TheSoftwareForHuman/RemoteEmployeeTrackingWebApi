<?php

namespace App\Http\Controllers;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketController extends Controller implements MessageComponentInterface
{
     private $connections = [];

     private $adim_connection_id = null;

     
     /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */   
     function onOpen(ConnectionInterface $conn)
     {
        $this->connections[$conn->resourceId] = compact('conn') + ['login' => null];
     }

     /** 
     * This is called before or after a socket is closed (depends on how it's closed).
     * SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */  
    function onClose(ConnectionInterface $conn)
    {
        unset($this->connections[$conn->resourceId]);

        $onlineUsers = [];

        if ( isset($this->connections[$this->adim_connection_id]) )
        {
            foreach($this->connections as $resourceId => &$connection)
            {
               if($this->adim_connection_id != $resourceId)
               {
                   $onlineUsers[$resourceId] = $connection['login'];
               }
            }

            $this->connections[$this->adim_connection_id]['conn']->send(json_encode(['online_users' => $onlineUsers]));
        }
    }

     /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        $userId = $this->connections[$conn->resourceId]['login'];

        unset($this->connections[$conn->resourceId]);

        $conn->send("An error has occurred with user $userId:".$e->getMessage()."\n");

        $conn->close();
    }

     /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $conn The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $conn, $msg)
    {
        if (strcmp($msg, 'ping') == 0)
        {
            return;
        }

        if(is_null($this->connections[$conn->resourceId]['login']))
        {
            $this->connections[$conn->resourceId]['login'] = $msg;

            if (strcmp($msg, 'admin') == 0)
            {
                $this->adim_connection_id = $conn->resourceId;
            }

            $onlineUsers = [];

            if ( isset($this->connections[$this->adim_connection_id]) )
            {
                foreach($this->connections as $resourceId => &$connection)
                {
                    if( $this->adim_connection_id != $resourceId)
                    {
                       $onlineUsers[$resourceId] = $connection['login'];
                    }
                }

                $this->connections[$this->adim_connection_id]['conn']->send(json_encode(['online_users' => $onlineUsers]));
            }
        } 
        else
        {
            $fromUserId = $this->connections[$conn->resourceId]['login'];

            $msg = json_decode($msg, true);

            if ( isset($msg['to']) && isset($this->connections[$msg['to']]) )
            {
                $this->connections[$msg['to']]['conn']->send(json_encode([
                    'text' => $msg['text'],
                    'pic' =>  $msg['pic'],
                    'link' => $msg['link'],
                    'force_open' => $msg['force_open']
                ]));
            }
        }
    }
}