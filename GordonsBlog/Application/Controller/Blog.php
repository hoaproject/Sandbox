<?php

namespace {

from('Application')
-> import('Model.Article')
-> import('Model.Comment');

}

namespace Application\Controller {

class Blog extends \Hoa\Dispatcher\Kit {

    public function IndexAction ( ) {

        $article              = new \Application\Model\Article();
        $list                 = $article->getShortList();
        $this->data->title    = 'All articles';
        $this->data->articles = $list;

        $this->view->addOverlay('hoa://Application/View/Blog/Index.xyl');
        $this->view->render();

        return;
    }

    public function ArticleAction ( $id ) {

        $this->view->addOverlay('hoa://Application/View/Blog/Article.xyl');
        $this->view->interprete();

        $form = $this->view->getElement('comment_submit');

        if(   true === $form->hasBeenSent()
           && true === $form->isValid()) {

            $formData = $form->getData();
            $comment  = new \Application\Model\Comment();
            $comment->insert(array(
                'article'  => $id,
                'author'   => $formData['username'],
                'posted'   => time(),
                'content'  => $formData['comment']
            ));

            $this->view->getOutputStream()->sendHeader(
                'Location',
                $this->router->unroute('a', array('id' => $id)),
                true,
                302
            );
        }

        $article              = new \Application\Model\Article();
        $article->open(array('id' => $id));

        $this->data->title    = $article->title;
        $this->data->article  = $article;
        $this->data->comments = $article->comments;

        $this->view->render();

        return;
    }

    public function ErrorAction ( \Exception $exception ) {

        echo $exception->getFormattedMessage();
        exit;
    }
}

}
