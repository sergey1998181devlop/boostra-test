<?php

class BlogSeoView extends View
{
  public function fetch()
  {
    if (!in_array('settings', $this->manager->permissions)) {
      return $this->design->fetch('403.tpl');
    }

    if ($this->request->method('post')) {
      $action = $this->request->post('action', 'string');
      $id = $this->request->post('id', 'string');

      if ($action == 'delete-article' && !empty($id)) {
        $this->articles->delete_article($id);
      }
    }

    $articles = $this->articles->get_articles();
    foreach ($articles as $article) {
      $article->author_data = $this->managers->get_manager((int) $article->author);
    }

    $this->design->assign('articles', $articles);

    return $this->design->fetch('blog_seo.tpl');
  }
}
