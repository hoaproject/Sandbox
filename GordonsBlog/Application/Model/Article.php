<?php

namespace {

from('Hoa')
-> import('Model.~')
-> import('Database.Dal');

}

namespace Application\Model {

class Article extends \Hoa\Model {

    /**
     * @invariant id: boundinteger(0);
     */
    protected $_id;

    /**
     * @invariant title: string(boundinteger(1, 255));
     */
    protected $_title;

    /**
     * @invariant posted: boundinteger(
     *                        timestamp('1 january 1999'),
     *                        timestamp('now')
     *                    );
     */
    protected $_posted;

    /**
     * @invariant content: string(boundinteger(1));
     */
    protected $_content;

    /**
     * @invariant comments: relation('Application\Model\Comment', boundinteger(0));
     */
    protected $_comments;



    protected function construct ( ) {

        $this->setMappingLayer(\Hoa\Database\Dal::getLastInstance());

        return;
    }

    public function open ( Array $constraints = array() ) {

        $constraints = array_merge($this->getConstraints(), $constraints);

        if(!isset($constraints['id']))
            throw new \Hoa\Model\Exception('The constraint “id” is needed.', 0);

        $data = $this->getMappingLayer()
                    ->prepare(
                        'SELECT id, title, content ' .
                        'FROM   article ' .
                        'WHERE  id = :id'
                    )
                    ->execute($constraints)
                    ->fetchAll();
        $this->map($data[0]);
        $this->comments->map(
            $this->getMappingLayer()
                 ->prepare(
                     'SELECT id, posted, author, content ' .
                     'FROM   comment '.
                     'WHERE  article = :article'
                 )
                 ->execute(array('article' => $constraints['id']))
                 ->fetchAll()
        );

        return;
    }

    public function getShortList ( ) {

        return $this->getMappingLayer()->query(
            'SELECT id, title, posted FROM article ORDER BY id DESC'
        )->fetchAll();
    }
}

}
