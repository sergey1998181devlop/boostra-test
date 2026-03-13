<?php

class ArticleView extends View
{
  public function fetch()
  {
    if (!in_array('settings', $this->manager->permissions)) {
      return $this->design->fetch('403.tpl');
    }

    if ($this->request->method('post')) {
      $articleId = $this->request->post('id');
      $title = $this->request->post('title');
      $description = $this->request->post('description');
      $keywords = $this->request->post('keywords');
      $slug = $this->request->post('slug');
      $content = $this->request->post('content');
      $publishAction = $this->request->post('publish');

      $errors = array();
      if (empty($title)) {
        $errors[] = 'empty_title';
      }
      if (empty($slug)) {
        $errors[] = 'empty_slug';
      }
      if (empty($content)) {
        $errors[] = 'empty_content';
      }

      $this->design->assign('errors', $errors);
      if (empty($errors)) {
        if (empty($articleId)) {
          $published = 0;
          if (!empty($publishAction)) {
            $published = 1;
          }

          $this->articles->new_article($slug, $title, $description, $keywords, $content, $this->manager->id, $published);
        } else {
          $article = $this->articles->get_article($articleId);
          $published = $article->published;
          if (!empty($publishAction)) {
            $published = 1;
          }

          $this->articles->update_article($articleId, $slug, $title, $description, $keywords, $content, $this->manager->id, $published);
        }

        $this->design->assign('message_success', 'saved');
      }
    }

    $id = $this->request->get('id', 'integer');
    if ($id) {
      $this->design->assign('article', $this->articles->get_article($id));
    }

    return $this->design->fetch('article.tpl');
  }
}
